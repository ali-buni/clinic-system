<?php

namespace App\Http\Requests\Medicine;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('doctor');
    }

    public function rules(): array
    {
        return [
            'api_medicine_id' => 'nullable|string',

            'ar_name'         => 'required_without:en_name|string|max:255|nullable',
            'en_name'         => 'required_without:ar_name|string|max:255|nullable',

            'generic_name_ar' => 'nullable|string|max:255',
            'generic_name_en' => 'nullable|string|max:255',
            'strength'        => 'nullable|string|max:50',

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
