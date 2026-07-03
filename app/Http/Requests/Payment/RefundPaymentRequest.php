<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class RefundPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'refunds' => 'required|array|min:1',
            'refunds.*.payment_id' => 'required|integer|exists:payments,id',
            'refunds.*.amount' => 'required|numeric|min:0.01|max:999999.99',
            'refunds.*.reason' => 'nullable|string|max:500',
        ];
    }
}
