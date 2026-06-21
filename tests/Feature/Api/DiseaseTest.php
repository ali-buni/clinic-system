<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class DiseaseTest extends TestCase
{
    const DOMAIN = 'diseases';

    public function test_search_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/diseases/search?query=Dia')
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'search-success', $response);
    }

    public function test_search_no_query()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/diseases/search')
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'search-error-validation', $response);
    }

    public function test_store_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/diseases/store'),
            [
                'ar_name' => 'ضغط الدم',
                'en_name' => 'Hypertension',
                'disease_nature' => 'chronic',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'store-success', $response);
    }

    public function test_store_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/diseases/store'),
            [
                'ar_name' => '',
                'en_name' => '',
                'disease_nature' => 'invalid',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'store-error-validation', $response);
    }

    public function test_store_unauthorized()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/diseases/store'),
            [
                'ar_name' => 'ضغط الدم',
                'en_name' => 'Hypertension',
                'disease_nature' => 'chronic',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::DOMAIN, 'store-error-unauthorized', $response);
    }

    public function test_store_unauthenticated()
    {
        $response = $this->postJson($this->uri('/clinic-system/clinic/diseases/store'), [
            'ar_name' => 'ضغط الدم',
            'en_name' => 'Hypertension',
            'disease_nature' => 'chronic',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'store-error-unauthenticated', $response);
    }
}
