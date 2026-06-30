<?php

namespace Tests\Feature\Entities;

use App\Models\Room;
use App\Models\Specialty;

class RoomTest extends BaseEntityTestCase
{
    protected string $entityName = 'room';

    public function test_user_rooms_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/user'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'user-rooms-success', 'GET', '/rooms/user', [], $response);
        $response->assertStatus(200);
    }

    public function test_user_rooms_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/rooms/user'));        $this->saveResult($this->entityName, 'user-rooms-unauthenticated', 'GET', '/rooms/user', [], $response);
        $response->assertStatus(401);
    }

    public function test_index_rooms_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->clinic->id),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'index-success', 'GET', '/rooms/{clinicId}', [], $response);
        $response->assertStatus(200);
    }

    public function test_index_rooms_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/rooms/' . $this->clinic->id));        $this->saveResult($this->entityName, 'index-unauthenticated', 'GET', '/rooms/{clinicId}', [], $response);
        $response->assertStatus(401);
    }

    public function test_index_rooms_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999'),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'index-not-found', 'GET', '/rooms/{clinicId}', [], $response);
        $response->assertStatus(404);
    }

    public function test_index_rooms_forbidden()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->clinic->id),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'index-forbidden', 'GET', '/rooms/{clinicId}', [], $response);
        $response->assertStatus(403);
    }

    public function test_index_rooms_with_info_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->clinic->id . '/info'),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'index-with-info-success', 'GET', '/rooms/{clinicId}/info', [], $response);
        $response->assertStatus(200);
    }

    public function test_index_rooms_with_info_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/rooms/' . $this->clinic->id . '/info'));        $this->saveResult($this->entityName, 'index-with-info-unauthenticated', 'GET', '/rooms/{clinicId}/info', [], $response);
        $response->assertStatus(401);
    }

    public function test_index_rooms_with_info_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/info'),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'index-with-info-not-found', 'GET', '/rooms/{clinicId}/info', [], $response);
        $response->assertStatus(404);
    }

    public function test_index_rooms_with_info_forbidden()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->clinic->id . '/info'),
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'index-with-info-forbidden', 'GET', '/rooms/{clinicId}/info', [], $response);
        $response->assertStatus(403);
    }

    public function test_get_room_details_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/details'),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'get-details-success', 'GET', '/rooms/{roomId}/details', [], $response);
        $response->assertStatus(200);
    }

    public function test_get_room_details_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/details'));        $this->saveResult($this->entityName, 'get-details-unauthenticated', 'GET', '/rooms/{roomId}/details', [], $response);
        $response->assertStatus(401);
    }

    public function test_get_room_details_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/details'),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'get-details-not-found', 'GET', '/rooms/{roomId}/details', [], $response);
        $response->assertStatus(404);
    }

    public function test_create_room_success()
    {
        $payload = [
            'name' => 'New Test Room',
            'clinic_id' => $this->clinic->id,
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'create-success', 'POST', '/rooms', $payload, $response);
        $response->assertStatus(201);
    }

    public function test_create_room_validation_fails()
    {
        $payload = ['name' => ''];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'create-validation', 'POST', '/rooms', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_create_room_unauthenticated()
    {
        $payload = ['name' => 'New Room', 'clinic_id' => $this->clinic->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms'),
            $payload
        );        $this->saveResult($this->entityName, 'create-unauthenticated', 'POST', '/rooms', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_create_room_forbidden()
    {
        $payload = ['name' => 'New Room', 'clinic_id' => $this->clinic->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'create-forbidden', 'POST', '/rooms', $payload, $response);
        $response->assertStatus(403);
    }

    public function test_update_room_success()
    {
        $payload = ['name' => 'Updated Room Name', 'clinic_id' => $this->clinic->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'update-success', 'POST', '/rooms/{roomId}', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_update_room_validation_fails()
    {
        $payload = ['name' => ''];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'update-validation', 'POST', '/rooms/{roomId}', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_update_room_unauthenticated()
    {
        $payload = ['name' => 'Updated Room Name'];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id),
            $payload
        );        $this->saveResult($this->entityName, 'update-unauthenticated', 'POST', '/rooms/{roomId}', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_update_room_not_found()
    {
        $payload = ['name' => 'Updated Room Name', 'clinic_id' => $this->clinic->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'update-not-found', 'POST', '/rooms/{roomId}', $payload, $response);
        $response->assertStatus(404);
    }

    public function test_update_room_forbidden()
    {
        $payload = ['name' => 'Updated Room Name', 'clinic_id' => $this->clinic->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'update-forbidden', 'POST', '/rooms/{roomId}', $payload, $response);
        $response->assertStatus(403);
    }

    public function test_destroy_room_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'Temp Room']);
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $newRoom->id),
            [],
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'destroy-success', 'DELETE', '/rooms/{roomId}', [], $response);
        $response->assertStatus(200);
    }

    public function test_destroy_room_unauthenticated()
    {
        $response = $this->deleteJson($this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id));        $this->saveResult($this->entityName, 'destroy-unauthenticated', 'DELETE', '/rooms/{roomId}', [], $response);
        $response->assertStatus(401);
    }

    public function test_destroy_room_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999'),
            [],
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'destroy-not-found', 'DELETE', '/rooms/{roomId}', [], $response);
        $response->assertStatus(404);
    }

    public function test_destroy_room_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'destroy-forbidden', 'DELETE', '/rooms/{roomId}', [], $response);
        $response->assertStatus(403);
    }

    public function test_add_doctor_to_room_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'Sync Room']);
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $newRoom->id . '/doctors'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'add-doctor-success', 'POST', '/rooms/{roomId}/doctors', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_add_doctor_to_room_unauthenticated()
    {
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/doctors'),
            $payload
        );        $this->saveResult($this->entityName, 'add-doctor-unauthenticated', 'POST', '/rooms/{roomId}/doctors', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_add_doctor_to_room_not_found()
    {
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/doctors'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'add-doctor-not-found', 'POST', '/rooms/{roomId}/doctors', $payload, $response);
        $response->assertStatus(404);
    }

    public function test_add_doctor_to_room_forbidden()
    {
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/doctors'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'add-doctor-forbidden', 'POST', '/rooms/{roomId}/doctors', $payload, $response);
        $response->assertStatus(403);
    }

    public function test_add_sec_to_room_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'Sec Sync Room']);
        $payload = ['secretary_id' => $this->secretary->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $newRoom->id . '/secretaries'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'add-sec-success', 'POST', '/rooms/{roomId}/secretaries', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_add_sec_to_room_unauthenticated()
    {
        $payload = ['secretary_id' => $this->secretary->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/secretaries'),
            $payload
        );        $this->saveResult($this->entityName, 'add-sec-unauthenticated', 'POST', '/rooms/{roomId}/secretaries', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_add_sec_to_room_not_found()
    {
        $payload = ['secretary_id' => $this->secretary->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/secretaries'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'add-sec-not-found', 'POST', '/rooms/{roomId}/secretaries', $payload, $response);
        $response->assertStatus(404);
    }

    public function test_add_sec_to_room_forbidden()
    {
        $payload = ['secretary_id' => $this->secretary->id];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/secretaries'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'add-sec-forbidden', 'POST', '/rooms/{roomId}/secretaries', $payload, $response);
        $response->assertStatus(403);
    }

    public function test_del_doctor_from_room_success()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/doctors/' . $this->doctor->id),
            [],
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'del-doctor-success', 'DELETE', '/rooms/{roomId}/doctors/{doctorId}', [], $response);
        $response->assertStatus(200);
    }

    public function test_del_doctor_from_room_unauthenticated()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/doctors/' . $this->doctor->id)
        );        $this->saveResult($this->entityName, 'del-doctor-unauthenticated', 'DELETE', '/rooms/{roomId}/doctors/{doctorId}', [], $response);
        $response->assertStatus(401);
    }

    public function test_del_doctor_from_room_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/doctors/' . $this->doctor->id),
            [],
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'del-doctor-not-found', 'DELETE', '/rooms/{roomId}/doctors/{doctorId}', [], $response);
        $response->assertStatus(404);
    }

    public function test_del_doctor_from_room_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/doctors/' . $this->doctor->id),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'del-doctor-forbidden', 'DELETE', '/rooms/{roomId}/doctors/{doctorId}', [], $response);
        $response->assertStatus(403);
    }

    public function test_del_sec_from_room_success()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/secretaries/' . $this->secretary->id),
            [],
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'del-sec-success', 'DELETE', '/rooms/{roomId}/secretaries/{secretaryId}', [], $response);
        $response->assertStatus(200);
    }

    public function test_del_sec_from_room_unauthenticated()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/secretaries/' . $this->secretary->id)
        );        $this->saveResult($this->entityName, 'del-sec-unauthenticated', 'DELETE', '/rooms/{roomId}/secretaries/{secretaryId}', [], $response);
        $response->assertStatus(401);
    }

    public function test_del_sec_from_room_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/secretaries/' . $this->secretary->id),
            [],
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'del-sec-not-found', 'DELETE', '/rooms/{roomId}/secretaries/{secretaryId}', [], $response);
        $response->assertStatus(404);
    }

    public function test_del_sec_from_room_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/secretaries/' . $this->secretary->id),
            [],
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'del-sec-forbidden', 'DELETE', '/rooms/{roomId}/secretaries/{secretaryId}', [], $response);
        $response->assertStatus(403);
    }
}
