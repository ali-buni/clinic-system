<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class UserTest extends TestCase
{
    const DOMAIN = 'users';

    public function test_info_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/users/info'),
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'info-success', $response);
    }

    public function test_info_unauthenticated()
    {
        $response = $this->getJson($this->uri('/clinic-system/clinic/clinic/users/info'));

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'info-error-unauthorized', $response);
    }

    public function test_get_image_url_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/users/image-url'),
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'image-url-success', $response);
    }

    public function test_update_image_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/users/update-image'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'update-image-error-validation', $response);
    }
}
