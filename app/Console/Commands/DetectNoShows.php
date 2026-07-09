<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;

class DetectNoShows extends Command
{
    protected $signature = 'app:detect-no-shows
        {--grace-minutes=30 : Minutes after appointment end to mark as no-show}';

    protected $description = 'Auto-mark past appointments as no-show';

    public function handle(): int
    {
        $graceMinutes = (int) $this->option('grace-minutes');
        $cutoff = now()->subMinutes($graceMinutes);

    $updated = Appointment::whereIn('status', ['scheduled', 'confirmed'])
            ->where('end_time', '<', $cutoff)
            ->update(['status' => 'no_show']);

        $this->info("Marked {$updated} appointments as no-show.");

        return self::SUCCESS;
    }
}
