<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\Doctor;

class DoctorObserver
{
    public function created(Doctor $doctor): void
    {
        LogActivityJob::dispatch(
            'doctor',
            'created doctor',
            get_class($doctor),
            $doctor->id,
            auth()->id(),
            [],
            'created'
        );
    }

    public function updated(Doctor $doctor): void
    {
        LogActivityJob::dispatch(
            'doctor',
            'updated doctor',
            get_class($doctor),
            $doctor->id,
            auth()->id(),
            [],
            'updated'
        );
    }

    public function deleted(Doctor $doctor): void
    {
        LogActivityJob::dispatch(
            'doctor',
            'deleted doctor',
            get_class($doctor),
            $doctor->id,
            auth()->id(),
            [],
            'deleted'
        );
    }

    public function restored(Doctor $doctor): void
    {
        LogActivityJob::dispatch(
            'doctor',
            'restored doctor',
            get_class($doctor),
            $doctor->id,
            auth()->id(),
            [],
            'restored'
        );
    }
}
