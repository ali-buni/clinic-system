<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogService;

class UserObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(User $user): void
    {
        $role = $user->roles->first()?->name;

        $this->activityLog->log(
            'user',
            'created user',
            $user,
            auth()->user(),
            ['role' => $role],
            'created'
        );
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        unset($changes['updated_at']);

        $details = ['changed_fields' => array_keys($changes)];

        if (isset($changes['fname']) || isset($changes['lname'])) {
            $details['name_changed'] = [
                'from' => $user->getOriginal('fname').' '.$user->getOriginal('lname'),
                'to' => ($changes['fname'] ?? $user->fname).' '.($changes['lname'] ?? $user->lname),
            ];
        }

        if (isset($changes['phone'])) {
            $details['phone_changed'] = [
                'from' => $user->getOriginal('phone'),
                'to' => $changes['phone'],
            ];
        }

        $this->activityLog->log(
            'user',
            'updated user',
            $user,
            auth()->user(),
            $details,
            'updated'
        );
    }

    public function deleted(User $user): void
    {
        $role = $user->roles->first()?->name;

        $this->activityLog->log(
            'user',
            'deleted user',
            $user,
            auth()->user(),
            ['role' => $role],
            'deleted'
        );
    }

    public function restored(User $user): void
    {
        $this->activityLog->log(
            'user',
            'restored user',
            $user,
            auth()->user(),
            [],
            'restored'
        );
    }
}
