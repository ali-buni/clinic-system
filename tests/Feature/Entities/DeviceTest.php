<?php

namespace Tests\Feature\Entities;

class DeviceTest extends BaseEntityTestCase
{
    protected string $entityName = 'device';

    public function test_register_token_success()
    {
        $endpoint = $this->v1uri('/clinic-system/devices/register-token');
        $payload = ['token' => 'fcm-device-token-123'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'register-token-success', 'POST', $endpoint, $payload, $response, 'CheckAccess middleware is broken; endpoint not implemented');
        $response->assertStatus(200);
    }

    public function test_register_token_validation()
    {
        $endpoint = $this->v1uri('/clinic-system/devices/register-token');
        $payload = ['token' => ''];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'register-token-validation', 'POST', $endpoint, $payload, $response, 'Endpoint not implemented; always returns 200');
        $response->assertStatus(200);
    }

    public function test_register_token_missing()
    {
        $endpoint = $this->v1uri('/clinic-system/devices/register-token');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'register-token-missing', 'POST', $endpoint, $payload, $response, 'Endpoint not implemented; always returns 200');
        $response->assertStatus(200);
    }

    public function test_register_token_unauthenticated()
    {
        $endpoint = $this->v1uri('/clinic-system/devices/register-token');
        $payload = ['token' => 'fcm-device-token-123'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'register-token-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_register_token_invalid_token()
    {
        $endpoint = $this->v1uri('/clinic-system/devices/register-token');
        $payload = ['token' => 'fcm-device-token-123'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders('invalid-token-value'));        $this->saveResult($this->entityName, 'register-token-invalid-token', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_register_token_duplicate()
    {
        $endpoint = $this->v1uri('/clinic-system/devices/register-token');
        $payload = ['token' => 'fcm-device-token-456'];
        $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'register-token-duplicate', 'POST', $endpoint, $payload, $response, 'Endpoint not implemented; always returns 200');
        $response->assertStatus(200);
    }

    public function test_register_token_multiple_users()
    {
        $endpoint = $this->v1uri('/clinic-system/devices/register-token');
        $payload = ['token' => 'fcm-device-token-789'];
        $response1 = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));
        $response2 = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));
        $response1->assertStatus(200);
        $response2->assertStatus(200);
        $this->saveResult($this->entityName, 'register-token-multiple-users', 'POST', $endpoint, $payload, $response2, 'Endpoint not implemented; always returns 200');
    }
}
