<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\PatientInfo;
use App\Models\Patient_record;

class AllApiPatientTest extends TestCase
{
    const PATIENTS = 'patients';
    const PATIENT_RECORDS = 'patient-records';

    // ========================================================================
    //  PATIENT ENDPOINTS
    // ========================================================================

    public function test_patients_index_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients?clinic_id=' . $this->clinic->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::PATIENTS, 'index-success', $response);
    }

    public function test_patients_index_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients?clinic_id=' . $this->clinic->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENTS, 'index-error-unauthorized', $response);
    }

    public function test_patients_show_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/' . $this->patient->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::PATIENTS, 'show-success', $response);
    }

    public function test_patients_show_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/99999'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENTS, 'show-error-not-found', $response);
    }

    public function test_patients_show_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/' . $this->patient->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENTS, 'show-error-unauthorized', $response);
    }

    public function test_patients_trashed_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/trashed?clinic_id=' . $this->clinic->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::PATIENTS, 'index-trashed-success', $response);
    }

    public function test_patients_trashed_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/trashed?clinic_id=' . $this->clinic->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENTS, 'index-trashed-error-unauthorized', $response);
    }

    public function test_patients_medical_history_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/' . $this->patient->id . '/medical-history'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::PATIENTS, 'medical-history-success', $response);
    }

    public function test_patients_medical_history_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/99999/medical-history'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENTS, 'medical-history-error-not-found', $response);
    }

    public function test_patients_medical_history_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/' . $this->patient->id . '/medical-history')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENTS, 'medical-history-error-unauthorized', $response);
    }

    public function test_patients_update_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patients/update'),
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
        $this->saveFixture(self::PATIENTS, 'update-success', $response);
    }

    public function test_patients_update_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patients/update'),
            [
                'patient_id' => 'invalid',
                'blood_type' => 'invalid-type',
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::PATIENTS, 'update-error-validation', $response);
    }

    public function test_patients_update_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patients/update'),
            ['patient_id' => $this->patient->id]
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENTS, 'update-error-unauthorized', $response);
    }

    public function test_patients_destroy_success()
    {
        $newPatientInfo = PatientInfo::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/patients/delete'),
            ['patient_id' => (string)$newPatientInfo->id],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::PATIENTS, 'destroy-success', $response);
    }

    public function test_patients_destroy_unauthenticated()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/patients/delete'),
            ['patient_id' => '1']
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENTS, 'destroy-error-unauthorized', $response);
    }

    public function test_patients_restore_success()
    {
        $newPatientInfo = PatientInfo::factory()->create([
            'clinic_id' => $this->clinic->id,
        ]);
        $newPatientInfo->delete();

        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/restore?patient_id=' . $newPatientInfo->id),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::PATIENTS, 'restore-success', $response);
    }

    public function test_patients_restore_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/restore?patient_id=1')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENTS, 'restore-error-unauthorized', $response);
    }

    // ========================================================================
    //  PATIENT RECORD ENDPOINTS
    // ========================================================================

    public function test_records_store_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patient-records'),
            [
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'clinic_id' => $this->clinic->id,
                'appointment_id' => $this->appointment->id,
                'diagnosis_summary' => 'Patient diagnosed with hypertension',
                'description' => 'Patient shows elevated blood pressure levels',
                'status' => 'open',
                'notes' => 'Follow up in 2 weeks',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::PATIENT_RECORDS, 'store-success', $response);
    }

    public function test_records_store_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patient-records'),
            [
                'patient_id' => '',
                'doctor_id' => '',
                'diagnosis_summary' => '',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::PATIENT_RECORDS, 'store-error-validation', $response);
    }

    public function test_records_store_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/clinic/patient-records'), [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'appointment_id' => $this->appointment->id,
            'diagnosis_summary' => 'Test diagnosis',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENT_RECORDS, 'store-error-unauthorized', $response);
    }

    public function test_records_index_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records?clinic_id=' . $this->clinic->id),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::PATIENT_RECORDS, 'index-success', $response);
    }

    public function test_records_index_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records?clinic_id=' . $this->clinic->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENT_RECORDS, 'index-error-unauthorized', $response);
    }

    public function test_records_show_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/' . $this->patientRecord->id),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::PATIENT_RECORDS, 'show-success', $response);
    }

    public function test_records_show_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/99999'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENT_RECORDS, 'show-error-not-found', $response);
    }

    public function test_records_show_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/' . $this->patientRecord->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENT_RECORDS, 'show-error-unauthorized', $response);
    }

    public function test_records_update_success()
    {
        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/patient-records/' . $this->patientRecord->id),
            [
                'diagnosis_summary' => 'Updated diagnosis summary',
                'description' => 'Updated description',
                'status' => 'follow-up',
                'notes' => 'Updated notes',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::PATIENT_RECORDS, 'update-success', $response);
    }

    public function test_records_update_validation_fails()
    {
        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/patient-records/' . $this->patientRecord->id),
            ['status' => 'invalid-status-value'],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::PATIENT_RECORDS, 'update-error-validation', $response);
    }

    public function test_records_update_not_found()
    {
        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/patient-records/99999'),
            ['diagnosis_summary' => 'Updated'],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENT_RECORDS, 'update-error-not-found', $response);
    }

    public function test_records_update_unauthenticated()
    {
        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/patient-records/' . $this->patientRecord->id),
            ['diagnosis_summary' => 'Updated']
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENT_RECORDS, 'update-error-unauthorized', $response);
    }

    public function test_records_destroy_success()
    {
        $newRecord = Patient_record::factory()->create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
        ]);

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/patient-records/' . $newRecord->id),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::PATIENT_RECORDS, 'destroy-success', $response);
    }

    public function test_records_destroy_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/patient-records/99999'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENT_RECORDS, 'destroy-error-not-found', $response);
    }

    public function test_records_destroy_unauthenticated()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/patient-records/' . $this->patientRecord->id),
            []
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENT_RECORDS, 'destroy-error-unauthorized', $response);
    }

    public function test_records_history_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/patient/' . $this->patient->id . '/history'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::PATIENT_RECORDS, 'history-success', $response);
    }

    public function test_records_history_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/patient/99999/history'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENT_RECORDS, 'history-error-not-found', $response);
    }

    public function test_records_history_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/patient/' . $this->patient->id . '/history')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENT_RECORDS, 'history-error-unauthorized', $response);
    }

    public function test_records_get_by_doctor_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/patient/' . $this->patient->id . '/doctor/' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::PATIENT_RECORDS, 'get-by-doctor-success', $response);
    }

    public function test_records_get_by_doctor_patient_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/patient/99999/doctor/' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENT_RECORDS, 'get-by-doctor-error-patient-not-found', $response);
    }

    public function test_records_get_by_doctor_doctor_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/patient/' . $this->patient->id . '/doctor/99999'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENT_RECORDS, 'get-by-doctor-error-doctor-not-found', $response);
    }

    public function test_records_get_by_doctor_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/patient/' . $this->patient->id . '/doctor/' . $this->doctor->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENT_RECORDS, 'get-by-doctor-error-unauthorized', $response);
    }

    public function test_records_rooms_search_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patient-records/rooms/search'),
            ['room_ids' => [$this->room->id]],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::PATIENT_RECORDS, 'rooms-search-success', $response);
    }

    public function test_records_rooms_search_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patient-records/rooms/search'),
            ['room_ids' => []],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::PATIENT_RECORDS, 'rooms-search-error-validation', $response);
    }

    public function test_records_rooms_search_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patient-records/rooms/search'),
            ['room_ids' => [$this->room->id]]
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENT_RECORDS, 'rooms-search-error-unauthorized', $response);
    }

    public function test_records_get_all_by_doctor_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/doctor/' . $this->doctor->id . '/all'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::PATIENT_RECORDS, 'get-all-by-doctor-success', $response);
    }

    public function test_records_get_all_by_doctor_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/doctor/99999/all'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENT_RECORDS, 'get-all-by-doctor-error-not-found', $response);
    }

    public function test_records_get_all_by_doctor_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patient-records/doctor/' . $this->doctor->id . '/all')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::PATIENT_RECORDS, 'get-all-by-doctor-error-unauthorized', $response);
    }

    public function test_patients_destroy_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/patients/delete'),
            ['patient_id' => '99999'],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENTS, 'destroy-error-not-found', $response);
    }

    public function test_patients_destroy_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/patients/delete'),
            ['patient_id' => (string)$this->patient->id],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::PATIENTS, 'destroy-error-forbidden', $response);
    }

    public function test_patients_restore_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/patients/restore?patient_id=99999'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENTS, 'restore-error-not-found', $response);
    }

    public function test_patients_update_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patients/update'),
            [
                'patient_id' => '99999',
                'fname' => 'Test',
            ],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::PATIENTS, 'update-error-not-found', $response);
    }

    public function test_patients_update_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/patients/update'),
            ['patient_id' => (string)$this->patient->id],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::PATIENTS, 'update-error-forbidden', $response);
    }
}
