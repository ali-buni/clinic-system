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

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if ($user && $user->patientProfile && $this->input('patient_id') !== null
                && (int) $this->input('patient_id') !== (int) $user->patientProfile->id) {
                $validator->errors()->add('patient_id', 'You can only access your own patient data.');
            }
        });
    }
}
