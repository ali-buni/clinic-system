<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

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
        Log::channel('structured')->info('appointment created', ['appointment_id' => $appointment->id]);
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
        Log::channel('structured')->info('appointment updated', ['appointment_id' => $appointment->id]);
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
        Log::channel('structured')->info('appointment deleted', ['appointment_id' => $appointment->id]);
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
        Log::channel('structured')->info('appointment restored', ['appointment_id' => $appointment->id]);
    }
}
