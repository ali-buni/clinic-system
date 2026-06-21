<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class AppointmentTypeTest extends TestCase
{
    const DOMAIN = 'appointment-types';

    public function test_index_success()
    {
        $response = $this->getJson($this->uri('/clinic-system/appointment-types'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'index-success', $response);
    }

    public function test_add_success()
    {
        $response = $this->postJson($this->uri('/clinic-system/appointment-types'), [
            'ar_name' => 'موعد جديد',
            'en_name' => 'New Type',
            'types' => 2,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'add-success', $response);
    }

    public function test_add_validation_fails()
    {
        $response = $this->postJson($this->uri('/clinic-system/appointment-types'), [
            'ar_name' => '',
            'en_name' => '',
            'types' => 0,
        ]);

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'add-error-validation', $response);
    }

    public function test_delete_success()
    {
        $type = \App\Models\Appointment_type::create([
            'ar_name' => 'Temporary',
            'en_name' => 'Temp',
            'types' => 1,
        ]);

        $response = $this->deleteJson($this->uri('/clinic-system/appointment-types/' . $type->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'delete-success', $response);
    }

    public function test_delete_not_found()
    {
        $response = $this->deleteJson($this->uri('/clinic-system/appointment-types/99999'));

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'delete-error-not-found', $response);
    }
}
