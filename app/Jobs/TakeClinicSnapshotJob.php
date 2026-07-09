<?php

namespace App\Jobs;

use App\Models\ClinicAnalyticsSnapshot;
use App\Services\Analytics\{
    OperationalService,
    FinancialService,
    PatientAnalyticsService,
    MedicalAnalyticsService,
    PredictiveService,
    ClinicHealthScoreService
};
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TakeClinicSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int $clinicId,
        public readonly string $date,
    ) {}

    public function handle(
        OperationalService $ops,
        FinancialService $fin,
        PatientAnalyticsService $pat,
        MedicalAnalyticsService $med,
        PredictiveService $pre,
        ClinicHealthScoreService $score,
    ): void {
        $date = Carbon::parse($this->date);
        $dateString = $date->toDateString();

        $snapshots = [
            ['metric_name' => 'doctor_utilization', 'data' => $ops->getDoctorUtilization($this->clinicId, 'day', $dateString, $dateString)],
            ['metric_name' => 'appointment_stats', 'data' => $ops->getAppointmentsByPeriod($this->clinicId, 'total')],
            ['metric_name' => 'completion_rate', 'data' => $ops->getCompletionByPeriod($this->clinicId, 'total')],
            ['metric_name' => 'no_show_rate', 'data' => $ops->getNoShowByPeriod($this->clinicId, 'total')],
            ['metric_name' => 'revenue_summary', 'data' => $fin->getRevenueByPeriod($this->clinicId, 'total')],
            ['metric_name' => 'revenue_by_doctor', 'data' => $fin->getRevenueByDoctor($this->clinicId, 'total')],
            ['metric_name' => 'outstanding_balance', 'data' => $fin->getOutstandingBalance($this->clinicId, 'total')],
            ['metric_name' => 'retention_summary', 'data' => $pat->getRetentionMetrics($this->clinicId, 'total')],
            ['metric_name' => 'lost_patients', 'data' => $pat->getLostPatients($this->clinicId, 6, 'total')],
            ['metric_name' => 'top_diseases', 'data' => $med->getTopDiseases($this->clinicId)],
            ['metric_name' => 'predictive_crowding', 'data' => $pre->getCrowdingRisk($this->clinicId)],
            ['metric_name' => 'predictive_no_show', 'data' => $pre->getNoShowPrediction($this->clinicId)],
            ['metric_name' => 'predictive_busy_hours', 'data' => $pre->getBusyHours($this->clinicId, 'total')],
            ['metric_name' => 'predictive_utilization_forecast', 'data' => $pre->getUtilizationForecast($this->clinicId, 'total')],
            ['metric_name' => 'utilization_by_period', 'data' => $ops->getDoctorUtilization($this->clinicId, 'total')],
            ['metric_name' => 'health_score', 'data' => $score->calculateScore($this->clinicId, 'total')],
        ];

        $saved = 0;
        foreach ($snapshots as $snapshot) {
            try {
                ClinicAnalyticsSnapshot::updateOrCreate(
                    [
                        'clinic_id'     => $this->clinicId,
                        'metric_name'   => $snapshot['metric_name'],
                        'snapshot_date' => $date,
                    ],
                    ['data' => $snapshot['data']],
                );
                $saved++;
            } catch (\Throwable $e) {
                Log::error("Snapshot metric '{$snapshot['metric_name']}' failed for clinic #{$this->clinicId}", [
                    'clinic_id' => $this->clinicId,
                    'metric'    => $snapshot['metric_name'],
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        Log::channel('structured')->info('Clinic snapshot completed', [
            'clinic_id' => $this->clinicId,
            'date' => $this->date,
            'metrics_saved' => $saved,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('structured')->error('TakeClinicSnapshotJob failed', [
            'clinic_id' => $this->clinicId,
            'date' => $this->date,
            'error' => $exception->getMessage(),
        ]);
    }
}
