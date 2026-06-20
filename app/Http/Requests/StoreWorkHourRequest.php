<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'doctor_id'            => 'required|integer|exists:doctors,id',
            'day_of_week'          => 'required|integer|between:0,6',
            'start_time'           => 'required|date_format:H:i',
            'end_time'             => 'required|date_format:H:i|after:start_time',
            'is_active'            => 'sometimes|boolean',
            'max_patients_per_day' => 'sometimes|integer|min:1',
            'break_start'          => 'nullable|date_format:H:i|after:start_time|before:end_time',
            'break_end'            => 'nullable|date_format:H:i|after:break_start|before:end_time',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(function ($rule) {
                return str_replace('required', 'sometimes', $rule);
            }, $rules);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'day_of_week.required' => 'يوم الأسبوع مطلوب.',
            'day_of_week.integer' => 'يوم الأسبوع يجب أن يكون رقمًا.',
            'day_of_week.between' => 'يوم الأسبوع يجب أن يكون بين 0 و 6.',

            'start_time.required' => 'وقت البداية مطلوب.',
            'start_time.date_format' => 'وقت البداية يجب أن يكون بصيغة H:i (مثال: 14:30).',

            'end_time.required' => 'وقت النهاية مطلوب.',
            'end_time.date_format' => 'وقت النهاية يجب أن يكون بصيغة H:i (مثال: 14:30).',
            'end_time.after' => 'وقت نهاية الدوام يجب أن يكون بعد وقت البداية.',

            'max_patients_per_day.integer' => 'عدد المرضى يجب أن يكون رقمًا صحيحًا.',
            'max_patients_per_day.min' => 'عدد المرضى يجب أن يكون على الأقل 1.',

            'break_start.date_format' => 'بداية الاستراحة يجب أن تكون بصيغة H:i.',
            'break_start.after' => 'بداية الاستراحة يجب أن تكون بعد بداية الدوام.',
            'break_start.before' => 'بداية الاستراحة يجب أن تكون قبل نهاية الدوام.',

            'break_end.date_format' => 'نهاية الاستراحة يجب أن تكون بصيغة H:i.',
            'break_end.after' => 'نهاية الاستراحة يجب أن تكون بعد بداية الاستراحة.',
            'break_end.before' => 'نهاية الاستراحة يجب أن تكون قبل نهاية الدوام.',
        ];
    }
}
