<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NewSecretaryRequest extends FormRequest
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
        // TODO: permission to create secretary
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
            'email' => 'required|email|max:255|unique:users,email',

            'dob' => 'required|date|before:today',
            'gender' => 'required|in:male,female,unknown',
            'clinic_id' => 'required|exists:clinics,id',
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'integer|exists:rooms,id',
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

            // email validation messages
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',

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
            'room_ids.required' => 'Please select a room.',
            'room_ids.*.exists' => 'Selected room does not exist.',
        ];
    }
}
