<?php

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class PatientChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|min:1|max:1000',
            'session_id' => 'nullable|string|max:100',
            'patient_id' => 'required|integer|exists:patient_infos,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'patient_id' => $this->route('patient_id') ?? $this->input('patient_id'),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if ($user && $user->patientProfile && (int) $this->patient_id !== (int) $user->patientProfile->id) {
                $validator->errors()->add('patient_id', 'You can only access your own patient data.');
            }
        });
    }
}
