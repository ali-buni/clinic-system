<?php

namespace Tests\Feature\Api;

use App\Models\Medicine;
use App\Models\Disease;
use Tests\TestCase;

class MedicineDiseaseTest extends TestCase
{
    // ========== GET /clinic-system/clinic/medicines/search ==========
    public function test_search_medicines_success()
    {
        Medicine::factory()->create(['en_name' => 'Paracetamol']);

        $response = $this->getJson('/api/clinic-system/clinic/medicines/search?' . http_build_query([
            'query' => 'Para',
            'per_page' => 10,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('medicines', 'search-success', $response);
    }

    public function test_search_medicines_fails_no_query()
    {
        $response = $this->getJson('/api/clinic-system/clinic/medicines/search');

        $response->assertStatus(422);
        $this->saveFixture('medicines', 'search-error-validation', $response);
    }

    // ========== POST /clinic-system/clinic/medicines/store ==========
    public function test_store_medicine_success()
    {
        $response = $this->postJson('/api/clinic-system/clinic/medicines/store', [
            'en_name' => 'Ibuprofen',
            'ar_name' => 'ايبوبروفين',
            'strength' => '400mg',
            'form' => 'tablet',
        ], $this->authHeaders($this->doctorToken));

        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('medicines', 'store-success', $response);
    }

    public function test_store_medicine_fails_validation()
    {
        $response = $this->postJson('/api/clinic-system/clinic/medicines/store', [], $this->authHeaders($this->doctorToken));

        $response->assertStatus(422);
        $this->saveFixture('medicines', 'store-error-validation', $response);
    }

    // ========== GET /clinic-system/clinic/diseases/search ==========
    public function test_search_diseases_success()
    {
        Disease::factory()->create(['en_name' => 'Diabetes']);

        $response = $this->getJson('/api/clinic-system/clinic/diseases/search?' . http_build_query([
            'query' => 'Diab',
            'per_page' => 10,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('diseases', 'search-success', $response);
    }

    public function test_search_diseases_fails_no_query()
    {
        $response = $this->getJson('/api/clinic-system/clinic/diseases/search');

        $response->assertStatus(422);
        $this->saveFixture('diseases', 'search-error-validation', $response);
    }

    // ========== POST /clinic-system/clinic/diseases/store ==========
    public function test_store_disease_success()
    {
        $response = $this->postJson('/api/clinic-system/clinic/diseases/store', [
            'ar_name' => 'سكري',
            'en_name' => 'Diabetes',
            'disease_nature' => 'chronic',
            'code' => 'E10',
            'description' => 'Type 1 Diabetes Mellitus',
        ], $this->authHeaders($this->doctorToken));

        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('diseases', 'store-success', $response);
    }

    public function test_store_disease_fails_validation()
    {
        $response = $this->postJson('/api/clinic-system/clinic/diseases/store', [], $this->authHeaders($this->doctorToken));

        $response->assertStatus(422);
        $this->saveFixture('diseases', 'store-error-validation', $response);
    }
}
