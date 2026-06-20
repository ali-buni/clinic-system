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
        $data = [];

        if ($role === 'doctor') {
            $doctor = $this->doctorProfile;
            if ($doctor) {
                $data['specialties'] = $doctor->specialties->map(fn($s) => $s->only(['ar_name', 'en_name']))->values();
                $data['appointment_duration'] = $doctor->appointment_duration;
                $data['bio'] = $doctor->bio;
                $data['consultation_fee'] = $doctor->consultation_fee;
            }
        }

        if ($role === 'patient') {
            $profile = $this->patientProfile;
            if ($profile) {
                $data['patient_info'] = new PatientInfoResource($profile);
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
            ...$data,
        ];
    }
}
