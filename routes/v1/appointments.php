<?php

use App\Http\Controllers\Appointment\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/appointments')->controller(AppointmentController::class)->group(function () {
    Route::post('/book', 'book')
        ->middleware(['checkaccess:role:patient']);
    Route::post('/{id}/reschedule', 'reschedule')
        ->middleware(['checkaccess:role:doctor,secretary,patient']);
    Route::post('/{id}/cancel', 'cancel')
        ->middleware(['checkaccess:role:doctor,secretary,patient']);
    Route::post('/{id}/complete', 'complete')
        ->middleware(['checkaccess:role:doctor', 'checkaccess:permission:manage appointments']);
    Route::post('/{id}/confirmed', 'markConfirmed')
        ->middleware(['checkaccess:role:doctor,secretary', 'checkaccess:permission:manage appointments']);
    Route::get('/room', 'roomAppointments')
        ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view appointments']);
    Route::get('/available-slots', 'availableSlots')
        ->middleware('checkaccess:role:patient,doctor,secretary,owner');
    Route::get('/doctor/{doctorId}/schedule', 'doctorSchedule')
        ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view schedules']);
    Route::get('/clinic/{clinicId}/schedule', 'clinicSchedule')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:view schedules']);
    Route::get('/patient/{patientId}', 'patientAppointments')
        ->middleware(['checkaccess:role:patient', 'checkaccess:permission:view appointments']);
    Route::get('/doctor/{doctorId}', 'doctorAppointments')
        ->middleware(['checkaccess:role:doctor,secretary,owner', 'checkaccess:permission:view appointments']);
    Route::get('/clinic/{clinicId}', 'clinicAppointments')
        ->middleware(['checkaccess:role:owner', 'checkaccess:permission:view appointments']);
    Route::get('/{id}', 'show')
        ->middleware(['checkaccess:role:patient,doctor,secretary,owner', 'checkaccess:permission:view appointments']);
});
