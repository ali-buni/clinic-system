<?php

use App\Http\Controllers\Room\RoomController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/rooms')->controller(RoomController::class)->group(function () {
    Route::get('/user', 'userRooms')
        ->middleware('checkaccess:role:doctor,secretary');
    Route::get('/{clinicId}', 'index')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::get('/{clinicId}/info', 'indexWithInfo')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::get('/{roomId}/details', 'get')
        ->middleware(['checkaccess:role:owner,secretary,doctor']);
    Route::post('/', 'create')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::patch('/{roomId}', 'update')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::delete('/{roomId}', 'destroy')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::post('/add/doctors', 'addDoctorToRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::post('/add/secretaries', 'addSecToRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::delete('/remove/doctors/', 'delDoctorFromRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::delete('/remove/secretaries/', 'delSecFromRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
});
