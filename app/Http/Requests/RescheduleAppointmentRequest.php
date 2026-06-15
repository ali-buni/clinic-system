<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => 'required|date|date_format:H:i',
            'date' => 'nullable|date|date_format:Y-m-d',
            'type_id' => 'sometimes|exists:appointment_types,id'
        ];
    }
}
