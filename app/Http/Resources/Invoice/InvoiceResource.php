<?php

namespace App\Http\Resources\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalCost = (float) $this->total_cost;
        $remaining = $this->getRemainingBalance();
        $paid = $totalCost - $remaining;

        return [
            'id'               => $this->id,
            'invoice_number'   => $this->invoice_number,
            'status'           => $this->status,
            'total_cost'       => $totalCost,
            'paid_amount'      => number_format($paid, 2, '.', ''),
            'remaining_amount' => number_format($remaining, 2, '.', ''),
            'description'      => $this->description,
            'clinic_id'        => $this->clinic_id,
            'patient_id'       => $this->patient_id,
            'appointment_id'   => $this->appointment_id,
            'created_at'       => $this->created_at?->toDateTimeString(),
            'updated_at'       => $this->updated_at?->toDateTimeString(),

            $this->mergeWhen($this->relationLoaded('payments'), [
                'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            ]),

            $this->mergeWhen($this->relationLoaded('items'), [
                'items' => $this->whenLoaded('items'),
            ]),
        ];
    }
}
