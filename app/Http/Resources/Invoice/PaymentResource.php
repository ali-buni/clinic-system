<?php

namespace App\Http\Resources\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'invoice_id'        => $this->invoice_id,
            'payment_method'    => $this->whenLoaded('paymentMethod', fn() => [
                'id'      => $this->paymentMethod->id,
                'ar_name' => $this->paymentMethod->ar_name,
                'en_name' => $this->paymentMethod->en_name,
            ]),
            'amount'            => (float) $this->amount,
            'refunded_amount'   => (float) ($this->refunded_amount ?? 0),
            'refundable_amount' => (float) $this->getRefundableAmount(),
            'paid_at'           => $this->paid_at?->toDateTimeString(),
            'created_at'        => $this->created_at?->toDateTimeString(),
        ];
    }
}
