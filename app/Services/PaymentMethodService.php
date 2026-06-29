<?php

namespace App\Services;

use App\Models\Payment_method;
use Illuminate\Support\Collection;

class PaymentMethodService
{
    /**
     * جلب جميع طرق الدفع النشطة في النظام
     */
    public function getActiveMethods(): Collection
    {
        return Payment_method::where('is_active', true)
            ->get(['id', 'ar_name', 'en_name', 'is_active']);
    }

    public function createMethod(array $data): Payment_method
    {
        return Payment_method::create([
            'ar_name'   => $data['ar_name'],
            'en_name'   => $data['en_name'],
            'is_active' => true,
        ]);
    }


    public function deleteMethod(int $id): bool
    {
        $method = Payment_method::findOrFail($id);
        return $method->delete();
    }
}