<?php

use App\Http\Controllers\Doctor\DoctorScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('/schedules')->controller(DoctorScheduleController::class)->group(function () {
    Route::middleware([
        'auth:sanctum',
        'throttle:100,1',
    ])->group(function () {
        Route::post('/', 'store')
            ->middleware(['checkaccess:role:owner,doctor', 'checkaccess:permission:manage schedules', 'resourceAccess:doctor_self:doctor_id']);
        Route::put('/', 'update')
            ->middleware(['checkaccess:role:owner,doctor', 'checkaccess:permission:manage schedules', 'resourceAccess:doctor_self:doctor_id']);
        Route::delete('/{dayOfWeek}/{doctorId}', 'destroy')
            ->middleware(['checkaccess:role:owner,doctor', 'checkaccess:permission:manage schedules', 'resourceAccess:doctor_self:doctorId']);
    });
    Route::get('/weekly/{doctorId}', 'getWeeklySchedule');
    Route::get('/work-hour/{doctorId}', 'getWorkHourByDate');
});
