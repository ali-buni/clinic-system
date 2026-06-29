<?php

namespace App\Http\Requests;

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
            'clinic_id'                 => 'required|integer',
            'patient_id'                => 'required|integer',
            'appointment_id'            => 'required|integer',
            'description'               => 'nullable|string',
            'invoice_items'             => 'required|array|min:1',
            'invoice_items.*.item_id'   => 'required|integer',
            'invoice_items.*.quantity'  => 'required|integer|min:1',
            'invoice_items.*.price'     => 'required|numeric|min:0',
        ];
    }
}