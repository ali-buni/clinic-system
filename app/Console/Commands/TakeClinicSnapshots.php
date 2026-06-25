<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Models\ClinicAnalyticsSnapshot;
use App\Services\Analytics\OperationalService;

class TakeClinicSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:take-clinic-snapshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take daily snapshots for all clinics';

    /**
     * Execute the console command.
     */
    public function handle(OperationalService $ops)
    {
        $clinics = Clinic::all();

        foreach ($clinics as $clinic) {
            $data = $ops->getDoctorUtilization($clinic->id, date('Y-m-d'));

            ClinicAnalyticsSnapshot::create([
                'clinic_id' => $clinic->id,
                'metric_name' => 'doctor_utilization',
                'data' => $data,
                'snapshot_date' => now()
            ]);
        }

        $this->info('Snapshots created for all clinics!');
    }
}
