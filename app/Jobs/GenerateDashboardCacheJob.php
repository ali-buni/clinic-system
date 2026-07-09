<?php

namespace App\Jobs;

use App\Services\Analytics\{
    OperationalService,
    FinancialService,
    PatientAnalyticsService,
    MedicalAnalyticsService,
    PredictiveService,
    ClinicHealthScoreService
};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateDashboardCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public readonly int $clinicId,
        public readonly string $period = 'total',
        public readonly ?string $from = null,
        public readonly ?string $to = null,
    ) {}

    public function handle(
        OperationalService $ops,
        FinancialService $fin,
        PatientAnalyticsService $pat,
        MedicalAnalyticsService $med,
        PredictiveService $pre,
        ClinicHealthScoreService $scoreService,
    ): void {
        $cacheKey = "dashboard:clinic:{$this->clinicId}:{$this->period}:from:{$this->from}:to:{$this->to}";
        if (Cache::has($cacheKey)) {
            Log::channel('structured')->info('Dashboard cache already exists', [
                'clinic_id' => $this->clinicId,
                'period' => $this->period,
            ]);
            return;
        }
        $data = [
            'operational' => [
                'today_utilization' => $ops->getDoctorUtilization($this->clinicId, 'day', $this->from, $this->to),
                'appointments' => $ops->getAppointmentsByPeriod($this->clinicId, $this->period, $this->from, $this->to),
                'completion' => $ops->getCompletionByPeriod($this->clinicId, $this->period, $this->from, $this->to),
                'no_show' => $ops->getNoShowByPeriod($this->clinicId, $this->period, $this->from, $this->to),
                'doctor_utilization_by_period' => $ops->getDoctorUtilization($this->clinicId, $this->period, $this->from, $this->to),
            ],
            'financial' => [
                'by_period' => $fin->getRevenueByPeriod($this->clinicId, $this->period, $this->from, $this->to),
                'by_doctor' => $fin->getRevenueByDoctor($this->clinicId, $this->period, $this->from, $this->to),
                'outstanding_balance' => $fin->getOutstandingBalance($this->clinicId, $this->period, $this->from, $this->to),
            ],
            'patients' => [
                'retention' => $pat->getRetentionMetrics($this->clinicId, $this->period),
                'lost_patients' => $pat->getLostPatients($this->clinicId, 6, $this->period),
            ],
            'medical' => [
                'top_diseases' => $med->getTopDiseases($this->clinicId),
                'by_age' => $med->getDiseasesByAgeGroup($this->clinicId),
            ],
            'predictive' => [
                'crowding' => $pre->getCrowdingRisk($this->clinicId, $this->from, $this->to),
                'no_show_prediction' => $pre->getNoShowPrediction($this->clinicId, $this->from, $this->to),
                'busy_hours' => $pre->getBusyHours($this->clinicId, $this->period, $this->from, $this->to),
                'utilization_forecast' => $pre->getUtilizationForecast($this->clinicId, $this->period, $this->from, $this->to),
            ],
            'health_score' => $scoreService->calculateScore($this->clinicId, $this->period, $this->from, $this->to),
        ];

        Cache::put($cacheKey, $data, now()->addHour());

        Log::channel('structured')->info('Dashboard cache generated', [
            'clinic_id' => $this->clinicId,
            'period' => $this->period,
        ]);
    }
}
