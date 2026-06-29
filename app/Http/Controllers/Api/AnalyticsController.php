<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClinicAnalyticsSnapshot;
use App\Services\Analytics\{OperationalService, FinancialService, PatientAnalyticsService, MedicalAnalyticsService, PredictiveService, NLAService, ClinicHealthScoreService};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    private function resolveClinicId(Request $request): int
    {
        $user = $request->user();

        $clinicId = $user->clinicOwner?->id
            ?? $user->doctorProfile?->clinic_id
            ?? $user->secretaryProfile?->clinic_id;

        abort_unless($clinicId, 403, 'Unauthorized: no clinic access');

        return (int) $clinicId;
    }

    private function respond(string $status, mixed $data = null, array $extra = [], int $httpCode = 200): JsonResponse
    {
        return response()->json(
            array_filter(array_merge(
                ['status' => $status],
                $data !== null ? ['data' => $data] : [],
                $extra,
            ), fn($v) => $v !== null),
            $httpCode,
        );
    }

    private function resolvePeriod(array $validated): string
    {
        return $validated['period'] ?? 'total';
    }

    private function resolveDateRange(array $validated): array
    {
        return [
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        ];
    }

    private function periodRule(): array
    {
        return ['period' => 'sometimes|in:year,month,day,total'];
    }

    private function dateRangeRules(): array
    {
        return [
            'from' => 'sometimes|date_format:Y-m-d',
            'to'   => 'sometimes|date_format:Y-m-d',
        ];
    }

    public function getOperationalReport(Request $request, OperationalService $ops): JsonResponse
    {
        $clinicId = $this->resolveClinicId($request);
        $validated = $request->validate(array_merge($this->periodRule(), $this->dateRangeRules()));
        $period = $this->resolvePeriod($validated);
        [$from, $to] = $this->resolveDateRange($validated);

        return $this->respond('success', null, [
            'period'             => $period,
            'from'               => $from,
            'to'                 => $to,
            'appointments'       => $ops->getAppointmentsByPeriod($clinicId, $period, $from, $to),
            'completion'         => $ops->getCompletionByPeriod($clinicId, $period, $from, $to),
            'no_show'            => $ops->getNoShowByPeriod($clinicId, $period, $from, $to),
            'doctor_utilization' => $ops->getDoctorUtilization($clinicId, $period, $from, $to),
        ]);
    }

    public function getFinancialReport(Request $request, FinancialService $fin): JsonResponse
    {
        $clinicId = $this->resolveClinicId($request);
        $validated = $request->validate(array_merge($this->periodRule(), $this->dateRangeRules()));

        $period = $this->resolvePeriod($validated);
        [$from, $to] = $this->resolveDateRange($validated);

        return $this->respond('success', null, [
            'period'              => $period,
            'from'                => $from,
            'to'                  => $to,
            'by_period'           => $fin->getRevenueByPeriod($clinicId, $period, $from, $to),
            'by_doctor'           => $fin->getRevenueByDoctor($clinicId, $period, $from, $to),
            'outstanding_balance' => $fin->getOutstandingBalance($clinicId, $period, $from, $to),
        ]);
    }

    public function getPatientReport(Request $request, PatientAnalyticsService $pat): JsonResponse
    {
        $clinicId = $this->resolveClinicId($request);
        $validated = $request->validate($this->periodRule());
        $period = $this->resolvePeriod($validated);

        return $this->respond('success', null, [
            'period'        => $period,
            'retention'     => $pat->getRetentionMetrics($clinicId, $period),
            'lost_patients' => $pat->getLostPatients($clinicId, 6, $period),
        ]);
    }

    public function getMedicalReport(Request $request, MedicalAnalyticsService $med): JsonResponse
    {
        $clinicId = $this->resolveClinicId($request);
        return $this->respond('success', null, [
            'top_diseases' => $med->getTopDiseases($clinicId),
            'by_age'       => $med->getDiseasesByAgeGroup($clinicId),
        ]);
    }

    public function getPredictiveReport(Request $request, PredictiveService $pre): JsonResponse
    {
        $clinicId = $this->resolveClinicId($request);
        $validated = $request->validate(array_merge($this->periodRule(), $this->dateRangeRules()));
        $period = $this->resolvePeriod($validated);
        [$from, $to] = $this->resolveDateRange($validated);

        return $this->respond('success', null, [
            'period'                => $period,
            'from'                  => $from,
            'to'                    => $to,
            'crowding'              => $pre->getCrowdingRisk($clinicId, $from, $to),
            'no_show_prediction'    => $pre->getNoShowPrediction($clinicId, $from, $to),
            'busy_hours'            => $pre->getBusyHours($clinicId, $period, $from, $to),
            'utilization_forecast'  => $pre->getUtilizationForecast($clinicId, $period, $from, $to),
            'ai_insight'            => $pre->getAiInsight($clinicId, $period, $from, $to),
        ]);
    }

    public function askAnalytics(Request $request, NLAService $nla, OperationalService $ops, FinancialService $fin, MedicalAnalyticsService $med): JsonResponse
    {
        $validated = $request->validate(['question' => 'required|string|max:500']);
        $clinicId = $this->resolveClinicId($request);

        $contextData = [
            'operations' => $ops->getDoctorUtilization($clinicId, 'day', today()->toDateString(), today()->toDateString()),
            'financials' => $fin->getRevenueByDoctor($clinicId, 'total'),
            'medical'    => $med->getTopDiseases($clinicId),
        ];

        $contextString = json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return response()->json([
            'answer' => $nla->askAnalytics($validated['question'], $contextString),
        ]);
    }

    public function getHealthScore(Request $request, ClinicHealthScoreService $scoreService): JsonResponse
    {
        $clinicId = $this->resolveClinicId($request);
        $validated = $request->validate(array_merge($this->periodRule(), $this->dateRangeRules()));
        $period = $this->resolvePeriod($validated);
        [$from, $to] = $this->resolveDateRange($validated);

        return $this->respond('success', $scoreService->calculateScore($clinicId, $period, $from, $to));
    }

    public function getDashboard(Request $request, OperationalService $ops, FinancialService $fin, PatientAnalyticsService $pat, MedicalAnalyticsService $med, PredictiveService $pre, ClinicHealthScoreService $scoreService): JsonResponse
    {
        $clinicId = $this->resolveClinicId($request);
        $validated = $request->validate(array_merge($this->periodRule(), $this->dateRangeRules()));
        $period = $this->resolvePeriod($validated);
        [$from, $to] = $this->resolveDateRange($validated);

        return $this->respond('success', null, [
            'period'       => $period,
            'from'         => $from,
            'to'           => $to,
            'operational'  => [
                'today_utilization' => $ops->getDoctorUtilization($clinicId, 'day', today()->toDateString(), today()->toDateString()),
                'appointments'      => $ops->getAppointmentsByPeriod($clinicId, $period, $from, $to),
                'completion'        => $ops->getCompletionByPeriod($clinicId, $period, $from, $to),
                'no_show'           => $ops->getNoShowByPeriod($clinicId, $period, $from, $to),
                'doctor_utilization_by_period' => $ops->getDoctorUtilization($clinicId, $period, $from, $to),
            ],
            'financial'    => [
                'by_period'           => $fin->getRevenueByPeriod($clinicId, $period, $from, $to),
                'by_doctor'           => $fin->getRevenueByDoctor($clinicId, $period, $from, $to),
                'outstanding_balance' => $fin->getOutstandingBalance($clinicId, $period, $from, $to),
            ],
            'patients'     => [
                'retention'     => $pat->getRetentionMetrics($clinicId, $period),
                'lost_patients' => $pat->getLostPatients($clinicId, 6, $period),
            ],
            'medical'      => [
                'top_diseases' => $med->getTopDiseases($clinicId),
                'by_age'       => $med->getDiseasesByAgeGroup($clinicId),
            ],
            'predictive'   => [
                'crowding'             => $pre->getCrowdingRisk($clinicId, $from, $to),
                'no_show_prediction'   => $pre->getNoShowPrediction($clinicId, $from, $to),
                'busy_hours'           => $pre->getBusyHours($clinicId, $period, $from, $to),
                'utilization_forecast' => $pre->getUtilizationForecast($clinicId, $period, $from, $to),
            ],
            'health_score' => $scoreService->calculateScore($clinicId, $period, $from, $to),
        ]);
    }
}
