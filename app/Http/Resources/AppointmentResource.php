<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray($request)
    {
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
                    'name' => $this->patient->fname . ' ' . $this->patient->lname ?? null,
                    'phone' => $this->patient->phone ?? null
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
            'date' => Carbon::parse($this->start_time)->format('Y:m:d'),
            'dayOfWeek' => Carbon::parse($this->start_time)->dayOfWeek,
            'start_time' => Carbon::parse($this->start_time)->format('h:i'),
            'end_time' => Carbon::parse($this->end_time)->format('h:i'),
            'status' => $this->status,
            'visit_reason' => $this->visit_reason,
            'cancel_reason' => $this->cancel_reason,
            'notes' => $this->notes,
            'created_at' => Carbon::parse($this->created_at)->format('Y:m:d'),
        ];
    }
}
