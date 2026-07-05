<?php

use App\Http\Controllers\Schedule\ScheduleOverrideController;
use Illuminate\Support\Facades\Route;

Route::prefix('/schedule-overrides')->controller(ScheduleOverrideController::class)->group(function () {
    Route::middleware([
        'auth:sanctum',
        'throttle:100,1',
    ])->group(function () {
        Route::post('/', 'store')
            ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:manage overrides', 'resourceAccess:doctor_self:doctor_id']);
        Route::put('/{id}', 'update')
            ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:manage overrides', 'resourceAccess:doctor_self:doctor_id']);
        Route::delete('/{id}', 'destroy')
            ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:manage overrides', 'resourceAccess:owns:Schedule_override:id']);
        Route::get('/{id}', 'show')
            ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view overrides', 'resourceAccess:doctor_self:doctor_id']);
        Route::get('/', 'index')
            ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view overrides', 'resourceAccess:doctor_self:doctor_id']);
        Route::get('/date/single', 'getByDate')
            ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view overrides', 'resourceAccess:doctor_self:doctor_id']);
        Route::get('/date/range', 'getByDateRange')
            ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view overrides', 'resourceAccess:doctor_self:doctor_id']);
    });
});
