<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\DoctorWallet;

class DoctorWalletObserver
{
    public function created(DoctorWallet $wallet): void
    {
        LogActivityJob::dispatch(
            'doctor_wallet',
            'created doctor wallet',
            get_class($wallet),
            $wallet->id,
            auth()->id(),
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

        LogActivityJob::dispatch(
            'doctor_wallet',
            'updated doctor wallet',
            get_class($wallet),
            $wallet->id,
            auth()->id(),
            $details,
            'updated'
        );
    }
}
