<?php

namespace Tests\Feature\Entities;

class AppointmentTest extends BaseEntityTestCase
{
    protected string $entityName = 'appointment';

    public function test_book_success(): void
    {
        $tomorrow = now()->addDays(1)->format('Y-m-d');
        $payload = [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'appointment_type_id' => $this->appointmentType->id,
            'date' => $tomorrow,
            'start_time' => '14:00',
            'visit_reason' => 'Test booking',
        ];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/book'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'book-success', 'POST', '/clinic-system/clinic/appointments/book', $payload, $response);
        $response->assertStatus(201);
    }

    public function test_book_validation(): void
    {
        $payload = [];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/book'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'book-validation', 'POST', '/clinic-system/clinic/appointments/book', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_book_unauthenticated(): void
    {
        $tomorrow = now()->addDays(1)->format('Y-m-d');
        $payload = [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'appointment_type_id' => $this->appointmentType->id,
            'date' => $tomorrow,
            'start_time' => '14:00',
        ];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/book'),
            $payload
        );        $this->saveResult($this->entityName, 'book-unauthenticated', 'POST', '/clinic-system/clinic/appointments/book', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_book_forbidden(): void
    {
        $tomorrow = now()->addDays(1)->format('Y-m-d');
        $payload = [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'appointment_type_id' => $this->appointmentType->id,
            'date' => $tomorrow,
            'start_time' => '14:00',
        ];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/book'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'book-forbidden', 'POST', '/clinic-system/clinic/appointments/book', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_show_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id),
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'show-success', 'GET', '/clinic-system/clinic/appointments/' . $this->appointment->id, [], $response);
        $response->assertStatus(200);
    }

    public function test_show_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/99999'),
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'show-not-found', 'GET', '/clinic-system/clinic/appointments/99999', [], $response);
        $response->assertStatus(404);
    }

    public function test_show_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id)
        );        $this->saveResult($this->entityName, 'show-unauthenticated', 'GET', '/clinic-system/clinic/appointments/' . $this->appointment->id, [], $response);
        $response->assertStatus(401);
    }

    public function test_cancel_success(): void
    {
        $payload = ['cancel_reason' => 'Patient changed mind'];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/cancel'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'cancel-success', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/cancel', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_cancel_not_found(): void
    {
        $payload = ['cancel_reason' => 'No reason'];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/99999/cancel'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'cancel-not-found', 'POST', '/clinic-system/clinic/appointments/99999/cancel', $payload, $response);
        $response->assertStatus(404);
    }

    public function test_cancel_unauthenticated(): void
    {
        $payload = ['cancel_reason' => 'No reason'];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/cancel'),
            $payload
        );        $this->saveResult($this->entityName, 'cancel-unauthenticated', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/cancel', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_cancel_validation(): void
    {
        $payload = ['cancel_reason' => ['invalid']];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/cancel'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'cancel-validation', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/cancel', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_mark_confirmed_success(): void
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed'),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'mark-confirmed-success', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed', [], $response);
        $response->assertStatus(200);
    }

    public function test_mark_confirmed_not_found(): void
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/99999/confirmed'),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'mark-confirmed-not-found', 'POST', '/clinic-system/clinic/appointments/99999/confirmed', [], $response);
        $response->assertStatus(404);
    }

    public function test_mark_confirmed_unauthenticated(): void
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed'),
            []
        );        $this->saveResult($this->entityName, 'mark-confirmed-unauthenticated', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed', [], $response);
        $response->assertStatus(401);
    }

    public function test_mark_confirmed_forbidden(): void
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed'),
            [],
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'mark-confirmed-forbidden', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed', [], $response);
        $response->assertStatus(200);
    }

    public function test_complete_success(): void
    {
        $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete'),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'complete-success', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete', [], $response);
        $response->assertStatus(200);
    }

    public function test_complete_not_confirmed(): void
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete'),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'complete-not-confirmed', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete', [], $response);
        $response->assertStatus(400);
    }

    public function test_complete_not_found(): void
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/99999/complete'),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'complete-not-found', 'POST', '/clinic-system/clinic/appointments/99999/complete', [], $response);
        $response->assertStatus(404);
    }

    public function test_complete_unauthenticated(): void
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete'),
            []
        );        $this->saveResult($this->entityName, 'complete-unauthenticated', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete', [], $response);
        $response->assertStatus(401);
    }

    public function test_complete_forbidden(): void
    {
        $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete'),
            [],
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'complete-forbidden', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete', [], $response);
        $response->assertStatus(200);
    }

    public function test_reschedule_success(): void
    {
        $tomorrow = now()->addDays(1)->format('Y-m-d');
        $payload = [
            'start_time' => '15:00',
            'date' => $tomorrow,
        ];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/reschedule'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'reschedule-success', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/reschedule', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_reschedule_validation(): void
    {
        $payload = [];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/reschedule'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'reschedule-validation', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/reschedule', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_reschedule_not_found(): void
    {
        $tomorrow = now()->addDays(1)->format('Y-m-d');
        $payload = [
            'start_time' => '15:00',
            'date' => $tomorrow,
        ];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/99999/reschedule'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'reschedule-not-found', 'POST', '/clinic-system/clinic/appointments/99999/reschedule', $payload, $response);
        $response->assertStatus(404);
    }

    public function test_reschedule_unauthenticated(): void
    {
        $tomorrow = now()->addDays(1)->format('Y-m-d');
        $payload = [
            'start_time' => '15:00',
            'date' => $tomorrow,
        ];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/reschedule'),
            $payload
        );        $this->saveResult($this->entityName, 'reschedule-unauthenticated', 'POST', '/clinic-system/clinic/appointments/' . $this->appointment->id . '/reschedule', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_patient_appointments_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/patient/' . $this->patient->id),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'patient-appointments-success', 'GET', '/clinic-system/clinic/appointments/patient/' . $this->patient->id, [], $response);
        $response->assertStatus(200);
    }

    public function test_patient_appointments_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/patient/99999'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'patient-appointments-not-found', 'GET', '/clinic-system/clinic/appointments/patient/99999', [], $response);
        $response->assertStatus(200);
    }

    public function test_patient_appointments_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/patient/' . $this->patient->id)
        );        $this->saveResult($this->entityName, 'patient-appointments-unauthenticated', 'GET', '/clinic-system/clinic/appointments/patient/' . $this->patient->id, [], $response);
        $response->assertStatus(401);
    }

    public function test_doctor_appointments_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'doctor-appointments-success', 'GET', '/clinic-system/clinic/appointments/doctor/' . $this->doctor->id, [], $response);
        $response->assertStatus(200);
    }

    public function test_doctor_appointments_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/99999'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'doctor-appointments-not-found', 'GET', '/clinic-system/clinic/appointments/doctor/99999', [], $response);
        $response->assertStatus(200);
    }

    public function test_doctor_appointments_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/' . $this->doctor->id)
        );        $this->saveResult($this->entityName, 'doctor-appointments-unauthenticated', 'GET', '/clinic-system/clinic/appointments/doctor/' . $this->doctor->id, [], $response);
        $response->assertStatus(401);
    }

    public function test_clinic_appointments_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/' . $this->clinic->id),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'clinic-appointments-success', 'GET', '/clinic-system/clinic/appointments/clinic/' . $this->clinic->id, [], $response);
        $response->assertStatus(200);
    }

    public function test_clinic_appointments_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/99999'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'clinic-appointments-not-found', 'GET', '/clinic-system/clinic/appointments/clinic/99999', [], $response);
        $response->assertStatus(200);
    }

    public function test_clinic_appointments_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/' . $this->clinic->id)
        );        $this->saveResult($this->entityName, 'clinic-appointments-unauthenticated', 'GET', '/clinic-system/clinic/appointments/clinic/' . $this->clinic->id, [], $response);
        $response->assertStatus(401);
    }

    public function test_room_appointments_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/room') . '?roomIds[0]=' . $this->room->id,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'room-appointments-success', 'GET', '/clinic-system/clinic/appointments/room?roomIds[0]=' . $this->room->id, ['roomIds' => [$this->room->id]], $response);
        $response->assertStatus(200);
    }

    public function test_room_appointments_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/room') . '?roomIds[0]=' . $this->room->id
        );        $this->saveResult($this->entityName, 'room-appointments-unauthenticated', 'GET', '/clinic-system/clinic/appointments/room?roomIds[0]=' . $this->room->id, ['roomIds' => [$this->room->id]], $response);
        $response->assertStatus(401);
    }

    public function test_doctor_schedule_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/' . $this->doctor->id . '/schedule') . '?date=' . now()->addDays(1)->format('Y-m-d'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'doctor-schedule-success', 'GET', '/clinic-system/clinic/appointments/doctor/' . $this->doctor->id . '/schedule?date=' . now()->addDays(1)->format('Y-m-d'), ['date' => now()->addDays(1)->format('Y-m-d')], $response);
        $response->assertStatus(200);
    }

    public function test_doctor_schedule_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/99999/schedule') . '?date=' . now()->addDays(1)->format('Y-m-d'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'doctor-schedule-not-found', 'GET', '/clinic-system/clinic/appointments/doctor/99999/schedule?date=' . now()->addDays(1)->format('Y-m-d'), ['date' => now()->addDays(1)->format('Y-m-d')], $response);
        $response->assertStatus(400);
    }

    public function test_doctor_schedule_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/' . $this->doctor->id . '/schedule') . '?date=' . now()->addDays(1)->format('Y-m-d')
        );        $this->saveResult($this->entityName, 'doctor-schedule-unauthenticated', 'GET', '/clinic-system/clinic/appointments/doctor/' . $this->doctor->id . '/schedule?date=' . now()->addDays(1)->format('Y-m-d'), ['date' => now()->addDays(1)->format('Y-m-d')], $response);
        $response->assertStatus(401);
    }

    public function test_clinic_schedule_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/' . $this->clinic->id . '/schedule') . '?date=' . now()->addDays(1)->format('Y-m-d'),
            $this->authHeaders($this->secretaryToken)
        );        $this->saveResult($this->entityName, 'clinic-schedule-success', 'GET', '/clinic-system/clinic/appointments/clinic/' . $this->clinic->id . '/schedule?date=' . now()->addDays(1)->format('Y-m-d'), ['date' => now()->addDays(1)->format('Y-m-d')], $response);
        $response->assertStatus(200);
    }

    public function test_clinic_schedule_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/99999/schedule') . '?date=' . now()->addDays(1)->format('Y-m-d'),
            $this->authHeaders($this->secretaryToken)
        );        $this->saveResult($this->entityName, 'clinic-schedule-not-found', 'GET', '/clinic-system/clinic/appointments/clinic/99999/schedule?date=' . now()->addDays(1)->format('Y-m-d'), ['date' => now()->addDays(1)->format('Y-m-d')], $response);
        $response->assertStatus(400);
    }

    public function test_clinic_schedule_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/' . $this->clinic->id . '/schedule') . '?date=' . now()->addDays(1)->format('Y-m-d')
        );        $this->saveResult($this->entityName, 'clinic-schedule-unauthenticated', 'GET', '/clinic-system/clinic/appointments/clinic/' . $this->clinic->id . '/schedule?date=' . now()->addDays(1)->format('Y-m-d'), ['date' => now()->addDays(1)->format('Y-m-d')], $response);
        $response->assertStatus(401);
    }

    public function test_available_slots_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/available-slots') . '?doctor_id=' . $this->doctor->id . '&date=' . now()->addDays(1)->format('Y-m-d'),
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'available-slots-success', 'GET', '/clinic-system/clinic/appointments/available-slots?doctor_id=' . $this->doctor->id . '&date=' . now()->addDays(1)->format('Y-m-d'), ['doctor_id' => $this->doctor->id, 'date' => now()->addDays(1)->format('Y-m-d')], $response);
        $response->assertStatus(200);
    }

    public function test_available_slots_validation(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/available-slots'),
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'available-slots-validation', 'GET', '/clinic-system/clinic/appointments/available-slots', [], $response);
        $response->assertStatus(422);
    }

    public function test_available_slots_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/available-slots') . '?doctor_id=' . $this->doctor->id . '&date=' . now()->addDays(1)->format('Y-m-d')
        );        $this->saveResult($this->entityName, 'available-slots-unauthenticated', 'GET', '/clinic-system/clinic/appointments/available-slots?doctor_id=' . $this->doctor->id . '&date=' . now()->addDays(1)->format('Y-m-d'), ['doctor_id' => $this->doctor->id, 'date' => now()->addDays(1)->format('Y-m-d')], $response);
        $response->assertStatus(401);
    }
}
