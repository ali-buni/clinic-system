<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => 'required|integer|exists:patients,id',
            'doctor_id' => 'required|integer|exists:doctors,id',
            'clinic_id' => 'required|integer|exists:clinics,id',
            'room_id' => 'nullable|integer|exists:rooms,id',
            'appointment_type_id' => 'required|integer|exists:appointment_types,id',
            'start_time' => 'required|date|date_format:H:i',
            'date' => 'required|date|date_format:Y-m-d',
            'visit_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
