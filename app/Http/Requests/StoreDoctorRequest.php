<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fname'            => 'required|string|max:50',
            'lname'            => 'required|string|max:50',
            'phone'            => 'required|string|unique:users,phone',
            'password'         => 'required|string|min:6',
            'dob'              => 'nullable|string|min:100',
            'gender'           => 'required|string|in:male,female,unknown',
            'room_id'          => 'nullable|exists:rooms,id',
            'appointment_duration' => 'nullable|integer|min:5|max:120',
            'bio'              => 'nullable|string',
            'consultation_fee' => 'required|numeric|min:0',
        ];
    }
}
