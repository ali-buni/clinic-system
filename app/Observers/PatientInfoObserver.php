<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\PatientInfo;

class PatientInfoObserver
{
    public function created(PatientInfo $patientInfo): void
    {
        LogActivityJob::dispatch(
            'patient_info',
            'created patient info',
            get_class($patientInfo),
            $patientInfo->id,
            auth()->id(),
            [],
            'created'
        );
    }

    public function updated(PatientInfo $patientInfo): void
    {
        LogActivityJob::dispatch(
            'patient_info',
            'updated patient info',
            get_class($patientInfo),
            $patientInfo->id,
            auth()->id(),
            [],
            'updated'
        );
    }

    public function deleted(PatientInfo $patientInfo): void
    {
        LogActivityJob::dispatch(
            'patient_info',
            'deleted patient info',
            get_class($patientInfo),
            $patientInfo->id,
            auth()->id(),
            [],
            'deleted'
        );
    }

    public function restored(PatientInfo $patientInfo): void
    {
        LogActivityJob::dispatch(
            'patient_info',
            'restored patient info',
            get_class($patientInfo),
            $patientInfo->id,
            auth()->id(),
            [],
            'restored'
        );
    }
}
