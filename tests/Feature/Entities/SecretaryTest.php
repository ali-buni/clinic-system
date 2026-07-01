<?php

namespace Tests\Feature\Entities;

class SecretaryTest extends BaseEntityTestCase
{
    protected string $entityName = 'secretary';

    public function test_secretary_info_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/secretaries/' . $this->secretary->id),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'info-success', 'GET', '/secretaries/{id}', [], $response);
        $response->assertStatus(200);
    }

    public function test_secretary_info_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/secretaries/' . $this->secretary->id));        $this->saveResult($this->entityName, 'info-unauthenticated', 'GET', '/secretaries/{id}', [], $response);
        $response->assertStatus(401);
    }

    public function test_secretary_info_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/secretaries/99999'),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'info-not-found', 'GET', '/secretaries/{id}', [], $response);
        $response->assertStatus(404);
    }

    public function test_secretary_info_forbidden()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/secretaries/' . $this->secretary->id),
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'info-forbidden', 'GET', '/secretaries/{id}', [], $response);
        $response->assertStatus(403);
    }

    public function test_secretary_update_success()
    {
        $payload = [
            'fname' => 'Updated',
            'lname' => 'Secretary',
            'clinic_id' => $this->clinic->id,
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/update'),
            $payload,
            $this->authHeaders($this->secretaryToken)
        );        $this->saveResult($this->entityName, 'update-success', 'POST', '/secretaries/update', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_secretary_update_validation_fails()
    {
        $payload = [
            'dob' => 'invalid-date',
            'gender' => 'invalid',
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/update'),
            $payload,
            $this->authHeaders($this->secretaryToken)
        );        $this->saveResult($this->entityName, 'update-validation', 'POST', '/secretaries/update', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_secretary_update_unauthenticated()
    {
        $payload = [
            'fname' => 'Updated',
            'lname' => 'Secretary',
            'clinic_id' => $this->clinic->id,
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/update'),
            $payload
        );        $this->saveResult($this->entityName, 'update-unauthenticated', 'POST', '/secretaries/update', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_secretary_update_forbidden()
    {
        $payload = [
            'fname' => 'Updated',
            'lname' => 'Secretary',
            'clinic_id' => $this->clinic->id,
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/update'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'update-forbidden', 'POST', '/secretaries/update', $payload, $response);
        $response->assertStatus(403);
    }
}
