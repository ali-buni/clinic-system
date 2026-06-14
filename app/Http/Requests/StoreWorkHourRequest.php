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

        return [
            'day_of_week'          => "required|integer|between:0,6",
            'start_time'           => "required|date_format:H:i",
            'end_time'             => "required|date_format:H:i|after:start_time",
            'is_active'            => 'sometimes|boolean',
            'max_patients_per_day' => 'sometimes|integer|min:1',
            'break_start'          => 'sometimes|nullable|date_format:H:i|after:start_time|before:end_time',
            'break_end'            => 'sometimes|nullable|date_format:H:i|after:break_start|before:end_time',
        ];
    }

    /**
     * تخصيص رسائل الخطأ عشان الفرونت إند يفهم شو الطبخة
     */
    public function messages(): array
    {
        return [
            'end_time.after' => 'وقت نهاية الدوام يجب أن يكون بعد وقت البداية.',
            'break_start.after' => 'بداية الاستراحة يجب أن تكون بعد بداية الدوام.',
            'break_start.before' => 'بداية الاستراحة يجب أن تكون قبل نهاية الدوام.',
            'break_end.after' => 'نهاية الاستراحة يجب أن تكون بعد بداية الاستراحة.',
            'break_end.before' => 'نهاية الاستراحة يجب أن تكون قبل نهاية الدوام.',
        ];
    }
}
