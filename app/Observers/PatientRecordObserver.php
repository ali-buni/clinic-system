<?php

namespace App\Observers;

use App\Models\Patient_record;
use App\Services\ActivityLogService;

class PatientRecordObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(Patient_record $record): void
    {
        $this->activityLog->log(
            'patient_record',
            'created patient record',
            $record,
            auth()->user(),
            [],
            'created'
        );
    }

    public function updated(Patient_record $record): void
    {
        $this->activityLog->log(
            'patient_record',
            'updated patient record',
            $record,
            auth()->user(),
            [],
            'updated'
        );
    }

    public function deleted(Patient_record $record): void
    {
        $this->activityLog->log(
            'patient_record',
            'deleted patient record',
            $record,
            auth()->user(),
            [],
            'deleted'
        );
    }

    public function restored(Patient_record $record): void
    {
        $this->activityLog->log(
            'patient_record',
            'restored patient record',
            $record,
            auth()->user(),
            [],
            'restored'
        );
    }
}
