<?php

namespace App\Http\Requests\Invoice;

use App\Models\Invoice;
use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'nullable|string|max:2000',
            'updated_items' => 'nullable|array|min:1',
            'updated_items.*.item_id' => [
                'required_with:updated_items',
                'integer',
                'exists:items,id',
                function ($attribute, $value, $fail) {
                    $invoice = Invoice::find($this->route('invoiceId'));
                    if (! $invoice) {
                        return;
                    }
                    $item = Item::find($value);
                    if ($item && $item->clinic_id !== null && (int) $item->clinic_id !== (int) $invoice->clinic_id) {
                        $fail('The item does not belong to the invoice clinic.');
                    }
                },
            ],
            'updated_items.*.quantity' => 'required_with:updated_items|integer|min:1',
            'updated_items.*.price' => 'required_with:updated_items|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'updated_items.*.item_id.required_with' => 'معرف العنصر مطلوب عند تحديث العناصر.',
            'updated_items.*.item_id.exists' => 'العنصر غير موجود.',
            'updated_items.*.quantity.min' => 'الكمية يجب أن تكون 1 على الأقل.',
            'updated_items.*.price.min' => 'السعر يجب أن يكون 0 أو أكثر.',
        ];
    }
}
