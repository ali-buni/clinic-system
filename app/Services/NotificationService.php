<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\MobileNotification;
use Illuminate\Http\Request;

class NotificationService
{
    public function sendToUser(int $receiverId, string $title, string $body, array $data = []): bool
    {
        $receiver = User::with('fcmTokens')->find($receiverId);

        if (! $receiver) {
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

    public function listNotifications(User $user, Request $request): array
    {
        $query = $user->notifications();

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(min(max((int) $request->query('per_page', 15), 1), 50));

        $items = collect($notifications->items())->map(function ($notification) {
            $data = is_array($notification->data) ? $notification->data : [];

            return [
                'id' => $notification->id,
                'type' => $data['type'] ?? class_basename($notification->type),
                'title' => $data['title'] ?? null,
                'body' => $data['body'] ?? null,
                'data' => $data['data'] ?? [],
                'read' => $notification->read_at !== null,
                'read_at' => $notification->read_at?->toISOString(),
                'created_at' => $notification->created_at?->toISOString(),
            ];
        })->values()->all();

        return [
            'items' => $items,
            'pagination' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ];
    }

    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if (! $notification) {
            return false;
        }

        $notification->markAsRead();

        return true;
    }

    public function markAllAsRead(User $user): int
    {
        return (int) $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
