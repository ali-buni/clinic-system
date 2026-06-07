<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'user_id'               => $this->user_id,
            'clinic_id'             => $this->clinic_id,
            'room_id'               => $this->room_id,

            'first_name'            => $this->user?->fname,
            'last_name'             => $this->user?->lname,
            'full_name'             => trim($this->user?->fname . ' ' . $this->user?->lname),
            'phone'                 => $this->user?->phone,
            'dob'                   => $this->user?->dob,
            'gender'                => $this->user?->gender,

            'appointment_duration'  => $this->appointment_duration,
            'consultation_fee'      => $this->consultation_fee,
            'bio'                   => $this->bio,

            'created_at'            => $this->created_at?->format('Y-m-d'),
            'updated_at'            => $this->updated_at?->format('Y-m-d'),

            'user'                  => $this->whenLoaded('user'),
            'clinic'                => $this->whenLoaded('clinic'),
            'room'                  => $this->whenLoaded('room'),
        ];
    }
}
