<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\Appointment;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        LogActivityJob::dispatch(
            'appointment',
            'created appointment',
            get_class($appointment),
            $appointment->id,
            auth()->id(),
            [],
            'created'
        );
    }

    public function updated(Appointment $appointment): void
    {
        LogActivityJob::dispatch(
            'appointment',
            'updated appointment',
            get_class($appointment),
            $appointment->id,
            auth()->id(),
            [],
            'updated'
        );
    }

    public function deleted(Appointment $appointment): void
    {
        LogActivityJob::dispatch(
            'appointment',
            'deleted appointment',
            get_class($appointment),
            $appointment->id,
            auth()->id(),
            [],
            'deleted'
        );
    }

    public function restored(Appointment $appointment): void
    {
        LogActivityJob::dispatch(
            'appointment',
            'restored appointment',
            get_class($appointment),
            $appointment->id,
            auth()->id(),
            [],
            'restored'
        );
    }
}
