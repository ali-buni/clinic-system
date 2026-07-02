<?php

namespace App\Http\Requests\PaymentMethod;

use App\Enums\PaymentMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ar_name' => 'required|string|max:255',
            'en_name' => 'required|string|max:255',
            'type'    => ['required', 'string', Rule::enum(PaymentMethodType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'ar_name.required' => 'الاسم بالعربية مطلوب.',
            'en_name.required' => 'الاسم بالإنجليزية مطلوب.',
            'type.required'    => 'نوع طريقة الدفع مطلوب.',
            'type.enum'        => 'نوع طريقة الدفع غير صالح.',
        ];
    }
}
