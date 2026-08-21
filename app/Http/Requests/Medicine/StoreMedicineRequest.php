<?php

namespace App\Http\Requests\Medicine;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'api_medicine_id' => 'nullable|string',

            'ar_name'         => ['nullable', 'string', 'max:255', 'required_without:en_name'],
            'en_name'         => ['nullable', 'string', 'max:255', 'required_without:ar_name'],

            'generic_name_ar' => 'nullable|string|max:255',
            'generic_name_en' => 'nullable|string|max:255',
            'strength'        => 'nullable|string|max:5000',

            'form'            => 'nullable|in:tablet,capsule,syrup,injection,ointment',
        ];
    }

    public function messages(): array
    {
        return [
            'ar_name.required_without' => 'Please provide either the Arabic or English name of the medicine.',
            'en_name.required_without' => 'Please provide either the Arabic or English name of the medicine.',
            'form.in'                  => 'The selected medicine form is invalid. Choose from: tablet, capsule, syrup, injection, ointment.',
        ];
    }
}
