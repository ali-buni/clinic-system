<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Schedule_override;

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

    // ---- Schedule Override Tests ----

    public function test_override_store_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/schedule/override/add'),
            [
                'doctor_id'     => $this->doctor->id,
                'override_date' => now()->addDays(5)->format('Y-m-d'),
                'start_time'    => '14:00',
                'end_time'      => '16:00',
                'reason'        => 'Personal appointment',
                'is_closed'     => false,
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'override-store-success', $response);
    }

    public function test_override_store_closed_day_success()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/schedule/override/add'),
            [
                'doctor_id'     => $this->doctor->id,
                'override_date' => now()->addDays(6)->format('Y-m-d'),
                'is_closed'     => true,
                'reason'        => 'Public holiday',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'override-store-closed-day-success', $response);
    }

    public function test_override_store_validation_fails()
    {
        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/schedule/override/add'),
            [
                'doctor_id'     => 'invalid',
                'override_date' => 'not-a-date',
                'end_time'      => '08:00',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'override-store-error-validation', $response);
    }

    public function test_override_store_unauthenticated()
    {
        $response = $this->postJson($this->uri('/clinic-system/clinic/schedule/override/add'), [
            'doctor_id'     => $this->doctor->id,
            'override_date' => now()->addDays(5)->format('Y-m-d'),
            'is_closed'     => true,
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::DOMAIN, 'override-store-error-unauthorized', $response);
    }

    public function test_override_store_date_conflict()
    {
        Schedule_override::factory()->create([
            'doctor_id'     => $this->doctor->id,
            'override_date' => now()->addDays(3)->format('Y-m-d'),
            'is_closed'     => true,
        ]);

        $response = $this->postJson(
            $this->uri('/clinic-system/clinic/schedule/override/add'),
            [
                'doctor_id'     => $this->doctor->id,
                'override_date' => now()->addDays(3)->format('Y-m-d'),
                'start_time'    => '10:00',
                'end_time'      => '12:00',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOMAIN, 'override-store-error-date-conflict', $response);
    }

    public function test_override_update_success()
    {
        $override = Schedule_override::factory()->create([
            'doctor_id'     => $this->doctor->id,
            'override_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time'    => '09:00',
            'end_time'      => '12:00',
            'reason'        => 'Training',
            'is_closed'     => false,
        ]);

        $response = $this->putJson(
            $this->uri('/clinic-system/clinic/schedule/override/' . $override->id . '/edit'),
            [
                'doctor_id'     => $this->doctor->id,
                'start_time'    => '10:00',
                'end_time'      => '14:00',
                'reason'        => 'Extended training',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'override-update-success', $response);
    }

    public function test_override_destroy_success()
    {
        $override = Schedule_override::factory()->create([
            'doctor_id'     => $this->doctor->id,
            'override_date' => now()->addDays(15)->format('Y-m-d'),
            'is_closed'     => true,
        ]);

        $response = $this->deleteJson(
            $this->uri('/clinic-system/clinic/schedule/override/' . $override->id . '/delete'),
            ['doctor_id' => $this->doctor->id],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOMAIN, 'override-destroy-success', $response);
    }

    public function test_override_show_success()
    {
        $override = Schedule_override::factory()->create([
            'doctor_id'     => $this->doctor->id,
            'override_date' => now()->addDays(20)->format('Y-m-d'),
            'is_closed'     => false,
            'start_time'    => '08:00',
            'end_time'      => '11:00',
        ]);

        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/schedule/override/' . $override->id . '?doctor_id=' . $this->doctor->id)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'override-show-success', $response);
    }

    public function test_override_index_success()
    {
        Schedule_override::factory()->count(3)->create([
            'doctor_id' => $this->doctor->id,
        ]);

        $response = $this->getJson(
            $this->uri('/clinic-system/clinic/schedule/override?doctor_id=' . $this->doctor->id)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'override-index-success', $response);
    }

    public function test_override_get_by_date_success()
    {
        $date = now()->addDays(25)->format('Y-m-d');
        Schedule_override::factory()->create([
            'doctor_id'     => $this->doctor->id,
            'override_date' => $date,
            'is_closed'     => true,
        ]);

        $response = $this->getJson(
            $this->uri("/clinic-system/clinic/schedule/override/date/single?doctor_id={$this->doctor->id}&date={$date}")
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'override-get-by-date-success', $response);
    }

    public function test_override_get_by_date_range_success()
    {
        $from = now()->addDays(1)->format('Y-m-d');
        $to   = now()->addDays(7)->format('Y-m-d');

        Schedule_override::factory()->create([
            'doctor_id'     => $this->doctor->id,
            'override_date' => now()->addDays(2)->format('Y-m-d'),
            'is_closed'     => false,
            'start_time'    => '12:00',
            'end_time'      => '14:00',
        ]);

        $response = $this->getJson(
            $this->uri("/clinic-system/clinic/schedule/override/date/range?doctor_id={$this->doctor->id}&from={$from}&to={$to}")
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOMAIN, 'override-get-by-date-range-success', $response);
    }
}
