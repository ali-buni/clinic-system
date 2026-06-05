<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string|exists:users,phone|starts_with:09',
            'code' => 'required|string|digits:6',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required',
            'phone.string' => 'Phone number must be a string',
            'phone.exists' => 'Phone number does not exist',
            'phone.start_with' => 'Phone number must start with 09',
            'code.required' => 'Code number is required',
            'code.string' => 'Code number must be a string',
            'code.digits' => 'Code must be exactly 6 digits',
        ];
    }
}
