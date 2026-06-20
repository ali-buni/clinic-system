<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmailVerificationCode extends Notification
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
            ->subject('Email Verification Code')
            ->line('Your email verification code is: ' . $this->code)
            ->line('This code will expire in 15 minutes.')
            ->line('If you did not request this, please ignore this email.');
    }
}
