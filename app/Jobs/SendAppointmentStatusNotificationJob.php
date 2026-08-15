<?php

namespace App\Jobs;

use App\Events\SendMsgEvent;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\MobileNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAppointmentStatusNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly int $appointmentId,
        public readonly string $action,
        public readonly string $recipientType,
        public readonly ?string $scheduleDate = null,
        public readonly ?string $scheduleTime = null,
        public readonly ?string $previousScheduleDate = null,
        public readonly ?string $previousScheduleTime = null,
    ) {}

    public function handle(): void
    {
        $appointment = Appointment::with(['doctor.user', 'patient.user', 'clinic.owner'])->findOrFail($this->appointmentId);

        $message = $this->buildMessage($appointment);
        $recipients = $this->resolveRecipients($appointment);

        foreach ($recipients as $recipient) {
            if (! $recipient || ! $recipient->getKey()) {
                continue;
            }

            $recipient->notify(new MobileNotification(
                $this->resolveTitle(),
                $message,
                [
                    'appointment_id' => $this->appointmentId,
                    'type' => $this->action,
                    'message' => $message,
                ]
            ));

            if ($recipient->phone) {
                event(new SendMsgEvent($recipient->phone, $message));
            }
        }

        Log::channel('structured')->info('Appointment notification dispatched', [
            'appointment_id' => $this->appointmentId,
            'action' => $this->action,
            'recipient_type' => $this->recipientType,
            'recipients_count' => count($recipients),
            'schedule_date' => $this->scheduleDate,
            'schedule_time' => $this->scheduleTime,
            'previous_schedule_date' => $this->previousScheduleDate,
            'previous_schedule_time' => $this->previousScheduleTime,
        ]);
    }

    public function buildMessage(object $appointment): string
    {
        $scheduleLabel = $this->resolveScheduleLabel($appointment);
        $previousScheduleLabel = $this->resolvePreviousScheduleLabel();

        return match ($this->action) {
            'booked' => $scheduleLabel
                ? "A new appointment has been booked for {$scheduleLabel}."
                : 'A new appointment has been booked.',
            'updated' => $previousScheduleLabel && $scheduleLabel
                ? "Your appointment was updated from {$previousScheduleLabel} to {$scheduleLabel}."
                : ($scheduleLabel ? "Your appointment was updated. New schedule: {$scheduleLabel}." : 'Your appointment was updated.'),
            'cancelled' => "Your appointment has been cancelled. Reason: {$appointment->cancel_reason}".($scheduleLabel ? " Scheduled time: {$scheduleLabel}." : ''),
            'completed' => $scheduleLabel
                ? "Your appointment has been completed for {$scheduleLabel}."
                : 'Your appointment has been completed.',
            'confirmed' => $scheduleLabel
                ? "Your appointment has been confirmed for {$scheduleLabel}."
                : 'Your appointment has been confirmed.',
            'reminder' => $scheduleLabel
                ? "Reminder: You have an upcoming appointment on {$scheduleLabel}."
                : 'Reminder: You have an upcoming appointment.',
            'payment_reminder' => $scheduleLabel
                ? "Payment reminder: You have an unpaid invoice for your appointment on {$scheduleLabel}. Please complete payment before the appointment."
                : 'Payment reminder: You have an unpaid invoice for your upcoming appointment. Please complete payment.',
            default => 'Appointment status update: '.$this->action.($scheduleLabel ? " for {$scheduleLabel}." : '.'),
        };
    }

    private function resolveTitle(): string
    {
        return match ($this->action) {
            'booked' => 'Appointment Booked',
            'updated' => 'Appointment Updated',
            'cancelled' => 'Appointment Cancelled',
            'completed' => 'Appointment Completed',
            'confirmed' => 'Appointment Confirmed',
            'reminder' => 'Appointment Reminder',
            'payment_reminder' => 'Payment Reminder',
            default => 'Appointment Update',
        };
    }

    private function resolveScheduleLabel(object $appointment): string
    {
        $date = $this->scheduleDate ?? ($appointment->start_time ? Carbon::parse($appointment->start_time)->toDateString() : null);
        $time = $this->scheduleTime ?? ($appointment->start_time ? Carbon::parse($appointment->start_time)->format('H:i') : null);

        if ($date && $time) {
            return "{$date} {$time}";
        }

        return $date ?? $time ?? '';
    }

    private function resolvePreviousScheduleLabel(): string
    {
        $date = $this->previousScheduleDate;
        $time = $this->previousScheduleTime;

        if ($date && $time) {
            return "{$date} {$time}";
        }

        return $date ?? $time ?? '';
    }

    private function resolveRecipients(Appointment $appointment): array
    {
        return match ($this->recipientType) {
            'doctor' => [$appointment->doctor?->user],
            'patient' => [$appointment->patient?->user],
            'doctor_and_secretary' => array_values(array_filter(array_merge(
                [$appointment->doctor?->user],
                $appointment->doctor?->room?->secretaries
                    ?->map(fn ($secretary) => $secretary->user)
                    ->filter()
                    ->all() ?? [],
            ))),
            'secretary' => $appointment->clinic ? User::where('clinic_id', $appointment->clinic_id)
                ->where('role', 'secretary')
                ->get()
                ->all() : [],
            'all' => array_filter([
                $appointment->doctor?->user,
                $appointment->patient?->user,
            ]),
            default => [],
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('structured')->error('SendAppointmentStatusNotificationJob failed', [
            'appointment_id' => $this->appointmentId,
            'action' => $this->action,
            'error' => $exception->getMessage(),
        ]);
    }
}
