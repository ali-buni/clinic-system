<?php

namespace App\Services;

use App\Models\Payment_method;
use Illuminate\Support\Collection;

class PaymentMethodService
{
    public function getActiveMethods(): Collection
    {
        return Payment_method::where('is_active', true)
            ->get(['id', 'ar_name', 'en_name', 'type']);
    }

    public function createMethod(array $data): Payment_method
    {
        return Payment_method::create([
            'ar_name'   => $data['ar_name'],
            'en_name'   => $data['en_name'],
            'type'      => $data['type'],
            'is_active' => false,
        ]);
    }

    public function stopMethod(Payment_method $payment): bool
    {
        return $payment->update(['is_active' => false]);
    }

    public function deleteMethod(Payment_method $method): void
    {
        $method->delete();
    }
}
