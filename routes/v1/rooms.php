<?php

use App\Http\Controllers\Room\RoomController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/rooms')->controller(RoomController::class)->group(function () {
    Route::get('/user', 'userRooms')
        ->middleware('checkaccess:role:patient,doctor,secretary,owner');
    Route::get('/{clinicId}', 'index')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::get('/{clinicId}/info', 'indexWithInfo')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::get('/{roomId}/details', 'get')
        ->middleware(['checkaccess:role:owner,secretary,doctor']);
    Route::post('/', 'create')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::post('/{roomId}', 'update')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::delete('/{roomId}', 'destroy')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::post('/{roomId}/doctors', 'addDoctorToRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::post('/{roomId}/secretaries', 'addSecToRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::delete('/{roomId}/doctors/{doctorId}', 'delDoctorFromRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::delete('/{roomId}/secretaries/{secretaryId}', 'delSecFromRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
});
