<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\ActivityLogService;

class PaymentObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(Payment $payment): void
    {
        $this->activityLog->log(
            'payment',
            'created payment',
            $payment,
            auth()->user(),
            ['amount' => $payment->amount, 'invoice_id' => $payment->invoice_id],
            'created'
        );
    }

    public function updated(Payment $payment): void
    {
        $changes = $payment->getChanges();
        unset($changes['updated_at']);

        $details = ['changed_fields' => array_keys($changes)];

        if ($payment->wasChanged('paid_at') && $payment->paid_at !== null) {
            $details['payment_confirmed'] = true;
            $details['paid_at'] = $payment->paid_at->toDateTimeString();
        }

        if ($payment->wasChanged('refunded_amount')) {
            $details['refund_increment'] = [
                'from' => $payment->getOriginal('refunded_amount'),
                'to' => $payment->refunded_amount,
            ];
        }

        $this->activityLog->log(
            'payment',
            'updated payment',
            $payment,
            auth()->user(),
            $details,
            'updated'
        );
    }

    public function deleted(Payment $payment): void
    {
        $this->activityLog->log(
            'payment',
            'deleted payment',
            $payment,
            auth()->user(),
            [],
            'deleted'
        );
    }

    public function restored(Payment $payment): void
    {
        $this->activityLog->log(
            'payment',
            'restored payment',
            $payment,
            auth()->user(),
            [],
            'restored'
        );
    }
}
