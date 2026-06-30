<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Room;
use App\Models\Specialty;
use Illuminate\Http\UploadedFile;

class AllApiClinicRoomTest extends TestCase
{
    // ==================== Clinic Routes ====================

    // --- GET /clinic/info -> ClinicController@clinicInfo ---

    public function test_clinic_info_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/info'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('clinic', 'info-success', $response);
    }

    public function test_clinic_info_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/info'));
        $response->assertStatus(401);
        $this->saveFixture('clinic', 'info-error-unauthorized', $response);
    }

    public function test_clinic_info_patient_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/info'),
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('clinic', 'info-patient-error-not-found', $response);
    }

    // --- POST /clinic/update/{clinicId} -> ClinicController@updateClinic ---

    public function test_update_clinic_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/update/' . $this->clinic->id),
            [
                'title' => 'Updated Clinic Name Longer',
                'location' => 'New Location Street 123, City',
                'phone' => '0912345678',
            ],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture('clinic', 'update-success', $response);
    }

    public function test_update_clinic_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/update/' . $this->clinic->id),
            [
                'title' => 'Short',
                'location' => 'Small',
                'phone' => 'invalid-phone',
            ],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture('clinic', 'update-error-validation', $response);
    }

    public function test_update_clinic_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/update/' . $this->clinic->id),
            [
                'title' => 'Updated Clinic Name Longer',
                'location' => 'New Location Street 123, City',
                'phone' => '0912345678',
            ]
        );
        $response->assertStatus(401);
        $this->saveFixture('clinic', 'update-error-unauthorized', $response);
    }

    public function test_update_clinic_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/update/' . $this->clinic->id),
            [
                'title' => 'Updated Clinic Name Longer',
                'location' => 'New Location Street 123, City',
                'phone' => '0912345678',
            ],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('clinic', 'update-error-forbidden', $response);
    }

    public function test_update_clinic_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/update/99999'),
            [
                'title' => 'Updated Clinic Name Longer',
                'location' => 'New Location Street 123, City',
                'phone' => '0912345678',
            ],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('clinic', 'update-error-not-found', $response);
    }

    // --- POST /clinic/doctors/register -> ClinicController@createDoctor ---

    public function test_create_doctor_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'New Doctor Room']);
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/register'),
            [
                'fname' => 'New',
                'lname' => 'Doctor',
                'email' => 'newdoctor2@test.com',
                'dob' => '1985-05-15',
                'gender' => 'male',
                'clinic_id' => $this->clinic->id,
                'room_id' => $newRoom->id,
                'appointment_duration' => 30,
                'consultation_fee' => 200,
                'bio' => 'Experienced doctor',
                'specialty_ids' => [Specialty::first()->id],
            ],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture('clinic', 'create-doctor-success', $response);
    }

    public function test_create_doctor_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/register'),
            [
                'fname' => '',
                'email' => 'not-an-email',
            ],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture('clinic', 'create-doctor-error-validation', $response);
    }

    public function test_create_doctor_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/register'),
            [
                'fname' => 'New',
                'lname' => 'Doctor',
                'email' => 'newdoctor3@test.com',
                'dob' => '1985-05-15',
                'gender' => 'male',
                'clinic_id' => $this->clinic->id,
            ]
        );
        $response->assertStatus(401);
        $this->saveFixture('clinic', 'create-doctor-error-unauthorized', $response);
    }

    public function test_create_doctor_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/register'),
            [
                'fname' => 'New',
                'lname' => 'Doctor',
                'email' => 'newdoctor4@test.com',
                'dob' => '1985-05-15',
                'gender' => 'male',
                'clinic_id' => $this->clinic->id,
            ],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('clinic', 'create-doctor-error-forbidden', $response);
    }

    // --- POST /clinic/secretaries/register -> ClinicController@createSecretary ---

    public function test_create_secretary_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/register'),
            [
                'fname' => 'New',
                'lname' => 'Secretary',
                'email' => 'newsecretary2@test.com',
                'dob' => '1990-01-01',
                'gender' => 'female',
                'clinic_id' => $this->clinic->id,
                'room_ids' => [$this->room->id],
            ],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture('clinic', 'create-secretary-success', $response);
    }

    public function test_create_secretary_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/register'),
            [
                'fname' => '',
                'room_ids' => [],
            ],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture('clinic', 'create-secretary-error-validation', $response);
    }

    public function test_create_secretary_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/register'),
            [
                'fname' => 'New',
                'lname' => 'Secretary',
                'email' => 'newsecretary3@test.com',
                'dob' => '1990-01-01',
                'gender' => 'female',
                'clinic_id' => $this->clinic->id,
                'room_ids' => [$this->room->id],
            ]
        );
        $response->assertStatus(401);
        $this->saveFixture('clinic', 'create-secretary-error-unauthorized', $response);
    }

    public function test_create_secretary_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/register'),
            [
                'fname' => 'New',
                'lname' => 'Secretary',
                'email' => 'newsecretary4@test.com',
                'dob' => '1990-01-01',
                'gender' => 'female',
                'clinic_id' => $this->clinic->id,
                'room_ids' => [$this->room->id],
            ],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('clinic', 'create-secretary-error-forbidden', $response);
    }

    // ==================== Room Routes ====================

    // --- GET /clinic/rooms/user -> RoomController@userRooms ---

    public function test_user_rooms_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/user'),
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('rooms', 'user-rooms-success', $response);
    }

    public function test_user_rooms_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/rooms/user'));
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'user-rooms-error-unauthorized', $response);
    }

    public function test_user_rooms_forbidden()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/user'),
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('rooms', 'user-rooms-error-forbidden', $response);
    }

    // --- GET /clinic/rooms/{clinicId} -> RoomController@index ---

    public function test_index_rooms_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->clinic->id),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('rooms', 'index-success', $response);
    }

    public function test_index_rooms_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/rooms/' . $this->clinic->id));
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'index-error-unauthorized', $response);
    }

    public function test_index_rooms_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('rooms', 'index-error-not-found', $response);
    }

    // --- GET /clinic/rooms/{clinicId}/info -> RoomController@indexWithInfo ---

    public function test_index_rooms_with_info_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->clinic->id . '/info'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('rooms', 'index-with-info-success', $response);
    }

    public function test_index_rooms_with_info_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/rooms/' . $this->clinic->id . '/info'));
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'index-with-info-error-unauthorized', $response);
    }

    public function test_index_rooms_with_info_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/info'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('rooms', 'index-with-info-error-not-found', $response);
    }

    // --- GET /clinic/rooms/{roomId}/details -> RoomController@get ---

    public function test_get_room_details_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/details'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('rooms', 'get-details-success', $response);
    }

    public function test_get_room_details_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/details'));
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'get-details-error-unauthorized', $response);
    }

    public function test_get_room_details_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/details'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('rooms', 'get-details-error-not-found', $response);
    }

    // --- POST /clinic/rooms -> RoomController@create ---

    public function test_create_room_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms'),
            [
                'name' => 'New Test Room',
                'clinic_id' => $this->clinic->id,
            ],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture('rooms', 'create-success', $response);
    }

    public function test_create_room_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms'),
            ['name' => ''],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture('rooms', 'create-error-validation', $response);
    }

    public function test_create_room_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms'),
            ['name' => 'New Room', 'clinic_id' => $this->clinic->id]
        );
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'create-error-unauthorized', $response);
    }

    public function test_create_room_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms'),
            ['name' => 'New Room', 'clinic_id' => $this->clinic->id],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('rooms', 'create-error-forbidden', $response);
    }

    // --- POST /clinic/rooms/{roomId} -> RoomController@update ---

    public function test_update_room_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id),
            ['name' => 'Updated Room Name', 'clinic_id' => $this->clinic->id],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture('rooms', 'update-success', $response);
    }

    public function test_update_room_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id),
            ['name' => ''],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture('rooms', 'update-error-validation', $response);
    }

    public function test_update_room_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id),
            ['name' => 'Updated Room Name']
        );
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'update-error-unauthorized', $response);
    }

    public function test_update_room_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999'),
            ['name' => 'Updated Room Name', 'clinic_id' => $this->clinic->id],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('rooms', 'update-error-not-found', $response);
    }

    public function test_update_room_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id),
            ['name' => 'Updated Room Name', 'clinic_id' => $this->clinic->id],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('rooms', 'update-error-forbidden', $response);
    }

    // --- DELETE /clinic/rooms/{roomId} -> RoomController@destroy ---

    public function test_destroy_room_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'Temp Room']);
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $newRoom->id),
            [],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture('rooms', 'destroy-success', $response);
    }

    public function test_destroy_room_unauthenticated()
    {
        $response = $this->deleteJson($this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id));
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'destroy-error-unauthorized', $response);
    }

    public function test_destroy_room_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999'),
            [],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('rooms', 'destroy-error-not-found', $response);
    }

    public function test_destroy_room_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id),
            [],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('rooms', 'destroy-error-forbidden', $response);
    }

    // --- POST /clinic/rooms/{roomId}/doctors -> RoomController@addDoctorToRoom ---

    public function test_add_doctor_to_room_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'Sync Room']);
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $newRoom->id . '/doctors'),
            ['doctor_id' => $this->doctor->id],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture('rooms', 'add-doctor-to-room-success', $response);
    }

    public function test_add_doctor_to_room_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/doctors'),
            ['doctor_id' => $this->doctor->id]
        );
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'add-doctor-to-room-error-unauthorized', $response);
    }

    public function test_add_doctor_to_room_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/doctors'),
            ['doctor_id' => $this->doctor->id],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('rooms', 'add-doctor-to-room-error-not-found', $response);
    }

    public function test_add_doctor_to_room_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/doctors'),
            ['doctor_id' => $this->doctor->id],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('rooms', 'add-doctor-to-room-error-forbidden', $response);
    }

    // --- POST /clinic/rooms/{roomId}/secretaries -> RoomController@addSecToRoom ---

    public function test_add_sec_to_room_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'Sec Sync Room']);
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $newRoom->id . '/secretaries'),
            ['secretary_id' => $this->secretary->id],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture('rooms', 'add-sec-to-room-success', $response);
    }

    public function test_add_sec_to_room_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/secretaries'),
            ['secretary_id' => $this->secretary->id]
        );
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'add-sec-to-room-error-unauthorized', $response);
    }

    public function test_add_sec_to_room_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/secretaries'),
            ['secretary_id' => $this->secretary->id],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('rooms', 'add-sec-to-room-error-not-found', $response);
    }

    public function test_add_sec_to_room_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/secretaries'),
            ['secretary_id' => $this->secretary->id],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('rooms', 'add-sec-to-room-error-forbidden', $response);
    }

    // --- DELETE /clinic/rooms/{roomId}/doctors/{doctorId} -> RoomController@delDoctorFromRoom ---

    public function test_del_doctor_from_room_success()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/doctors/' . $this->doctor->id),
            [],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture('rooms', 'del-doctor-from-room-success', $response);
    }

    public function test_del_doctor_from_room_unauthenticated()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/doctors/' . $this->doctor->id)
        );
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'del-doctor-from-room-error-unauthorized', $response);
    }

    public function test_del_doctor_from_room_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/doctors/' . $this->doctor->id),
            [],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('rooms', 'del-doctor-from-room-error-not-found', $response);
    }

    public function test_del_doctor_from_room_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/doctors/' . $this->doctor->id),
            [],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('rooms', 'del-doctor-from-room-error-forbidden', $response);
    }

    // --- DELETE /clinic/rooms/{roomId}/secretaries/{secretaryId} -> RoomController@delSecFromRoom ---

    public function test_del_sec_from_room_success()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/secretaries/' . $this->secretary->id),
            [],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture('rooms', 'del-sec-from-room-success', $response);
    }

    public function test_del_sec_from_room_unauthenticated()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/secretaries/' . $this->secretary->id)
        );
        $response->assertStatus(401);
        $this->saveFixture('rooms', 'del-sec-from-room-error-unauthorized', $response);
    }

    public function test_del_sec_from_room_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/99999/secretaries/' . $this->secretary->id),
            [],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('rooms', 'del-sec-from-room-error-not-found', $response);
    }

    public function test_del_sec_from_room_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/rooms/' . $this->room->id . '/secretaries/' . $this->secretary->id),
            [],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('rooms', 'del-sec-from-room-error-forbidden', $response);
    }

    // ==================== Secretary Routes ====================

    // --- GET /clinic/secretaries/{id} -> SecretaryController@info ---

    public function test_secretary_info_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/secretaries/' . $this->secretary->id),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('secretaries', 'info-success', $response);
    }

    public function test_secretary_info_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/secretaries/' . $this->secretary->id));
        $response->assertStatus(401);
        $this->saveFixture('secretaries', 'info-error-unauthorized', $response);
    }

    public function test_secretary_info_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/secretaries/99999'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(404);
        $this->saveFixture('secretaries', 'info-error-not-found', $response);
    }

    public function test_secretary_info_forbidden()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/secretaries/' . $this->secretary->id),
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('secretaries', 'info-error-forbidden', $response);
    }

    // --- POST /clinic/secretaries/update -> SecretaryController@update ---

    public function test_secretary_update_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/update'),
            [
                'fname' => 'Updated',
                'lname' => 'Secretary',
                'clinic_id' => $this->clinic->id,
            ],
            $this->authHeaders($this->secretaryToken)
        );
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture('secretaries', 'update-success', $response);
    }

    public function test_secretary_update_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/update'),
            [
                'dob' => 'invalid-date',
                'gender' => 'invalid',
            ],
            $this->authHeaders($this->secretaryToken)
        );
        $response->assertStatus(422);
        $this->saveFixture('secretaries', 'update-error-validation', $response);
    }

    public function test_secretary_update_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/update'),
            [
                'fname' => 'Updated',
                'lname' => 'Secretary',
                'clinic_id' => $this->clinic->id,
            ]
        );
        $response->assertStatus(401);
        $this->saveFixture('secretaries', 'update-error-unauthorized', $response);
    }

    public function test_secretary_update_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/update'),
            [
                'fname' => 'Updated',
                'lname' => 'Secretary',
                'clinic_id' => $this->clinic->id,
            ],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(403);
        $this->saveFixture('secretaries', 'update-error-forbidden', $response);
    }

    // ==================== User Routes ====================

    // --- GET /clinic/users/info -> UserController@info ---

    public function test_users_info_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/users/info'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture('users', 'info-success', $response);
    }

    public function test_users_info_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/users/info'));
        $response->assertStatus(401);
        $this->saveFixture('users', 'info-error-unauthorized', $response);
    }

    // --- POST /clinic/users/update-image -> UserController@updateImage ---

    public function test_update_image_success()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $response = $this->post(
            $this->v1uri('/clinic-system/clinic/users/update-image'),
            ['image' => $file],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture('users', 'update-image-success', $response);
    }

    public function test_update_image_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/users/update-image'),
            ['image' => 'not-an-image'],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture('users', 'update-image-error-validation', $response);
    }

    public function test_update_image_unauthenticated()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $response = $this->post(
            $this->v1uri('/clinic-system/clinic/users/update-image'),
            ['image' => $file]
        );
        $response->assertStatus(401);
        $this->saveFixture('users', 'update-image-error-unauthorized', $response);
    }

    // --- GET /clinic/users/image-url -> UserController@getImageUrl ---

    public function test_get_image_url_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/users/image-url'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture('users', 'image-url-success', $response);
    }

    public function test_get_image_url_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/users/image-url'));
        $response->assertStatus(401);
        $this->saveFixture('users', 'image-url-error-unauthorized', $response);
    }

    // ==================== Auth User Endpoint (/api/user) ====================

    public function test_get_authenticated_user_success()
    {
        $response = $this->getJson(
            $this->uri('/user'),
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'fname', 'lname', 'email']);
        $this->saveFixture('user', 'get-user-success', $response);
    }

    public function test_get_authenticated_user_unauthenticated()
    {
        $response = $this->getJson($this->uri('/user'));
        $response->assertStatus(401);
        $this->saveFixture('user', 'get-user-error-unauthorized', $response);
    }

    public function test_get_authenticated_user_as_doctor()
    {
        $response = $this->getJson(
            $this->uri('/user'),
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(200);
        $this->saveFixture('user', 'get-user-doctor-success', $response);
    }

    public function test_get_authenticated_user_as_owner()
    {
        $response = $this->getJson(
            $this->uri('/user'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture('user', 'get-user-owner-success', $response);
    }

    public function test_get_authenticated_user_as_secretary()
    {
        $response = $this->getJson(
            $this->uri('/user'),
            $this->authHeaders($this->secretaryToken)
        );
        $response->assertStatus(200);
        $this->saveFixture('user', 'get-user-secretary-success', $response);
    }

    public function test_create_doctor_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/register'),
            [
                'fname' => 'New',
                'lname' => 'Doctor',
                'email' => 'newdoctor_nf@test.com',
                'dob' => '1985-05-15',
                'gender' => 'male',
                'clinic_id' => 99999,
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture('clinic', 'create-doctor-error-not-found', $response);
    }

    public function test_create_secretary_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/register'),
            [
                'fname' => 'New',
                'lname' => 'Secretary',
                'email' => 'newsecretary_nf@test.com',
                'dob' => '1990-01-01',
                'gender' => 'female',
                'clinic_id' => 99999,
                'room_ids' => [$this->room->id],
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture('clinic', 'create-secretary-error-not-found', $response);
    }

    public function test_create_room_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/rooms'),
            ['name' => 'New Room', 'clinic_id' => 99999],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture('rooms', 'create-error-not-found', $response);
    }

    public function test_user_rooms_not_found()
    {
        $newUser = \App\Models\User::factory()->create();
        $newUser->assignRole('doctor');
        $token = $newUser->createToken('test')->plainTextToken;

        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/rooms/user'),
            $this->authHeaders($token)
        );

        $response->assertStatus(404);
        $this->saveFixture('rooms', 'user-rooms-error-not-found', $response);
    }

    public function test_get_authenticated_user_forbidden()
    {
        $response = $this->getJson(
            $this->uri('/user'),
            $this->authHeaders('invalid-token-12345')
        );

        $response->assertStatus(401);
        $this->saveFixture('user', 'get-user-error-invalid-token', $response);
    }
}
