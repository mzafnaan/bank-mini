<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\NasabahApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public auth with Rate Limiting (anti brute-force & spam)
    Route::middleware('throttle:10,1')->post('/auth/login', [AuthApiController::class, 'login']);
    Route::middleware('throttle:3,1')->post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword']);

    // Protected customer routes (generous throttle for mobile active polling & browsing)
    Route::middleware(['auth:sanctum', 'role:customer', 'throttle:240,1'])->group(function () {
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
            Route::get('/stats', [NasabahApiController::class, 'stats']);
            Route::get('/riwayat', [NasabahApiController::class, 'transactionHistory']);
            Route::get('/riwayat/{id}', [NasabahApiController::class, 'transactionDetail']);
            Route::get('/mutasi/export-excel', [NasabahApiController::class, 'exportExcel']);
            Route::get('/mutasi/print', [NasabahApiController::class, 'printReport']);
            Route::get('/rekening', [NasabahApiController::class, 'accountInfo']);

            // Deactivation Approval (Nasabah approves / rejects admin request)
            Route::post('/deactivation/approve', [NasabahApiController::class, 'approveDeactivation']);
            Route::post('/deactivation/reject', [NasabahApiController::class, 'rejectDeactivation']);

            // Withdrawal Requests (Mobile PIN Verification - dedicated prefix prevents clash with polling)
            Route::get('/withdrawal-requests/pending', [NasabahApiController::class, 'pendingWithdrawalRequest']);
            Route::middleware('throttle:10,1,pin_auth')->post('/withdrawal-requests/{withdrawalRequest}/authorize', [NasabahApiController::class, 'authorizeWithdrawal']);
            Route::post('/withdrawal-requests/{withdrawalRequest}/reject', [NasabahApiController::class, 'rejectWithdrawal']);
        });
    });
});
