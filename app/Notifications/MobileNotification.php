<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class MobileNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $body;
    protected array $data;

    public function __construct(string $title, string $body, array $data = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $fcmNotification = FcmNotification::create()
            ->title($this->title)
            ->body($this->body);

        return FcmMessage::create()
            ->setNotification($fcmNotification)
            ->setData(array_map('strval', $this->data));
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
