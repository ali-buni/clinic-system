<?php

namespace App\Services;

use App\Enums\WithdrawalStatus;
use App\Jobs\NotifyAdminsOfWithdrawalJob;
use App\Models\Doctor;
use App\Models\DoctorWallet;
use App\Models\DoctorWithdrawal;
use App\Notifications\WithdrawalCompleted;
use App\Notifications\WithdrawalRejected;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class DoctorWithdrawalService
{
    public function __construct(
        private readonly DoctorEarningsService $earningsService,
    ) {}

    public function requestWithdrawal(Doctor $doctor, float $amount): DoctorWithdrawal
    {
        if (DB::getDriverName() === 'mysql' && DB::transactionLevel() === 0) {
            DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        }

        return DB::transaction(function () use ($doctor, $amount) {
            if (! $doctor->stripe_connected_account_id) {
                throw new \RuntimeException('You must connect your Stripe account before requesting a withdrawal.');
            }

            try {
                $this->earningsService->initializeWallet($doctor);
            } catch (UniqueConstraintViolationException $e) {
                // A concurrent request created the wallet first; nothing to do.
            }

            $wallet = DoctorWallet::where('doctor_id', $doctor->id)->lockForUpdate()->firstOrFail();


            $wallet->update(['balance' => $this->earningsService->calculateAvailableBalance($doctor)]);

            if ($wallet->getAvailableBalance() < $amount) {
                throw new \RuntimeException('Insufficient balance. Available: $' . number_format($wallet->getAvailableBalance(), 2));
            }

            $wallet->deductFromBalance($amount);
            $wallet->addPending($amount);

            $withdrawal = $doctor->withdrawals()->create([
                'amount' => $amount,
                'stripe_connected_account_id' => $doctor->stripe_connected_account_id,
                'status' => WithdrawalStatus::Pending,
            ]);

            NotifyAdminsOfWithdrawalJob::dispatch($withdrawal->id);

            return $withdrawal;
        });
    }

    public function approveWithdrawal(DoctorWithdrawal $withdrawal, int $approvedBy): DoctorWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $approvedBy) {
            $withdrawal = DoctorWithdrawal::lockForUpdate()->findOrFail($withdrawal->id);

            if ($withdrawal->status !== WithdrawalStatus::Pending) {
                throw new \RuntimeException('Only pending withdrawals can be approved.');
            }

            $withdrawal->update([
                'status' => WithdrawalStatus::Completed,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'processed_at' => now(),
            ]);

            $wallet = DoctorWallet::where('doctor_id', $withdrawal->doctor_id)->lockForUpdate()->first();
            if ($wallet) {
                $wallet->removePending((float) $withdrawal->amount);
            }

            $withdrawal->doctor->user->notify(new WithdrawalCompleted($withdrawal));

            return $withdrawal;
        });
    }

    public function rejectWithdrawal(DoctorWithdrawal $withdrawal, int $approvedBy, string $reason): DoctorWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $approvedBy, $reason) {
            $withdrawal = DoctorWithdrawal::lockForUpdate()->findOrFail($withdrawal->id);

            if ($withdrawal->status !== WithdrawalStatus::Pending) {
                throw new \RuntimeException('Only pending withdrawals can be rejected.');
            }

            $wallet = DoctorWallet::where('doctor_id', $withdrawal->doctor_id)->lockForUpdate()->first();
            if ($wallet) {
                $wallet->removePending((float) $withdrawal->amount);
                $wallet->addToBalance((float) $withdrawal->amount);
            }

            $withdrawal->update([
                'status' => WithdrawalStatus::Rejected,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $withdrawal->doctor->user->notify(new WithdrawalRejected($withdrawal));

            return $withdrawal;
        });
    }

    public function getBalance(Doctor $doctor): array
    {
        $this->earningsService->syncWalletBalance($doctor);
        $wallet = $doctor->wallet;

        return [
            'balance' => $wallet ? $wallet->getAvailableBalance() : 0,
            'pending_withdrawal' => $wallet ? (float) $wallet->pending_withdrawal : 0,
            'total_earnings' => $this->earningsService->calculateDoctorShare($doctor),
        ];
    }
}
