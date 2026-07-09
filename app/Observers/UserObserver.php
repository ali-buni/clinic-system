<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        $role = $user->roles->first()?->name;

        LogActivityJob::dispatch(
            'user',
            'created user',
            get_class($user),
            $user->id,
            auth()->id(),
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

        LogActivityJob::dispatch(
            'user',
            'updated user',
            get_class($user),
            $user->id,
            auth()->id(),
            $details,
            'updated'
        );
    }

    public function deleted(User $user): void
    {
        $role = $user->roles->first()?->name;

        LogActivityJob::dispatch(
            'user',
            'deleted user',
            get_class($user),
            $user->id,
            auth()->id(),
            ['role' => $role],
            'deleted'
        );
    }

    public function restored(User $user): void
    {
        LogActivityJob::dispatch(
            'user',
            'restored user',
            get_class($user),
            $user->id,
            auth()->id(),
            [],
            'restored'
        );
    }
}
