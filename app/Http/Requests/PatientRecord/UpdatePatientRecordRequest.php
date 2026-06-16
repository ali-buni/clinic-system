<?php

namespace App\Http\Requests\PatientRecord;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'diagnosis_summary' => 'nullable|string|max:1000',
            'description'       => 'nullable|string|max:1000',
            'status'            => 'nullable|string|in:open,closed,follow-up',
            'cost'        => 'nullable|numeric|min:0',
            'valid_until' => 'nullable|date',
            'notes'             => 'nullable|string|max:2000',

            'diseases' => 'nullable|array',
            'diseases.*.name' => 'required|string',
            'diseases.*.ar_name' => 'nullable|string',
            'diseases.*.code' => 'nullable|string',
            'diseases.*.disease_nature' => 'nullable|string|in:infectious,genetic,chronic,acute,mental,other',

            'prescription_items' => 'nullable|array',
            'prescription_items.*.en_name' => 'required|string|max:255',
            'prescription_items.*.ar_name' => 'nullable|string',
            'prescription_items.*.strength' => 'nullable|string',
            'prescription_items.*.form' => 'nullable|string|in:tablet,capsule,syrup,injection,ointment',
            'prescription_items.*.dosage_instruction' => 'nullable|string|max:500',
            'prescription_items.*.frequency' => 'nullable|string|max:100',
            'prescription_items.*.duration' => 'nullable|string|max:100',
        ];
    }
}
