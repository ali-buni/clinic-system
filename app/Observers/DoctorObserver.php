<?php

namespace App\Observers;

use App\Models\Doctor;
use App\Services\ActivityLogService;

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
    }
}
