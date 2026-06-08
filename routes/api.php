<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\VerificationController;
use App\Models\User;
use App\Services\ModelFilter;
use App\Services\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\userController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/clinic-system')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/signout', 'signOut');
            Route::post('/reset-password', 'resetPassword');
            Route::post('/refresh-token', 'refreshToken');
        });
    });

    Route::controller(VerificationController::class)->group(function () {
        Route::post('/verify-code', 'verifyCode');
        Route::post('/resend-code', 'resendVerificationCode');
    });

    Route::middleware('auth:sanctum')->prefix('/clinic')->group(function () {
        Route::controller(ClinicController::class)->group(function () {
            Route::get('/info', 'clinicInfo');
            Route::post('/update/{clinicId}', 'updateClinic');

            Route::post('doctor/register', 'createDoctor');
            Route::post('secretary/register', 'createSecretary');
        });

        Route::prefix('/rooms')->controller(RoomController::class)->group(function () {
            Route::get('/{clinicId}', 'index');
            Route::get('/{clinicId}/info', 'indexWithInfo');
            Route::get('/{roomId}/details', 'get');
            Route::get('/userRooms/get', 'userRooms');
            Route::post('/', 'create');
            Route::post('/{roomId}', 'update');
            Route::delete('/{roomId}', 'destroy');
        });

        Route::prefix('/secretaries')->controller(SecretaryController::class)->group(function () {
            Route::get('/{id}', 'info');
            Route::post('/{id}', 'update');
        });

        Route::prefix('/patients')->controller(PatientController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/trashed', 'indexTrashed');
            Route::get('/{patientId}/show', 'show');
            Route::post('/create', 'store');
            Route::post('/update', 'update');
            Route::delete('/delete', 'destroy');
            Route::get('/restore', 'restore');
        });

        Route::prefix('/users')->controller(userController::class)->group(function () {
            Route::get('/info', 'info');
        });
    });
});

Route::get('/filter', function (Request $request) {
    return ApiResponse::pagination(ModelFilter::filter(new User(), $request->all()));
});


Route::prefix('/clinic-system')->group(function () {
    Route::get('medicines/search', [MedicineController::class, 'search']);
    Route::prefix('diseases')->controller(DiseaseController::class)->group(function () {
        Route::get('search', 'searchDisease');
        Route::post('store', 'store');
    });
});

Route::middleware('auth:sanctum')->prefix('/clinic-system')->group(function () {
    Route::put('doctors/{id}', [DoctorController::class, 'update']);

    Route::get('rooms/{room_id}/doctors', [DoctorController::class, 'roomDoctors']);

    Route::delete('doctors/{id}/leave', [DoctorController::class, 'destroy']);

    Route::post('doctors/{id}/restore', [DoctorController::class, 'restore'])
        ->withTrashed();

    Route::delete('doctors/{id}/force', [DoctorController::class, 'forceDelete'])
        ->withTrashed();

    Route::get('doctors', [DoctorController::class, 'index'])
        ->name('doctors.index');

    Route::post('medicines/store', [MedicineController::class, 'store']);
});
