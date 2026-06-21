<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class SecretaryTest extends TestCase
{
    const DOMAIN = 'secretaries';

    public function test_info_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/secretaries/' . $this->secretary->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'info-success', $response);
    }

    public function test_info_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/secretaries/99999'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'info-error-not-found', $response);
    }

    public function test_info_unauthenticated()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/secretaries/' . $this->secretary->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'info-error-unauthorized', $response);
    }

    public function test_update_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/secretaries/update'),
            [
                'fname' => 'Updated',
                'lname' => 'Secretary',
                'clinic_id' => $this->clinic->id,
            ],
            $this->authHeaders($this->secretaryToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'update-success', $response);
    }

    public function test_update_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/secretaries/update'),
            [
                'dob' => 'invalid-date',
                'gender' => 'invalid',
            ],
            $this->authHeaders($this->secretaryToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'update-error-validation', $response);
    }
}
