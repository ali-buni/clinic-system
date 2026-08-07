<?php

use App\Http\Controllers\LocationApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
    Route::get('/countries', [LocationApiController::class, 'countries']);
    Route::get('/countries/{countryCode}/governorates', [LocationApiController::class, 'governorates']);
    Route::get('/countries/{countryCode}/governorates/{governorateCode}/cities', [LocationApiController::class, 'cities']);
});
