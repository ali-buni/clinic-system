<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ScheduleTest extends TestCase
{
    const DOMAIN = 'schedules';

    public function test_store_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/schedule/add'),
            [
                'doctor_id' => $this->doctor->id,
                'day_of_week' => 1,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_active' => true,
                'max_patients_per_day' => 15,
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'store-success', $response);
    }

    public function test_store_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/schedule/add'),
            [
                'doctor_id' => 'invalid',
                'day_of_week' => 7,
                'start_time' => 'invalid-time',
                'end_time' => '08:00',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'store-error-validation', $response);
    }

    public function test_store_unauthenticated()
    {
        $response = $this->postJson($this->uri('/clinic-system/clinic/schedule/add'), [
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'store-error-unauthorized', $response);
    }

    public function test_update_success()
    {
        $response = $this->putJson(
            $this->uri('/clinic-system/clinic/schedule/edit'),
            [
                'doctor_id' => $this->doctor->id,
                'day_of_week' => $this->workHour->day_of_week,
                'start_time' => '10:00',
                'end_time' => '16:00',
                'max_patients_per_day' => 10,
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'update-success', $response);
    }

    public function test_destroy_success()
    {
        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/schedule/delete/' . $this->workHour->day_of_week . '/' . $this->doctor->id),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'destroy-success', $response);
    }

    public function test_get_weekly_schedule_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/schedule/get-weekly/' . $this->doctor->id)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'get-weekly-success', $response);
    }

    public function test_get_weekly_schedule_not_found()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/schedule/get-weekly/99999')
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOMAIN, 'get-weekly-error-not-found', $response);
    }

    public function test_get_work_hour_by_date_success()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/schedule/work-hour/' . $this->doctor->id . '?date=' . now()->format('Y-m-d'))
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'work-hour-by-date-success', $response);
    }

    public function test_get_work_hour_by_date_validation_fails()
    {
        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/schedule/work-hour/' . $this->doctor->id . '?date=invalid-date')
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'work-hour-by-date-error-validation', $response);
    }
}
