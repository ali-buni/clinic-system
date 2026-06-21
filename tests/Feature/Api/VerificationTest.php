<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class VerificationTest extends TestCase
{
    const DOMAIN = 'verification';

    public function test_verify_code_validation_fails()
    {
        $response = $this->postJson($this->uri('/clinic-system/verify-code'), [
            'login' => '',
            'code' => '123',
            'type' => 'invalid',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'verify-code-error-validation', $response);
    }

    public function test_resend_code_validation_fails()
    {
        $response = $this->postJson($this->uri('/clinic-system/resend-code'), [
            'login' => '',
            'password' => 'short',
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'resend-code-error-validation', $response);
    }

    public function test_resend_code_invalid_credentials()
    {
        $response = $this->postJson($this->uri('/clinic-system/resend-code'), [
            'login' => 'patient@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'resend-code-error-invalid-credentials', $response);
    }
}
