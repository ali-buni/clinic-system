<?php

namespace App\Services\Analytics;

use App\Services\Analytics\FinancialService;
use App\Services\Analytics\PatientAnalyticsService;
use App\Services\Analytics\SettingService;

class ClinicHealthScoreService
{
    protected $financialService;
    protected $patientService;

    public function __construct(FinancialService $financialService, PatientAnalyticsService $patientService)
    {
        $this->financialService = $financialService;
        $this->patientService = $patientService;
    }

    public function calculateScore(int $clinicId)
    {
        $revenue = $this->financialService->getOutstandingBalance($clinicId);
        $retentionData = $this->patientService->getRetentionMetrics($clinicId);
        $retentionRate = (float)str_replace('%', '', $retentionData['retention_rate']);

        $targetRevenue = SettingService::get('target_revenue', 10000);
        $wRevenue = SettingService::get('weight_revenue', 0.4);
        $wRetention = SettingService::get('weight_retention', 0.6);

        $revenueScore = min(($revenue / $targetRevenue) * 100, 100);
        $finalScore = ($revenueScore * $wRevenue) + ($retentionRate * $wRetention);
        return [
            'score' => round($finalScore, 2),
            'status' => $this->getStatus($finalScore),
            'recommendation' => $this->getRecommendation($finalScore, $retentionRate)
        ];
    }

    private function getStatus($score)
    {
        if ($score >= 80) return 'ممتاز';
        if ($score >= 50) return 'جيد';
        return 'يحتاج تحسين';
    }

    private function getRecommendation($score, $retention)
    {
        if ($score < 50) return 'أداء العيادة منخفض، يرجى مراجعة التقارير المالية.';
        if ($retention < 30) return 'العيادة تعمل بشكل جيد، لكن ركز على زيادة نسبة الاحتفاظ بالمرضى.';
        return 'العيادة في وضع ممتاز، استمر في مراقبة الجودة.';
    }
}
