<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\DoctorSpecialtyController;
use App\Http\Controllers\VerificationController;
use App\Models\User;
use App\Services\ApiResponse;
use App\Services\ModelFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/clinic-system')->group(function () {
    Route::prefix('/doctor')->group(function () {
        Route::prefix('/specialty')->group(function () {
            Route::controller(DoctorSpecialtyController::class)->group(function () {
                Route::middleware('auth:sanctum', 'role:doctor')->group(function () {
                    Route::post('/add', 'attachSpecialties');
                    Route::put('/edit', 'syncSpecialties');
                    Route::delete('/delete/{specialty}', 'detachSpecialty');

                    Route::put('/changePrimary/{specialtyId}', 'changePrimary');
                });
                Route::get('showPrimary/{userId}', 'showPrimary');
                Route::get('getAll/{userId}', 'showDoctorSpecialties');
            });
        });
        Route::prefix('/schedule')->group(function () {
            Route::controller(DoctorScheduleController::class)->group(function () {
                Route::middleware('auth:sanctum', 'role:doctor')->group(function () {
                    Route::post('/add', 'store');
                    Route::put('/edit/{id}', 'update');
                    Route::delete('/delete/{id}', 'destroy');
                });
                Route::get('/get-weekly/{userId}', [DoctorScheduleController::class, 'getWeeklySchedule']);
                Route::get('/work-hour/{userId}', [DoctorScheduleController::class, 'getWorkHourByDate']);
                Route::get('/working-days/{userId}', [DoctorScheduleController::class, 'getWorkingDays']);
            });
        });
    });
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/signout', 'signOUt');
            Route::post('/reset-password', 'resetPassword');
            Route::post('/refresh-token', 'refreshToken');
        });
    });

    Route::controller(VerificationController::class)->group(function () {
        Route::post('/verify-code', 'verifyCode');
        Route::post('/resend-code', 'resendVerificationCode');
    });
});

Route::get('/filter', function (Request $request) {
    return ApiResponse::pagination(ModelFilter::filter(new User, $request->all()));
});
