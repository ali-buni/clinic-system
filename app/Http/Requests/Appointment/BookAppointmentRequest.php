<?php

namespace App\Http\Requests\Appointment;

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
            'patient_id' => 'required|integer|exists:patient_infos,id',
            'doctor_id' => 'required|integer|exists:doctors,id',
            'clinic_id' => 'required|integer|exists:clinics,id',
            'appointment_type_id' => 'required|integer|exists:appointment_types,id',
            'start_time' => 'required|date_format:H:i',
            'date' => 'required|date|date_format:Y-m-d|after:today',
            'visit_reason' => 'nullable|string',
        ];
    }
}
