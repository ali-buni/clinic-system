<?php

use App\Http\Controllers\Schedule\ScheduleOverrideController;
use Illuminate\Support\Facades\Route;

Route::prefix('/schedule-overrides')->controller(ScheduleOverrideController::class)->group(function () {
    Route::middleware([
        'auth:sanctum',
        'throttle:100,1',
        'checkaccess:role:doctor,secretary,owner',
        'checkaccess:permission:manage overrides',
    ])->group(function () {
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });
    Route::middleware([
        'auth:sanctum',
        'throttle:100,1',
        'checkaccess:role:doctor,secretary,owner',
        'checkaccess:permission:view overrides',
    ])->group(function () {
        Route::get('/{id}', 'show');
        Route::get('/', 'index');
        Route::get('/date/single', 'getByDate');
        Route::get('/date/range', 'getByDateRange');
    });
});
