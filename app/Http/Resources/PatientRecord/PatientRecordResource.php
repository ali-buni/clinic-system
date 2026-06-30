<?php

namespace App\Http\Resources\PatientRecord;

use App\Helpers\ResourceSecurityHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $requester = $request->user();
        $ownerId = $this->patient?->user_id;

        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'appointment_id' => $this->appointment_id,

            'diagnosis_summary' => ResourceSecurityHelper::gateField('diagnosis_summary', $this->diagnosis_summary, $requester, $ownerId),
            'description' => ResourceSecurityHelper::gateField('description', $this->description, $requester, $ownerId),
            'status' => $this->status,
            'notes' => ResourceSecurityHelper::gateField('notes', $this->notes, $requester, $ownerId),

            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'name' => $this->patient->user?->fname . ' ' . $this->patient->user?->lname,
                    'phone' => ResourceSecurityHelper::maskPhone($this->patient->user?->phone, request()->user(), $this->patient?->user_id),
                ];
            }),

            'doctor' => $this->whenLoaded('doctor', function () {
                $this->doctor->loadMissing('user');
                return [
                    'name' => $this->doctor->user?->fname . " " . $this->doctor->user?->lname,
                ];
            }),

            'diseases' => $this->whenLoaded('diseases', function () {
                return $this->diseases->map(function ($disease) {
                    return [
                        'id' => $disease->id,
                        'code' => $disease->code,
                        'en_name' => $disease->en_name,
                        'ar_name' => $disease->ar_name,
                        'status' => $disease->pivot->status,
                        'description' => $disease->description,
                        'disease_nature' => $disease->disease_nature,
                        'severity' => $disease->pivot->severity,
                    ];
                });
            }),

            'prescriptions' => $this->whenLoaded('prescriptions', function () {
                return $this->prescriptions->map(function ($prescription) {
                    return [
                        'id' => $prescription->id,
                        'cost' => $prescription->cost,
                        'issued_at' => $prescription->issued_at?->toDateTimeString(),
                        'valid_until' => $prescription->valid_until?->toDateTimeString(),
                        'notes' => $prescription->notes,

                        'items' => $prescription->items->map(function ($item) {
                            return [
                                ...optional($item->medicine)->only([
                                    'id',
                                    'api_medicine_id',
                                    'en_name',
                                    'ar_name',
                                    'generic_name_en',
                                    'generic_name_ar',
                                    'form',
                                    'strength'
                                ]),
                                'dosage_instruction' => $item->dosage_instruction,
                                'frequency' => $item->frequency,
                                'duration' => $item->duration,
                            ];
                        }),
                    ];
                });
            }),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
