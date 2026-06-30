<?php

namespace Tests\Feature\Entities;

use App\Models\Specialty;

class SpecialtyTest extends BaseEntityTestCase
{
    protected string $entityName = 'specialty';

    public function test_specialties_index_success()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/specialties'));        $this->saveResult($this->entityName, 'index-success', 'GET', '/specialties', [], $response);
        $response->assertStatus(200);
    }

    public function test_specialties_attach_success()
    {
        $specialty = Specialty::factory()->create(['ar_name' => 'اختبار', 'en_name' => 'Test Spec']);
        $payload = ['specialty_ids' => [$specialty->id]];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'attach-success', 'POST', '/specialties', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_specialties_attach_validation_fails()
    {
        $payload = ['specialty_ids' => []];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'attach-validation', 'POST', '/specialties', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_specialties_attach_unauthenticated()
    {
        $payload = ['specialty_ids' => [Specialty::first()->id]];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties'),
            $payload
        );        $this->saveResult($this->entityName, 'attach-unauthenticated', 'POST', '/specialties', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_specialties_attach_forbidden()
    {
        $payload = ['specialty_ids' => [Specialty::first()->id]];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties'),
            $payload,
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'attach-forbidden', 'POST', '/specialties', $payload, $response);
        $response->assertStatus(403);
    }

    public function test_specialties_detach_success()
    {
        $specialty = Specialty::factory()->create(['ar_name' => 'Detach', 'en_name' => 'Detach Spec']);
        $this->doctor->specialties()->attach($specialty->id, ['is_primary' => false]);
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/specialties/' . $specialty->id),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'detach-success', 'DELETE', '/specialties/{specialId}', [], $response);
        $response->assertStatus(200);
    }

    public function test_specialties_detach_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/specialties/99999'),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'detach-not-found', 'DELETE', '/specialties/{specialId}', [], $response);
        $response->assertStatus(404);
    }

    public function test_specialties_detach_unauthenticated()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/specialties/' . Specialty::first()->id)
        );        $this->saveResult($this->entityName, 'detach-unauthenticated', 'DELETE', '/specialties/{specialId}', [], $response);
        $response->assertStatus(401);
    }

    public function test_specialties_detach_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/specialties/' . Specialty::first()->id),
            [],
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'detach-forbidden', 'DELETE', '/specialties/{specialId}', [], $response);
        $response->assertStatus(403);
    }

    public function test_specialties_change_primary_success()
    {
        $specialty = Specialty::factory()->create(['ar_name' => 'Primary', 'en_name' => 'Primary Spec']);
        $this->doctor->specialties()->attach($specialty->id, ['is_primary' => false]);
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties/' . $specialty->id . '/primary'),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'change-primary-success', 'POST', '/specialties/{specialtyId}/primary', [], $response);
        $response->assertStatus(200);
    }

    public function test_specialties_change_primary_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties/' . Specialty::first()->id . '/primary')
        );        $this->saveResult($this->entityName, 'change-primary-unauthenticated', 'POST', '/specialties/{specialtyId}/primary', [], $response);
        $response->assertStatus(401);
    }

    public function test_specialties_change_primary_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties/' . Specialty::first()->id . '/primary'),
            [],
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'change-primary-forbidden', 'POST', '/specialties/{specialtyId}/primary', [], $response);
        $response->assertStatus(403);
    }

    public function test_specialties_show_primary_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/' . $this->doctor->id . '/primary'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'show-primary-success', 'GET', '/specialties/doctor/{doctorId}/primary', [], $response);
        $response->assertStatus(200);
    }

    public function test_specialties_show_primary_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/' . $this->doctor->id . '/primary')
        );        $this->saveResult($this->entityName, 'show-primary-unauthenticated', 'GET', '/specialties/doctor/{doctorId}/primary', [], $response);
        $response->assertStatus(401);
    }

    public function test_specialties_doctor_specialties_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'doctor-specialties-success', 'GET', '/specialties/doctor/{doctorId}', [], $response);
        $response->assertStatus(200);
    }

    public function test_specialties_doctor_specialties_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/' . $this->doctor->id)
        );        $this->saveResult($this->entityName, 'doctor-specialties-unauthenticated', 'GET', '/specialties/doctor/{doctorId}', [], $response);
        $response->assertStatus(401);
    }

    public function test_specialties_doctor_specialties_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/99999'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'doctor-specialties-not-found', 'GET', '/specialties/doctor/{doctorId}', [], $response);
        $response->assertStatus(404);
    }

    public function test_specialties_doctor_specialties_forbidden()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/' . $this->doctor->id),
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'doctor-specialties-forbidden', 'GET', '/specialties/doctor/{doctorId}', [], $response);
        $response->assertStatus(403);
    }
}
