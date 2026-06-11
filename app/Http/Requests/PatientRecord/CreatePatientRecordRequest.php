<?php

namespace App\Http\Requests\PatientRecord;

use Illuminate\Foundation\Http\FormRequest;

class CreatePatientRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'         => 'required|exists:patients,id',
            'doctor_id'          => 'required|exists:doctors,id',
            'clinic_id'          => 'required|exists:clinics,id',
            'appointment_id'     => 'required|exists:appointments,id',
            'diagnosis_summary'  => 'required|string|max:1000',
            'description'        => 'nullable|string|max:1000',
            'status'             => 'nullable|string|in:open,closed,follow-up',
            'notes'              => 'nullable|string|max:2000',
            'diseases'           => 'nullable|array',
            'diseases.*'         => 'exists:diseases,id',
            'prescription_items' => 'nullable|array',
            'prescription_items.*.medicine_id'       => 'required|exists:medicines,id',
            'prescription_items.*.dosage_instruction' => 'nullable|string|max:500',
            'prescription_items.*.frequency'         => 'nullable|string|max:100',
            'prescription_items.*.duration'          => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'diagnosis_summary.required' => 'Diagnosis summary is required.',
            'status.in'                  => 'Status must be one of: open, closed, follow-up.',        ];
    }
}
