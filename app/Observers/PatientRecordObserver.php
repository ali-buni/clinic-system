<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\Patient_record;

class PatientRecordObserver
{
    public function created(Patient_record $record): void
    {
        LogActivityJob::dispatch(
            'patient_record',
            'created patient record',
            get_class($record),
            $record->id,
            auth()->id(),
            [],
            'created'
        );
    }

    public function updated(Patient_record $record): void
    {
        LogActivityJob::dispatch(
            'patient_record',
            'updated patient record',
            get_class($record),
            $record->id,
            auth()->id(),
            [],
            'updated'
        );
    }

    public function deleted(Patient_record $record): void
    {
        LogActivityJob::dispatch(
            'patient_record',
            'deleted patient record',
            get_class($record),
            $record->id,
            auth()->id(),
            [],
            'deleted'
        );
    }

    public function restored(Patient_record $record): void
    {
        LogActivityJob::dispatch(
            'patient_record',
            'restored patient record',
            get_class($record),
            $record->id,
            auth()->id(),
            [],
            'restored'
        );
    }
}
