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

    Route::apiResource('doctors', DoctorController::class);

    Route::get('clinic/doctors', [DoctorController::class, 'clinicDoctors']);
    Route::get('rooms/{room}/doctors', [DoctorController::class, 'roomDoctors']);

    Route::apiResource('diseases', DiseaseController::class)->only(['index', 'store']);
    Route::apiResource('medicines', MedicineController::class)->only(['index', 'store']);
});
