<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\PatientInfo;

class PatientTest extends TestCase
{
    const DOMAIN = 'patients';

    public function test_index_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patients?clinic_id=' . $this->clinic->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'index-success', $response);
    }

    public function test_index_no_clinic_id()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patients'),
            $this->authHeaders($this->ownerToken)
        );

        $this->saveFixture(self::DOMAIN, 'index-error-no-clinic', $response);
        $response->assertStatus(500);
    }

    public function test_index_unauthenticated()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patients?clinic_id=' . $this->clinic->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'index-error-unauthorized', $response);
    }

    public function test_show_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patients/' . $this->patient->id . '/show'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'show-success', $response);
    }

    public function test_show_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patients/99999/show'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(500);
        $this->saveFixture(self::DOMAIN, 'show-error-not-found', $response);
    }

    public function test_update_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/patients/update'),
            [
                'patient_id' => $this->patient->id,
                'fname' => 'Updated',
                'lname' => 'Patient',
                'nationality' => 'Updated Nationality',
                'address' => 'Updated Address',
                'blood_type' => 'A+',
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'update-success', $response);
    }

    public function test_update_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/clinic/patients/update'),
            [
                'patient_id' => 'invalid',
                'blood_type' => 'invalid-type',
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'update-error-validation', $response);
    }

    public function test_destroy_success()
    {
        $newPatientInfo = PatientInfo::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);

        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/clinic/patients/delete'),
            ['patient_id' => (string)$newPatientInfo->id],
            $this->authHeaders($this->ownerToken)
        );

        $this->saveFixture(self::DOMAIN, 'destroy-success', $response);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_restore_success()
    {
        $newPatientInfo = PatientInfo::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);
        $newPatientInfo->delete();

        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patients/restore?patient_id=' . $newPatientInfo->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'restore-success', $response);
    }

    public function test_medical_history_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patients/' . $this->patient->id . '/medical-history'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'medical-history-success', $response);
    }

    public function test_medical_history_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patients/99999/medical-history'),
            $this->authHeaders($this->ownerToken)
        );

        $this->saveFixture(self::DOMAIN, 'medical-history-error-not-found', $response);
        $response->assertStatus(500);
    }

    public function test_index_trashed_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/clinic/patients/trashed?clinic_id=' . $this->clinic->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'index-trashed-success', $response);
    }
}
