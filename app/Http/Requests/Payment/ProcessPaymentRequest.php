<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'amount'            => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.required' => 'طريقة الدفع مطلوبة.',
            'payment_method_id.exists'   => 'طريقة الدفع غير موجودة.',
            'amount.required'            => 'المبلغ مطلوب.',
            'amount.numeric'             => 'المبلغ يجب أن يكون رقماً.',
            'amount.min'                 => 'المبلغ يجب أن يكون 0.01 على الأقل.',
        ];
    }
}
