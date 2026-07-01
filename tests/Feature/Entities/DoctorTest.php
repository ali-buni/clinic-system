<?php

namespace Tests\Feature\Entities;

use App\Models\Doctor;
use App\Models\User;

class DoctorTest extends BaseEntityTestCase
{
    protected string $entityName = 'doctor';

    public function test_info_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/' . $this->doctor->id . '/info');
        $payload = [];
        $response = $this->getJson($endpoint, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'info-success', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_info_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/99999/info');
        $payload = [];
        $response = $this->getJson($endpoint, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'info-not-found', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_info_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/' . $this->doctor->id . '/info');
        $payload = [];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'info-unauthenticated', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_info_forbidden(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/' . $this->doctor->id . '/info');
        $payload = [];
        $response = $this->getJson($endpoint, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'info-forbidden', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_update_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/update');
        $payload = [
            'consultation_fee' => 250,
            'bio' => 'Updated bio',
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'update-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_update_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/update');
        $payload = [
            'consultation_fee' => -5,
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'update-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_update_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/update');
        $payload = [
            'consultation_fee' => 250,
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'update-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_update_forbidden(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/update');
        $payload = [
            'consultation_fee' => 250,
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'update-forbidden', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }

    public function test_filter_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/filter');
        $payload = [];
        $response = $this->getJson($endpoint, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'filter-success', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_filter_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/filter');
        $payload = [];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'filter-unauthenticated', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_filter_forbidden(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/filter');
        $payload = [];
        $response = $this->getJson($endpoint, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'filter-forbidden', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(403);
    }

    public function test_destroy_success(): void
    {
        $newDoctorUser = User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);

        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/leave');
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'destroy-success', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_destroy_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/99999/leave');
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'destroy-not-found', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_destroy_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/' . $this->doctor->id . '/leave');
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload);        $this->saveResult($this->entityName, 'destroy-unauthenticated', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_destroy_forbidden(): void
    {
        $newDoctorUser = User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);

        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/leave');
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'destroy-forbidden', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_restore_success(): void
    {
        $newDoctorUser = User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);
        $newDoctor->delete();

        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/restore');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'restore-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_restore_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/99999/restore');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'restore-not-found', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_restore_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/1/restore');
        $payload = [];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'restore-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_restore_forbidden(): void
    {
        $newDoctorUser = User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);
        $newDoctor->delete();

        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/restore');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'restore-forbidden', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_force_delete_success(): void
    {
        $newDoctorUser = User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);
        $newDoctor->delete();

        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/force');
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'force-delete-success', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_force_delete_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/99999/force');
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'force-delete-not-found', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_force_delete_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/1/force');
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload);        $this->saveResult($this->entityName, 'force-delete-unauthenticated', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_force_delete_forbidden(): void
    {
        $newDoctorUser = User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);
        $newDoctor->delete();

        $endpoint = $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/force');
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'force-delete-forbidden', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }
}
