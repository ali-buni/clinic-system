<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckFailedJobs extends Command
{
    protected $signature = 'app:check-failed-jobs
        {--threshold=10 : Alert if failed jobs exceed this count}';

    protected $description = 'Monitor failed queue jobs and alert if threshold exceeded';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $failedCount = DB::table('failed_jobs')->count();

        if ($failedCount > $threshold) {
            Log::channel('structured')->warning('Failed jobs threshold exceeded', [
                'failed_count' => $failedCount,
                'threshold' => $threshold,
            ]);
            $this->warn("ALERT: {$failedCount} failed jobs exceed threshold of {$threshold}.");
        } else {
            $this->info("Failed jobs: {$failedCount} (within threshold of {$threshold}).");
        }

        return self::SUCCESS;
    }
}
