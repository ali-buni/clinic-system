<?php

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentAssistantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => 'required|string|min:1|max:500',
            'patient_id' => 'nullable|integer|exists:patient_infos,id',
            'clinic_id' => 'nullable|integer|exists:clinics,id',
        ];
    }
}
