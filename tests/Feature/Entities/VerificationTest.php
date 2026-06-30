<?php

namespace Tests\Feature\Entities;

use App\Models\Verification_code;
use Illuminate\Support\Facades\Hash;

class VerificationTest extends BaseEntityTestCase
{
    protected string $entityName = 'verification';

    public function test_verify_code_success()
    {
        $code = '123456';
        Verification_code::create([
            'user_id' => $this->ownerUser->id,
            'type' => 'email',
            'sent_to' => $this->ownerUser->email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        $endpoint = $this->v1uri('/clinic-system/verify-code');
        $payload = [
            'login' => $this->ownerUser->email,
            'code' => $code,
            'type' => 'email',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'verify-code-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_verify_code_validation()
    {
        $endpoint = $this->v1uri('/clinic-system/verify-code');
        $payload = [
            'login' => 'not-an-email',
            'code' => 'abc',
            'type' => 'invalid',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'verify-code-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_verify_code_missing()
    {
        $endpoint = $this->v1uri('/clinic-system/verify-code');
        $payload = [];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'verify-code-missing', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_verify_code_not_found()
    {
        $endpoint = $this->v1uri('/clinic-system/verify-code');
        $payload = [
            'login' => 'nonexistent@test.com',
            'code' => '123456',
            'type' => 'email',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'verify-code-not-found', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }

    public function test_verify_code_invalid_code()
    {
        $correctCode = '123456';
        Verification_code::create([
            'user_id' => $this->ownerUser->id,
            'type' => 'email',
            'sent_to' => $this->ownerUser->email,
            'code_hash' => Hash::make($correctCode),
            'expires_at' => now()->addMinutes(15),
        ]);

        $endpoint = $this->v1uri('/clinic-system/verify-code');
        $payload = [
            'login' => $this->ownerUser->email,
            'code' => '654321',
            'type' => 'email',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'verify-code-invalid-code', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }

    public function test_resend_code_success()
    {
        $endpoint = $this->v1uri('/clinic-system/resend-code');
        $payload = [
            'login' => $this->ownerUser->email,
            'password' => 'password',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'resend-code-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_resend_code_validation()
    {
        $endpoint = $this->v1uri('/clinic-system/resend-code');
        $payload = [
            'login' => 'not-an-email',
            'password' => 'short',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'resend-code-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_resend_code_missing()
    {
        $endpoint = $this->v1uri('/clinic-system/resend-code');
        $payload = [];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'resend-code-missing', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_resend_code_invalid_credentials()
    {
        $endpoint = $this->v1uri('/clinic-system/resend-code');
        $payload = [
            'login' => $this->ownerUser->email,
            'password' => 'wrongpassword',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'resend-code-invalid-credentials', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_resend_code_not_found()
    {
        $endpoint = $this->v1uri('/clinic-system/resend-code');
        $payload = [
            'login' => 'nonexistent@test.com',
            'password' => 'password',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'resend-code-not-found', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }
}
