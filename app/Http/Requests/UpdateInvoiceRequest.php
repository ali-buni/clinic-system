<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // الصلاحيات معزولة بالـ Middleware المركزية كالعادة
    }

    public function rules(): array
    {
        return [
            'invoice_id'                 => 'required|integer|exists:invoices,id',
            'description'                => 'nullable|string',
            'updated_items'              => 'nullable|array|min:1',
            'updated_items.*.item_id'    => 'required_with:updated_items|integer',
            'updated_items.*.quantity'   => 'required_with:updated_items|integer|min:1',
            'updated_items.*.price'      => 'required_with:updated_items|numeric|min:0',
        ];
    }
}