<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'phone' => ['required', 'string', Rule::unique('users', 'phone')->ignore($this->route('user'))],
            'password' => 'nullable|min:8|confirmed',
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female',
            'role' => 'required|in:owner,doctor,secretary,patient',
        ];
    }
}
