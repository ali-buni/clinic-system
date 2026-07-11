<?php

namespace App\Http\Resources\Doctor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientDoctorSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => trim(($this->user?->fname ?? '').' '.($this->user?->lname ?? '')),
            'consultation_fee' => $this->consultation_fee,
            'appointment_duration' => $this->appointment_duration,
            'gender' => $this->user?->gender,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'clinic' => $this->whenLoaded('clinic', fn () => [
                'id' => $this->clinic->id,
                'title' => $this->clinic->title,
                'location' => $this->clinic->location,
            ]),
            'specialties' => $this->specialties->map(fn ($s) => [
                'id' => $s->id,
                'en_name' => $s->en_name,
                'ar_name' => $s->ar_name,
                'is_primary' => (bool) ($s->pivot->is_primary ?? false),
            ])->values(),
        ];
    }
}
