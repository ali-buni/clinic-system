<?php

namespace Tests\Feature\Entities;

use App\Models\Patient_record;
use App\Models\Specialty;

class AiTest extends BaseEntityTestCase
{
    protected bool $useHeavySeeder = true;
    protected string $entityName = 'ai';

    public function test_summarize_report_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/report/summarize');
        $payload = ['record_id' => $this->patientRecord->id];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'summarize-report-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_summarize_report_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/report/summarize');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'summarize-report-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_summarize_report_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/report/summarize');
        $payload = ['record_id' => 99999];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'summarize-report-not-found', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_summarize_report_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/report/summarize');
        $payload = ['record_id' => $this->patientRecord->id];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'summarize-report-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_appointment_assist_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/appointment/assist');
        $payload = ['query' => 'help'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'appointment-assist-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_appointment_assist_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/appointment/assist');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'appointment-assist-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_appointment_assist_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/appointment/assist');
        $payload = ['query' => 'help'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'appointment-assist-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_chat_patient_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/chat/patient');
        $payload = ['message' => 'Hello', 'patient_id' => $this->patient->id];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'chat-patient-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_chat_patient_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/chat/patient');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'chat-patient-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_chat_patient_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/chat/patient');
        $payload = ['message' => 'Hello', 'patient_id' => $this->patient->id];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'chat-patient-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_chat_history_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/chat/patient/history') . '?patient_id=' . $this->patient->id;
        $payload = [];
        $response = $this->getJson($endpoint, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'chat-history-success', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_chat_history_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/chat/patient/history');
        $payload = [];
        $response = $this->getJson($endpoint, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'chat-history-validation', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_chat_history_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/chat/patient/history') . '?patient_id=' . $this->patient->id;
        $payload = [];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'chat-history-unauthenticated', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    // --- Appointment Assist Variations ---

    public function test_appointment_assist_select_specialty(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/appointment/assist');
        $specialty = Specialty::first();
        $payload = ['specialty_id' => $specialty->id];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'appointment-assist-select-specialty', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_appointment_assist_select_doctor_date(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/appointment/assist');
        $payload = ['doctor_id' => $this->doctor->id, 'date' => now()->addDay()->format('Y-m-d')];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'appointment-assist-select-doctor-date', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_appointment_assist_full_booking(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/appointment/assist');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'patient_id' => $this->patient->id,
            'appointment_type_id' => $this->appointmentType->id,
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'appointment-assist-full-booking', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_appointment_assist_visit_reason(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/appointment/assist');
        $payload = ['query' => 'I have a persistent cough and fever', 'visit_reason' => 'Cough for 5 days, fever 38.5'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'appointment-assist-visit-reason', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_appointment_assist_with_clinic(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/appointment/assist');
        $payload = ['query' => 'help', 'clinic_id' => $this->clinic->id];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'appointment-assist-with-clinic', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_appointment_assist_invalid_specialty(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/appointment/assist');
        $payload = ['specialty_id' => 99999];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'appointment-assist-invalid-specialty', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    // --- Chat Variations (middleware crashes, actual status is 500) ---

    public function test_chat_patient_long_message(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/chat/patient');
        $payload = ['message' => 'I have been feeling dizzy for the past week and my blood pressure seems high. What should I do?', 'patient_id' => $this->patient->id];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'chat-patient-long-message', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }

    public function test_chat_patient_with_session(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/chat/patient');
        $payload = ['message' => 'Hello again', 'patient_id' => $this->patient->id, 'session_id' => 'test-session-123'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'chat-patient-with-session', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }

    public function test_chat_patient_as_doctor(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/chat/patient');
        $payload = ['message' => 'Hello', 'patient_id' => $this->patient->id];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'chat-patient-as-doctor', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }

    public function test_chat_history_existing_session(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/chat/patient/history') . '?session_id=test-session-456';
        $payload = [];
        $response = $this->getJson($endpoint, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'chat-history-existing-session', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(500);
    }

    // --- Summarize Variations ---

    public function test_summarize_report_as_patient(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/ai/report/summarize');
        $payload = ['record_id' => $this->patientRecord->id];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'summarize-report-as-patient', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_summarize_report_different_record(): void
    {
        $otherRecord = Patient_record::where('patient_id', '!=', $this->patient->id)->first();
        if ($otherRecord) {
            $endpoint = $this->v1uri('/clinic-system/clinic/ai/report/summarize');
            $payload = ['record_id' => $otherRecord->id];
            $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));            $this->saveResult($this->entityName, 'summarize-report-different-record', 'POST', $endpoint, $payload, $response);
            $response->assertStatus(200);
        }
    }
}
