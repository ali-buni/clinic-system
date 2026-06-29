<?php

namespace App\Observers;

use App\Models\Doctor;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

class DoctorObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(Doctor $doctor): void
    {
        $this->activityLog->log(
            'doctor',
            'created doctor',
            $doctor,
            auth()->user(),
            [],
            'created'
        );
        Log::channel('structured')->info('doctor created', ['doctor_id' => $doctor->id]);
    }

    public function updated(Doctor $doctor): void
    {
        $this->activityLog->log(
            'doctor',
            'updated doctor',
            $doctor,
            auth()->user(),
            [],
            'updated'
        );
        Log::channel('structured')->info('doctor updated', ['doctor_id' => $doctor->id]);
    }

    public function deleted(Doctor $doctor): void
    {
        $this->activityLog->log(
            'doctor',
            'deleted doctor',
            $doctor,
            auth()->user(),
            [],
            'deleted'
        );
        Log::channel('structured')->info('doctor deleted', ['doctor_id' => $doctor->id]);
    }

    public function restored(Doctor $doctor): void
    {
        $this->activityLog->log(
            'doctor',
            'restored doctor',
            $doctor,
            auth()->user(),
            [],
            'restored'
        );
        Log::channel('structured')->info('doctor restored', ['doctor_id' => $doctor->id]);
    }
}
