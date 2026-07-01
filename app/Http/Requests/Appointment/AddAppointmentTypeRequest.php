<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class AddAppointmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ar_name' => 'required|string|max:255',
            'en_name' => 'required|string|max:255',
            'types'   => 'required|integer|min:1|max:3'
        ];
    }
}
