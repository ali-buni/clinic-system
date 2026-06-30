<?php

namespace App\Http\Resources\Disease;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiseaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'icd10_code'     => $this->code,
            'arabic_name'    => $this->ar_name,
            'english_name'   => $this->en_name,
            'description'    => $this->description,
            'nature'         => $this->disease_nature,
            'is_custom'      => (bool) $this->is_custom,
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
