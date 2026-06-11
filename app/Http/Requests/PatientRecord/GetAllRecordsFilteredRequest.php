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
        ];
    }
}
