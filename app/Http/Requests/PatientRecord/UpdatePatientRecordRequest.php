<?php

namespace App\Http\Requests\PatientRecord;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add policy later
    }

    public function rules(): array
    {
        return [
            'diagnosis_summary' => 'nullable|string|max:1000',
            'description'       => 'nullable|string|max:1000',
            'status'            => 'nullable|string|in:open,closed,follow-up',
            'notes'             => 'nullable|string|max:2000',
            'updated_diseases'  => 'nullable|array',
            'updated_diseases.*'=> 'exists:diseases,id',
            'updated_items'     => 'nullable|array',
            'updated_items.*.medicine_id'       => 'required|exists:medicines,id',
            'updated_items.*.dosage_instruction'=> 'nullable|string|max:500',
            'updated_items.*.frequency'         => 'nullable|string|max:100',
            'updated_items.*.duration'          => 'nullable|string|max:100',
        ];
    }
}
