<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\DoctorWithdrawal;

class DoctorWithdrawalObserver
{
    public function created(DoctorWithdrawal $withdrawal): void
    {
        LogActivityJob::dispatch(
            'doctor_withdrawal',
            'created withdrawal request',
            get_class($withdrawal),
            $withdrawal->id,
            auth()->id(),
            [
                'doctor_id' => $withdrawal->doctor_id,
                'amount' => $withdrawal->amount,
                'status' => $withdrawal->status->value,
            ],
            'created'
        );
    }

    public function updated(DoctorWithdrawal $withdrawal): void
    {
        $changes = $withdrawal->getChanges();
        unset($changes['updated_at']);

        $details = ['changed_fields' => array_keys($changes)];

        if (isset($changes['status'])) {
            $details['status_transition'] = [
                'from' => $withdrawal->getOriginal('status'),
                'to' => $changes['status'],
            ];
        }

        if (isset($changes['approved_by'])) {
            $details['approved_by'] = $changes['approved_by'];
        }

        if (isset($changes['rejection_reason'])) {
            $details['rejection_reason'] = $changes['rejection_reason'];
        }

        LogActivityJob::dispatch(
            'doctor_withdrawal',
            'updated withdrawal',
            get_class($withdrawal),
            $withdrawal->id,
            auth()->id(),
            $details,
            'updated'
        );
    }
}
