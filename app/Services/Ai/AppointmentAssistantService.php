<?php

namespace App\Services\Ai;

class AppointmentAssistantService
{
    public function __construct(
        protected SpecialtyMatcher $matcher,
        protected BookingHandler $booking,
    ) {}

    public function processRequest(array $data): array
    {
        $hasQuery = !empty($data['query']);
        $hasSpecialty = !empty($data['specialty_id']);
        $hasDoctor = !empty($data['doctor_id']);
        $hasDate = !empty($data['date']);
        $hasTime = !empty($data['start_time']);
        $hasPatient = !empty($data['patient_id']);
        $clinicId = $data['clinic_id'] ?? auth()->user()?->clinic_id;
        $date = $data['date'] ?? now()->format('Y-m-d');

        if ($hasQuery && !$hasSpecialty && !$hasDoctor) {
            return $this->matcher->suggest($data['query']);
        }

        if ($hasSpecialty && !$hasDoctor) {
            return $this->booking->getSpecialtyWithDoctors((int) $data['specialty_id'], $clinicId, $date);
        }

        if ($hasDoctor && $hasDate && !$hasTime) {
            return $this->booking->getDoctorSlots((int) $data['doctor_id'], $date);
        }

        if ($hasDoctor && $hasDate && $hasTime && $hasPatient) {
            return $this->booking->bookAppointment(
                doctorId: (int) $data['doctor_id'],
                date: $date,
                time: $data['start_time'],
                patientId: (int) $data['patient_id'],
                clinicId: $clinicId,
                typeId: isset($data['appointment_type_id']) ? (int) $data['appointment_type_id'] : null,
                reason: $data['visit_reason'] ?? null,
            );
        }

        return [
            'error' => 'Tell us what you need (symptoms, specialty, or doctor name) to get started.',
            'next_step' => 'provide_input',
        ];
    }
}
