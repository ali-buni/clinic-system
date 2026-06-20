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
            'phone' => 'required_without:email|string|exists:users,phone',
            'email' => 'required_without:phone|email|exists:users,email',
            'code' => 'required|string|digits:6',
            'type' => 'nullable|string|in:phone,email',
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
