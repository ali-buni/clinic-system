<?php

namespace App\Http\Resources\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'ar_name'   => $this->ar_name,
            'en_name'   => $this->en_name,
            'type'      => $this->type->value,
            'is_active' => $this->is_active,
        ];
    }
}
