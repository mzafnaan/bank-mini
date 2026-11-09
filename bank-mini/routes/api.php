<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\NasabahApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public auth with Rate Limiting (anti brute-force & spam)
    Route::middleware('throttle:10,1')->post('/auth/login', [AuthApiController::class, 'login']);
    Route::middleware('throttle:3,1')->post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword']);

    // Protected customer routes
    Route::middleware(['auth:sanctum', 'role:customer', 'throttle:60,1'])->group(function () {
        // Customer auth & security
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthApiController::class, 'me']);
            Route::post('/logout', [AuthApiController::class, 'logout']);
            Route::post('/change-password', [AuthApiController::class, 'changePassword']);
            Route::post('/change-pin', [AuthApiController::class, 'changePin']);
            Route::post('/reset-pin-with-password', [AuthApiController::class, 'resetPinWithPassword']);
            Route::get('/email', [AuthApiController::class, 'getEmailStatus']);
            Route::post('/change-email', [AuthApiController::class, 'updateEmail']);
            Route::post('/resend-email', [AuthApiController::class, 'resendEmailVerification']);
            Route::post('/cancel-pending-email', [AuthApiController::class, 'cancelPendingEmail']);
        });

        // Nasabah operations
        Route::prefix('nasabah')->group(function () {
            Route::get('/dashboard', [NasabahApiController::class, 'dashboard']);
            Route::get('/riwayat', [NasabahApiController::class, 'transactionHistory']);
            Route::get('/riwayat/{id}', [NasabahApiController::class, 'transactionDetail']);
            Route::get('/rekening', [NasabahApiController::class, 'accountInfo']);

            // Withdrawal Requests (Mobile PIN Verification - strict throttle to protect 6-digit PIN)
            Route::get('/withdrawal-requests/pending', [NasabahApiController::class, 'pendingWithdrawalRequest']);
            Route::middleware('throttle:5,1')->post('/withdrawal-requests/{withdrawalRequest}/authorize', [NasabahApiController::class, 'authorizeWithdrawal']);
            Route::post('/withdrawal-requests/{withdrawalRequest}/reject', [NasabahApiController::class, 'rejectWithdrawal']);
        });
    });
});
