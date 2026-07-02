<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class GetRoomsInvoicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_ids'   => 'required|array|min:1',
            'room_ids.*' => 'required|integer|exists:rooms,id',
        ];
    }

    public function messages(): array
    {
        return [
            'room_ids.required'   => 'معرفات الغرف مطلوبة.',
            'room_ids.array'      => 'يجب أن تكون معرفات الغرف مصفوفة.',
            'room_ids.min'        => 'يجب تحديد غرفة واحدة على الأقل.',
            'room_ids.*.integer'  => 'معرف الغرفة يجب أن يكون رقماً.',
            'room_ids.*.exists'   => 'الغرفة غير موجودة.',
        ];
    }
}
