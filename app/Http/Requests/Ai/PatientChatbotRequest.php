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
}
