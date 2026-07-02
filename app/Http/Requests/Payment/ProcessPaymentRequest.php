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
            'invoice_id' => 'required|integer|exists:invoices,id',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'amount'            => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            // invoice_id messages
            'invoice_id.required' => 'معرف الفاتورة مطلوب.',
            'invoice_id.integer'  => 'معرف الفاتورة يجب أن يكون رقماً صحيحاً.',
            'invoice_id.exists'   => 'الفاتورة غير موجودة.',

            // payment_method_id messages
            'payment_method_id.required' => 'طريقة الدفع مطلوبة.',
            'payment_method_id.exists'   => 'طريقة الدفع غير موجودة.',

            // amount messages
            'amount.required' => 'المبلغ مطلوب.',
            'amount.numeric'  => 'المبلغ يجب أن يكون رقماً.',
            'amount.min'      => 'المبلغ يجب أن يكون 0.01 على الأقل.',
        ];
    }
}
