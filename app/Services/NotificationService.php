<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\MobileNotification;

class NotificationService
{
    public function sendToUser(int $receiverId, string $title, string $body, array $data = []): bool
    {
        $receiver = User::with('fcmTokens')->find($receiverId);

        if (!$receiver) {
            return false;
        }

        if ($receiver->fcmTokens->isEmpty()) {
            return false;
        }

        $receiver->notify(new MobileNotification($title, $body, $data));
        return true;
    }

    public function sendToMultipleUsers(array $receiverIds, string $title, string $body, array $data = []): void
    {
        $users = User::with('fcmTokens')->whereIn('id', $receiverIds)->get();

        foreach ($users as $user) {
            if ($user->fcmTokens->isNotEmpty()) {
                $user->notify(new MobileNotification($title, $body, $data));
            }
        }
    }
}
