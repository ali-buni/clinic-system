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
            'user_id' => 'nullable|exists:users,id',
            'location_country' => 'required|string|max:255',
            'location_governorate' => 'required|string|max:255',
            'location_city' => 'required|string|max:255',
            'location_name' => 'nullable|string|max:255',
        ];
    }
}
