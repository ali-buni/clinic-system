<?php

namespace App\Notifications;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendStripeAccountLink extends Notification
{
    use Queueable;

    public function __construct(private string $url, private Doctor $doctor) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = trim(($this->doctor->user->fname ?? '') . ' ' . ($this->doctor->user->lname ?? '')) ?: 'Doctor';
        return (new MailMessage)
            ->subject('Stripe Account Link')
            ->line("Dear $name,")
            ->line('You need to create a Stripe account to receive payments.')
            ->line(' Please click the button below to create your account.')
            ->action('Make Account', url($this->url));
    }
}
