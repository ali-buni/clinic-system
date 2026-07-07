<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Services\ActivityLogService;

class AppointmentObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(Appointment $appointment): void
    {
        $this->activityLog->log(
            'appointment',
            'created appointment',
            $appointment,
            auth()->user(),
            [],
            'created'
        );
    }

    public function updated(Appointment $appointment): void
    {
        $this->activityLog->log(
            'appointment',
            'updated appointment',
            $appointment,
            auth()->user(),
            [],
            'updated'
        );
    }

    public function deleted(Appointment $appointment): void
    {
        $this->activityLog->log(
            'appointment',
            'deleted appointment',
            $appointment,
            auth()->user(),
            [],
            'deleted'
        );
    }

    public function restored(Appointment $appointment): void
    {
        $this->activityLog->log(
            'appointment',
            'restored appointment',
            $appointment,
            auth()->user(),
            [],
            'restored'
        );
    }
}
