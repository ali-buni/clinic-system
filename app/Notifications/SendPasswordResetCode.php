<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPasswordResetCode extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password Reset Code')
            ->line('Your password reset code is: ' . $this->code)
            ->line('This code will expire in 15 minutes.')
            ->line('If you did not request a password reset, please ignore this email.');
    }
}
