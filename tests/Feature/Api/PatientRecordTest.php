<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Patient_record;
use App\Models\PatientInfo;
use App\Models\Doctor;

class PatientRecordTest extends TestCase
{
    const DOMAIN = 'patient-records';

    public function test_store_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records'),
            [
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'clinic_id' => $this->clinic->id,
                'appointment_id' => $this->appointment->id,
                'diagnosis_summary' => 'Patient diagnosed with hypertension',
                'description' => 'Patient shows elevated blood pressure levels',
                'status' => 'open',
                'notes' => 'Follow up in 2 weeks',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'store-success', $response);
    }

    public function test_store_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records'),
            [
                'patient_id' => '',
                'doctor_id' => '',
                'diagnosis_summary' => '',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'store-error-validation', $response);
    }

    public function test_store_unauthenticated()
    {
        $response = $this->postJson($this->uri('/clinic-system/clinic/clinic/patient-records'), [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'appointment_id' => $this->appointment->id,
            'diagnosis_summary' => 'Test diagnosis',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'store-error-unauthorized', $response);
    }

    public function test_show_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/show/' . $this->patientRecord->id),
            $this->authHeaders($this->doctorToken)
        );

        $this->saveFixture(self::DOMAIN, 'show-success', $response);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_show_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/show/99999'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'show-error-not-found', $response);
    }

    public function test_update_success()
    {
        $response = $this->putJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/' . $this->patientRecord->id),
            [
                'diagnosis_summary' => 'Updated diagnosis summary',
                'description' => 'Updated description',
                'status' => 'follow-up',
                'notes' => 'Updated notes',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $this->saveFixture(self::DOMAIN, 'update-success', $response);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_destroy_success()
    {
        $newRecord = Patient_record::factory()->create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
        ]);

        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/' . $newRecord->id),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $this->saveFixture(self::DOMAIN, 'destroy-success', $response);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_destroy_not_found()
    {
        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/99999'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'destroy-error-not-found', $response);
    }

    public function test_index_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/filtered?clinic_id=' . $this->clinic->id),
            $this->authHeaders($this->doctorToken)
        );

        $this->saveFixture(self::DOMAIN, 'index-success', $response);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_history_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/patient/' . $this->patient->id . '/history'),
            $this->authHeaders($this->doctorToken)
        );

        $this->saveFixture(self::DOMAIN, 'history-success', $response);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_history_patient_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/patient/99999/history'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'history-error-not-found', $response);
    }

    public function test_get_by_doctor_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/patient/' . $this->patient->id . '/doctor/' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );

        $this->saveFixture(self::DOMAIN, 'get-by-doctor-success', $response);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_get_by_doctor_patient_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/patient/99999/doctor/' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'get-by-doctor-error-patient-not-found', $response);
    }

    public function test_get_by_doctor_doctor_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/patient/' . $this->patient->id . '/doctor/99999'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'get-by-doctor-error-doctor-not-found', $response);
    }

    public function test_get_by_room_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/rooms/search'),
            ['room_ids' => [$this->room->id]],
            $this->authHeaders($this->doctorToken)
        );

        $this->saveFixture(self::DOMAIN, 'get-by-room-success', $response);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_get_by_room_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/rooms/search'),
            ['room_ids' => []],
            $this->authHeaders($this->doctorToken)
        );

        $this->saveFixture(self::DOMAIN, 'get-by-room-error-validation', $response);
        $response->assertStatus(422);
    }

    public function test_get_all_by_doctor_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/doctor/' . $this->doctor->id . '/all'),
            $this->authHeaders($this->doctorToken)
        );

        $this->saveFixture(self::DOMAIN, 'get-all-by-doctor-success', $response);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_get_all_by_doctor_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patient-records/doctor/99999/all'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'get-all-by-doctor-error-not-found', $response);
    }
}
