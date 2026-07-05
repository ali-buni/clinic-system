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
            'deleted_at'            => $this->deleted_at?->format('Y-m-d H:i:s'),
            'room'                  => $this->whenLoaded('room', function () {
                return $this->room ? [
                    'id'   => $this->room->id,
                    'name' => $this->room->name,
                ] : null;
            }),
            'specialties'           => $this->specialties->map(function ($specialty) {
                return [
                    'id'      => $specialty->id,
                    'ar_name' => $specialty->ar_name,
                    'en_name' => $specialty->en_name,
                    'pivot'   => [
                        'is_primary' => (bool) ($specialty->pivot->is_primary ?? false),
                    ],
                ];
            })->values(),
        ];
    }
}
