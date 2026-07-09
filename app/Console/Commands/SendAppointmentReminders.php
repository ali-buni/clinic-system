<?php

namespace App\Console\Commands;

use App\Jobs\SendAppointmentStatusNotificationJob;
use App\Models\Appointment;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'app:send-appointment-reminders
        {--hours=24 : Look ahead N hours}';

    protected $description = 'Send reminders for upcoming appointments';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $appointments = Appointment::where('start_time', '<=', now()->addHours($hours))
            ->where('start_time', '>=', now())
            ->where('status', 'scheduled')
            ->get();

        $count = 0;
        foreach ($appointments as $appointment) {
            SendAppointmentStatusNotificationJob::dispatch(
                $appointment->id,
                'reminder',
                'patient',
            );
            $count++;
        }

        $this->info("Dispatched {$count} appointment reminders.");

        return self::SUCCESS;
    }
}
