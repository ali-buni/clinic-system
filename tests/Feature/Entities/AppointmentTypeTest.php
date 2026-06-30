<?php

namespace Tests\Feature\Entities;

use App\Models\Appointment_type;

class AppointmentTypeTest extends BaseEntityTestCase
{
    protected string $entityName = 'appointment-type';

    public function test_appointment_types_index_success()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/appointment-types'));        $this->saveResult($this->entityName, 'index-success', 'GET', '/appointment-types', [], $response);
        $response->assertStatus(200);
    }

    public function test_appointment_types_add_success()
    {
        $payload = [
            'ar_name' => 'موعد',
            'en_name' => 'Appt',
            'types' => 1,
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/appointment-types'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'add-success', 'POST', '/appointment-types', $payload, $response);
        $response->assertStatus(201);
    }

    public function test_appointment_types_add_validation_fails()
    {
        $payload = [
            'ar_name' => '',
            'en_name' => '',
            'types' => 0,
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/appointment-types'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'add-validation', 'POST', '/appointment-types', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_appointment_types_delete_success()
    {
        $type = Appointment_type::create([
            'ar_name' => 'Temporary',
            'en_name' => 'Temp',
            'types' => 1,
        ]);
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/appointment-types/' . $type->id),
            [],
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'delete-success', 'DELETE', '/appointment-types/{id}', [], $response);
        $response->assertStatus(200);
    }

    public function test_appointment_types_delete_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/appointment-types/99999'),
            [],
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'delete-not-found', 'DELETE', '/appointment-types/{id}', [], $response);
        $response->assertStatus(404);
    }
}
