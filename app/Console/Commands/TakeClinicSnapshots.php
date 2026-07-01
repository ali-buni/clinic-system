<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\ClinicAnalyticsSnapshot;
use App\Services\Analytics\OperationalService;
use App\Services\Analytics\FinancialService;
use App\Services\Analytics\PatientAnalyticsService;
use App\Services\Analytics\MedicalAnalyticsService;
use App\Services\Analytics\PredictiveService;
use App\Services\Analytics\ClinicHealthScoreService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TakeClinicSnapshots extends Command
{
    protected $signature = 'app:take-clinic-snapshots
        {--clinic= : Single clinic ID for targeted run}
        {--date= : Snapshot date (Y-m-d), defaults to today}';

    protected $description = 'Capture a daily analytics snapshot for all clinics';

    public function handle(
        OperationalService $ops,
        FinancialService $fin,
        PatientAnalyticsService $pat,
        MedicalAnalyticsService $med,
        PredictiveService $pre,
        ClinicHealthScoreService $score,
    ): int {
        $query = Clinic::query();
        if ($clinicId = $this->option('clinic')) {
            $query->where('id', (int) $clinicId);
        }

        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::yesterday()->startOfDay();
        $clinics = $query->cursor();
        $totalClinics = 0;
        $totalMetrics = 0;
        $failCount = 0;

        foreach ($clinics as $clinic) {
            $totalClinics++;
            $this->line("Clinic #{$clinic->id} {$clinic->name} ...");

            $snapshots = $this->buildSnapshotSet($clinic->id, $date, $ops, $fin, $pat, $med, $pre, $score);
            $clinicSaved = 0;

            foreach ($snapshots as $snapshot) {
                try {
                    ClinicAnalyticsSnapshot::updateOrCreate(
                        [
                            'clinic_id'     => $clinic->id,
                            'metric_name'   => $snapshot['metric_name'],
                            'snapshot_date' => $date,
                        ],
                        ['data' => $snapshot['data']],
                    );
                    $clinicSaved++;
                } catch (\Throwable $e) {
                    $failCount++;
                    Log::error("Snapshot metric '{$snapshot['metric_name']}' failed for clinic #{$clinic->id}", [
                        'clinic_id' => $clinic->id,
                        'metric'    => $snapshot['metric_name'],
                        'error'     => $e->getMessage(),
                    ]);
                }
            }

            $totalMetrics += $clinicSaved;
            $this->info("  ✓ {$clinicSaved} metrics saved");
        }

        $this->newLine();
        $this->info("Done — {$totalClinics} clinics, {$totalMetrics} metrics stored.");
        if ($failCount > 0) {
            $this->warn("{$failCount} metrics failed (check logs).");
        }

        return $failCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function buildSnapshotSet(
        int $clinicId,
        Carbon $date,
        OperationalService $ops,
        FinancialService $fin,
        PatientAnalyticsService $pat,
        MedicalAnalyticsService $med,
        PredictiveService $pre,
        ClinicHealthScoreService $score,
    ): array {
        $dateString = $date->toDateString();

        return [
            [
                'metric_name' => 'doctor_utilization',
                'data'        => $ops->getDoctorUtilization($clinicId, 'day', $dateString, $dateString),
            ],
            [
                'metric_name' => 'appointment_stats',
                'data'        => $ops->getAppointmentsByPeriod($clinicId, 'total'),
            ],
            [
                'metric_name' => 'completion_rate',
                'data'        => $ops->getCompletionByPeriod($clinicId, 'total'),
            ],
            [
                'metric_name' => 'no_show_rate',
                'data'        => $ops->getNoShowByPeriod($clinicId, 'total'),
            ],
            [
                'metric_name' => 'revenue_summary',
                'data'        => $fin->getRevenueByPeriod($clinicId, 'total'),
            ],
            [
                'metric_name' => 'revenue_by_doctor',
                'data'        => $fin->getRevenueByDoctor($clinicId, 'total'),
            ],
            [
                'metric_name' => 'outstanding_balance',
                'data'        => $fin->getOutstandingBalance($clinicId, 'total'),
            ],
            [
                'metric_name' => 'retention_summary',
                'data'        => $pat->getRetentionMetrics($clinicId, 'total'),
            ],
            [
                'metric_name' => 'lost_patients',
                'data'        => $pat->getLostPatients($clinicId, 6, 'total'),
            ],
            [
                'metric_name' => 'top_diseases',
                'data'        => $med->getTopDiseases($clinicId),
            ],
            [
                'metric_name' => 'predictive_crowding',
                'data'        => $pre->getCrowdingRisk($clinicId),
            ],
            [
                'metric_name' => 'predictive_no_show',
                'data'        => $pre->getNoShowPrediction($clinicId),
            ],
            [
                'metric_name' => 'predictive_busy_hours',
                'data'        => $pre->getBusyHours($clinicId, 'total'),
            ],
            [
                'metric_name' => 'predictive_utilization_forecast',
                'data'        => $pre->getUtilizationForecast($clinicId, 'total'),
            ],
            [
                'metric_name' => 'utilization_by_period',
                'data'        => $ops->getDoctorUtilization($clinicId, 'total'),
            ],
            [
                'metric_name' => 'health_score',
                'data'        => $score->calculateScore($clinicId, 'total'),
            ],
        ];
    }
}
