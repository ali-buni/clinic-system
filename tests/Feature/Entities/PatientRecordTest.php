<?php

namespace Tests\Feature\Entities;

use App\Models\Patient_record;

class PatientRecordTest extends BaseEntityTestCase
{
    protected string $entityName = 'patient-record';

    public function test_store_success(): void
    {
        $payload = [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'appointment_id' => $this->appointment->id,
            'diagnosis_summary' => 'Test diagnosis',
            'description' => 'Test description',
            'status' => 'open',
            'notes' => 'Test notes',
        ];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patient-records'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'store-success', 'POST', '/clinic-system/clinic/patient-records', $payload, $response);
        $response->assertStatus(201);
    }

    public function test_store_validation(): void
    {
        $payload = [];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patient-records'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'store-validation', 'POST', '/clinic-system/clinic/patient-records', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_store_unauthenticated(): void
    {
        $payload = [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'appointment_id' => $this->appointment->id,
            'diagnosis_summary' => 'Test diagnosis',
        ];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patient-records'),
            $payload
        );        $this->saveResult($this->entityName, 'store-unauthenticated', 'POST', '/clinic-system/clinic/patient-records', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_update_success(): void
    {
        $payload = [
            'diagnosis_summary' => 'Updated diagnosis',
            'description' => 'Updated description',
            'status' => 'closed',
        ];

        $response = $this->putJson(
            $this->v1uri('/patient-records/' . $this->patientRecord->id),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'update-success', 'PUT', '/patient-records/' . $this->patientRecord->id, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_update_validation(): void
    {
        $payload = [
            'diagnosis_summary' => str_repeat('a', 1001),
        ];

        $response = $this->putJson(
            $this->v1uri('/patient-records/' . $this->patientRecord->id),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'update-validation', 'PUT', '/patient-records/' . $this->patientRecord->id, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_update_not_found(): void
    {
        $payload = [
            'diagnosis_summary' => 'Updated diagnosis',
        ];

        $response = $this->putJson(
            $this->v1uri('/patient-records/99999'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'update-not-found', 'PUT', '/patient-records/99999', $payload, $response);
        $response->assertStatus(404);
    }

    public function test_update_unauthenticated(): void
    {
        $payload = [
            'diagnosis_summary' => 'Updated diagnosis',
        ];

        $response = $this->putJson(
            $this->v1uri('/patient-records/' . $this->patientRecord->id),
            $payload
        );        $this->saveResult($this->entityName, 'update-unauthenticated', 'PUT', '/patient-records/' . $this->patientRecord->id, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_destroy_success(): void
    {
        $newRecord = Patient_record::factory()->create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
        ]);

        $response = $this->deleteJson(
            $this->v1uri('/patient-records/' . $newRecord->id),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'destroy-success', 'DELETE', '/patient-records/' . $newRecord->id, [], $response);
        $response->assertStatus(200);
    }

    public function test_destroy_not_found(): void
    {
        $response = $this->deleteJson(
            $this->v1uri('/patient-records/99999'),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'destroy-not-found', 'DELETE', '/patient-records/99999', [], $response);
        $response->assertStatus(404);
    }

    public function test_destroy_unauthenticated(): void
    {
        $newRecord = Patient_record::factory()->create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
        ]);

        $response = $this->deleteJson(
            $this->v1uri('/patient-records/' . $newRecord->id)
        );        $this->saveResult($this->entityName, 'destroy-unauthenticated', 'DELETE', '/patient-records/' . $newRecord->id, [], $response);
        $response->assertStatus(401);
    }

    public function test_show_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/' . $this->patientRecord->id),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'show-success', 'GET', '/patient-records/' . $this->patientRecord->id, [], $response);
        $response->assertStatus(200);
    }

    public function test_show_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/99999'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'show-not-found', 'GET', '/patient-records/99999', [], $response);
        $response->assertStatus(404);
    }

    public function test_show_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/' . $this->patientRecord->id)
        );        $this->saveResult($this->entityName, 'show-unauthenticated', 'GET', '/patient-records/' . $this->patientRecord->id, [], $response);
        $response->assertStatus(401);
    }

    public function test_index_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records') . '?clinic_id=' . $this->clinic->id,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'index-success', 'GET', '/patient-records?clinic_id=' . $this->clinic->id, ['clinic_id' => $this->clinic->id], $response);
        $response->assertStatus(200);
    }

