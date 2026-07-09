<?php

namespace App\Console\Commands;

use App\Jobs\TakeClinicSnapshotJob;
use App\Models\Clinic;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TakeClinicSnapshots extends Command
{
    protected $signature = 'app:take-clinic-snapshots
        {--clinic= : Single clinic ID for targeted run}
        {--date= : Snapshot date (Y-m-d), defaults to today}';

    protected $description = 'Capture a daily analytics snapshot for all clinics';

    public function handle(): int
    {
        $query = Clinic::query();
        if ($clinicId = $this->option('clinic')) {
            $query->where('id', (int) $clinicId);
        }

        $date = $this->option('date') ? Carbon::parse($this->option('date'))->toDateString() : Carbon::yesterday()->startOfDay()->toDateString();
        $clinics = $query->cursor();
        $count = 0;

        foreach ($clinics as $clinic) {
            TakeClinicSnapshotJob::dispatch($clinic->id, $date);
            $count++;
        }

        $this->info("Dispatched {$count} snapshot jobs.");

        return self::SUCCESS;
    }
}
