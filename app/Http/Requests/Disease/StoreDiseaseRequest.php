<?php

namespace App\Http\Requests\Disease;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiseaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('doctor');
    }

    public function rules(): array
    {
        return [
            'code'           => 'nullable|string|max:50',
            'ar_name'        => 'required|string|max:255',
            'en_name'        => 'required|string|max:255',
            'description'    => 'nullable|string',
            'disease_nature' => 'required|in:infectious,genetic,chronic,acute,mental,other',
        ];
    }

    public function messages(): array
    {
        return [
            'ar_name.required'   => 'The Arabic name of the disease is required.',
            'en_name.required'   => 'The English name of the disease is required.',
            'disease_nature.in'  => 'The selected disease nature is invalid.',
        ];
    }
}
