<?php

namespace App\Http\Requests\Room;

use App\Services\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('owner');
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|min:2|max:120|unique:rooms,name',
        ];

        if ($this->isMethod('post')) {
            $rules['clinic_id'] = 'required|integer|exists:clinics,id';
        } else {
            $rules['clinic_id'] = 'sometimes|integer|exists:clinics,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A room name is required.',
            'name.string' => 'The room name must be a valid string.',
            'name.min' => 'The room name must be at least 2 characters long.',
            'name.max' => 'The room name may not be greater than 120 characters.',
            'clinic_id.required' => 'A clinic id is required.',
            'clinic_id.integer' => 'The clinic id must be a valid number.',
            'clinic_id.exists' => 'The selected clinic does not exist.',
        ];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException('You do not have permission to perform this action.');
        // return ApiResponse::permissionDenied('You do not have permission to perform this action.')->send();
    }
}
