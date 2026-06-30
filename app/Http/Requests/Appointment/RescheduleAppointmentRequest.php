<?php

namespace App\Http\Requests\Appointment;

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
            'start_time' => 'required|date_format:H:i',
            'date' => 'required|date|date_format:Y-m-d',
            'type_id' => 'sometimes|exists:appointment_types,id'
        ];
    }
}
