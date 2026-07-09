<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\Refund;

class RefundObserver
{
    public function created(Refund $refund): void
    {
        LogActivityJob::dispatch(
            'refund',
            'created refund',
            get_class($refund),
            $refund->id,
            auth()->id(),
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
