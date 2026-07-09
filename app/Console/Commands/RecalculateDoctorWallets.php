<?php

namespace App\Console\Commands;

use App\Jobs\SyncDoctorWalletJob;
use App\Models\Doctor;
use Illuminate\Console\Command;

class RecalculateDoctorWallets extends Command
{
    protected $signature = 'app:recalculate-doctor-wallets
        {--clinic= : Target specific clinic ID}';

    protected $description = 'Recalculate all doctor wallet balances for consistency';

    public function handle(): int
    {
        $query = Doctor::query();

        if ($clinicId = $this->option('clinic')) {
            $query->where('clinic_id', (int) $clinicId);
        }

        $doctors = $query->cursor();
        $count = 0;

        foreach ($doctors as $doctor) {
            SyncDoctorWalletJob::dispatch($doctor->id);
            $count++;
        }

        $this->info("Dispatched {$count} wallet sync jobs.");

        return self::SUCCESS;
    }
}
