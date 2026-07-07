<?php

namespace App\Observers;

use App\Models\Verification_code;
use App\Services\ActivityLogService;

class VerificationCodeObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(Verification_code $code): void
    {
        $this->activityLog->log(
            'verification_code',
            'created verification code',
            $code,
            auth()->user(),
            [
                'user_id' => $code->user_id,
                'type' => $code->type,
                'sent_to' => $code->sent_to,
            ],
            'created'
        );
    }

    public function deleted(Verification_code $code): void
    {
        $this->activityLog->log(
            'verification_code',
            'deleted verification code',
            $code,
            auth()->user(),
            [
                'user_id' => $code->user_id,
                'type' => $code->type,
            ],
            'deleted'
        );
    }
}
