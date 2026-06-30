<?php

namespace Tests\Feature\Entities;

class MedicineTest extends BaseEntityTestCase
{
    protected string $entityName = 'medicine';

    public function test_search_success(): void
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/medicines/search') . '?query=test');        $this->saveResult($this->entityName, 'search-success', 'GET', '/clinic-system/clinic/medicines/search?query=test', ['query' => 'test'], $response);
        $response->assertStatus(200);
    }

    public function test_search_validation(): void
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/medicines/search'));        $this->saveResult($this->entityName, 'search-validation', 'GET', '/clinic-system/clinic/medicines/search', [], $response);
        $response->assertStatus(422);
    }

    public function test_store_success(): void
    {
        $payload = ['ar_name' => 'Test Medicine'];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/medicines'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'store-success', 'POST', '/clinic-system/clinic/medicines', $payload, $response);
        $response->assertStatus(201);
    }

    public function test_store_validation(): void
    {
        $payload = [];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/medicines'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'store-validation', 'POST', '/clinic-system/clinic/medicines', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_store_unauthenticated(): void
    {
        $payload = ['ar_name' => 'Test Medicine'];

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/medicines'),
            $payload
        );        $this->saveResult($this->entityName, 'store-unauthenticated', 'POST', '/clinic-system/clinic/medicines', $payload, $response);
        $response->assertStatus(401);
    }
}
