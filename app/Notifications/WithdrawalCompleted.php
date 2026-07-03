<?php

namespace App\Notifications;

use App\Models\DoctorWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalCompleted extends Notification implements ShouldQueue
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
            ->subject('Withdrawal Completed')
            ->line('Your withdrawal has been completed successfully.')
            ->line('Amount: $' . number_format($this->withdrawal->amount, 2))
            ->line('Transfer ID: ' . $this->withdrawal->stripe_transfer_id)
            ->line('The funds have been transferred to your Stripe account.');
    }
}
