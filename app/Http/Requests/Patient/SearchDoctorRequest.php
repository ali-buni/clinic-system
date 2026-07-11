<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class SearchDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'consultation_fee_min' => 'nullable|numeric|min:0',
            'consultation_fee_max' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|string|in:consultation_fee,appointment_duration,name',
            'sort_direction' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Name must be text.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'location.string' => 'Location must be text.',
            'location.max' => 'Location cannot exceed 255 characters.',
            'specialty.string' => 'Specialty must be text.',
            'specialty.max' => 'Specialty cannot exceed 255 characters.',
            'consultation_fee_min.numeric' => 'Minimum consultation fee must be a number.',
            'consultation_fee_min.min' => 'Minimum consultation fee cannot be negative.',
            'consultation_fee_max.numeric' => 'Maximum consultation fee must be a number.',
            'consultation_fee_max.min' => 'Maximum consultation fee cannot be negative.',
            'sort_by.in' => 'Sort field must be one of: consultation_fee, appointment_duration, name.',
            'sort_direction.in' => 'Sort direction must be either "asc" or "desc".',
            'per_page.integer' => 'Items per page must be a number.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 100.',
            'page.integer' => 'Page number must be a valid number.',
            'page.min' => 'Page number must be at least 1.',
        ];
    }
}
