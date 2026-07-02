<?php

namespace App\Http\Resources\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Invoice\PaymentResource;
use App\Http\Resources\Invoice\InvoiceItemResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalPaid = (float) ($this->total_paid ?? 0);
        $totalCost = (float) $this->total_cost;
        $remaining = $totalCost - $totalPaid;

        return [
            'id'               => $this->id,
            'invoice_number'   => $this->invoice_number,
            'status'           => $this->status,
            'total_cost'       => $totalCost,
            'paid_amount'      => $totalPaid,
            'remaining_amount' => max(0, $remaining),
            'description'      => $this->description,
            'clinic_id'        => $this->clinic_id,
            'patient_id'       => $this->patient_id,
            'appointment_id'   => $this->appointment_id,
            'created_at'       => $this->created_at?->toDateTimeString(),

            $this->mergeWhen($this->relationLoaded('completedPayments'), [
                'payments' => PaymentResource::collection($this->whenLoaded('completedPayments')),
            ]),

            $this->mergeWhen($this->relationLoaded('items'), [
                'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            ]),
        ];
    }
}
