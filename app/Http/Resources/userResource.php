<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class userResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
            $u = $p?->user;
            if ($p) {
                $info['clinic_id'] = $p->clinic_id;
                $info['nationality'] = $p->nationality;
                $info['address'] = $p->address;
                $info['marital_status'] = $p->marital_status;
                $info['emergency_phone'] = $p->emergency_phone;
                $info['allergies'] = $p->allergies;
                $info['chronic_conditions'] = $p->chronic_conditions;
                $info['career'] = $p->career;
                $info['blood_type'] = $p->blood_type;
            }
        }

        return [
            'id'            => $this->id,
            'name'          => $this->fname . ' ' . $this->lname,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'gender'        => $this->gender,
            'dob'           => Carbon::parse($this->dob)->format('Y-m-d'),
            'profile_image' => $this->profile_image,
            'created'       => $this->created_at->format('Y-m-d'),
            'role'          => $role,
            ...$info,
        ];
    }
}
