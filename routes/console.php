<?php

use Illuminate\Support\Facades\Schedule;

// Schedule::command('app:take-clinic-snapshots')->daily();

Schedule::command('app:send-appointment-reminders')->hourly();

Schedule::command('app:cleanup-expired-verifications')->daily();

Schedule::command('app:process-pending-refunds')->everyFifteenMinutes();

Schedule::command('app:recalculate-doctor-wallets')->daily();

Schedule::command('app:detect-no-shows')->dailyAt('23:00');

Schedule::command('app:check-failed-jobs')->daily();
