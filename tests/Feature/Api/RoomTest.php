<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Room;

class RoomTest extends TestCase
{
    const DOMAIN = 'rooms';

    public function test_index_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/' . $this->clinic->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'index-success', $response);
    }

    public function test_index_unauthenticated()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/' . $this->clinic->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'index-error-unauthorized', $response);
    }

    public function test_index_with_info_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/' . $this->clinic->id . '/info'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'index-with-info-success', $response);
    }

    public function test_get_room_details_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/' . $this->room->id . '/details'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'get-details-success', $response);
    }

    public function test_get_room_details_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/99999/details'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'get-details-error-not-found', $response);
    }

    public function test_user_rooms_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/userRooms/get'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'user-rooms-success', $response);
    }

    public function test_create_room_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/rooms'),
            [
                'name' => 'New Test Room',
                'clinic_id' => $this->clinic->id,
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'create-success', $response);
    }

    public function test_create_room_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/rooms'),
            ['name' => ''],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'create-error-validation', $response);
    }

    public function test_create_room_forbidden()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/rooms'),
            [
                'name' => 'New Room',
                'clinic_id' => $this->clinic->id,
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::DOMAIN, 'create-error-forbidden', $response);
    }

    public function test_update_room_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/' . $this->room->id),
            ['name' => 'Updated Room Name', 'clinic_id' => $this->clinic->id],
            $this->authHeaders($this->ownerToken)
        );

        $this->saveFixture(self::DOMAIN, 'update-success', $response);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_destroy_room_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'Temp Room']);

        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/' . $newRoom->id),
            [],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'destroy-success', $response);
    }

    // ---- Sync: Doctor Room ----

    public function test_add_doctor_to_room_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'Sync Room']);

        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/sync/doctorRoom'),
            [
                'room_id' => $newRoom->id,
                'doctor_id' => $this->doctor->id,
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $this->saveFixture(self::DOMAIN, 'add-doctor-to-room-success', $response);
    }

    public function test_del_doctor_from_room_success()
    {
        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/detach/doctorRoom'),
            [
                'room_id' => $this->room->id,
                'doctor_id' => $this->doctor->id,
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $this->saveFixture(self::DOMAIN, 'del-doctor-from-room-success', $response);
    }

    // ---- Sync: Secretary Room ----

    public function test_add_sec_to_room_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'Sec Sync Room']);

        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/sync/secRooms'),
            [
                'room_ids' => [$newRoom->id],
                'secretary_id' => $this->secretary->id,
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $this->saveFixture(self::DOMAIN, 'add-sec-to-room-success', $response);
    }

    public function test_del_sec_from_room_success()
    {
        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/clinic/rooms/detach/secRooms'),
            [
                'room_ids' => [$this->room->id],
                'secretary_id' => $this->secretary->id,
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $this->saveFixture(self::DOMAIN, 'del-sec-from-room-success', $response);
    }
}
