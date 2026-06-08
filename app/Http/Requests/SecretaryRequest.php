<?php

namespace App\Http\Requests;

use App\Models\Secretary;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SecretaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow if user is authenticated and has a secretary profile
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $secretaryProfile = $user->secretaryProfile;
        return $secretaryProfile !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'clinic_id' => 'sometimes|exists:clinics,id',
            'fname' => 'sometimes|string|max:255',
            'lname' => 'sometimes|string|max:255',
            'dob' => 'sometimes|date|before:today',
            'gender' => 'sometimes|in:male,female,unknown'
        ];
    }
}
