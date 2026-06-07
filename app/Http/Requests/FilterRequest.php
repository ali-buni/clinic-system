<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $route = request()->route();
        $action = $route->getActionMethod();
        $data = [];
        if ($action == 'searchDisease' || $action == 'searchMedicine') {
            $data = ['query' => 'required|string|min:2'];
        }
        return [
            'search' => 'nullable|string|max:255',
            'column' => 'nullable|string|max:100',
            'sort' => 'nullable|string|max:100',
            'direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'clinic_id' => 'nullable|integer|exists:clinics,id',
            ...$data,
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'Search term must be text.',
            'search.max' => 'Search term cannot exceed 255 characters.',
            'column.string' => 'Column name must be text.',
            'column.max' => 'Column name cannot exceed 100 characters.',
            'sort.string' => 'Sort field must be text.',
            'sort.max' => 'Sort field cannot exceed 100 characters.',
            'direction.in' => 'Sort direction must be either "asc" or "desc".',
            'per_page.integer' => 'Items per page must be a number.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 100.',
            'page.integer' => 'Page number must be a valid number.',
            'page.min' => 'Page number must be at least 1.',
            'clinic_id.integer' => 'Clinic ID must be a valid number.',
            'clinic_id.exists' => 'The selected clinic does not exist.',
        ];
    }
}
