<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'phone' => ['required', 'string', Rule::unique('clinics', 'phone')->ignore($this->route('clinic'))],
            'location' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ];
    }
}
