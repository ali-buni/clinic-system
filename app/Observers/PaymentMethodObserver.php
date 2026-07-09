<?php

namespace App\Observers;

use App\Jobs\LogActivityJob;
use App\Models\Payment_method;

class PaymentMethodObserver
{
    public function created(Payment_method $method): void
    {
        LogActivityJob::dispatch(
            'payment_method',
            'created payment method',
            get_class($method),
            $method->id,
            auth()->id(),
            ['type' => $method->type->value, 'en_name' => $method->en_name],
            'created'
        );
    }

    public function updated(Payment_method $method): void
    {
        $changes = $method->getChanges();
        unset($changes['updated_at']);

        LogActivityJob::dispatch(
            'payment_method',
            'updated payment method',
            get_class($method),
            $method->id,
            auth()->id(),
            ['changed_fields' => array_keys($changes)],
            'updated'
        );
    }

    public function deleted(Payment_method $method): void
    {
        LogActivityJob::dispatch(
            'payment_method',
            'deleted payment method',
            get_class($method),
            $method->id,
            auth()->id(),
            [],
            'deleted'
        );
    }
}
