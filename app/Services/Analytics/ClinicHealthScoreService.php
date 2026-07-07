<?php

namespace App\Services\Analytics;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use Carbon\Carbon;

class ClinicHealthScoreService
{
    private const FULFILLMENT_WEIGHT = 0.6;

    private const UTILIZATION_WEIGHT = 0.4;

    public function __construct(
        private readonly PatientAnalyticsService $patientService,
        private readonly OperationalService $operationalService,
        private readonly SettingService $settings,
    ) {}

    public function calculateScore(int $clinicId, string $period = 'total', ?string $from = null, ?string $to = null): array
    {
        $period = in_array($period, ['year', 'month', 'day', 'total']) ? $period : 'total';
        [$fromDate, $toDate] = $this->resolveDateRange($period, $from, $to);

        $financialHealth = $this->getFinancialHealth($clinicId, $fromDate, $toDate);
        $operationalHealth = $this->getOperationalHealth($clinicId, $fromDate, $toDate);
        $patientHealth = $this->getPatientHealth($clinicId, $fromDate, $toDate);

        $weights = $this->normalizeWeights([
            'financial' => (float) $this->settings->get('weight_financial', 0.35),
            'operational' => (float) $this->settings->get('weight_operational', 0.30),
            'patient' => (float) $this->settings->get('weight_patient', 0.35),
        ]);

        $finalScore = ($financialHealth['score_numeric'] * $weights['financial'])
            + ($operationalHealth['score_numeric'] * $weights['operational'])
            + ($patientHealth['score_numeric'] * $weights['patient']);

        return [
            'from' => $fromDate?->toDateString(),
            'to' => $toDate->toDateString(),
            'overall_score' => round($finalScore, 2),
            'overall_status' => $this->getStatus($finalScore),
            'sub_scores' => [
                'financial' => $financialHealth,
                'operational' => $operationalHealth,
                'patient' => $patientHealth,
            ],
            'recommendations' => $this->generateRecommendations($financialHealth, $operationalHealth, $patientHealth),
        ];
    }

    private function getFinancialHealth(int $clinicId, ?Carbon $from, ?Carbon $to): array
    {
        $revenueTotal = Invoice::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->get()
            ->sum('total_cost');

        $outstandingTotal = Invoice::where('clinic_id', $clinicId)
            ->where('status', 'unpaid')
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->get()
            ->sum('total_cost');

        $targetRevenue = (float) $this->settings->get('target_revenue', 10000);

        $revenueScore = $targetRevenue > 0
            ? min(($revenueTotal / $targetRevenue) * 100, 100)
            : 0;

        $outstandingRatio = ($revenueTotal + $outstandingTotal) > 0
            ? round(($outstandingTotal / ($revenueTotal + $outstandingTotal)) * 100, 2)
            : 0;

        $adjustedScore = $revenueScore * (1 - ($outstandingRatio / 200));

        $scoreVal = round(max(0, $adjustedScore), 2);

        return [
            'score' => $scoreVal,
            'score_numeric' => $scoreVal,
            'status' => $this->getStatus($scoreVal),
            'total_revenue' => round($revenueTotal, 2),
            'outstanding' => round($outstandingTotal, 2),
            'outstanding_ratio' => $outstandingRatio.'%',
        ];
    }

    private function getOperationalHealth(int $clinicId, ?Carbon $from, ?Carbon $to): array
    {
        $totalDoctors = Doctor::where('clinic_id', $clinicId)->count();

        $totalNonCancelled = Appointment::where('clinic_id', $clinicId)
            ->whereIn('status', ['completed', 'no_show', 'scheduled', 'in_progress'])
            ->when($from, fn ($q) => $q->where('start_time', '>=', $from))
            ->when($to, fn ($q) => $q->where('start_time', '<=', $to))
            ->count();

        $completed = Appointment::where('clinic_id', $clinicId)
            ->where('status', 'completed')
            ->when($from, fn ($q) => $q->where('start_time', '>=', $from))
            ->when($to, fn ($q) => $q->where('start_time', '<=', $to))
            ->count();

        $noShowCount = Appointment::where('clinic_id', $clinicId)
            ->where('status', 'no_show')
            ->when($from, fn ($q) => $q->where('start_time', '>=', $from))
            ->when($to, fn ($q) => $q->where('start_time', '<=', $to))
            ->count();

        $fulfillmentRate = $totalNonCancelled > 0
            ? round(($completed / $totalNonCancelled) * 100, 2)
            : 100;

        $doctorUtilization = $this->averageUtilization($clinicId, $from, $to);

        $scoreVal = round(
            ($fulfillmentRate * self::FULFILLMENT_WEIGHT) + ($doctorUtilization * self::UTILIZATION_WEIGHT),
            2,
        );

        return [
            'score' => $scoreVal,
            'score_numeric' => $scoreVal,
            'status' => $this->getStatus($scoreVal),
            'active_doctors' => $totalDoctors,
            'completion_rate' => $fulfillmentRate.'%',
            'avg_utilization' => round($doctorUtilization, 2).'%',
            'no_show_count' => $noShowCount,
        ];
    }

