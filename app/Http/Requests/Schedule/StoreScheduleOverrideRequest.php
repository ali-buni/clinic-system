<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleOverrideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'doctor_id'     => 'required|integer|exists:doctors,id',
            'override_date' => 'required|date_format:Y-m-d',
            'override_type' => 'nullable|string|max:50',
            'start_time'    => 'nullable|date_format:H:i',
            'end_time'      => 'nullable|date_format:H:i|after:start_time',
            'reason'        => 'nullable|string|max:500',
            'is_closed'     => 'sometimes|boolean',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(function ($rule) {
                return str_replace('required', 'sometimes', $rule);
            }, $rules);

            $rules['doctor_id'] = str_replace('sometimes', 'required', $rules['doctor_id']);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'doctor_id.required'     => 'الطبيب مطلوب.',
            'doctor_id.exists'       => 'الطبيب غير موجود.',
            'override_date.required' => 'التاريخ مطلوب.',
            'override_date.date_format' => 'صيغة التاريخ يجب أن تكون Y-m-d.',
            'start_time.date_format' => 'وقت البداية يجب أن يكون بصيغة H:i.',
            'end_time.date_format'   => 'وقت النهاية يجب أن يكون بصيغة H:i.',
            'end_time.after'         => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
            'reason.max'             => 'السبب يجب ألا يتجاوز 500 حرف.',
            'is_closed.boolean'      => 'حقل الإغلاق يجب أن يكون قيمته صح أو خطأ.',
        ];
    }
}
