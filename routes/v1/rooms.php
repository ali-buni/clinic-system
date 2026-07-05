<?php

use App\Http\Controllers\Room\RoomController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/rooms')->controller(RoomController::class)->group(function () {
    Route::get('/user', 'userRooms')
        ->middleware('checkaccess:role:doctor,secretary');
    Route::get('/{clinicId}', 'index')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owner_clinic:clinicId']);
    Route::get('/{clinicId}/info', 'indexWithInfo')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owner_clinic:clinicId']);
    Route::get('/{roomId}/details', 'get')
        ->middleware(['checkaccess:role:owner,secretary,doctor', 'resourceAccess:secretary_rooms:roomId', 'resourceAccess:owner_clinic:Room:roomId']);
    Route::post('/', 'create')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin']);
    Route::patch('/{roomId}', 'update')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owner_clinic:Room:roomId']);
    Route::delete('/{roomId}', 'destroy')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owner_clinic:Room:roomId']);
    Route::post('/add/doctors', 'addDoctorToRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owner_clinic:Room:room_id']);
    Route::post('/add/secretaries', 'addSecToRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owner_clinic:Room:room_id']);
    Route::delete('/remove/doctors/', 'delDoctorFromRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owner_clinic:Room:room_id']);
    Route::delete('/remove/secretaries/', 'delSecFromRoom')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:admin', 'resourceAccess:owner_clinic:Room:room_id']);
});
