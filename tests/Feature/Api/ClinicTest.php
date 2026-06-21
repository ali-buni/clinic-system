<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Clinic;
use App\Models\Room;

class ClinicTest extends TestCase
{
    const DOMAIN = 'clinic';

    public function test_clinic_info_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/info'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'info-success', $response);
    }

    public function test_clinic_info_unauthenticated()
    {
        $response = $this->getJson($this->uri('/clinic-system/clinic/clinic/info'));

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'info-error-unauthorized', $response);
    }

    public function test_clinic_info_forbidden()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/info'),
            $this->authHeaders($this->patientToken)
        );

        // Patient has no clinic, returns 404 from controller
        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'info-error-forbidden', $response);
    }

    public function test_update_clinic_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/update/' . $this->clinic->id),
            [
                'title' => 'Updated Clinic Name',
                'location' => 'New Location Street 123, City',
                'phone' => '0912345678',
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'update-success', $response);
    }

    public function test_update_clinic_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/update/' . $this->clinic->id),
            [
                'title' => 'Short',
                'location' => 'Small',
                'phone' => 'invalid-phone',
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'update-error-validation', $response);
    }

    public function test_update_clinic_forbidden()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/update/' . $this->clinic->id),
            [
                'title' => 'Updated Clinic Name',
                'location' => 'New Location Street 123, City',
                'phone' => '0912345678',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::DOMAIN, 'update-error-forbidden', $response);
    }

    // ---- Create Doctor (by owner) ----

    public function test_create_doctor_success()
    {
        $newRoom = Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'New Room']);

        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/doctor/register'),
            [
                'fname' => 'New',
                'lname' => 'Doctor',
                'phone' => '0933333333',
                'dob' => '1985-05-15',
                'gender' => 'male',
                'clinic_id' => $this->clinic->id,
                'room_id' => $newRoom->id,
                'appointment_duration' => 30,
                'consultation_fee' => 200,
                'bio' => 'Experienced doctor',
                'specialty_ids' => [\App\Models\Specialty::first()->id],
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'create-doctor-success', $response);
    }

    public function test_create_doctor_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/doctor/register'),
            [
                'fname' => '',
                'phone' => 'invalid',
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'create-doctor-error-validation', $response);
    }

    // ---- Create Secretary (by owner) ----

    public function test_create_secretary_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/secretary/register'),
            [
                'fname' => 'New',
                'lname' => 'Secretary',
                'phone' => '0944444444',
                'dob' => '1990-01-01',
                'gender' => 'female',
                'clinic_id' => $this->clinic->id,
                'room_ids' => [$this->room->id],
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'create-secretary-success', $response);
    }

    public function test_create_secretary_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/secretary/register'),
            [
                'fname' => '',
                'room_ids' => [],
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'create-secretary-error-validation', $response);
    }
}
