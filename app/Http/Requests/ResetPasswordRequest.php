<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'phone' => 'required|string|exists:users,phone',
            'password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|different:password',
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
            'password.required' => 'Current password is required',
            'password.string' => 'Current password must be a string',
            'password.min' => 'Current password must be at least 8 characters',
            'new_password.required' => 'New password is required',
            'new_password.string' => 'New password must be a string',
            'new_password.min' => 'New password must be at least 8 characters',
            'new_password.different' => 'New password must be different from current password',
        ];
    }
}
