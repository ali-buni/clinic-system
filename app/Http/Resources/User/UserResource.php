<?php

namespace App\Http\Resources\User;

use App\Helpers\ResourceSecurityHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $requester = $request->user();
        $ownerId = $this->id;
        $role = $this->getRoleNames()->first() ?? 'secretary';
        $info = [];
        $isShow = request()->route()?->getActionMethod() === 'show';

        if ($role === 'doctor') {
            $doctor = $this->doctorProfile;
            if ($doctor) {
                $info['specialties'] = $doctor->specialties->map(fn($s) => $s->only(['ar_name', 'en_name']))->values();
                $info['appointment_duration'] = $doctor->appointment_duration;
                $info['bio'] = $doctor->bio;
                $info['consultation_fee'] = $doctor->consultation_fee;
            }
        }

        if ($role === 'patient') {
            $p = $this->patientProfile;
            if ($p) {
                $info['clinic_id'] = $p->clinic_id;
                $info['nationality'] = ResourceSecurityHelper::gateField('nationality', $p->nationality, $requester, $ownerId);
                $info['address'] = ResourceSecurityHelper::gateField('address', $p->address, $requester, $ownerId);
                $info['marital_status'] = $p->marital_status;
                $info['emergency_phone'] = ResourceSecurityHelper::gateField('emergency_phone', $p->emergency_phone, $requester, $ownerId);
                $info['allergies'] = ResourceSecurityHelper::gateField('allergies', $p->allergies, $requester, $ownerId);
                $info['chronic_conditions'] = ResourceSecurityHelper::gateField('chronic_conditions', $p->chronic_conditions, $requester, $ownerId);
                $info['career'] = $p->career;
                $info['blood_type'] = ResourceSecurityHelper::gateField('blood_type', $p->blood_type, $requester, $ownerId);
            }
        }

        return [
            'id'            => $this->id,
            'name'          => $this->fname . ' ' . $this->lname,
            'phone'         => ResourceSecurityHelper::maskPhone($this->phone, $requester, $ownerId),
            'email'         => ResourceSecurityHelper::maskEmail($this->email, $requester, $ownerId),
            'gender'        => $this->gender,
            'dob'           => Carbon::parse($this->dob)->format('Y-m-d'),
            'profile_image' => $this->profile_image,
            'created'       => $this->created_at->format('Y-m-d'),
            'role'          => $role,
            ...$info,
        ];
    }
}
