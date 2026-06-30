<?php

namespace Tests\Feature\Entities;

use App\Models\PatientInfo;

class PatientTest extends BaseEntityTestCase
{
    protected string $entityName = 'patient';

    public function test_index_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients') . '?clinic_id=' . $this->clinic->id,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'index-success', 'GET', '/clinic-system/clinic/patients?clinic_id=' . $this->clinic->id, ['clinic_id' => $this->clinic->id], $response);
        $response->assertStatus(200);
    }

    public function test_index_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients') . '?clinic_id=' . $this->clinic->id
        );        $this->saveResult($this->entityName, 'index-unauthenticated', 'GET', '/clinic-system/clinic/patients?clinic_id=' . $this->clinic->id, ['clinic_id' => $this->clinic->id], $response);
        $response->assertStatus(401);
    }

    public function test_trashed_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/trashed') . '?clinic_id=' . $this->clinic->id,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'trashed-success', 'GET', '/clinic-system/clinic/patients/trashed?clinic_id=' . $this->clinic->id, ['clinic_id' => $this->clinic->id], $response);
        $response->assertStatus(200);
    }

    public function test_trashed_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/trashed') . '?clinic_id=' . $this->clinic->id
        );        $this->saveResult($this->entityName, 'trashed-unauthenticated', 'GET', '/clinic-system/clinic/patients/trashed?clinic_id=' . $this->clinic->id, ['clinic_id' => $this->clinic->id], $response);
        $response->assertStatus(401);
    }

    public function test_show_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/' . $this->patient->id),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'show-success', 'GET', '/clinic-system/clinic/patients/' . $this->patient->id, [], $response);
        $response->assertStatus(200);
    }

    public function test_show_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patients/99999'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'show-not-found', 'GET', '/patients/99999', [], $response);
        $response->assertStatus(500);
    }

    public function test_show_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/' . $this->patient->id)
        );        $this->saveResult($this->entityName, 'show-unauthenticated', 'GET', '/clinic-system/clinic/patients/' . $this->patient->id, [], $response);
        $response->assertStatus(401);
    }

    public function test_medical_history_success(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/' . $this->patient->id . '/medical-history'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'medical-history-success', 'GET', '/clinic-system/clinic/patients/' . $this->patient->id . '/medical-history', [], $response);
        $response->assertStatus(200);
    }

    public function test_medical_history_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patients/99999/medical-history'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'medical-history-not-found', 'GET', '/patients/99999/medical-history', [], $response);
        $response->assertStatus(500);
    }

    public function test_medical_history_unauthenticated(): void
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/' . $this->patient->id . '/medical-history')
        );        $this->saveResult($this->entityName, 'medical-history-unauthenticated', 'GET', '/clinic-system/clinic/patients/' . $this->patient->id . '/medical-history', [], $response);
        $response->assertStatus(401);
    }

    public function test_update_success(): void
    {
        $payload = [
            'patient_id' => (string) $this->patient->id,
            'fname' => 'UpdatedName',
        ];

        $response = $this->postJson(
            $this->v1uri('/patients/update'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'update-success', 'POST', '/patients/update', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_update_validation(): void
    {
        $payload = [];

        $response = $this->postJson(
            $this->v1uri('/patients/update'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'update-validation', 'POST', '/patients/update', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_update_unauthenticated(): void
    {
        $payload = [
            'patient_id' => (string) $this->patient->id,
            'fname' => 'UpdatedName',
        ];

        $response = $this->postJson(
            $this->v1uri('/patients/update'),
            $payload
        );        $this->saveResult($this->entityName, 'update-unauthenticated', 'POST', '/patients/update', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_update_not_found(): void
    {
        $payload = [
            'patient_id' => '99999',
            'fname' => 'UpdatedName',
        ];

        $response = $this->postJson(
            $this->v1uri('/patients/update'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'update-not-found', 'POST', '/patients/update', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_update_forbidden(): void
    {
        $payload = [
            'patient_id' => (string) $this->patient->id,
            'fname' => 'UpdatedName',
        ];

        $response = $this->postJson(
            $this->v1uri('/patients/update'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'update-forbidden', 'POST', '/patients/update', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_destroy_success(): void
    {
        $newPatient = PatientInfo::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);

        $payload = ['patient_id' => (string) $newPatient->id];

        $response = $this->postJson(
            $this->v1uri('/patients/delete'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'destroy-success', 'POST', '/patients/delete', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_destroy_not_found(): void
    {
        $payload = ['patient_id' => '99999'];

        $response = $this->postJson(
            $this->v1uri('/patients/delete'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'destroy-not-found', 'POST', '/patients/delete', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_destroy_unauthenticated(): void
    {
        $newPatient = PatientInfo::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);

        $payload = ['patient_id' => (string) $newPatient->id];

        $response = $this->postJson(
            $this->v1uri('/patients/delete'),
            $payload
        );        $this->saveResult($this->entityName, 'destroy-unauthenticated', 'POST', '/patients/delete', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_destroy_forbidden(): void
    {
        $newPatient = PatientInfo::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);

        $payload = ['patient_id' => (string) $newPatient->id];

        $response = $this->postJson(
            $this->v1uri('/patients/delete'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'destroy-forbidden', 'POST', '/patients/delete', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_restore_success(): void
    {
        $newPatient = PatientInfo::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);
        $newPatient->delete();

        $response = $this->getJson(
            $this->v1uri('/patients/restore') . '?patient_id=' . $newPatient->id,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'restore-success', 'GET', '/patients/restore?patient_id=' . $newPatient->id, ['patient_id' => $newPatient->id], $response);
        $response->assertStatus(200);
    }

    public function test_restore_not_found(): void
    {
        $response = $this->getJson(
            $this->v1uri('/patients/restore') . '?patient_id=99999',
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'restore-not-found', 'GET', '/patients/restore?patient_id=99999', ['patient_id' => '99999'], $response);
        $response->assertStatus(422);
    }

    public function test_restore_unauthenticated(): void
    {
        $newPatient = PatientInfo::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);
        $newPatient->delete();

        $response = $this->getJson(
            $this->v1uri('/patients/restore') . '?patient_id=' . $newPatient->id
        );        $this->saveResult($this->entityName, 'restore-unauthenticated', 'GET', '/patients/restore?patient_id=' . $newPatient->id, ['patient_id' => $newPatient->id], $response);
        $response->assertStatus(401);
    }
}
