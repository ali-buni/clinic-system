<?php

namespace App\Observers;

use App\Models\PatientInfo;
use App\Services\ActivityLogService;

class PatientInfoObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(PatientInfo $patientInfo): void
    {
        $this->activityLog->log(
            'patient_info',
            'created patient info',
            $patientInfo,
            auth()->user(),
            [],
            'created'
        );
    }

    public function updated(PatientInfo $patientInfo): void
    {
        $this->activityLog->log(
            'patient_info',
            'updated patient info',
            $patientInfo,
            auth()->user(),
            [],
            'updated'
        );
    }

    public function deleted(PatientInfo $patientInfo): void
    {
        $this->activityLog->log(
            'patient_info',
            'deleted patient info',
            $patientInfo,
            auth()->user(),
            [],
            'deleted'
        );
    }

    public function restored(PatientInfo $patientInfo): void
    {
        $this->activityLog->log(
            'patient_info',
            'restored patient info',
            $patientInfo,
            auth()->user(),
            [],
            'restored'
        );
    }
}
