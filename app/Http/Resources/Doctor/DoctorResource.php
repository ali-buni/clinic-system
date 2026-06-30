<?php

namespace App\Http\Resources\Doctor;

use App\Helpers\ResourceSecurityHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $requester = $request->user();
        $ownerId = $this->user_id;

        return [
            'id'                    => $this->id,
            'user_id'               => $this->user_id,
            'clinic_id'             => $this->clinic_id,
            'room_id'               => $this->room_id,
            'name'                  => $this->user?->fname . ' ' . $this->user?->lname,
            'phone'                 => ResourceSecurityHelper::maskPhone($this->user?->phone, $requester, $ownerId),
            'email'                 => ResourceSecurityHelper::maskEmail($this->user?->email, $requester, $ownerId),
            'dob'                   => $this->user?->dob,
            'gender'                => $this->user?->gender,
            'created_at'            => $this->created_at?->format('Y-m-d'),
            'appointment_duration'  => $this->appointment_duration,
            'consultation_fee'      => $this->consultation_fee,
            'bio'                   => $this->bio,
            'specialties'           => $this->specialties->map(function ($specialty) {
                return $specialty->only(['ar_name', 'en_name']);
            })->values(),
        ];
    }
}
