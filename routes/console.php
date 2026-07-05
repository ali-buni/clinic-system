<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// InfinityFree: Runs every 15 minutes (cron minimum)
// Original: daily() — still works fine with 15-minute cron
Schedule::command('app:take-clinic-snapshots')->daily();
