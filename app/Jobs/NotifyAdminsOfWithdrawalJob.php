<?php

namespace App\Jobs;

use App\Models\DoctorWithdrawal;
use App\Models\User;
use App\Notifications\WithdrawalRequestSubmitted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyAdminsOfWithdrawalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly int $withdrawalId,
    ) {}

    public function handle(): void
    {
        $withdrawal = DoctorWithdrawal::with('doctor')->findOrFail($this->withdrawalId);
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new WithdrawalRequestSubmitted($withdrawal));
        }

        Log::channel('structured')->info('Withdrawal notification sent to admins', [
            'withdrawal_id' => $this->withdrawalId,
            'admins_notified' => $admins->count(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('structured')->error('NotifyAdminsOfWithdrawalJob failed', [
            'withdrawal_id' => $this->withdrawalId,
            'error' => $exception->getMessage(),
        ]);
    }
}