    public function test_index_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records') . '?clinic_id=' . $this->clinic->id
        );        $this->saveResult($this->entityName, 'index-unauthenticated', 'GET', '/patient-records?clinic_id=' . $this->clinic->id, ['clinic_id' => $this->clinic->id], $response);
        $response->assertStatus(401);
    }

    public function test_history_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/patient/' . $this->patient->id . '/history'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'history-success', 'GET', '/patient-records/patient/' . $this->patient->id . '/history', [], $response);
        $response->assertStatus(200);
    }

    public function test_history_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/patient/99999/history'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'history-not-found', 'GET', '/patient-records/patient/99999/history', [], $response);
        $response->assertStatus(404);
    }

    public function test_history_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/patient/' . $this->patient->id . '/history')
        );        $this->saveResult($this->entityName, 'history-unauthenticated', 'GET', '/patient-records/patient/' . $this->patient->id . '/history', [], $response);
        $response->assertStatus(401);
    }

    public function test_get_by_doctor_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/patient/' . $this->patient->id . '/doctor/' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'get-by-doctor-success', 'GET', '/patient-records/patient/' . $this->patient->id . '/doctor/' . $this->doctor->id, [], $response);
        $response->assertStatus(200);
    }

    public function test_get_by_doctor_patient_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/patient/99999/doctor/' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'get-by-doctor-patient-not-found', 'GET', '/patient-records/patient/99999/doctor/' . $this->doctor->id, [], $response);
        $response->assertStatus(404);
    }

    public function test_get_by_doctor_doctor_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/patient/' . $this->patient->id . '/doctor/99999'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'get-by-doctor-doctor-not-found', 'GET', '/patient-records/patient/' . $this->patient->id . '/doctor/99999', [], $response);
        $response->assertStatus(404);
    }

    public function test_get_by_doctor_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/patient/' . $this->patient->id . '/doctor/' . $this->doctor->id)
        );        $this->saveResult($this->entityName, 'get-by-doctor-unauthenticated', 'GET', '/patient-records/patient/' . $this->patient->id . '/doctor/' . $this->doctor->id, [], $response);
        $response->assertStatus(401);
    }

    public function test_rooms_search_success(): void
    {
        $payload = ['room_ids' => [$this->room->id]];

        $response = $this->postJson(
            $this->v1uri('/patient-records/rooms/search'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'rooms-search-success', 'POST', '/patient-records/rooms/search', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_rooms_search_validation(): void
    {
        $payload = [];

        $response = $this->postJson(
            $this->v1uri('/patient-records/rooms/search'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'rooms-search-validation', 'POST', '/patient-records/rooms/search', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_rooms_search_unauthenticated(): void
    {
        $payload = ['room_ids' => [$this->room->id]];

        $response = $this->postJson(
            $this->v1uri('/patient-records/rooms/search'),
            $payload
        );        $this->saveResult($this->entityName, 'rooms-search-unauthenticated', 'POST', '/patient-records/rooms/search', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_get_all_by_doctor_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/doctor/' . $this->doctor->id . '/all'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'get-all-by-doctor-success', 'GET', '/patient-records/doctor/' . $this->doctor->id . '/all', [], $response);
        $response->assertStatus(200);
    }

    public function test_get_all_by_doctor_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/doctor/99999/all'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'get-all-by-doctor-not-found', 'GET', '/patient-records/doctor/99999/all', [], $response);
        $response->assertStatus(404);
    }

    public function test_get_all_by_doctor_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patient-records/doctor/' . $this->doctor->id . '/all')
        );        $this->saveResult($this->entityName, 'get-all-by-doctor-unauthenticated', 'GET', '/patient-records/doctor/' . $this->doctor->id . '/all', [], $response);
        $response->assertStatus(401);
    }
}
