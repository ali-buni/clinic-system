<?php

namespace App\Services;

use App\Enums\WithdrawalStatus;
use App\Models\Doctor;
use App\Models\DoctorWithdrawal;
use App\Notifications\WithdrawalCompleted;
use App\Notifications\WithdrawalRejected;
use App\Notifications\WithdrawalRequestSubmitted;
use Illuminate\Support\Facades\DB;

class DoctorWithdrawalService
{
    public function __construct(
        private readonly DoctorEarningsService $earningsService,
        private readonly StripeConnectService $stripeConnectService,
    ) {}

    public function requestWithdrawal(Doctor $doctor, float $amount): DoctorWithdrawal
    {
        return DB::transaction(function () use ($doctor, $amount) {
            if (!$doctor->stripe_connected_account_id) {
                throw new \RuntimeException('You must connect your Stripe account before requesting a withdrawal.');
            }

            $this->earningsService->syncWalletBalance($doctor);
            $wallet = $doctor->wallet;

            if (!$wallet || $wallet->getAvailableBalance() < $amount) {
                throw new \RuntimeException('Insufficient balance. Available: $' . number_format($wallet?->getAvailableBalance() ?? 0, 2));
            }

            $wallet->deductFromBalance($amount);
            $wallet->addPending($amount);

            $withdrawal = $doctor->withdrawals()->create([
                'amount' => $amount,
                'stripe_connected_account_id' => $doctor->stripe_connected_account_id,
                'status' => WithdrawalStatus::Pending,
            ]);

            $this->notifyAdmins($withdrawal);

            return $withdrawal;
        });
    }

    public function approveWithdrawal(DoctorWithdrawal $withdrawal, int $approvedBy): DoctorWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $approvedBy) {
            if ($withdrawal->status !== WithdrawalStatus::Pending) {
                throw new \RuntimeException('Only pending withdrawals can be approved.');
            }

            $transferId = $this->stripeConnectService->createTransfer(
                $withdrawal->doctor,
                (float) $withdrawal->amount
            );

            $withdrawal->update([
                'status' => WithdrawalStatus::Completed,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'stripe_transfer_id' => $transferId,
                'processed_at' => now(),
            ]);

            $wallet = $withdrawal->doctor->wallet;
            if ($wallet) {
                $wallet->removePending((float) $withdrawal->amount);
            }

            $withdrawal->doctor->notify(new WithdrawalCompleted($withdrawal));

            return $withdrawal;
        });
    }

    public function rejectWithdrawal(DoctorWithdrawal $withdrawal, int $approvedBy, string $reason): DoctorWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $approvedBy, $reason) {
            if ($withdrawal->status !== WithdrawalStatus::Pending) {
                throw new \RuntimeException('Only pending withdrawals can be rejected.');
            }

            $withdrawal->update([
                'status' => WithdrawalStatus::Rejected,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $wallet = $withdrawal->doctor->wallet;
            if ($wallet) {
                $wallet->removePending((float) $withdrawal->amount);
                $wallet->addToBalance((float) $withdrawal->amount);
            }

            $withdrawal->doctor->notify(new WithdrawalRejected($withdrawal));

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

    private function notifyAdmins(DoctorWithdrawal $withdrawal): void
    {
        $admins = \App\Models\User::role('admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new WithdrawalRequestSubmitted($withdrawal));
        }
    }
}
