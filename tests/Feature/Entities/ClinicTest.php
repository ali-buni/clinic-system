<?php

namespace Tests\Feature\Entities;

class ClinicTest extends BaseEntityTestCase
{
    protected string $entityName = 'clinic';

    public function test_clinic_info_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/info'),
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'info-success', 'GET', '/clinic/info', [], $response);
        $response->assertStatus(200);
    }

    public function test_clinic_info_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/info'));        $this->saveResult($this->entityName, 'info-unauthenticated', 'GET', '/clinic/info', [], $response);
        $response->assertStatus(404);
    }

    public function test_clinic_info_patient_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/info'),
            $this->authHeaders($this->patientToken)
        );        $this->saveResult($this->entityName, 'info-patient-not-found', 'GET', '/clinic/info', [], $response);
        $response->assertStatus(404);
    }

    public function test_update_clinic_success()
    {
        $payload = [
            'title' => 'Updated Clinic Name Longer',
            'location' => 'New Location Street 123, City',
            'phone' => '0912345678',
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/update/' . $this->clinic->id),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'update-success', 'POST', '/clinic/update/{clinicId}', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_update_clinic_validation_fails()
    {
        $payload = [
            'title' => 'Short',
            'location' => 'Small',
            'phone' => 'invalid-phone',
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/update/' . $this->clinic->id),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'update-validation', 'POST', '/clinic/update/{clinicId}', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_update_clinic_unauthenticated()
    {
        $payload = [
            'title' => 'Updated Clinic Name Longer',
            'location' => 'New Location Street 123, City',
            'phone' => '0912345678',
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/update/' . $this->clinic->id),
            $payload
        );        $this->saveResult($this->entityName, 'update-unauthenticated', 'POST', '/clinic/update/{clinicId}', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_update_clinic_forbidden()
    {
        $payload = [
            'title' => 'Updated Clinic Name Longer',
            'location' => 'New Location Street 123, City',
            'phone' => '0912345678',
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/update/' . $this->clinic->id),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'update-forbidden', 'POST', '/clinic/update/{clinicId}', $payload, $response);
        $response->assertStatus(403);
    }

    public function test_update_clinic_not_found()
    {
        $payload = [
            'title' => 'Updated Clinic Name Longer',
            'location' => 'New Location Street 123, City',
            'phone' => '0912345678',
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/update/99999'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'update-not-found', 'POST', '/clinic/update/{clinicId}', $payload, $response);
        $response->assertStatus(404);
    }

    public function test_create_doctor_success()
    {
        $newRoom = \App\Models\Room::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'New Doctor Room']);
        $payload = [
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
            'specialty_ids' => [\App\Models\Specialty::first()->id],
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/register'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'create-doctor-success', 'POST', '/clinic/doctors/register', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_create_doctor_validation_fails()
    {
        $payload = [
            'fname' => '',
            'email' => 'not-an-email',
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/register'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'create-doctor-validation', 'POST', '/clinic/doctors/register', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_create_doctor_unauthenticated()
    {
        $payload = [
            'fname' => 'New',
            'lname' => 'Doctor',
            'email' => 'newdoctor3@test.com',
            'dob' => '1985-05-15',
            'gender' => 'male',
            'clinic_id' => $this->clinic->id,
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/register'),
            $payload
        );        $this->saveResult($this->entityName, 'create-doctor-unauthenticated', 'POST', '/clinic/doctors/register', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_create_doctor_forbidden()
    {
        $payload = [
            'fname' => 'New',
            'lname' => 'Doctor',
            'email' => 'newdoctor4@test.com',
            'dob' => '1985-05-15',
            'gender' => 'male',
            'clinic_id' => $this->clinic->id,
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/register'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'create-doctor-forbidden', 'POST', '/clinic/doctors/register', $payload, $response);
        $response->assertStatus(403);
    }

    public function test_create_doctor_not_found()
    {
        $payload = [
            'fname' => 'New',
            'lname' => 'Doctor',
            'email' => 'newdoctor_nf@test.com',
            'dob' => '1985-05-15',
            'gender' => 'male',
            'clinic_id' => 99999,
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/register'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'create-doctor-not-found', 'POST', '/clinic/doctors/register', $payload, $response);
        $response->assertStatus(404);
    }

    public function test_create_secretary_success()
    {
        $payload = [
            'fname' => 'New',
            'lname' => 'Secretary',
            'email' => 'newsecretary2@test.com',
            'dob' => '1990-01-01',
            'gender' => 'female',
            'clinic_id' => $this->clinic->id,
            'room_ids' => [$this->room->id],
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/register'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'create-secretary-success', 'POST', '/clinic/secretaries/register', $payload, $response);
        $response->assertStatus(200);
    }

    public function test_create_secretary_validation_fails()
    {
        $payload = [
            'fname' => '',
            'room_ids' => [],
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/register'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'create-secretary-validation', 'POST', '/clinic/secretaries/register', $payload, $response);
        $response->assertStatus(422);
    }

    public function test_create_secretary_unauthenticated()
    {
        $payload = [
            'fname' => 'New',
            'lname' => 'Secretary',
            'email' => 'newsecretary3@test.com',
            'dob' => '1990-01-01',
            'gender' => 'female',
            'clinic_id' => $this->clinic->id,
            'room_ids' => [$this->room->id],
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/register'),
            $payload
        );        $this->saveResult($this->entityName, 'create-secretary-unauthenticated', 'POST', '/clinic/secretaries/register', $payload, $response);
        $response->assertStatus(401);
    }

    public function test_create_secretary_forbidden()
    {
        $payload = [
            'fname' => 'New',
            'lname' => 'Secretary',
            'email' => 'newsecretary4@test.com',
            'dob' => '1990-01-01',
            'gender' => 'female',
            'clinic_id' => $this->clinic->id,
            'room_ids' => [$this->room->id],
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/register'),
            $payload,
            $this->authHeaders($this->doctorToken)
        );        $this->saveResult($this->entityName, 'create-secretary-forbidden', 'POST', '/clinic/secretaries/register', $payload, $response);
        $response->assertStatus(403);
    }

    public function test_create_secretary_not_found()
    {
        $payload = [
            'fname' => 'New',
            'lname' => 'Secretary',
            'email' => 'newsecretary_nf@test.com',
            'dob' => '1990-01-01',
            'gender' => 'female',
            'clinic_id' => 99999,
            'room_ids' => [$this->room->id],
        ];
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/secretaries/register'),
            $payload,
            $this->authHeaders($this->ownerToken)
        );        $this->saveResult($this->entityName, 'create-secretary-not-found', 'POST', '/clinic/secretaries/register', $payload, $response);
        $response->assertStatus(404);
    }
}
