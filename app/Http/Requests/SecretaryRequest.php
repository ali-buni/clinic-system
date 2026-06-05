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
        $user = Auth::user();
        $secretary_id = $this->route('id');
        $secretary = Secretary::query()->find($secretary_id);
        if (!$user || !$secretary) {
            return false;
        }
        return $user->id == $secretary->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_id' => 'sometimes|exists:rooms,id',
            // 'clinic_id' => 'sometimes|exists:clinics,id',
            'fname' => 'sometimes|string|max:255',
            'lname' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|digits_between:10,15|starts_with:09',
            'dob' => 'sometimes|date|before:today',
            'gender' => 'sometimes|in:male,female,unknown'
        ];
    }
}
