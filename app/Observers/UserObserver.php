<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(User $user): void
    {
        $this->activityLog->log(
            'user',
            'created user',
            $user,
            auth()->user(),
            [],
            'created'
        );
        Log::channel('structured')->info('user created', ['user_id' => $user->id]);
    }

    public function updated(User $user): void
    {
        $this->activityLog->log(
            'user',
            'updated user',
            $user,
            auth()->user(),
            [],
            'updated'
        );
        Log::channel('structured')->info('user updated', ['user_id' => $user->id]);
    }

    public function deleted(User $user): void
    {
        $this->activityLog->log(
            'user',
            'deleted user',
            $user,
            auth()->user(),
            [],
            'deleted'
        );
        Log::channel('structured')->info('user deleted', ['user_id' => $user->id]);
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
        Log::channel('structured')->info('user restored', ['user_id' => $user->id]);
    }
}
