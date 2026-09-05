<?php

use App\Http\Controllers\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/notifications')->controller(NotificationController::class)->group(function () {
    Route::get('/', 'index')
        ->middleware('checkaccess:role:patient,doctor,secretary,owner');
    Route::post('/mark-all-read', 'markAllAsRead')
        ->middleware('checkaccess:role:patient,doctor,secretary,owner');
    Route::post('/{id}/mark-read', 'markAsRead')
        ->middleware('checkaccess:role:patient,doctor,secretary,owner');
});
