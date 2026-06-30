<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class AppointmentTest extends TestCase
{
    const DOMAIN = 'appointments';

    public function test_book_success()
    {
        $date = now()->addWeek()->format('Y-m-d');

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/book'),
            [
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'clinic_id' => $this->clinic->id,
                'appointment_type_id' => $this->appointmentType->id,
                'start_time' => '11:00',
                'date' => $date,
                'visit_reason' => 'Routine checkup',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'book-success', $response);
    }

    public function test_book_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/book'),
            [
                'patient_id' => '',
                'doctor_id' => '',
                'clinic_id' => '',
                'appointment_type_id' => '',
                'start_time' => 'invalid',
                'date' => 'invalid',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'book-error-validation', $response);
    }

    public function test_book_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/clinic/appointments/book'), [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'appointment_type_id' => $this->appointmentType->id,
            'start_time' => '11:00',
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'book-error-unauthorized', $response);
    }

    public function test_show_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'show-success', $response);
    }

    public function test_show_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/99999'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'show-error-not-found', $response);
    }

    public function test_cancel_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/cancel'),
            ['cancel_reason' => 'Patient requested cancellation'],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'cancel-success', $response);
    }

    public function test_cancel_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/99999/cancel'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'cancel-error-not-found', $response);
    }

    public function test_mark_confirmed_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'mark-confirmed-success', $response);
    }

    public function test_complete_success()
    {
        $this->appointment->update(['status' => 'confirmed']);

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'complete-success', $response);
    }

    public function test_complete_not_confirmed()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(400);
        $this->saveFixture(self::DOMAIN, 'complete-error-not-confirmed', $response);
    }

    public function test_reschedule_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/reschedule'),
            [
                'start_time' => '14:00',
                'date' => now()->addWeek()->format('Y-m-d'),
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'reschedule-success', $response);
    }

    public function test_reschedule_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/reschedule'),
            [
                'start_time' => 'invalid',
                'date' => 'invalid',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'reschedule-error-validation', $response);
    }

    public function test_patient_appointments_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/patient/' . $this->patient->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'patient-appointments-success', $response);
    }

    public function test_doctor_appointments_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/' . $this->doctor->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'doctor-appointments-success', $response);
    }

    public function test_clinic_appointments_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/' . $this->clinic->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'clinic-appointments-success', $response);
    }

    public function test_room_appointments_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/room?roomIds[0]=' . $this->room->id),
            $this->authHeaders($this->ownerToken)
        );

        $this->saveFixture(self::DOMAIN, 'room-appointments-success', $response);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_doctor_schedule_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/' . $this->doctor->id . '/schedule?date=' . now()->addDays(1)->format('Y-m-d')),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'doctor-schedule-success', $response);
    }

    public function test_clinic_schedule_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/' . $this->clinic->id . '/schedule?date=' . now()->addDays(1)->format('Y-m-d')),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'clinic-schedule-success', $response);
    }

    public function test_available_slots_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/available-slots?doctor_id=' . $this->doctor->id . '&date=' . now()->addWeek()->format('Y-m-d')),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'available-slots-success', $response);
    }

    public function test_available_slots_validation_fails()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/available-slots'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'available-slots-error-validation', $response);
    }

    public function test_show_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'show-error-unauthorized', $response);
    }

    public function test_cancel_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/cancel'),
            ['cancel_reason' => 'Test']
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'cancel-error-unauthorized', $response);
    }

    public function test_cancel_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/cancel'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'cancel-error-validation', $response);
    }

    public function test_mark_confirmed_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/99999/confirmed'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'mark-confirmed-error-not-found', $response);
    }

    public function test_mark_confirmed_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed'),
            []
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'mark-confirmed-error-unauthorized', $response);
    }

    public function test_mark_confirmed_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/confirmed'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::DOMAIN, 'mark-confirmed-error-forbidden', $response);
    }

    public function test_complete_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/99999/complete'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'complete-error-not-found', $response);
    }

    public function test_complete_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete'),
            []
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'complete-error-unauthorized', $response);
    }

    public function test_complete_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/complete'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::DOMAIN, 'complete-error-forbidden', $response);
    }

    public function test_reschedule_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/99999/reschedule'),
            [
                'start_time' => '14:00',
                'date' => now()->addWeek()->format('Y-m-d'),
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'reschedule-error-not-found', $response);
    }

    public function test_reschedule_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/appointments/' . $this->appointment->id . '/reschedule'),
            [
                'start_time' => '14:00',
                'date' => now()->addWeek()->format('Y-m-d'),
            ]
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'reschedule-error-unauthorized', $response);
    }

    public function test_patient_appointments_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/patient/99999'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'patient-appointments-error-not-found', $response);
    }

    public function test_patient_appointments_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/patient/' . $this->patient->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'patient-appointments-error-unauthorized', $response);
    }

    public function test_doctor_appointments_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/99999'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'doctor-appointments-error-not-found', $response);
    }

    public function test_doctor_appointments_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/' . $this->doctor->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'doctor-appointments-error-unauthorized', $response);
    }

    public function test_clinic_appointments_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/99999'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'clinic-appointments-error-not-found', $response);
    }

    public function test_clinic_appointments_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/' . $this->clinic->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'clinic-appointments-error-unauthorized', $response);
    }

    public function test_room_appointments_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/room')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'room-appointments-error-unauthorized', $response);
    }

    public function test_doctor_schedule_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/99999/schedule?date=' . now()->addDays(1)->format('Y-m-d')),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'doctor-schedule-error-not-found', $response);
    }

    public function test_doctor_schedule_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/doctor/' . $this->doctor->id . '/schedule?date=' . now()->addDays(1)->format('Y-m-d'))
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'doctor-schedule-error-unauthorized', $response);
    }

    public function test_clinic_schedule_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/99999/schedule?date=' . now()->addDays(1)->format('Y-m-d')),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'clinic-schedule-error-not-found', $response);
    }

    public function test_clinic_schedule_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/clinic/' . $this->clinic->id . '/schedule?date=' . now()->addDays(1)->format('Y-m-d'))
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'clinic-schedule-error-unauthorized', $response);
    }

    public function test_available_slots_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/appointments/available-slots')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'available-slots-error-unauthorized', $response);
    }
}
