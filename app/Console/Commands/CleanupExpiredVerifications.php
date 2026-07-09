<?php

namespace App\Console\Commands;

use App\Models\Verification_code;
use Illuminate\Console\Command;

class CleanupExpiredVerifications extends Command
{
    protected $signature = 'app:cleanup-expired-verifications
        {--older-than=24 : Delete codes older than N hours}';

    protected $description = 'Remove expired verification codes';

    public function handle(): int
    {
        $hours = (int) $this->option('older-than');
        $deleted = Verification_code::where('created_at', '<', now()->subHours($hours))->delete();

        $this->info("Deleted {$deleted} expired verification codes.");

        return self::SUCCESS;
    }
}
