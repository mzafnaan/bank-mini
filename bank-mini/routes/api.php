<?php

use App\Http\Controllers\Api\AuthApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthApiController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:customer'])->prefix('auth')->group(function () {
        Route::get('/me', [AuthApiController::class, 'me']);
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::post('/change-password', [AuthApiController::class, 'changePassword']);
        Route::post('/change-pin', [AuthApiController::class, 'changePin']);
    });
});
