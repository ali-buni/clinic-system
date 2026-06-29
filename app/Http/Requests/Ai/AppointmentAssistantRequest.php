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
            'query' => 'nullable|string|min:3|max:500',
            'specialty_id' => 'nullable|integer|exists:specialties,id',
            'doctor_id' => 'nullable|integer|exists:doctors,id',
            'date' => 'nullable|date|date_format:Y-m-d',
            'start_time' => 'nullable|date_format:H:i',
            'patient_id' => 'nullable|integer|exists:patient_infos,id',
            'appointment_type_id' => 'nullable|integer|exists:appointment_types,id',
            'visit_reason' => 'nullable|string|max:500',
            'clinic_id' => 'nullable|integer|exists:clinics,id',
            'session_id' => 'nullable|string|max:100',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('query') && !$this->filled('specialty_id') && !$this->filled('doctor_id')) {
                $validator->errors()->add('input', 'Provide a query (symptoms/specialty/doctor), specialty_id, or doctor_id to proceed.');
            }
        });
    }
}
