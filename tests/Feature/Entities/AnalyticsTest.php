<?php

namespace Tests\Feature\Entities;

class AnalyticsTest extends BaseEntityTestCase
{
    protected bool $useHeavySeeder = true;
    protected string $entityName = 'analytics';

    public function test_operational_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/operational');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'operational-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_operational_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/operational');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'operational-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_operational_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/operational');
        $payload = ['period' => ''];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'operational-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_financial_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/financial');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'financial-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_financial_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/financial');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'financial-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_financial_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/financial');
        $payload = ['period' => ''];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'financial-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_patients_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/patients');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'patients-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_patients_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/patients');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'patients-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_patients_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/patients');
        $payload = ['period' => ''];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'patients-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_medical_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/medical');
        $payload = [];
        $response = $this->getJson($endpoint, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'medical-success', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_medical_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/medical');
        $payload = [];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'medical-unauthenticated', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_predictive_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/predictive');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'predictive-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_predictive_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/predictive');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'predictive-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_predictive_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/predictive');
        $payload = ['period' => ''];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'predictive-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_nla_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/nla');
        $payload = ['question' => 'How many patients today?'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'nla-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_nla_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/nla');
        $payload = ['question' => 'How many patients today?'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'nla-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_nla_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/nla');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'nla-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_health_score_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/health-score');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'health-score-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_health_score_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/health-score');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'health-score-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_health_score_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/health-score');
        $payload = ['period' => ''];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'health-score-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_dashboard_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/dashboard');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'dashboard-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_dashboard_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/dashboard');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'dashboard-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_dashboard_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/dashboard');
        $payload = ['period' => ''];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'dashboard-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    // --- Period Variations ---

    public function test_operational_period_year(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/operational');
        $payload = ['period' => 'year'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'operational-period-year', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_operational_period_month(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/operational');
        $payload = ['period' => 'month'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'operational-period-month', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_operational_period_day(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/operational');
        $payload = ['period' => 'day'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'operational-period-day', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_operational_date_range(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/operational');
        $from = now()->subMonth()->format('Y-m-d');
        $to = now()->format('Y-m-d');
        $payload = ['period' => 'total', 'from' => $from, 'to' => $to];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'operational-date-range', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_financial_period_year(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/financial');
        $payload = ['period' => 'year'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'financial-period-year', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_financial_period_month(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/financial');
        $payload = ['period' => 'month'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'financial-period-month', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_financial_date_range(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/financial');
        $from = now()->subMonth()->format('Y-m-d');
        $to = now()->format('Y-m-d');
        $payload = ['period' => 'total', 'from' => $from, 'to' => $to];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'financial-date-range', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_patients_period_year(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/patients');
        $payload = ['period' => 'year'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'patients-period-year', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_patients_period_month(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/patients');
        $payload = ['period' => 'month'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'patients-period-month', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_predictive_period_year(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/predictive');
        $payload = ['period' => 'year'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'predictive-period-year', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_predictive_period_month(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/predictive');
        $payload = ['period' => 'month'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'predictive-period-month', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_predictive_date_range(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/predictive');
        $from = now()->subMonth()->format('Y-m-d');
        $to = now()->format('Y-m-d');
        $payload = ['period' => 'total', 'from' => $from, 'to' => $to];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'predictive-date-range', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_nla_different_question(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/nla');
        $payload = ['question' => 'What is the revenue for this month?'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'nla-different-question', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_nla_patient_question(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/nla');
        $payload = ['question' => 'How many patients visited last month?'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'nla-patient-question', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_health_score_period_year(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/health-score');
        $payload = ['period' => 'year'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'health-score-period-year', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_health_score_date_range(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/health-score');
        $from = now()->subMonth()->format('Y-m-d');
        $to = now()->format('Y-m-d');
        $payload = ['period' => 'total', 'from' => $from, 'to' => $to];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'health-score-date-range', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_dashboard_period_year(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/dashboard');
        $payload = ['period' => 'year'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'dashboard-period-year', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_dashboard_period_month(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/dashboard');
        $payload = ['period' => 'month'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->ownerToken));        $this->saveResult($this->entityName, 'dashboard-period-month', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    // --- Role-based Access ---

    public function test_operational_as_doctor(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/operational');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'operational-as-doctor', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_financial_as_secretary(): void
    {
        $endpoint = $this->v1uri('/clinic-system/analytics/financial');
        $payload = ['period' => 'total'];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->secretaryToken));        $this->saveResult($this->entityName, 'financial-as-secretary', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }
}
