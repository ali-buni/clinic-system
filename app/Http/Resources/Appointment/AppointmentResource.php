<?php

namespace App\Http\Resources\Appointment;

use App\Helpers\ResourceSecurityHelper;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray($request)
    {
        $requester = $request->user();
        $ownerId = $this->patient?->user_id;

        return [
            'id' => $this->id,
            'clinic_id' => $this->clinic_id,
            'doctor' => $this->whenLoaded('doctor', function () {
                return [
                    'id' => $this->doctor->id ?? null,
                    'name' => $this->doctor->user->fname . ' ' . $this->doctor->user->lname ?? null,
                ];
            }),
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id ?? null,
                    'name' => $this->patient->user?->fname . ' ' . $this->patient->user?->lname,
                    'phone' => ResourceSecurityHelper::maskPhone($this->patient->user?->phone, request()->user(), $this->patient?->user_id),
                ];
            }),
            'room' => $this->whenLoaded('room', function () {
                return [
                    'id' => $this->room->id ?? null,
                    'name' => $this->room->name ?? null
                ];
            }),
            'appointment_type' => $this->whenLoaded('type', function () {
                return [
                    'id' => $this->type->id,
                    'ar_name' => $this->type->ar_name,
                    'en_name' => $this->type->en_name,
                ];
            }),
            'date' => Carbon::parse($this->start_time)->format('Y-m-d'),
            'dayOfWeek' => Carbon::parse($this->start_time)->dayOfWeek,
            'start_time' => Carbon::parse($this->start_time)->format('H:i'),
            'end_time' => Carbon::parse($this->end_time)->format('H:i'),
            'status' => $this->status,
            'visit_reason' => ResourceSecurityHelper::gateField('visit_reason', $this->visit_reason, $requester, $ownerId),
            'cancel_reason' => ResourceSecurityHelper::gateField('cancel_reason', $this->cancel_reason, $requester, $ownerId),
            'notes' => $this->notes,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d'),

            'invoices' => $this->whenLoaded('invoices', function () {
                return $this->invoices->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'amount' => $invoice->total_cost,
                        'status' => $invoice->status,
                    ];
                });
            }),
        ];
    }
}
