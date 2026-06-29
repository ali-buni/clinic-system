<?php

namespace App\Http\Resources;

use App\Helpers\ResourceSecurityHelper;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientMedicalHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        $requester = $request->user();
        $user = $this->user;
        $ownerId = $this->user_id;

        return [
            'id'            => $this->id,
            'name'          => $user ? trim(($user->fname ?? '') . ' ' . ($user->lname ?? '')) : null,
            'phone'         => ResourceSecurityHelper::maskPhone($user?->phone, $requester, $ownerId),
            'email'         => ResourceSecurityHelper::maskEmail($user?->email, $requester, $ownerId),
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
                'visit_reason' => ResourceSecurityHelper::gateField('visit_reason', $a->visit_reason, $requester, $ownerId),
            ]),

            'records' => $this->records->map(fn($r) => [
                'id'                => $r->id,
                'doctor_name'       => $r->doctor?->user?->fname . ' ' . $r->doctor?->user?->lname,
                'diagnosis_summary' => ResourceSecurityHelper::gateField('diagnosis_summary', $r->diagnosis_summary, $requester, $ownerId),
                'description'       => ResourceSecurityHelper::gateField('description', $r->description, $requester, $ownerId),
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
                    'issued_at' => Carbon::parse($p->issued_at)?->format('Y-m-d'),
                    'valid_until' => Carbon::parse($p->valid_until)?->format('Y-m-d'),
                    'items'     => $p->items->map(fn($i) => [
                        'id'                 => $i->id,
                        'medicine'           => $i->medicine?->en_name,
                        'medicine_ar'        => $i->medicine?->ar_name,
                        'dosage_instruction' => $i->dosage_instruction,
                        'frequency'          => $i->frequency,
                        'duration'           => $i->duration,
                    ]),
                ]),
                'created_at'        => Carbon::parse($r->created_at)?->format('Y-m-d'),
            ]),

            'invoices' => $this->invoices->map(fn($i) => [
                'id'             => $i->id,
                'invoice_number' => $i->invoice_number,
                'status'         => $i->status,
                'total_cost'     => $i->total_cost,
                'issued_at'      => Carbon::parse($i->created_at)?->format('Y-m-d'),
            ]),
        ];
    }
}
