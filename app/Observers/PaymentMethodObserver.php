<?php

namespace App\Observers;

use App\Models\Payment_method;
use App\Services\ActivityLogService;

class PaymentMethodObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    public function created(Payment_method $method): void
    {
        $this->activityLog->log(
            'payment_method',
            'created payment method',
            $method,
            auth()->user(),
            ['type' => $method->type->value, 'en_name' => $method->en_name],
            'created'
        );
    }

    public function updated(Payment_method $method): void
    {
        $changes = $method->getChanges();
        unset($changes['updated_at']);

        $this->activityLog->log(
            'payment_method',
            'updated payment method',
            $method,
            auth()->user(),
            ['changed_fields' => array_keys($changes)],
            'updated'
        );
    }

    public function deleted(Payment_method $method): void
    {
        $this->activityLog->log(
            'payment_method',
            'deleted payment method',
            $method,
            auth()->user(),
            [],
            'deleted'
        );
    }
}
