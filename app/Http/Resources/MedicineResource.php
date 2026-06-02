<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'api_id'          => $this->api_medicine_id,
            'arabic_name'     => $this->ar_name,
            'english_name'    => $this->en_name,
            'generic_arabic'  => $this->generic_name_ar,
            'generic_english' => $this->generic_name_en,
            'strength'        => $this->strength,
            'form'            => $this->form,
            'is_custom_added' => (bool) $this->is_custom,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
