<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NewDoctorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->hasRole('owner');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fname' => 'required|string|min:2|max:50',
            'lname' => 'required|string|min:2|max:50',
            'phone' => 'required|digits:10|starts_with:09|unique:users,phone',
            'dob' => 'required|date|before:today',
            'gender' => 'required|in:male,female,unknown',
            'clinic_id' => 'required|exists:clinics,id',
            'room_id' => 'required|exists:rooms,id',
            'appointment_duration' => 'required|integer|min:5|max:120',
            'bio' => 'nullable|string|max:1000',
            'consultation_fee' => 'required|numeric|min:0',
            'speciality_ids' => 'required|array|min:1',
            'speciality_ids.*' => 'exists:specialities,id',
        ];
    }

    public function messages(): array
    {
        return [
            // fname validation messages
            'fname.required' => 'First name is required.',
            'fname.string' => 'First name must be a valid text.',
            'fname.min' => 'First name must be at least 2 characters.',
            'fname.max' => 'First name cannot exceed 50 characters.',

            // lname validation messages
            'lname.required' => 'Last name is required.',
            'lname.string' => 'Last name must be a valid text.',
            'lname.min' => 'Last name must be at least 2 characters.',
            'lname.max' => 'Last name cannot exceed 50 characters.',

            // phone validation messages
            'phone.required' => 'Phone number is required.',
            'phone.digits' => 'Phone number must be exactly 10 digits.',
            'phone.starts_with' => 'Phone number must start with 09.',

            // dob validation messages
            'dob.required' => 'Date of birth is required.',
            'dob.date' => 'Please provide a valid date format.',
            'dob.before' => 'Date of birth must be before today.',

            // gender validation messages
            'gender.required' => 'Gender selection is required.',
            'gender.in' => 'Gender must be either male, female, or unknown.',

            // clinic_id validation messages
            'clinic_id.required' => 'Please select a clinic.',
            'clinic_id.exists' => 'Selected clinic does not exist.',

            // room_id validation messages
            'room_id.required' => 'Please select a room.',
            'room_id.exists' => 'Selected room does not exist.',

            // appointment_duration validation messages
            'appointment_duration.required' => 'Appointment duration is required.',
            'appointment_duration.integer' => 'Appointment duration must be a whole number.',
            'appointment_duration.min' => 'Appointment duration must be at least 5 minutes.',
            'appointment_duration.max' => 'Appointment duration cannot exceed 120 minutes.',

            // bio validation messages
            'bio.string' => 'Bio must be a valid text.',
            'bio.max' => 'Bio cannot exceed 1000 characters.',

            // consultation_fee validation messages
            'consultation_fee.required' => 'Consultation fee is required.',
            'consultation_fee.numeric' => 'Consultation fee must be a valid number.',
            'consultation_fee.min' => 'Consultation fee cannot be negative.',

            // speciality_ids validation messages
            'speciality_ids.required' => 'At least one speciality must be selected.',
            'speciality_ids.array' => 'Specialities must be provided as an array.',
            'speciality_ids.min' => 'Please select at least one speciality.',
            'speciality_ids.*.exists' => 'One or more selected specialities do not exist.',
        ];
    }
}
