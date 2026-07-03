<?php

namespace App\Notifications;

use App\Models\DoctorWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalApproved extends Notification implements ShouldQueue
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
            ->subject('Withdrawal Approved')
            ->line('Your withdrawal request has been approved.')
            ->line('Amount: $' . number_format($this->withdrawal->amount, 2))
            ->line('The transfer to your Stripe account is being processed.')
            ->line('You will receive another notification once the transfer is complete.');
    }
}
