<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Appointment_type;

class AllApiMiscTest extends TestCase
{
    const ANALYTICS_DOMAIN = 'analytics';
    const AI_DOMAIN = 'ai';
    const MEDICINES_DOMAIN = 'medicines';
    const DISEASES_DOMAIN = 'diseases';
    const APPOINTMENT_TYPES_DOMAIN = 'appointment-types';
    const FILTER_DOMAIN = 'filter';
    const GOOGLE_AUTH_DOMAIN = 'google-auth';
    const DEVICES_DOMAIN = 'devices';

    // ====================================================================
    // ANALYTICS ENDPOINTS
    // ====================================================================

    public function test_analytics_operational_report_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/operational'),
            ['period' => 'total'],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'operational-report-success', $response);
    }

    public function test_analytics_operational_report_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/analytics/operational'), ['period' => 'total']);
        $response->assertStatus(401);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'operational-report-error-unauthorized', $response);
    }

    public function test_analytics_financial_report_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/financial'),
            ['period' => 'total'],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'financial-report-success', $response);
    }

    public function test_analytics_financial_report_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/analytics/financial'), ['period' => 'total']);
        $response->assertStatus(401);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'financial-report-error-unauthorized', $response);
    }

    public function test_analytics_patient_report_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/patients'),
            ['period' => 'total'],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'patient-report-success', $response);
    }

    public function test_analytics_patient_report_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/analytics/patients'), ['period' => 'total']);
        $response->assertStatus(401);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'patient-report-error-unauthorized', $response);
    }

    public function test_analytics_medical_report_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/analytics/medical'),
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'medical-report-success', $response);
    }

    public function test_analytics_medical_report_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/analytics/medical'));
        $response->assertStatus(401);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'medical-report-error-unauthorized', $response);
    }

    public function test_analytics_predictive_report_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/predictive'),
            ['period' => 'total'],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'predictive-report-success', $response);
    }

    public function test_analytics_predictive_report_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/analytics/predictive'), ['period' => 'total']);
        $response->assertStatus(401);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'predictive-report-error-unauthorized', $response);
    }

    public function test_analytics_nla_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/nla'),
            ['question' => 'How many patients today?'],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'nla-ask-success', $response);
    }

    public function test_analytics_nla_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/nla'),
            [],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'nla-ask-error-validation', $response);
    }

    public function test_analytics_nla_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/analytics/nla'), ['question' => 'test']);
        $response->assertStatus(401);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'nla-ask-error-unauthorized', $response);
    }

    public function test_analytics_health_score_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/health-score'),
            ['period' => 'total'],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'health-score-success', $response);
    }

    public function test_analytics_health_score_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/analytics/health-score'), ['period' => 'total']);
        $response->assertStatus(401);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'health-score-error-unauthorized', $response);
    }

    public function test_analytics_dashboard_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/dashboard'),
            ['period' => 'total'],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'dashboard-success', $response);
    }

    public function test_analytics_dashboard_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/analytics/dashboard'), ['period' => 'total']);
        $response->assertStatus(401);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'dashboard-error-unauthorized', $response);
    }

    // ====================================================================
    // AI ENDPOINTS
    // ====================================================================

    public function test_ai_summarize_report_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/ai/report/summarize'),
            ['record_id' => $this->patientRecord->id],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::AI_DOMAIN, 'summarize-report-success', $response);
    }

    public function test_ai_summarize_report_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/ai/report/summarize'),
            [],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::AI_DOMAIN, 'summarize-report-error-validation', $response);
    }

    public function test_ai_summarize_report_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/ai/report/summarize'),
            ['record_id' => 99999],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(404);
        $this->saveFixture(self::AI_DOMAIN, 'summarize-report-error-not-found', $response);
    }

    public function test_ai_summarize_report_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/clinic/ai/report/summarize'), ['record_id' => 1]);
        $response->assertStatus(401);
        $this->saveFixture(self::AI_DOMAIN, 'summarize-report-error-unauthorized', $response);
    }

    public function test_ai_appointment_assist_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/ai/appointment/assist'),
            ['query' => 'help'],
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::AI_DOMAIN, 'appointment-assist-success', $response);
    }

    public function test_ai_appointment_assist_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/ai/appointment/assist'),
            [],
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::AI_DOMAIN, 'appointment-assist-error-validation', $response);
    }

    public function test_ai_appointment_assist_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/clinic/ai/appointment/assist'), ['query' => 'help']);
        $response->assertStatus(401);
        $this->saveFixture(self::AI_DOMAIN, 'appointment-assist-error-unauthorized', $response);
    }

    public function test_ai_chat_patient_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/ai/chat/patient'),
            ['message' => 'Hello', 'patient_id' => $this->patient->id],
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::AI_DOMAIN, 'chat-patient-success', $response);
    }

    public function test_ai_chat_patient_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/ai/chat/patient'),
            [],
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::AI_DOMAIN, 'chat-patient-error-validation', $response);
    }

    public function test_ai_chat_patient_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/clinic/ai/chat/patient'), ['message' => 'Hi', 'patient_id' => 1]);
        $response->assertStatus(401);
        $this->saveFixture(self::AI_DOMAIN, 'chat-patient-error-unauthorized', $response);
    }

    public function test_ai_chat_history_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/ai/chat/patient/history?patient_id=' . $this->patient->id),
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::AI_DOMAIN, 'chat-history-success', $response);
    }

    public function test_ai_chat_history_validation_fails()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/ai/chat/patient/history'),
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::AI_DOMAIN, 'chat-history-error-validation', $response);
    }

    public function test_ai_chat_history_unauthenticated()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/ai/chat/patient/history'));
        $response->assertStatus(401);
        $this->saveFixture(self::AI_DOMAIN, 'chat-history-error-unauthorized', $response);
    }

    // ====================================================================
    // MEDICINES ENDPOINTS
    // ====================================================================

    public function test_medicines_search_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/medicines/search?query=Para')
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::MEDICINES_DOMAIN, 'search-success', $response);
    }

    public function test_medicines_search_validation_fails()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/medicines/search')
        );
        $response->assertStatus(422);
        $this->saveFixture(self::MEDICINES_DOMAIN, 'search-error-validation', $response);
    }

    public function test_medicines_store_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/medicines'),
            [
                'en_name' => 'Test',
                'ar_name' => 'اختبار',
                'form' => 'tablet',
                'strength' => '500mg',
            ],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::MEDICINES_DOMAIN, 'store-success', $response);
    }

    public function test_medicines_store_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/medicines'),
            ['form' => 'invalid-form'],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::MEDICINES_DOMAIN, 'store-error-validation', $response);
    }

    public function test_medicines_store_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/clinic/medicines'), [
            'en_name' => 'Test',
            'ar_name' => 'اختبار',
            'form' => 'tablet',
        ]);
        $response->assertStatus(401);
        $this->saveFixture(self::MEDICINES_DOMAIN, 'store-error-unauthenticated', $response);
    }

    // ====================================================================
    // DISEASES ENDPOINTS
    // ====================================================================

    public function test_diseases_search_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/diseases/search?query=Dia')
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DISEASES_DOMAIN, 'search-success', $response);
    }

    public function test_diseases_search_validation_fails()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/diseases/search')
        );
        $response->assertStatus(422);
        $this->saveFixture(self::DISEASES_DOMAIN, 'search-error-validation', $response);
    }

    public function test_diseases_store_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/diseases'),
            [
                'en_name' => 'Test',
                'ar_name' => 'اختبار',
                'disease_nature' => 'chronic',
            ],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DISEASES_DOMAIN, 'store-success', $response);
    }

    public function test_diseases_store_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/diseases'),
            [
                'ar_name' => '',
                'en_name' => '',
                'disease_nature' => 'invalid',
            ],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::DISEASES_DOMAIN, 'store-error-validation', $response);
    }

    public function test_diseases_store_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/clinic/diseases'), [
            'en_name' => 'Test',
            'ar_name' => 'اختبار',
            'disease_nature' => 'chronic',
        ]);
        $response->assertStatus(401);
        $this->saveFixture(self::DISEASES_DOMAIN, 'store-error-unauthenticated', $response);
    }

    // ====================================================================
    // APPOINTMENT TYPES ENDPOINTS
    // ====================================================================

    public function test_appointment_types_index_success()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/appointment-types'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::APPOINTMENT_TYPES_DOMAIN, 'index-success', $response);
    }

    public function test_appointment_types_add_success()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/appointment-types'), [
            'ar_name' => 'موعد',
            'en_name' => 'Appt',
            'types' => 1,
        ]);
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::APPOINTMENT_TYPES_DOMAIN, 'add-success', $response);
    }

    public function test_appointment_types_add_validation_fails()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/appointment-types'), [
            'ar_name' => '',
            'en_name' => '',
            'types' => 0,
        ]);
        $response->assertStatus(422);
        $this->saveFixture(self::APPOINTMENT_TYPES_DOMAIN, 'add-error-validation', $response);
    }

    public function test_appointment_types_delete_success()
    {
        $type = Appointment_type::create([
            'ar_name' => 'Temporary',
            'en_name' => 'Temp',
            'types' => 1,
        ]);

        $response = $this->deleteJson($this->v1uri('/clinic-system/appointment-types/' . $type->id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::APPOINTMENT_TYPES_DOMAIN, 'delete-success', $response);
    }

    public function test_appointment_types_delete_not_found()
    {
        $response = $this->deleteJson($this->v1uri('/clinic-system/appointment-types/99999'));
        $response->assertStatus(404);
        $this->saveFixture(self::APPOINTMENT_TYPES_DOMAIN, 'delete-error-not-found', $response);
    }

    // ====================================================================
    // FILTER ENDPOINT
    // ====================================================================

    public function test_filter_success()
    {
        $response = $this->getJson($this->uri('/filter'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data', 'pagination']);
        $this->saveFixture(self::FILTER_DOMAIN, 'filter-users-success', $response);
    }

    public function test_filter_with_params_success()
    {
        $response = $this->getJson($this->uri('/filter?per_page=5&page=1'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data', 'pagination']);
        $this->saveFixture(self::FILTER_DOMAIN, 'filter-users-with-params-success', $response);
    }

    public function test_filter_invalid_per_page()
    {
        $response = $this->getJson($this->uri('/filter?per_page=999'));
        $response->assertStatus(422);
        $this->saveFixture(self::FILTER_DOMAIN, 'filter-users-error-validation', $response);
    }

    // ====================================================================
    // GOOGLE AUTH ENDPOINTS
    // ====================================================================

    public function test_google_auth_redirect_success()
    {
        $response = $this->getJson($this->uri('/auth/google'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['url']]);
        $this->saveFixture(self::GOOGLE_AUTH_DOMAIN, 'redirect-success', $response);
    }

    public function test_google_auth_callback_missing_code()
    {
        $response = $this->getJson($this->uri('/auth/google/callback'));
        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
        $this->saveFixture(self::GOOGLE_AUTH_DOMAIN, 'callback-error-invalid-credentials', $response);
    }

    public function test_google_auth_callback_with_code()
    {
        $response = $this->getJson($this->uri('/auth/google/callback?code=invalid-test-code'));
        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
        $this->saveFixture(self::GOOGLE_AUTH_DOMAIN, 'callback-with-invalid-code', $response);
    }

    // ====================================================================
    // DEVICE ENDPOINT
    // ====================================================================

    public function test_devices_register_token_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'sample-fcm-token-12345'],
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(200);
        $this->saveFixture(self::DEVICES_DOMAIN, 'register-token-response', $response);
    }

    public function test_devices_register_token_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => ''],
            $this->authHeaders($this->patientToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::DEVICES_DOMAIN, 'register-token-error-validation', $response);
    }

    public function test_devices_register_token_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/devices/register-token'),
            ['fcm_token' => 'sample-fcm-token']
        );
        $response->assertStatus(401);
        $this->saveFixture(self::DEVICES_DOMAIN, 'register-token-error-unauthorized', $response);
    }

    public function test_analytics_operational_report_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/operational'),
            ['period' => ''],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'operational-report-error-validation', $response);
    }

    public function test_analytics_financial_report_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/financial'),
            ['period' => ''],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'financial-report-error-validation', $response);
    }

    public function test_analytics_patient_report_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/patients'),
            ['period' => ''],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'patient-report-error-validation', $response);
    }

    public function test_analytics_predictive_report_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/predictive'),
            ['period' => ''],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'predictive-report-error-validation', $response);
    }

    public function test_analytics_health_score_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/health-score'),
            ['period' => ''],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'health-score-error-validation', $response);
    }

    public function test_analytics_dashboard_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/analytics/dashboard'),
            ['period' => ''],
            $this->authHeaders($this->ownerToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::ANALYTICS_DOMAIN, 'dashboard-error-validation', $response);
    }
}
