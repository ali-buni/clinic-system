<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => [
                'required',
                'exists:patient_infos,id',
            ],

            'clinic_id' => [
                'sometimes',
                'exists:clinics,id'
            ],

            'fname' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'lname' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'dob' => [
                'sometimes',
                'date',
                'before:today',
                'after:1900-01-01'
            ],

            'gender' => 'nullable|in:male,female,other,unknown',

            'phone' => [
                'sometimes',
                'string',
                'digits:10',
                Rule::unique('patient_infos', 'phone')->ignore($this->patient_id),
            ],

            'nationality' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:married,single,divorced,widowed,other',
            'emergency_phone' => 'nullable|string|digits_between:10,13|different:phone',
            'allergies' => 'nullable|string|max:1000',
            'chronic_conditions' => 'nullable|string|max:1000',
            'career' => 'nullable|string|max:255',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ];
    }

    public function messages()
    {
        return [
            // Patient ID
            'patient_id.required' => 'Patient ID is required for update.',
            'patient_id.exists' => 'The selected patient does not exist.',

            // Clinic
            'clinic_id.exists' => 'The selected clinic does not exist.',

            // Name
            'fname.string' => 'First name must be a string.',
            'fname.max' => 'First name must not exceed 255 characters.',
            'lname.string' => 'Last name must be a string.',
            'lname.max' => 'Last name must not exceed 255 characters.',

            // Date of birth
            'dob.date' => 'Date of birth must be a valid date.',
            'dob.before' => 'Date of birth must be before today.',
            'dob.after' => 'Date of birth is invalid.',

            // Phone
            'phone.string' => 'Phone number must be a string.',
            'phone.digits' => 'Phone number must be exactly 10 digits.',
            'phone.unique' => 'This phone number is already registered to another patient.',

            // Gender
            'gender.in' => 'Gender must be male, female, other, or unknown.',

            // Marital status
            'marital_status.in' => 'Marital status is invalid.',

            // Emergency phone
            'emergency_phone.digits_between' => 'Emergency phone must be between 10 and 13 digits.',
            'emergency_phone.different' => 'Emergency phone must be different from primary phone.',

            // Blood type
            'blood_type.in' => 'Blood type must be A+, A-, B+, B-, AB+, AB-, O+, or O-.',

            // Chronic conditions
            'chronic_conditions.max' => 'Chronic conditions must not exceed 1000 characters.',
            'allergies.max' => 'Allergies must not exceed 1000 characters.',
        ];
    }
}
