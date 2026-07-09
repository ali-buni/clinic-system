<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\Payment;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        LogActivityJob::dispatch(
            'payment',
            'created payment',
            get_class($payment),
            $payment->id,
            auth()->id(),
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

        LogActivityJob::dispatch(
            'payment',
            'updated payment',
            get_class($payment),
            $payment->id,
            auth()->id(),
            $details,
            'updated'
        );
    }

    public function deleted(Payment $payment): void
    {
        LogActivityJob::dispatch(
            'payment',
            'deleted payment',
            get_class($payment),
            $payment->id,
            auth()->id(),
            [],
            'deleted'
        );
    }

    public function restored(Payment $payment): void
    {
        LogActivityJob::dispatch(
            'payment',
            'restored payment',
            get_class($payment),
            $payment->id,
            auth()->id(),
            [],
            'restored'
        );
    }
}
