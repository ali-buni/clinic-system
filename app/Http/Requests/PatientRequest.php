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
        $isUpdate = request()->route()->getActionMethod() === 'update';

        return [
            'patient_id' => [
                $isUpdate ? 'required' : 'nullable',
                'exists:patients,id',
            ],

            'clinic_id' => [
                $isUpdate ? 'sometimes' : 'required',
                'exists:clinics,id'
            ],

            'fname' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255'
            ],

            'lname' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255'
            ],

            'dob' => [
                $isUpdate ? 'sometimes' : 'required',
                'date',
                'before:today',
                'after:1900-01-01'
            ],

            'gender' => 'nullable|in:male,female,other,unknown',

            'phone' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'digits:10',
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
            // Clinic
            'clinic_id.required' => 'Please select a clinic.',
            'clinic_id.exists' => 'The selected clinic does not exist.',

            // Name
            'fname.required' => 'First name is required.',
            'lname.required' => 'Last name is required.',

            // Date of birth
            'dob.required' => 'Date of birth is required.',
            'dob.before' => 'Date of birth must be before today.',
            'dob.after' => 'Date of birth is invalid.',

            // Phone
            'phone.required' => 'Phone number is required.',
            'phone.digits_between' => 'Phone number must be between 10 and 13 digits.',
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
        ];
    }
}
