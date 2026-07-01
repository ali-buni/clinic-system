<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorSpecialtiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specialty_ids' => ['required', 'array', 'min:1'],
            'specialty_ids.*' => ['integer', 'exists:specialties,id', 'min:1'],
        ];
    }
}
