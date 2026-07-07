<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetInvoicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'    => ['nullable', 'string', Rule::in(['draft', 'issued', 'partially_paid', 'paid', 'void', 'refunded'])],
            'clinic_id' => 'required|integer|exists:clinics,id',
            'date_from' => 'nullable|date|date_format:Y-m-d',
            'date_to'   => 'nullable|date|date_format:Y-m-d|after_or_equal:date_from',
            'per_page'  => 'nullable|integer|min:1|max:100',
            'page'      => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in'            => 'الحالة المرسلة غير معرّفة بالنظام.',
            'date_from.date_format' => 'صيغة تاريخ البدء يجب أن تكون YYYY-MM-DD.',
            'date_to.date_format'   => 'صيغة تاريخ الانتهاء يجب أن تكون YYYY-MM-DD.',
            'date_to.after_or_equal' => 'تاريخ الانتهاء لا يمكن أن يكون قبل تاريخ البدء.',
            'per_page.max'          => 'الحد الأقصى لعدد العناصر في الصفحة هو 100.',
            'per_page.min'          => 'الحد الأدنى لعدد العناصر في الصفحة هو 1.',
            'page.min'              => 'رقم الصفحة يجب أن يكون 1 أو أكبر.',
        ];
    }
}
