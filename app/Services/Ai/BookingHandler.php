<?php

namespace App\Services\Ai;

use App\Models\Appointment_type;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Services\AppointmentService;
use Illuminate\Support\Facades\Log;

class BookingHandler
{
    public function __construct(protected AppointmentService $appointmentService) {}

    public function getSpecialtyWithDoctors(int $specialtyId, ?int $clinicId, string $date): array
    {
        $specialty = Specialty::find($specialtyId);
        if (!$specialty) {
            return ['error' => 'Specialty not found', 'next_step' => 'error'];
        }

        return array_merge([
            'specialty' => [
                'id' => $specialty->id,
                'en_name' => $specialty->en_name,
                'ar_name' => $specialty->ar_name,
            ],
        ], $this->getDoctorsBySpecialty($specialtyId, $clinicId, $date), ['next_step' => 'select_doctor']);
    }

    public function getDoctorsBySpecialty(int $specialtyId, ?int $clinicId, string $date): array
    {
        $query = Doctor::whereHas('specialties', fn($q) => $q->where('specialty_id', $specialtyId))
            ->with('user');

        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        $doctors = $query->get();

        if ($doctors->isEmpty()) {
            return ['error' => 'No doctors found for this specialty', 'next_step' => 'error'];
        }

        $result = [];
        foreach ($doctors as $doctor) {
            $slots = [];
            try {
                $slots = $this->appointmentService->getAvailableSlots($doctor->id, $date);
                $slots = array_map(fn($s) => [
                    'start' => date('H:i', strtotime($s['start'])),
                    'end' => date('H:i', strtotime($s['end'])),
                ], $slots);
            } catch (\Throwable $e) {
                Log::warning("getAvailableSlots failed for doctor {$doctor->id}", ['error' => $e->getMessage()]);
            }

            $result[] = [
                'id' => $doctor->id,
                'name' => $doctor->user?->fname . ' ' . $doctor->user?->lname ?? 'Unknown',
                'bio' => $doctor->bio,
                'fee' => $doctor->consultation_fee,
                'available_slots' => $slots,
            ];
        }

        return ['doctors' => $result];
    }

    public function getDoctorSlots(int $doctorId, string $date): array
    {
        $doctor = Doctor::with('user')->find($doctorId);
        if (!$doctor) {
            return ['error' => 'Doctor not found', 'next_step' => 'error'];
        }

        $slots = [];
        try {
            $rawSlots = $this->appointmentService->getAvailableSlots($doctorId, $date);
            $slots = array_map(fn($s) => [
                'start' => date('H:i', strtotime($s['start'])),
                'end' => date('H:i', strtotime($s['end'])),
            ], $rawSlots);
        } catch (\Throwable $e) {
            Log::warning("getAvailableSlots failed for doctor {$doctorId}", ['error' => $e->getMessage()]);
        }

        return [
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->user?->fname . ' ' . $doctor->user?->lname ?? 'Unknown',
            ],
            'date' => $date,
            'available_slots' => $slots,
            'next_step' => 'select_time',
        ];
    }

    public function bookAppointment(
        int $doctorId,
        string $date,
        string $time,
        int $patientId,
        ?int $clinicId = null,
        ?int $typeId = null,
        ?string $reason = null,
    ): array {
        $doctor = Doctor::with('user')->find($doctorId);
        if (!$doctor) {
            return ['error' => 'Doctor not found', 'next_step' => 'error'];
        }

        $typeId = $typeId ?? 1;
        $appointmentType = Appointment_type::find($typeId);
        $slotMultiplier = $appointmentType?->types ?? 1;
        $slotMinutes = $doctor->appointment_duration ?? 30;
        $totalMinutes = $slotMultiplier * $slotMinutes;

        $startTime = $time;
        $endTime = date('H:i', strtotime($time) + $totalMinutes * 60);

        try {
            $appointment = $this->appointmentService->bookAppointment(
                doctorId: $doctorId,
                date: $date,
                startTime: $startTime,
                endTime: $endTime,
                attributes: array_filter([
                    'patient_id' => $patientId,
                    'clinic_id' => $clinicId ?? $doctor->clinic_id,
                    'room_id' => $doctor->room_id,
                    'appointment_type_id' => $typeId,
                    'visit_reason' => $reason,
                ]),
            );

            Log::channel('structured')->info('AI appointment booked successfully', [
                'appointment_id' => $appointment->id, 'doctor_id' => $doctorId, 'patient_id' => $patientId,
            ]);

            return [
                'appointment' => [
                    'id' => $appointment->id,
                    'doctor_name' => $doctor->user?->fname . ' ' . $doctor->user?->lname ?? 'Unknown',
                    'date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $appointment->status,
                ],
                'next_step' => 'complete',
            ];
        } catch (\Throwable $e) {
            Log::channel('structured')->error('AppointmentAssistant booking failed', [
                'doctor_id' => $doctorId, 'patient_id' => $patientId, 'error' => $e->getMessage(),
            ]);
            return ['error' => $e->getMessage(), 'next_step' => 'retry'];
        }
    }
}
