<?php

namespace App\Notifications;

use App\Models\DoctorWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private DoctorWithdrawal $withdrawal) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $doctor = $this->withdrawal->doctor;
        $doctorName = $doctor->user->fname . ' ' . $doctor->user->lname;

        return (new MailMessage)
            ->subject('New Withdrawal Request')
            ->line("Dr. {$doctorName} has requested a withdrawal.")
            ->line('Amount: $' . number_format($this->withdrawal->amount, 2))
            ->line('Status: Pending Approval')
            ->action('View Withdrawal', url("/admin/withdrawals/{$this->withdrawal->id}"))
            ->line('Please review and approve or reject this request.');
    }
}