    private function getPatientHealth(int $clinicId, ?Carbon $from, ?Carbon $to): array
    {
        $totalPatients = Appointment::where('clinic_id', $clinicId)
            ->whereNotNull('patient_id')
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->distinct('patient_id')
            ->count('patient_id');

        $retentionMetrics = $this->patientService->getRetentionMetrics($clinicId, $from ? 'total' : 'total');
        $retentionRate = $this->parsePercent($retentionMetrics['retention_rate'] ?? '0%');

        $lostPatients = $this->patientService->getLostPatients($clinicId, 6, 'total');
        $newCount = (int) (is_array($lostPatients) ? ($lostPatients['count_new'] ?? 0) : 0);
        $lostCount = (int) (is_array($lostPatients) ? ($lostPatients['count_lost'] ?? 0) : 0);

        $growthBalance = $newCount > 0 && $lostCount > 0
            ? ($newCount / ($newCount + $lostCount)) * 100
            : ($newCount > 0 ? 100 : ($lostCount > 0 ? 0 : 50));

        $scoreVal = round(($retentionRate * 0.6) + ($growthBalance * 0.4), 2);

        return [
            'score' => $scoreVal,
            'score_numeric' => $scoreVal,
            'status' => $this->getStatus($scoreVal),
            'total_patients' => $totalPatients,
            'retention_rate' => $retentionRate.'%',
            'lost_patients' => $lostCount,
            'new_patients' => $newCount,
            'growth_balance' => round($growthBalance, 2).'%',
        ];
    }

    private function resolveDateRange(string $period, ?string $from, ?string $to): array
    {
        $now = Carbon::now();

        $fromDate = $from
            ? Carbon::parse($from)
            : match ($period) {
                'year' => $now->copy()->subYear(),
                'month' => $now->copy()->subMonth(),
                'day' => $now->copy()->subDay(),
                default => null,
            };

        $toDate = $to ? Carbon::parse($to) : $now;

        return [$fromDate, $toDate];
    }

    private function normalizeWeights(array $weights): array
    {
        $total = array_sum($weights);
        if ($total <= 0) {
            return ['financial' => 0.34, 'operational' => 0.33, 'patient' => 0.33];
        }

        return array_map(fn ($w) => $w / $total, $weights);
    }

    private function averageUtilization(int $clinicId, ?Carbon $from, ?Carbon $to): float
    {
        $utilization = $this->operationalService->getDoctorUtilization($clinicId, 'total', $from?->toDateString(), $to?->toDateString());

        if ($utilization->isEmpty()) {
            return 0;
        }

        $rates = $utilization->flatMap(fn ($d) => collect($d['periods'] ?? [])
            ->pluck('utilization_rate')
            ->map(fn ($r) => (float) str_replace('%', '', $r)));

        return $rates->isNotEmpty() ? round($rates->avg(), 2) : 0;
    }

    private function parsePercent(string $value): float
    {
        return (float) str_replace('%', '', $value);
    }

    private function getStatus(float $score): string
    {
        $excellent = (float) $this->settings->get('threshold_excellent', 80);
        $good = (float) $this->settings->get('threshold_good', 60);
        $fair = (float) $this->settings->get('threshold_fair', 40);

        if ($score >= $excellent) {
            return 'Excellent';
        }
        if ($score >= $good) {
            return 'good';
        }
        if ($score >= $fair) {
            return 'fair';
        }

        return 'need enhance';
    }

    private function generateRecommendations(array $financial, array $operational, array $patient): array
    {
        $templates = [
            'financial' => [
                ['max' => 30, 'priority' => 'critical', 'message' => 'الأداء المالي حرج. راجع هيكل التسعير والمصاريف فوراً.'],
                ['max' => 50, 'priority' => 'high',     'message' => 'الأداء المالي منخفض. راجع التسعير وزِد جهود التحصيل.'],
                ['max' => 70, 'priority' => 'medium',   'message' => 'الأداء المالي مقبول. ابحث عن فرص لزيادة الإيرادات.'],
            ],
            'operational' => [
                ['max' => 30, 'priority' => 'critical', 'message' => 'الكفاءة التشغيلية حرجة. تدخل فوري لتحسين سير العمل.'],
                ['max' => 50, 'priority' => 'high',     'message' => 'الكفاءة التشغيلية منخفضة. راجع جداول الأطباء وقلل المواعيد الضائعة.'],
                ['max' => 70, 'priority' => 'medium',   'message' => 'الكفاءة التشغيلية مقبولة. حسّن توزيع المواعيد.'],
            ],
            'patient' => [
                ['max' => 30, 'priority' => 'critical', 'message' => 'نسبة الاحتفاظ بالمرضى حرجة. المرضى لا يعودون للعيادة.'],
                ['max' => 50, 'priority' => 'high',     'message' => 'نسبة الاحتفاظ بالمرضى منخفضة. أطلق حملات تواصل.'],
                ['max' => 70, 'priority' => 'medium',   'message' => 'نسبة الاحتفاظ مقبولة. عزز برنامج ولاء المرضى.'],
            ],
        ];

        $subScores = compact('financial', 'operational', 'patient');
        $recommendations = [];

        foreach ($templates as $area => $thresholds) {
            $scoreVal = $subScores[$area]['score_numeric'];
            foreach ($thresholds as $t) {
                if ($scoreVal < $t['max']) {
                    $recommendations[] = [
                        'area' => $area,
                        'priority' => $t['priority'],
                        'message' => $t['message'],
                    ];
                    break;
                }
            }
        }

        if ($financial['score_numeric'] >= 80 && $operational['score_numeric'] >= 80 && $patient['score_numeric'] >= 80) {
            $recommendations[] = [
                'area' => 'overall',
                'priority' => 'low',
                'message' => 'the clinic is performing excellently across all areas. maintain current strategies and continue monitoring.',
            ];
        }

        $priorityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
        usort($recommendations, fn ($a, $b) => ($priorityOrder[$a['priority']] ?? 99) - ($priorityOrder[$b['priority']] ?? 99));

        return $recommendations;
    }
}
