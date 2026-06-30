<?php

namespace App\Http\Requests\Clinic;

use App\Models\Clinic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ClinicRequest extends FormRequest
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

        // Verify clinic belongs to authenticated user
        $clinic = Clinic::query()
            ->where('id', $this->route('clinicId'))
            ->where('user_id', $user->id)
            ->exists();

        return $user->hasRole('owner') && $clinic;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'sometimes|digits:10|starts_with:09',
            'location' => 'sometimes|string|min:10',
            'title' => 'sometimes|string|min:6|max:60',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.digits' => 'Please enter a valid 10-digit phone number.',
            'phone.starts_with' => 'The phone number must start with 09 (e.g., 0912345678).',
            'location.string' => 'Please provide a valid location address.',
            'location.min' => 'Please provide a more detailed location (minimum 10 characters).',
            'title.string' => 'The title must be valid text.',
            'title.min' => 'The title must be at least 6 characters long.',
            'title.max' => 'The title cannot be longer than 60 characters.',
        ];
    }
}
