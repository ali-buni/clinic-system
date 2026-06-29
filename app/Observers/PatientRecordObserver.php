<?php

namespace App\Observers;

use App\Models\Patient_record;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

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
        Log::channel('structured')->info('patient record created', ['patient_record_id' => $record->id]);
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
        Log::channel('structured')->info('patient record updated', ['patient_record_id' => $record->id]);
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
        Log::channel('structured')->info('patient record deleted', ['patient_record_id' => $record->id]);
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
        Log::channel('structured')->info('patient record restored', ['patient_record_id' => $record->id]);
    }
}
