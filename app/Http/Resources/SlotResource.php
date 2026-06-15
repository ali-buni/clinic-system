<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Check if resource is array or object
        if (is_array($this->resource)) {
            return [
                'start' => Carbon::parse($this->resource['start'])->format("H:i"),
                'end' => Carbon::parse($this->resource['end'])->format("H:i"),
            ];
        }

        return [
            'start' => Carbon::parse($this->start)->format("H:i"),
            'end' => Carbon::parse($this->end)->format("H:i"),
        ];
    }
}
