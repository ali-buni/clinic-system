<?php

namespace App\Http\Requests\PatientRecord;

use Illuminate\Foundation\Http\FormRequest;

class GetAllRecordsFilteredRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disease_code' => 'nullable|string',
            'date_from'    => 'nullable|date',
            'date_to'      => 'nullable|date|after_or_equal:date_from',
            'patient_id'   => 'nullable|integer|exists:patients,id',
            'doctor_id'    => 'nullable|integer|exists:doctors,id',
            'status'       => 'nullable|in:open,closed,follow-up',
            'clinic_id'    => 'nullable|integer|exists:clinics,id',

            'search'       => 'nullable|string|max:255',
            'column'       => 'nullable|string|max:255',
            'sort'         => 'nullable|string|max:255',
            'direction'    => 'nullable|in:asc,desc',
            'per_page'     => 'nullable|integer|min:1|max:100',
            'page'         => 'nullable|integer|min:1',
        ];
    }
}
