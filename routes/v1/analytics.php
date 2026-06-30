<?php

use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->prefix('/analytics')->group(function () {
    Route::post('/operational', [AnalyticsController::class, 'getOperationalReport']);
    Route::post('/financial', [AnalyticsController::class, 'getFinancialReport']);
    Route::post('/patients', [AnalyticsController::class, 'getPatientReport']);
    Route::get('/medical', [AnalyticsController::class, 'getMedicalReport']);
    Route::post('/predictive', [AnalyticsController::class, 'getPredictiveReport']);
    Route::post('/nla', [AnalyticsController::class, 'askAnalytics']);
    Route::post('/health-score', [AnalyticsController::class, 'getHealthScore']);
    Route::post('/dashboard', [AnalyticsController::class, 'getDashboard']);
});
