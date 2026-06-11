<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'patient_id'        => $this->patient_id,
            'doctor_id'         => $this->doctor_id,
            'clinic_id'         => $this->clinic_id,
            'appointment_id'    => $this->appointment_id,
            'diagnosis_summary' => $this->diagnosis_summary,
            'description'       => $this->description,
            'status'            => $this->status,
            'notes'             => $this->notes,

            'diseases'          => $this->whenLoaded('diseases', fn() => $this->diseases->map(fn($d) => [
                'id'      => $d->id,
                'code'    => $d->code,
                'ar_name' => $d->ar_name,
                'en_name' => $d->en_name,
            ])),

            'prescriptions'     => $this->whenLoaded('prescriptions', fn() => $this->prescriptions->map(fn($p) => [
                'id'         => $p->id,
                'issued_at'  => $p->issued_at,
                'valid_until'=> $p->valid_until,
                'items'      => $p->items->map(fn($item) => [
                    'medicine_id'        => $item->medicine_id,
                    'dosage_instruction' => $item->dosage_instruction,
                    'frequency'          => $item->frequency,
                    'duration'           => $item->duration,
                ])
            ])),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
