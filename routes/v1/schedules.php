<?php

use App\Http\Controllers\Doctor\DoctorScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('/schedules')->controller(DoctorScheduleController::class)->group(function () {
    Route::middleware([
        'auth:sanctum',
        'throttle:100,1',
        'checkaccess:role:owner,doctor',
        'checkaccess:permission:manage schedules',
    ])->group(function () {
        Route::post('/', 'store');
        Route::put('/', 'update');
        Route::delete('/{dayOfWeek}/{doctorId}', 'destroy');
    });
    Route::get('/weekly/{doctorId}', 'getWeeklySchedule');
    Route::get('/work-hour/{doctorId}', 'getWorkHourByDate');
});
