<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\Verification_code;

class VerificationCodeObserver
{
    public function created(Verification_code $code): void
    {
        LogActivityJob::dispatch(
            'verification_code',
            'created verification code',
            get_class($code),
            $code->id,
            auth()->id(),
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
        LogActivityJob::dispatch(
            'verification_code',
            'deleted verification code',
            get_class($code),
            $code->id,
            auth()->id(),
            [
                'user_id' => $code->user_id,
                'type' => $code->type,
            ],
            'deleted'
        );
    }
}
