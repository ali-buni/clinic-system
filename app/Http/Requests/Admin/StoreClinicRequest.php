<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreClinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'phone' => 'required|string|unique:clinics,phone',
            'location' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ];
    }
}
