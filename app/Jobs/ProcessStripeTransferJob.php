<?php

namespace App\Jobs;

use App\Enums\WithdrawalStatus;
use App\Models\DoctorWithdrawal;
use App\Notifications\WithdrawalCompleted;
use App\Services\StripeConnectService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStripeTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        public readonly int $withdrawalId,
        public readonly int $approvedBy,
    ) {
        $this->afterCommit = true;
    }

    public function handle(StripeConnectService $stripeConnectService): void
    {
        $withdrawal = DoctorWithdrawal::with('doctor')->findOrFail($this->withdrawalId);

        if ($withdrawal->status !== WithdrawalStatus::Processing) {
            Log::channel('structured')->info('Withdrawal already processed, skipping job', [
                'withdrawal_id' => $withdrawal->id,
                'status' => $withdrawal->status,
            ]);

            return;
        }

        $transferId = $stripeConnectService->createTransfer(
            $withdrawal->doctor,
            (float) $withdrawal->amount
        );

        $withdrawal->update([
            'status' => WithdrawalStatus::Completed,
            'approved_by' => $this->approvedBy,
            'approved_at' => now(),
            'stripe_transfer_id' => $transferId,
            'processed_at' => now(),
        ]);

        $wallet = $withdrawal->doctor->wallet;
        if ($wallet) {
            $wallet->removePending((float) $withdrawal->amount);
        }

        $withdrawal->doctor->user->notify(new WithdrawalCompleted($withdrawal));

        Log::channel('structured')->info('Stripe transfer completed', [
            'withdrawal_id' => $withdrawal->id,
            'doctor_id' => $withdrawal->doctor_id,
            'amount' => $withdrawal->amount,
            'transfer_id' => $transferId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('structured')->error('ProcessStripeTransferJob failed', [
            'withdrawal_id' => $this->withdrawalId,
            'error' => $exception->getMessage(),
        ]);
    }
}
