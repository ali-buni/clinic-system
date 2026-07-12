<?php

namespace App\Console\Commands;

use App\Jobs\SendAppointmentStatusNotificationJob;
use App\Models\Appointment;
use App\Models\Invoice;
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

        $unpaidUpcoming = Appointment::where('start_time', '<=', now()->addHours(12))
            ->where('start_time', '>', now())
            ->where('status', 'scheduled')
            ->whereHas('invoices', fn($q) => $q->byDescription('Booking fee')
                ->where('status', '!=', 'paid'))
            ->get();

        foreach ($unpaidUpcoming as $appointment) {
            SendAppointmentStatusNotificationJob::dispatch(
                $appointment->id,
                'payment_reminder',
                'patient'
            );
            $count++;
        }

        $pastUnpaid = Appointment::where('status', 'scheduled')
            ->where('start_time', '<=', now()->addHours(2))
            ->where('start_time', '>=', now())
            ->whereHas('invoices', fn($q) => $q->byDescription('Booking fee')
                ->where('status', '!=', 'paid'))
            ->get();

        foreach ($pastUnpaid as $appointment) {
            $appointment->update(['status' => 'cancelled']);

            $bookingInvoice = Invoice::where('appointment_id', $appointment->id)
                ->byDescription('Booking fee')
                ->first();

            if ($bookingInvoice) {
                $bookingInvoice->update(['status' => 'void', 'cancel_reason' => 'No-paid booking fee']);
                SendAppointmentStatusNotificationJob::dispatch(
                    $appointment->id,
                    'cancelled',
                    'patient'
                );
                SendAppointmentStatusNotificationJob::dispatch(
                    $appointment->id,
                    'cancelled',
                    'doctor'
                );
            }
            $count++;
        }

        $this->info("Dispatched {$count} reminders and processed {$pastUnpaid->count()} no-shows.");

        return self::SUCCESS;
    }
}
