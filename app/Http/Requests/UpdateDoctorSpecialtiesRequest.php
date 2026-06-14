<?php
namespace App\Http\Requests;

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
            'specialty_ids' => ['required', 'array'],
            'specialty_ids.*' => ['integer', 'exists:specialties,id'], 
        ];
    }
}
