<?php

namespace App\Http\Resources\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pivot = $this->pivot;
        $unitPrice = (float) ($pivot->price ?? $this->price ?? 0);
        $quantity = (int) ($pivot->quantity ?? 0);

        return [
            'item_id' => $this->id,
            'item_name' => $pivot->item_name ?? $this->item_name,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $unitPrice * $quantity,
        ];
    }
}
