<?php

namespace App\Observers;

use App\Models\PatientInfo;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

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
        Log::channel('structured')->info('patient info created', ['patient_info_id' => $patientInfo->id]);
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
        Log::channel('structured')->info('patient info updated', ['patient_info_id' => $patientInfo->id]);
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
        Log::channel('structured')->info('patient info deleted', ['patient_info_id' => $patientInfo->id]);
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
        Log::channel('structured')->info('patient info restored', ['patient_info_id' => $patientInfo->id]);
    }
}
