<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class DeviceTest extends TestCase
{
    const DOMAIN = 'devices';

    public function test_register_token_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'sample-fcm-token-12345'],
            $this->authHeaders($this->patientToken)
        );

        // Currently returns 'Not implemented yet.' message
        $this->saveFixture(self::DOMAIN, 'register-token-response', $response);
    }

    public function test_register_token_unauthenticated()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'sample-fcm-token']
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'register-token-error-unauthorized', $response);
    }
}
