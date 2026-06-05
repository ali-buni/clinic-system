<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => trim(($this->fname ?? '') . ' ' . ($this->lname ?? '')),
            'phone' => $this->phone,
            'dob' => $this->dob,
            'clinic_id' => $this->clinic_id,
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'address' => $this->address,
            'marital_status' => $this->marital_status,
            'emergency_phone' => $this->emergency_phone,
            'allergies' => $this->allergies,
            'chronic_conditions' => $this->chronic_conditions,
            'career' => $this->career,
            'blood_type' => $this->blood_type,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d'),
        ];
    }
}
