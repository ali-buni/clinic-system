<?php

use App\Http\Controllers\Ai\MedicalReportController;
use App\Http\Controllers\Ai\AppointmentAssistantController;
use App\Http\Controllers\Ai\PatientChatbotController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('ai')->group(function () {
    Route::post('report/summarize', [MedicalReportController::class, 'summarize'])
        ->middleware(['throttle:15,1', 'checkaccess:role:doctor,patient']);
    Route::post('appointment/assist', [AppointmentAssistantController::class, 'assist'])
        ->middleware(['throttle:15,1', 'checkaccess:role:patient,doctor,secretary']);
    Route::post('chat/patient', [PatientChatbotController::class, 'chat'])
        ->middleware(['throttle:15,1', 'checkaccess:role:patient']);
    Route::get('chat/patient/history', [PatientChatbotController::class, 'history'])
        ->middleware(['throttle:15,1', 'checkaccess:role:patient']);
});
