<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }

    public function messages(): array
    {
        return [
            'ar_name.required' => 'الاسم بالعربية مطلوب.',
            'en_name.required' => 'الاسم بالإنجليزية مطلوب.',
        ];
    }
}
