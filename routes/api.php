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
    Route::prefix('/clinic')->group(function () {
        Route::prefix('/specialty')->controller(DoctorSpecialtyController::class)->group(function () {
            Route::middleware('auth:sanctum')->group(function () {
                Route::post('/add', 'attachSpecialties');
                Route::delete('/delete/{specialId}', 'detachSpecialty');
                Route::post('/changePrimary/{specialtyId}', 'changePrimary');
                Route::get('showPrimary/{doctorId}', 'showPrimary');
                Route::get('getAll', 'showDoctorSpecialties');
            });
            Route::get('index', 'index');
            // store
            // delete
        });
        Route::prefix('/schedule')->controller(DoctorScheduleController::class)->group(function () {
            Route::middleware('auth:sanctum')->group(function () {
                Route::post('/add', 'store');
                Route::put('/edit', 'update');
                Route::delete('/delete/{dayOfWeek}', 'destroy');
            });
            Route::get('/get-weekly/{doctorId}', 'getWeeklySchedule');
            Route::get('/work-hour/{doctorId}', 'getWorkHourByDate');
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
