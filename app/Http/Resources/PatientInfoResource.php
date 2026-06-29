<?php

namespace App\Http\Resources;

use App\Helpers\ResourceSecurityHelper;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientInfoResource extends JsonResource
{
    public function toArray($request)
    {
        $requester = $request->user();
        $user = $this->user;
        $ownerId = $this->user_id;

        $data = [];
        $route = request()->route();
        $action = $route?->getActionMethod();
        if ($action === 'show') {
            $data = [
                'nationality'        => ResourceSecurityHelper::gateField('nationality', $this->nationality, $requester, $ownerId),
                'address'            => ResourceSecurityHelper::gateField('address', $this->address, $requester, $ownerId),
                'marital_status'     => $this->marital_status,
                'emergency_phone'    => ResourceSecurityHelper::gateField('emergency_phone', $this->emergency_phone, $requester, $ownerId),
                'allergies'          => ResourceSecurityHelper::gateField('allergies', $this->allergies, $requester, $ownerId),
                'chronic_conditions' => ResourceSecurityHelper::gateField('chronic_conditions', $this->chronic_conditions, $requester, $ownerId),
                'career'             => $this->career,
                'blood_type'         => ResourceSecurityHelper::gateField('blood_type', $this->blood_type, $requester, $ownerId),
                'phone'              => ResourceSecurityHelper::maskPhone($user?->phone, $requester, $ownerId),
                'email'              => ResourceSecurityHelper::maskEmail($user?->email, $requester, $ownerId),
                'dob'                => $user?->dob ? Carbon::parse($user->dob)->format('Y-m-d') : null,
                'created_at'         => Carbon::parse($this->created_at)->format('Y-m-d'),
            ];
        }

        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'name'          => $user ? trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) : null,
            'gender'        => $user?->gender,
            'profile_image' => $user?->profile_image,
            'clinic_id'     => $this->clinic_id,
            ...$data,
        ];
    }
}
