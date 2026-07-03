<?php

namespace App\Notifications;

use App\Models\DoctorWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private DoctorWithdrawal $withdrawal) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Withdrawal Rejected')
            ->line('Your withdrawal request has been rejected.')
            ->line('Amount: $' . number_format($this->withdrawal->amount, 2))
            ->line('Reason: ' . ($this->withdrawal->rejection_reason ?? 'No reason provided'))
            ->line('The amount has been returned to your available balance.');
    }
}
