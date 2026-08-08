<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (User::where('email_hash', User::hashEmail($value))->exists()) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|min:8|confirmed',
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female',
            'role' => 'required|in:owner,doctor,secretary,patient',
        ];
    }
}
