<?php

namespace App\Http\Requests\PatientRecord;

use Illuminate\Foundation\Http\FormRequest;

class CreatePatientRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'         => 'required|exists:patient_infos,id',
            'doctor_id'          => 'required|exists:doctors,id',
            'clinic_id'          => 'required|exists:clinics,id',
            'appointment_id'     => 'required|exists:appointments,id',
            'diagnosis_summary'  => 'required|string|max:1000',
            'description'        => 'nullable|string|max:1000',
            'status'             => 'nullable|string|in:open,closed,follow-up',
            'notes'              => 'nullable|string|max:2000',

            'diseases' => 'nullable|array',
            'diseases.*.id' => 'required_without:diseases.*.code|integer|exists:diseases,id',
            'diseases.*.code' => 'required_without:diseases.*.id|string',

            'diseases.*.en_name' => 'required_without:diseases.*.id|string',
            'diseases.*.ar_name' => 'required_without:diseases.*.id|string',
            'diseases.*.disease_nature' => 'required_without:diseases.*.id|in:infectious,genetic,chronic,acute,mental,other',
            'diseases.*.description' => 'nullable|string',
            'diseases.*.status'      => 'nullable|in:active,resolved,chronic',
            'diseases.*.severity'    => 'nullable|in:mild,moderate,severe',

            'prescription_items' => 'nullable|array',
            'prescription_items.*.id' => 'required_without:prescription_items.*.api_medicine_id|integer|exists:medicines,id',
            'prescription_items.*.api_medicine_id' => 'required_without:prescription_items.*.id|string',

            'prescription_items.*.en_name' => 'required_without:prescription_items.*.id|string|max:255',
            'prescription_items.*.ar_name' => 'required_without:prescription_items.*.id|string|max:255',
            'prescription_items.*.generic_name_en' => 'required_without:prescription_items.*.id|string|max:255',
            'prescription_items.*.generic_name_ar' => 'required_without:prescription_items.*.id|string|max:255',
            'prescription_items.*.form'    => 'required_without:prescription_items.*.id|string',
            'prescription_items.*.strength' => 'required_without:prescription_items.*.id|string',

            'prescription_items.*.dosage_instruction' => 'nullable|string|max:500',
            'prescription_items.*.frequency'         => 'nullable|string|max:100',
            'prescription_items.*.duration'          => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'diagnosis_summary.required' => 'Diagnosis summary is required.',
            'status.in'                  => 'Status must be one of: open, closed, follow-up.',
        ];
    }
}
