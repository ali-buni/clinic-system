<?php

namespace App\Http\Requests\Invoice;

use App\Models\Appointment;
use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => [
                'required',
                'integer',
                'exists:appointments,id',
                function ($attribute, $value, $fail) {
                    $appointment = Appointment::find($value);
                    if ($appointment && $appointment->invoices()->count() >= 2) {
                        $fail('This appointment already has the maximum number of invoices (2).');
                    }
                },
            ],
            'description' => 'nullable|string|max:2000',
            'invoice_items' => 'required|array|min:1',
            'invoice_items.*.item_id' => [
                'required',
                'integer',
                'exists:items,id',
                function ($attribute, $value, $fail) {
                    $appointment = Appointment::find($this->input('appointment_id'));
                    if (! $appointment) {
                        return;
                    }
                    $item = Item::find($value);
                    if ($item && $item->clinic_id !== null && (int) $item->clinic_id !== (int) $appointment->clinic_id) {
                        $fail('The item does not belong to the appointment clinic.');
                    }
                },
            ],
            'invoice_items.*.quantity' => 'required|integer|min:1',
            'invoice_items.*.price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_id.required' => 'معرف الموعد مطلوب.',
            'invoice_items.required' => 'يجب إضافة عنصر واحد على الأقل للفاتورة.',
            'invoice_items.*.item_id.required' => 'معرف العنصر مطلوب.',
            'invoice_items.*.quantity.min' => 'الكمية يجب أن تكون 1 على الأقل.',
            'invoice_items.*.price.min' => 'السعر يجب أن يكون 0 أو أكثر.',
        ];
    }
}
