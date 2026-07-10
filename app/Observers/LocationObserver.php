<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\Location;

class LocationObserver
{
    public function created(Location $location): void
    {
        LogActivityJob::dispatch(
            'location',
            'created location',
            get_class($location),
            $location->id,
            auth()->id(),
            [],
            'created'
        );
    }

    public function updated(Location $location): void
    {
        LogActivityJob::dispatch(
            'location',
            'updated location',
            get_class($location),
            $location->id,
            auth()->id(),
            [],
            'updated'
        );
    }

    public function deleted(Location $location): void
    {
        LogActivityJob::dispatch(
            'location',
            'deleted location',
            get_class($location),
            $location->id,
            auth()->id(),
            [],
            'deleted'
        );
    }
}
