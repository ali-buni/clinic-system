<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientInfoResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $this->user;

        $data = [];
        $route = request()->route();
        $action = $route?->getActionMethod();
        if ($action === 'show') {
            $data = [
                'nationality'        => $this->nationality,
                'address'            => $this->address,
                'marital_status'     => $this->marital_status,
                'emergency_phone'    => $this->emergency_phone,
                'allergies'          => $this->allergies,
                'chronic_conditions' => $this->chronic_conditions,
                'career'             => $this->career,
                'blood_type'         => $this->blood_type,
            ];
        }

        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'name'          => $user ? trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) : null,
            'phone'         => $user?->phone,
            'email'         => $user?->email,
            'dob'           => $user?->dob ? Carbon::parse($user->dob)->format('Y-m-d') : null,
            'gender'        => $user?->gender,
            'profile_image' => $user?->profile_image,
            'clinic_id'     => $this->clinic_id,
            'created_at'    => Carbon::parse($this->created_at)->format('Y-m-d'),
            ...$data,
        ];
    }
}
