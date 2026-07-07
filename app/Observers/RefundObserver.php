<?php

namespace App\Observers;

use App\Models\Refund;
use App\Services\ActivityLogService;

class RefundObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(Refund $refund): void
    {
        $this->activityLog->log(
            'refund',
            'created refund',
            $refund,
            auth()->user(),
            [
                'amount' => $refund->amount,
                'payment_id' => $refund->payment_id,
                'invoice_id' => $refund->invoice_id,
                'reason' => $refund->reason,
            ],
            'created'
        );
    }
}
