<?php

namespace Tests\Feature\Entities;

use App\Models\Schedule_override;
use Carbon\Carbon;

class ScheduleOverrideTest extends BaseEntityTestCase
{
    protected string $entityName = 'schedule-override';

    public function test_store_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'override_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
            'override_type' => 'time_change',
            'start_time' => '10:00',
            'end_time' => '14:00',
            'reason' => 'Special schedule',
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'store-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(201);
    }

    public function test_store_closed_day_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'override_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'override_type' => 'closed',
            'reason' => 'Public holiday',
            'is_closed' => true,
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'store-closed-day-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(201);
    }

    public function test_store_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'store-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_store_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'override_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'store-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_store_duplicate_date(): void
    {
        $existingOverride = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'override_date' => $existingOverride->override_date,
            'is_closed' => true,
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'store-duplicate-date', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_store_forbidden(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'override_date' => Carbon::now()->addDays(25)->format('Y-m-d'),
            'override_type' => 'time_change',
            'start_time' => '09:00',
            'end_time' => '13:00',
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'store-forbidden', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(201);
    }

    public function test_update_success(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id);
        $payload = [
            'doctor_id' => $this->doctor->id,
            'reason' => 'Updated reason',
        ];
        $response = $this->putJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'update-success', 'PUT', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_update_validation(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id);
        $payload = [];
        $response = $this->putJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'update-validation', 'PUT', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_update_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/99999');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'reason' => 'Does not matter',
        ];
        $response = $this->putJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'update-not-found', 'PUT', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_update_unauthenticated(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id);
        $payload = [
            'doctor_id' => $this->doctor->id,
            'reason' => 'Does not matter',
        ];
        $response = $this->putJson($endpoint, $payload);        $this->saveResult($this->entityName, 'update-unauthenticated', 'PUT', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_update_forbidden(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id);
        $payload = [
            'doctor_id' => $this->doctor->id,
            'reason' => 'Does not matter',
        ];
        $response = $this->putJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'update-forbidden', 'PUT', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_destroy_success(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id);
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'destroy-success', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_destroy_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/99999');
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'destroy-not-found', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_destroy_unauthenticated(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id);
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->deleteJson($endpoint, $payload);        $this->saveResult($this->entityName, 'destroy-unauthenticated', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_destroy_forbidden(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id);
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'destroy-forbidden', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_show_success(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id) . '?doctor_id=' . $this->doctor->id;
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->getJson($endpoint, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'show-success', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_show_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/99999') . '?doctor_id=' . $this->doctor->id;
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->getJson($endpoint, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'show-not-found', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_show_unauthenticated(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id) . '?doctor_id=' . $this->doctor->id;
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'show-unauthenticated', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_index_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides') . '?doctor_id=' . $this->doctor->id;
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->getJson($endpoint, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'index-success', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_index_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides') . '?doctor_id=' . $this->doctor->id;
        $payload = ['doctor_id' => $this->doctor->id];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'index-unauthenticated', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_get_by_date_success(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/date/single')
            . '?doctor_id=' . $this->doctor->id
            . '&date=' . $override->override_date;
        $payload = ['doctor_id' => $this->doctor->id, 'date' => $override->override_date];
        $response = $this->getJson($endpoint, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'get-by-date-success', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_get_by_date_unauthenticated(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/date/single')
            . '?doctor_id=' . $this->doctor->id
            . '&date=' . $override->override_date;
        $payload = ['doctor_id' => $this->doctor->id, 'date' => $override->override_date];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'get-by-date-unauthenticated', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_get_by_date_range_success(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $from = Carbon::parse($override->override_date)->subDay()->format('Y-m-d');
        $to = Carbon::parse($override->override_date)->addDay()->format('Y-m-d');
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/date/range')
            . '?doctor_id=' . $this->doctor->id
            . '&from=' . $from
            . '&to=' . $to;
        $payload = ['doctor_id' => $this->doctor->id, 'from' => $from, 'to' => $to];
        $response = $this->getJson($endpoint, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'get-by-date-range-success', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_get_by_date_range_unauthenticated(): void
    {
        $override = $this->doctor->scheduleOverrides()->first();
        $from = Carbon::parse($override->override_date)->subDay()->format('Y-m-d');
        $to = Carbon::parse($override->override_date)->addDay()->format('Y-m-d');
        $endpoint = $this->v1uri('/clinic-system/clinic/schedule-overrides/date/range')
            . '?doctor_id=' . $this->doctor->id
            . '&from=' . $from
            . '&to=' . $to;
        $payload = ['doctor_id' => $this->doctor->id, 'from' => $from, 'to' => $to];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'get-by-date-range-unauthenticated', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }
}
