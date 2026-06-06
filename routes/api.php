<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Models\User;
use App\Services\ModelFilter;
use App\Services\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\MedicineController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('/clinic-system')->group(function (){
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
    return ApiResponse::pagination(ModelFilter::filter(new User(), $request->all()));
});


Route::prefix('/clinic-system')->group(function (){
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
    return ApiResponse::pagination(ModelFilter::filter(new User(), $request->all()));
});


Route::prefix('/clinic-system')->group(function () {
    Route::get('diseases/search', [DiseaseController::class, 'search']);
    Route::get('medicines/search', [MedicineController::class, 'search']);
});

Route::middleware('auth:sanctum')->prefix('/clinic-system')->group(function () {
    Route::put('doctors/{id}', [DoctorController::class, 'update'])
        ->name('doctors.update');

    Route::get('rooms/{room_id}/doctors', [DoctorController::class, 'roomDoctors'])
         ->name('rooms.doctors');

    Route::delete('doctors/{id}/leave', [DoctorController::class, 'destroy'])
         ->name('doctors.leave');

    Route::post('doctors/{id}/restore', [DoctorController::class, 'restore'])
         ->name('doctors.restore')
         ->withTrashed();

    Route::delete('doctors/{id}/force', [DoctorController::class, 'forceDelete'])
         ->name('doctors.force-delete')
         ->withTrashed();

    Route::get('doctors', [DoctorController::class, 'index'])
         ->name('doctors.index');

    Route::apiResource('diseases', DiseaseController::class)->only(['index', 'store']);
    Route::apiResource('medicines', MedicineController::class)->only(['index', 'store']);
});
