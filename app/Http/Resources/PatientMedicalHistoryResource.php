<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientMedicalHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $this->user;

        return [
            'id'            => $this->id,
            'name'          => $user ? trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) : null,
            'phone'         => $user?->phone,
            'email'         => $user?->email,
            'gender'        => $user?->gender,
            'dob'           => $user?->dob ? Carbon::parse($user->dob)->format('Y-m-d') : null,
            'profile_image' => $user?->profile_image,
            'clinic_id'     => $this->clinic_id,

            'appointments' => $this->appointments->map(fn($a) => [
                'id'          => $a->id,
                'doctor_name' => $a->doctor?->user?->fname . ' ' . $a->doctor?->user?->lname,
                'type'        => $a->appointmentType?->en_name,
                'start_time'  => $a->start_time?->format('Y-m-d H:i'),
                'end_time'    => $a->end_time?->format('Y-m-d H:i'),
                'status'      => $a->status,
                'visit_reason' => $a->visit_reason,
            ]),

            'records' => $this->records->map(fn($r) => [
                'id'                => $r->id,
                'doctor_name'       => $r->doctor?->user?->fname . ' ' . $r->doctor?->user?->lname,
                'diagnosis_summary' => $r->diagnosis_summary,
                'description'       => $r->description,
                'status'            => $r->status,
                'diseases'          => $r->diseases->map(fn($d) => [
                    'id'       => $d->id,
                    'en_name'  => $d->en_name,
                    'ar_name'  => $d->ar_name,
                    'icd_code' => $d->icd_code,
                    'status'   => $d->pivot?->status,
                    'severity' => $d->pivot?->severity,
                ]),
                'prescriptions'     => $r->prescriptions->map(fn($p) => [
                    'id'        => $p->id,
                    'doctor'    => $p->doctor?->user?->fname . ' ' . $p->doctor?->user?->lname,
                    'cost'      => $p->cost,
                    'issued_at' => $p->issued_at?->format('Y-m-d'),
                    'valid_until' => $p->valid_until?->format('Y-m-d'),
                    'items'     => $p->items->map(fn($i) => [
                        'id'                 => $i->id,
                        'medicine'           => $i->medicine?->en_name,
                        'medicine_ar'        => $i->medicine?->ar_name,
                        'dosage_instruction' => $i->dosage_instruction,
                        'frequency'          => $i->frequency,
                        'duration'           => $i->duration,
                    ]),
                ]),
                'created_at'        => $r->created_at?->format('Y-m-d'),
            ]),

            'invoices' => $this->invoices->map(fn($i) => [
                'id'             => $i->id,
                'invoice_number' => $i->invoice_number,
                'status'         => $i->status,
                'total_cost'     => $i->total_cost,
                'issued_at'      => $i->created_at?->format('Y-m-d'),
            ]),
        ];
    }
}
