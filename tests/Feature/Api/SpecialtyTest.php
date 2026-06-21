<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Specialty;

class SpecialtyTest extends TestCase
{
    const DOMAIN = 'specialties';

    public function test_index_success()
    {
        $response = $this->getJson($this->uri('/clinic-system/clinic/specialty/index'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'index-success', $response);
    }

    public function test_attach_specialties_success()
    {
        $specialty = \App\Models\Specialty::create(['ar_name' => 'اختبار', 'en_name' => 'Test Spec']);

        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/specialty/add'),
            ['specialty_ids' => [$specialty->id]],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'attach-specialties-success', $response);
    }

    public function test_attach_specialties_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/specialty/add'),
            ['specialty_ids' => []],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'attach-specialties-error-validation', $response);
    }

    public function test_attach_specialties_unauthenticated()
    {
        $response = $this->postJson($this->uri('/clinic-system/clinic/specialty/add'), [
            'specialty_ids' => [1],
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'attach-specialties-error-unauthorized', $response);
    }

    public function test_detach_specialty_success()
    {
        $specialty = \App\Models\Specialty::create(['ar_name' => 'اختبار', 'en_name' => 'Test Spec']);
        $this->doctor->specialties()->sync([$specialty->id]);

        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/specialty/delete/' . $specialty->id),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'detach-specialty-success', $response);
    }

    public function test_change_primary_success()
    {
        $specialty1 = \App\Models\Specialty::create(['ar_name' => 'اختبار1', 'en_name' => 'Test Spec 1']);
        $specialty2 = \App\Models\Specialty::create(['ar_name' => 'اختبار2', 'en_name' => 'Test Spec 2']);
        $this->doctor->specialties()->sync([
            $specialty1->id => ['is_primary' => true],
            $specialty2->id => ['is_primary' => false],
        ]);

        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/specialty/changePrimary/' . $specialty2->id),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'change-primary-success', $response);
    }

    public function test_show_primary_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/specialty/showPrimary/' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'show-primary-success', $response);
    }

    public function test_get_all_doctor_specialties_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/specialty/getAll'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'get-all-specialties-success', $response);
    }
}
