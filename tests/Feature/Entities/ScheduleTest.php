<?php

namespace Tests\Feature\Entities;

use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;

class ScheduleTest extends BaseEntityTestCase
{
    protected string $entityName = 'schedule';

    public function test_store_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules');
        $dayOfWeek = $this->workHour->day_of_week === 0 ? 5 : 0;
        $payload = [
            'doctor_id' => $this->doctor->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'max_patients_per_day' => 10,
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'store-success', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(201);
    }

    public function test_store_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules');
        $payload = [];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'store-validation', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_store_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 5,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ];
        $response = $this->postJson($endpoint, $payload);        $this->saveResult($this->entityName, 'store-unauthenticated', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_store_forbidden(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 5,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ];
        $response = $this->postJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'store-forbidden', 'POST', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_update_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'day_of_week' => $this->workHour->day_of_week,
            'start_time' => '10:00',
            'end_time' => '18:00',
        ];
        $response = $this->putJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'update-success', 'PUT', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_update_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules');
        $payload = [];
        $response = $this->putJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'update-validation', 'PUT', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_update_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules');
        $payload = [
            'doctor_id' => 99999,
            'day_of_week' => 1,
            'start_time' => '10:00',
            'end_time' => '18:00',
        ];
        $response = $this->putJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'update-not-found', 'PUT', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_update_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'day_of_week' => $this->workHour->day_of_week,
            'start_time' => '10:00',
            'end_time' => '18:00',
        ];
        $response = $this->putJson($endpoint, $payload);        $this->saveResult($this->entityName, 'update-unauthenticated', 'PUT', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_update_forbidden(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules');
        $payload = [
            'doctor_id' => $this->doctor->id,
            'day_of_week' => $this->workHour->day_of_week,
            'start_time' => '10:00',
            'end_time' => '18:00',
        ];
        $response = $this->putJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'update-forbidden', 'PUT', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_destroy_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules/' . $this->workHour->day_of_week . '/' . $this->doctor->id);
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'destroy-success', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_destroy_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules/1/99999');
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->doctorToken));        $this->saveResult($this->entityName, 'destroy-not-found', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_destroy_unauthenticated(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules/1/' . $this->doctor->id);
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload);        $this->saveResult($this->entityName, 'destroy-unauthenticated', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(401);
    }

    public function test_destroy_forbidden(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules/' . $this->workHour->day_of_week . '/' . $this->doctor->id);
        $payload = [];
        $response = $this->deleteJson($endpoint, $payload, $this->authHeaders($this->patientToken));        $this->saveResult($this->entityName, 'destroy-forbidden', 'DELETE', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_weekly_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules/weekly/' . $this->doctor->id);
        $payload = [];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'weekly-success', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_weekly_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules/weekly/99999');
        $payload = [];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'weekly-not-found', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }

    public function test_work_hour_success(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules/work-hour/' . $this->doctor->id);
        $date = Carbon::now()->startOfWeek()->format('Y-m-d');
        $payload = ['date' => $date];
        $response = $this->getJson($endpoint . '?date=' . $date);        $this->saveResult($this->entityName, 'work-hour-success', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(200);
    }

    public function test_work_hour_validation(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules/work-hour/' . $this->doctor->id);
        $payload = [];
        $response = $this->getJson($endpoint);        $this->saveResult($this->entityName, 'work-hour-validation', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(422);
    }

    public function test_work_hour_not_found(): void
    {
        $endpoint = $this->v1uri('/clinic-system/clinic/schedules/work-hour/99999');
        $payload = ['date' => '2026-06-30'];
        $response = $this->getJson($endpoint . '?date=2026-06-30');        $this->saveResult($this->entityName, 'work-hour-not-found', 'GET', $endpoint, $payload, $response);
        $response->assertStatus(404);
    }
}
