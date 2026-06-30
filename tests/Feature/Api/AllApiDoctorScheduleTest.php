<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\Schedule_override;

class AllApiDoctorScheduleTest extends TestCase
{
    const DOCTORS = 'doctors';
    const SCHEDULES = 'schedules';
    const SPECIALTIES = 'specialties';

    // ====================================================================
    //  DOCTOR ENDPOINTS
    // ====================================================================

    public function test_doctors_info_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $this->doctor->id . '/info'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOCTORS, 'info-success', $response);
    }

    public function test_doctors_info_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/doctors/99999/info'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOCTORS, 'info-error-not-found', $response);
    }

    public function test_doctors_info_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $this->doctor->id . '/info')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOCTORS, 'info-error-unauthorized', $response);
    }

    public function test_doctors_update_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/update'),
            [
                'appointment_duration' => 45,
                'consultation_fee' => 200,
                'bio' => 'Updated bio information',
                'specialties' => [Specialty::first()->id],
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOCTORS, 'update-success', $response);
    }

    public function test_doctors_update_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/update'),
            [
                'appointment_duration' => 0,
                'consultation_fee' => -10,
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::DOCTORS, 'update-error-validation', $response);
    }

    public function test_doctors_update_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/update'),
            ['appointment_duration' => 30]
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOCTORS, 'update-error-unauthorized', $response);
    }

    public function test_doctors_filter_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/doctors/filter'),
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::DOCTORS, 'index-success', $response);
    }

    public function test_doctors_filter_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/doctors/filter')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOCTORS, 'index-error-unauthorized', $response);
    }

    public function test_doctors_destroy_success()
    {
        $newDoctorUser = \App\Models\User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/leave'),
            [],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOCTORS, 'destroy-success', $response);
    }

    public function test_doctors_destroy_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/doctors/99999/leave'),
            [],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOCTORS, 'destroy-error-not-found', $response);
    }

    public function test_doctors_destroy_unauthenticated()
    {
        $newDoctorUser = \App\Models\User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/leave')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOCTORS, 'destroy-error-unauthorized', $response);
    }

    public function test_doctors_restore_success()
    {
        $newDoctorUser = \App\Models\User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);
        $newDoctor->delete();

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/restore'),
            [],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOCTORS, 'restore-success', $response);
    }

    public function test_doctors_restore_not_found()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/99999/restore'),
            [],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOCTORS, 'restore-error-not-found', $response);
    }

    public function test_doctors_restore_unauthenticated()
    {
        $newDoctorUser = \App\Models\User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);
        $newDoctor->delete();

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/restore')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOCTORS, 'restore-error-unauthorized', $response);
    }

    public function test_doctors_force_delete_success()
    {
        $newDoctorUser = \App\Models\User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);
        $newDoctor->delete();

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/force'),
            [],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::DOCTORS, 'force-delete-success', $response);
    }

    public function test_doctors_force_delete_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/doctors/99999/force'),
            [],
            $this->authHeaders($this->ownerToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::DOCTORS, 'force-delete-error-not-found', $response);
    }

    public function test_doctors_force_delete_unauthenticated()
    {
        $newDoctorUser = \App\Models\User::factory()->create();
        $newDoctorUser->assignRole('doctor');
        $newDoctor = Doctor::factory()->create([
            'user_id' => $newDoctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
        ]);
        $newDoctor->delete();

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $newDoctor->id . '/force')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::DOCTORS, 'force-delete-error-unauthorized', $response);
    }

    // ====================================================================
    //  SCHEDULE WORK HOUR ENDPOINTS
    // ====================================================================

    public function test_schedules_store_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/schedules'),
            [
                'doctor_id' => $this->doctor->id,
                'day_of_week' => 2,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_active' => true,
                'max_patients_per_day' => 15,
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::SCHEDULES, 'store-success', $response);
    }

    public function test_schedules_store_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/schedules'),
            [
                'doctor_id' => 'invalid',
                'day_of_week' => 7,
                'start_time' => 'invalid-time',
                'end_time' => '08:00',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::SCHEDULES, 'store-error-validation', $response);
    }

    public function test_schedules_store_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/clinic/schedules'), [
            'doctor_id' => $this->doctor->id,
            'day_of_week' => 2,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::SCHEDULES, 'store-error-unauthorized', $response);
    }

    public function test_schedules_update_success()
    {
        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/schedules'),
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
        $this->saveFixture(self::SCHEDULES, 'update-success', $response);
    }

    public function test_schedules_update_validation_fails()
    {
        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/schedules'),
            [
                'doctor_id' => 'invalid',
                'day_of_week' => 7,
                'start_time' => 'invalid',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::SCHEDULES, 'update-error-validation', $response);
    }

    public function test_schedules_update_not_found()
    {
        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/schedules'),
            [
                'doctor_id' => $this->doctor->id,
                'day_of_week' => 6,
                'start_time' => '09:00',
                'end_time' => '17:00',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::SCHEDULES, 'update-error-not-found', $response);
    }

    public function test_schedules_update_unauthenticated()
    {
        $response = $this->putJson($this->v1uri('/clinic-system/clinic/schedules'), [
            'doctor_id' => $this->doctor->id,
            'day_of_week' => $this->workHour->day_of_week,
            'start_time' => '10:00',
            'end_time' => '16:00',
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::SCHEDULES, 'update-error-unauthorized', $response);
    }

    public function test_schedules_destroy_success()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/schedules/' . $this->workHour->day_of_week . '/' . $this->doctor->id),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::SCHEDULES, 'destroy-success', $response);
    }

    public function test_schedules_destroy_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/schedules/1/99999'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::SCHEDULES, 'destroy-error-not-found', $response);
    }

    public function test_schedules_destroy_unauthenticated()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/schedules/' . $this->workHour->day_of_week . '/' . $this->doctor->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::SCHEDULES, 'destroy-error-unauthorized', $response);
    }

    public function test_schedules_weekly_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedules/weekly/' . $this->doctor->id)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::SCHEDULES, 'get-weekly-success', $response);
    }

    public function test_schedules_weekly_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedules/weekly/99999')
        );

        $response->assertStatus(404);
        $this->saveFixture(self::SCHEDULES, 'get-weekly-error-not-found', $response);
    }

    public function test_schedules_work_hour_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedules/work-hour/' . $this->doctor->id . '?date=' . now()->format('Y-m-d'))
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::SCHEDULES, 'work-hour-by-date-success', $response);
    }

    public function test_schedules_work_hour_validation_fails()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedules/work-hour/' . $this->doctor->id . '?date=invalid-date')
        );

        $response->assertStatus(422);
        $this->saveFixture(self::SCHEDULES, 'work-hour-by-date-error-validation', $response);
    }

    // ====================================================================
    //  SCHEDULE OVERRIDE ENDPOINTS
    // ====================================================================

    public function test_overrides_store_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides'),
            [
                'doctor_id' => $this->doctor->id,
                'override_date' => now()->addDays(5)->format('Y-m-d'),
                'start_time' => '14:00',
                'end_time' => '16:00',
                'reason' => 'Personal appointment',
                'is_closed' => false,
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::SCHEDULES, 'override-store-success', $response);
    }

    public function test_overrides_store_closed_day_success()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides'),
            [
                'doctor_id' => $this->doctor->id,
                'override_date' => now()->addDays(6)->format('Y-m-d'),
                'is_closed' => true,
                'reason' => 'Public holiday',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::SCHEDULES, 'override-store-closed-day-success', $response);
    }

    public function test_overrides_store_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides'),
            [
                'doctor_id' => 'invalid',
                'override_date' => 'not-a-date',
                'end_time' => '08:00',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::SCHEDULES, 'override-store-error-validation', $response);
    }

    public function test_overrides_store_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/clinic/schedule-overrides'), [
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(5)->format('Y-m-d'),
            'is_closed' => true,
        ]);

        $response->assertStatus(401);
        $this->saveFixture(self::SCHEDULES, 'override-store-error-unauthorized', $response);
    }

    public function test_overrides_store_duplicate_date()
    {
        Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(3)->format('Y-m-d'),
            'is_closed' => true,
        ]);

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides'),
            [
                'doctor_id' => $this->doctor->id,
                'override_date' => now()->addDays(3)->format('Y-m-d'),
                'start_time' => '10:00',
                'end_time' => '12:00',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::SCHEDULES, 'override-store-error-date-conflict', $response);
    }

    public function test_overrides_index_success()
    {
        Schedule_override::factory()->count(3)->create([
            'doctor_id' => $this->doctor->id,
        ]);

        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides?doctor_id=' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::SCHEDULES, 'override-index-success', $response);
    }

    public function test_overrides_index_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::SCHEDULES, 'override-index-error-unauthorized', $response);
    }

    public function test_overrides_show_success()
    {
        $override = Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(20)->format('Y-m-d'),
            'is_closed' => false,
            'start_time' => '08:00',
            'end_time' => '11:00',
        ]);

        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::SCHEDULES, 'override-show-success', $response);
    }

    public function test_overrides_show_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/99999'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::SCHEDULES, 'override-show-error-not-found', $response);
    }

    public function test_overrides_show_unauthenticated()
    {
        $override = Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(20)->format('Y-m-d'),
        ]);

        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::SCHEDULES, 'override-show-error-unauthorized', $response);
    }

    public function test_overrides_update_success()
    {
        $override = Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '12:00',
            'reason' => 'Training',
            'is_closed' => false,
        ]);

        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id),
            [
                'doctor_id' => $this->doctor->id,
                'start_time' => '10:00',
                'end_time' => '14:00',
                'reason' => 'Extended training',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::SCHEDULES, 'override-update-success', $response);
    }

    public function test_overrides_update_validation_fails()
    {
        $override = Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(11)->format('Y-m-d'),
            'is_closed' => true,
        ]);

        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id),
            [
                'doctor_id' => 'invalid',
                'override_date' => 'not-a-date',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(422);
        $this->saveFixture(self::SCHEDULES, 'override-update-error-validation', $response);
    }

    public function test_overrides_update_not_found()
    {
        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/99999'),
            [
                'doctor_id' => $this->doctor->id,
                'start_time' => '10:00',
                'end_time' => '14:00',
            ],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::SCHEDULES, 'override-update-error-not-found', $response);
    }

    public function test_overrides_update_unauthenticated()
    {
        $override = Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(12)->format('Y-m-d'),
            'is_closed' => true,
        ]);

        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id),
            [
                'doctor_id' => $this->doctor->id,
                'start_time' => '10:00',
            ]
        );

        $response->assertStatus(401);
        $this->saveFixture(self::SCHEDULES, 'override-update-error-unauthorized', $response);
    }

    public function test_overrides_destroy_success()
    {
        $override = Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(15)->format('Y-m-d'),
            'is_closed' => true,
        ]);

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::SCHEDULES, 'override-destroy-success', $response);
    }

    public function test_overrides_destroy_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/99999'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::SCHEDULES, 'override-destroy-error-not-found', $response);
    }

    public function test_overrides_destroy_unauthenticated()
    {
        $override = Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(16)->format('Y-m-d'),
            'is_closed' => true,
        ]);

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id)
        );

        $response->assertStatus(401);
        $this->saveFixture(self::SCHEDULES, 'override-destroy-error-unauthorized', $response);
    }

    public function test_overrides_get_by_date_success()
    {
        $date = now()->addDays(25)->format('Y-m-d');
        Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => $date,
            'is_closed' => true,
        ]);

        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/date/single?doctor_id=' . $this->doctor->id . '&date=' . $date),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::SCHEDULES, 'override-get-by-date-success', $response);
    }

    public function test_overrides_get_by_date_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/date/single?doctor_id=' . $this->doctor->id . '&date=' . now()->format('Y-m-d'))
        );

        $response->assertStatus(401);
        $this->saveFixture(self::SCHEDULES, 'override-get-by-date-error-unauthorized', $response);
    }

    public function test_overrides_get_by_date_range_success()
    {
        $from = now()->addDays(1)->format('Y-m-d');
        $to = now()->addDays(7)->format('Y-m-d');

        Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(2)->format('Y-m-d'),
            'is_closed' => false,
            'start_time' => '12:00',
            'end_time' => '14:00',
        ]);

        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/date/range?doctor_id=' . $this->doctor->id . '&from=' . $from . '&to=' . $to),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::SCHEDULES, 'override-get-by-date-range-success', $response);
    }

    public function test_overrides_get_by_date_range_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/date/range?doctor_id=' . $this->doctor->id . '&from=2026-01-01&to=2026-01-07')
        );

        $response->assertStatus(401);
        $this->saveFixture(self::SCHEDULES, 'override-get-by-date-range-error-unauthorized', $response);
    }

    // ====================================================================
    //  SPECIALTY ENDPOINTS
    // ====================================================================

    public function test_specialties_index_success()
    {
        $response = $this->getJson($this->v1uri('/clinic-system/clinic/specialties'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::SPECIALTIES, 'index-success', $response);
    }

    public function test_specialties_attach_success()
    {
        $specialty = Specialty::create(['ar_name' => 'اختبار', 'en_name' => 'Test Spec']);
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties'),
            ['specialty_ids' => [$specialty->id]],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::SPECIALTIES, 'attach-specialties-success', $response);
    }

    public function test_specialties_attach_validation_fails()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties'),
            ['specialty_ids' => []],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(422);
        $this->saveFixture(self::SPECIALTIES, 'attach-specialties-error-validation', $response);
    }

    public function test_specialties_attach_unauthenticated()
    {
        $response = $this->postJson($this->v1uri('/clinic-system/clinic/specialties'), [
            'specialty_ids' => [1],
        ]);
        $response->assertStatus(401);
        $this->saveFixture(self::SPECIALTIES, 'attach-specialties-error-unauthorized', $response);
    }

    public function test_specialties_detach_success()
    {
        $specialty = Specialty::create(['ar_name' => 'اختبار', 'en_name' => 'Test Spec']);
        $this->doctor->specialties()->sync([$specialty->id]);

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/specialties/' . $specialty->id),
            [],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::SPECIALTIES, 'detach-specialty-success', $response);
    }

    public function test_specialties_detach_not_found()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/specialties/99999'),
            [],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(500);
        $this->saveFixture(self::SPECIALTIES, 'detach-specialty-error-not-found', $response);
    }

    public function test_specialties_detach_unauthenticated()
    {
        $specialty = Specialty::create(['ar_name' => 'اختبار', 'en_name' => 'Test Spec']);
        $this->doctor->specialties()->sync([$specialty->id]);

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/specialties/' . $specialty->id)
        );
        $response->assertStatus(401);
        $this->saveFixture(self::SPECIALTIES, 'detach-specialty-error-unauthorized', $response);
    }

    public function test_specialties_change_primary_success()
    {
        $specialty1 = Specialty::create(['ar_name' => 'اختبار1', 'en_name' => 'Test Spec 1']);
        $specialty2 = Specialty::create(['ar_name' => 'اختبار2', 'en_name' => 'Test Spec 2']);
        $this->doctor->specialties()->sync([
            $specialty1->id => ['is_primary' => true],
            $specialty2->id => ['is_primary' => false],
        ]);

        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties/' . $specialty2->id . '/primary'),
            [],
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->saveFixture(self::SPECIALTIES, 'change-primary-success', $response);
    }

    public function test_specialties_change_primary_unauthenticated()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties/1/primary')
        );
        $response->assertStatus(401);
        $this->saveFixture(self::SPECIALTIES, 'change-primary-error-unauthorized', $response);
    }

    public function test_specialties_show_primary_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/' . $this->doctor->id . '/primary'),
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::SPECIALTIES, 'show-primary-success', $response);
    }

    public function test_specialties_show_primary_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/' . $this->doctor->id . '/primary')
        );
        $response->assertStatus(401);
        $this->saveFixture(self::SPECIALTIES, 'show-primary-error-unauthorized', $response);
    }

    public function test_specialties_show_doctor_specialties_success()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/' . $this->doctor->id),
            $this->authHeaders($this->doctorToken)
        );
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
        $this->saveFixture(self::SPECIALTIES, 'doctor-specialties-success', $response);
    }

    public function test_specialties_show_doctor_specialties_unauthenticated()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/' . $this->doctor->id)
        );
        $response->assertStatus(401);
        $this->saveFixture(self::SPECIALTIES, 'doctor-specialties-error-unauthorized', $response);
    }

    public function test_doctors_update_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/update'),
            ['appointment_duration' => 30],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::DOCTORS, 'update-error-forbidden', $response);
    }

    public function test_doctors_filter_forbidden()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/doctors/filter'),
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::DOCTORS, 'index-error-forbidden', $response);
    }

    public function test_doctors_destroy_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $this->doctor->id . '/leave'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::DOCTORS, 'destroy-error-forbidden', $response);
    }

    public function test_doctors_restore_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $this->doctor->id . '/restore'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::DOCTORS, 'restore-error-forbidden', $response);
    }

    public function test_doctors_force_delete_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/doctors/' . $this->doctor->id . '/force'),
            [],
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::DOCTORS, 'force-delete-error-forbidden', $response);
    }

    public function test_schedules_store_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/schedules'),
            [
                'doctor_id' => $this->doctor->id,
                'day_of_week' => 2,
                'start_time' => '09:00',
                'end_time' => '17:00',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::SCHEDULES, 'store-error-forbidden', $response);
    }

    public function test_schedules_update_forbidden()
    {
        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/schedules'),
            [
                'doctor_id' => $this->doctor->id,
                'day_of_week' => $this->workHour->day_of_week,
                'start_time' => '10:00',
                'end_time' => '16:00',
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::SCHEDULES, 'update-error-forbidden', $response);
    }

    public function test_schedules_destroy_forbidden()
    {
        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/schedules/' . $this->workHour->day_of_week . '/' . $this->doctor->id),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::SCHEDULES, 'destroy-error-forbidden', $response);
    }

    public function test_schedules_work_hour_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/schedules/work-hour/99999?date=' . now()->format('Y-m-d'))
        );

        $response->assertStatus(404);
        $this->saveFixture(self::SCHEDULES, 'work-hour-by-date-error-not-found', $response);
    }

    public function test_overrides_store_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides'),
            [
                'doctor_id' => $this->doctor->id,
                'override_date' => now()->addDays(5)->format('Y-m-d'),
                'is_closed' => true,
            ],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::SCHEDULES, 'override-store-error-forbidden', $response);
    }

    public function test_overrides_update_forbidden()
    {
        $override = \App\Models\Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(10)->format('Y-m-d'),
            'is_closed' => true,
        ]);

        $response = $this->putJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id),
            ['start_time' => '10:00'],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::SCHEDULES, 'override-update-error-forbidden', $response);
    }

    public function test_overrides_destroy_forbidden()
    {
        $override = \App\Models\Schedule_override::factory()->create([
            'doctor_id' => $this->doctor->id,
            'override_date' => now()->addDays(15)->format('Y-m-d'),
            'is_closed' => true,
        ]);

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/schedule-overrides/' . $override->id),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::SCHEDULES, 'override-destroy-error-forbidden', $response);
    }

    public function test_specialties_attach_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties'),
            ['specialty_ids' => [1]],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::SPECIALTIES, 'attach-specialties-error-forbidden', $response);
    }

    public function test_specialties_detach_forbidden()
    {
        $specialty = \App\Models\Specialty::create(['ar_name' => 'اختبار', 'en_name' => 'Test Spec']);
        $this->doctor->specialties()->sync([$specialty->id]);

        $response = $this->deleteJson(
            $this->v1uri('/clinic-system/clinic/specialties/' . $specialty->id),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::SPECIALTIES, 'detach-specialty-error-forbidden', $response);
    }

    public function test_specialties_change_primary_forbidden()
    {
        $response = $this->postJson(
            $this->v1uri('/clinic-system/clinic/specialties/1/primary'),
            [],
            $this->authHeaders($this->patientToken)
        );

        $response->assertStatus(403);
        $this->saveFixture(self::SPECIALTIES, 'change-primary-error-forbidden', $response);
    }

    public function test_specialties_show_doctor_specialties_not_found()
    {
        $response = $this->getJson(
            $this->v1uri('/clinic-system/clinic/specialties/doctor/99999'),
            $this->authHeaders($this->doctorToken)
        );

        $response->assertStatus(404);
        $this->saveFixture(self::SPECIALTIES, 'doctor-specialties-error-not-found', $response);
    }
}
