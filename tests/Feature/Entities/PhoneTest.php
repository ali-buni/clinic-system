<?php

namespace Tests\Feature\Entities;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class PhoneTest extends BaseEntityTestCase
{
    protected string $entityName = 'phone';

    public function test_update_success()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/update');
        $payload = ['phone' => '0912345678'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'update-success', 'POST', $endpoint, $payload, $response, 'CheckAccess middleware is broken; may return 403 or 500');
        $response->assertStatus(200);
    }

    public function test_update_validation()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/update');
        $payload = ['phone' => '123'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'update-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_update_missing()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/update');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'update-missing', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_update_unauthenticated()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/update');
        $payload = ['phone' => '0912345678'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'update-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_update_invalid_token()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/update');
        $payload = ['phone' => '0912345678'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders('invalid-token-value'));        $this->saveResult($this->entityName, 'update-invalid-token', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_update_duplicate_phone()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/update');
        $payload = ['phone' => $this->doctorUser->phone];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'update-duplicate-phone', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_verify_update_success()
    {
        $code = '123456';
        $cacheKey = 'phone_update:' . $this->ownerUser->id;
        Cache::put($cacheKey, [
            'code' => Hash::make($code),
            'new_phone' => '0912345678',
            'attempts' => 0,
        ], now()->addMinutes(15));

        $endpoint = $this->v1uri('/clinic-system/phone/verify-update');
        $payload = ['code' => $code];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'verify-update-success', 'POST', $endpoint, $payload, $response, 'CheckAccess middleware is broken; may return 403 or 500');
        $response->assertStatus(200);
    }

    public function test_verify_update_validation()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/verify-update');
        $payload = ['code' => 'abc'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'verify-update-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_verify_update_missing()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/verify-update');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'verify-update-missing', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_verify_update_no_pending_request()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/verify-update');
        $payload = ['code' => '123456'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'verify-update-no-pending-request', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }

    public function test_verify_update_unauthenticated()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/verify-update');
        $payload = ['code' => '123456'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'verify-update-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_verify_update_invalid_token()
    {
        $endpoint = $this->v1uri('/clinic-system/phone/verify-update');
        $payload = ['code' => '123456'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders('invalid-token-value'));        $this->saveResult($this->entityName, 'verify-update-invalid-token', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_verify_update_wrong_code()
    {
        $code = '123456';
        $cacheKey = 'phone_update:' . $this->ownerUser->id;
        Cache::put($cacheKey, [
            'code' => Hash::make($code),
            'new_phone' => '0912345678',
            'attempts' => 0,
        ], now()->addMinutes(15));

        $endpoint = $this->v1uri('/clinic-system/phone/verify-update');
        $payload = ['code' => '654321'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'verify-update-wrong-code', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }
}
