<?php

namespace App\Http\Requests\PatientRecord;

use Illuminate\Contracts\Validation\Validator;
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
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|in:open,closed,follow-up',
            'clinic_id' => 'required|integer|exists:clinics,id',

            'search' => 'nullable|string|max:255',
            'column' => 'nullable|string|max:255',
            'sort' => 'nullable|string|max:255',
            'direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $user = auth()->user();

            $clinicId = match (true) {
                $user?->hasRole('owner') => $user->clinicOwner?->id,
                $user?->hasRole('doctor') => $user->doctorProfile?->clinic_id,
                default => null,
            };

            if ($clinicId !== null && (int) $this->input('clinic_id') !== (int) $clinicId) {
                $validator->errors()->add('clinic_id', 'Unauthorized clinic.');
            }
        });
    }
}
