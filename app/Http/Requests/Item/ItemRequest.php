<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'item_name' => ['required', 'string', 'max:255'],
        ];

        if ($this->user()?->hasRole('admin')) {
            $rules['clinic_id'] = ['nullable', 'integer', 'exists:clinics,id'];
        } else {
            $rules['clinic_id'] = ['required', 'integer', 'exists:clinics,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'item_name.required' => 'The item name is required.',
            'clinic_id.required' => 'The clinic id is required for non-admin users.',
            'clinic_id.exists' => 'The selected clinic does not exist.',
        ];
    }
}
