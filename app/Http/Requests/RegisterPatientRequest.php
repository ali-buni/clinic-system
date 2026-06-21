<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|digits_between:10,13',
            'dob' => 'nullable|date|before:today|after:1900-01-01',
            'gender' => 'nullable|in:male,female,other,unknown',
            'clinic_id' => 'required|exists:clinics,id',
            'nationality' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:married,single,divorced,widowed,other',
            'emergency_phone' => 'nullable|string|digits_between:10,13|different:phone',
            'allergies' => 'nullable|string|max:1000',
            'chronic_conditions' => 'nullable|string|max:1000',
            'career' => 'nullable|string|max:255',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'fname.required' => 'First name is required.',
            'lname.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'clinic_id.required' => 'Please select a clinic.',
            'clinic_id.exists' => 'The selected clinic does not exist.',
            'dob.before' => 'Date of birth must be before today.',
            'dob.after' => 'Date of birth is invalid.',
            'gender.in' => 'Gender must be male, female, other, or unknown.',
            'marital_status.in' => 'Marital status is invalid.',
            'emergency_phone.digits_between' => 'Emergency phone must be between 10 and 13 digits.',
            'emergency_phone.different' => 'Emergency phone must be different from primary phone.',
            'blood_type.in' => 'Blood type must be A+, A-, B+, B-, AB+, AB-, O+, or O-.',
        ];
    }
}
