<?php

namespace App\Observers;

use App\Models\DoctorWithdrawal;
use App\Services\ActivityLogService;

class DoctorWithdrawalObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(DoctorWithdrawal $withdrawal): void
    {
        $this->activityLog->log(
            'doctor_withdrawal',
            'created withdrawal request',
            $withdrawal,
            auth()->user(),
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

        $this->activityLog->log(
            'doctor_withdrawal',
            'updated withdrawal',
            $withdrawal,
            auth()->user(),
            $details,
            'updated'
        );
    }
}
