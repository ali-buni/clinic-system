<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => 'required|email',
            'code' => 'required|string|digits:6',
            'type' => 'required|string|in:phone,email',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.exists' => 'Phone number does not exist',
            'email.exists' => 'Email address does not exist',
            'code.required' => 'Code is required',
            'code.digits' => 'Code must be exactly 6 digits',
        ];
    }
}
