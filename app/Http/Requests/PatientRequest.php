<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clinic_id' => 'required|exists:clinics,id',
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'dob' => 'required|date|before:today',
            'gender' => 'nullable|in:male,female,unknown',
            'phone' => 'required|string|digits_between:10,13',
            'nationality' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:married,single,other',
            'emergency_phone' => 'nullable|string|digits_between:10,13',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'career' => 'nullable|string',
            'blood_type' => 'nullable|string|max:3',
        ];
    }

    public function messages()
    {
        return [
            'clinic_id.required' => 'Please select a clinic.',
            'clinic_id.exists' => 'The selected clinic does not exist.',
            'fname.required' => 'First name is required.',
            'lname.required' => 'Last name is required.',
            'dob.required' => 'Date of birth is required.',
            'dob.before' => 'Date of birth must be before today.',
            'phone.required' => 'Phone number is required.',
            'phone.digits_between' => 'Phone number must be between 10 and 13 digits.',
            'gender.in' => 'Gender must be male, female, or unknown.',
            'marital_status.in' => 'Marital status must be married, single, or other.',
            'emergency_phone.digits_between' => 'Emergency phone must be between 10 and 13 digits.',
        ];
    }
}
