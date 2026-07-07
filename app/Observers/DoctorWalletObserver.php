<?php

namespace App\Observers;

use App\Models\DoctorWallet;
use App\Services\ActivityLogService;

class DoctorWalletObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(DoctorWallet $wallet): void
    {
        $this->activityLog->log(
            'doctor_wallet',
            'created doctor wallet',
            $wallet,
            auth()->user(),
            ['doctor_id' => $wallet->doctor_id, 'balance' => $wallet->balance],
            'created'
        );
    }

    public function updated(DoctorWallet $wallet): void
    {
        $changes = $wallet->getChanges();
        unset($changes['updated_at']);

        $details = ['changed_fields' => array_keys($changes)];

        if (isset($changes['balance'])) {
            $oldBalance = $wallet->getOriginal('balance');
            $newBalance = $changes['balance'];
            $details['balance_change'] = [
                'from' => $oldBalance,
                'to' => $newBalance,
                'delta' => $newBalance - $oldBalance,
            ];
        }

        if (isset($changes['pending_withdrawal'])) {
            $oldPending = $wallet->getOriginal('pending_withdrawal');
            $newPending = $changes['pending_withdrawal'];
            $details['pending_withdrawal_change'] = [
                'from' => $oldPending,
                'to' => $newPending,
            ];
        }

        $this->activityLog->log(
            'doctor_wallet',
            'updated doctor wallet',
            $wallet,
            auth()->user(),
            $details,
            'updated'
        );
    }
}
