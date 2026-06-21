<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Doctor;
use App\Models\Specialty;

class DoctorTest extends TestCase
{
    const DOMAIN = 'doctors';

    public function test_info_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/doctors/' . $this->doctor->id . '/info'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'info-success', $response);
    }

    public function test_info_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/doctors/99999/info'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'info-error-not-found', $response);
    }

    public function test_info_unauthenticated()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/doctors/' . $this->doctor->id . '/info')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'info-error-unauthorized', $response);
    }

    public function test_update_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/doctors/update'),
            [
                'appointment_duration' => 45,
                'consultation_fee' => 200,
                'bio' => 'Updated bio information',
                'specialties' => [Specialty::first()->id],
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'update-success', $response);
    }

    public function test_update_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/doctors/update'),
            [
                'appointment_duration' => 0,
                'consultation_fee' => -10,
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'update-error-validation', $response);
    }

    public function test_index_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/doctors/filter'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'index-success', $response);
    }

    public function test_destroy_success()
    {
        $newDoctorUser = \App\Models\User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);

        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/clinic/doctors/' . $newDoctor->id . '/leave'),
            [],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'destroy-success', $response);
    }

    public function test_restore_success()
    {
        $newDoctorUser = \App\Models\User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);
        $newDoctor->delete();

        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/doctors/' . $newDoctor->id . '/restore'),
            [],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'restore-success', $response);
    }

    public function test_force_delete_success()
    {
        $newDoctorUser = \App\Models\User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);
        $newDoctor->delete();

        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/clinic/doctors/' . $newDoctor->id . '/force'),
            [],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'force-delete-success', $response);
    }
}
