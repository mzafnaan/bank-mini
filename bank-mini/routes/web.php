<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Administrator Routes
Route::middleware(['auth:web', 'role:administrator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');

    // Customer Onboarding & Management
    Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
    Route::post('/customers', [AdminController::class, 'storeCustomer'])->name('customers.store');
    Route::put('/customers/{customer}', [AdminController::class, 'updateCustomer'])->name('customers.update');

    // Bank Account Management
    Route::get('/accounts', [AdminController::class, 'accounts'])->name('accounts');
    Route::patch('/accounts/{bankAccount}/status', [AdminController::class, 'toggleAccountStatus'])->name('accounts.toggle-status');

    // Accounting Audit
    Route::get('/journals', [AdminController::class, 'journals'])->name('journals');
});

use App\Http\Controllers\Web\TellerController;

// Teller Routes
Route::middleware(['auth:web', 'role:teller'])->prefix('teller')->name('teller.')->group(function () {
    Route::get('/dashboard', [TellerController::class, 'dashboard'])->name('dashboard');

    // Customer / Account Identification
    Route::get('/identification', [TellerController::class, 'identification'])->name('identification');

    // Cash Deposit
    Route::get('/deposit', [TellerController::class, 'depositForm'])->name('deposit');
    Route::post('/deposit', [TellerController::class, 'storeDeposit'])->name('deposit.store');

    // Cash Withdrawal
    Route::get('/withdrawal', [TellerController::class, 'withdrawalForm'])->name('withdrawal');
    Route::post('/withdrawal', [TellerController::class, 'storeWithdrawal'])->name('withdrawal.store');
    Route::post('/withdrawal-request', [TellerController::class, 'storeWithdrawalRequest'])->name('withdrawal.request');

    // Teller Transaction History & Receipts
    Route::get('/transactions', [TellerController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{transaction}/receipt', [TellerController::class, 'receipt'])->name('transactions.receipt');

    // Daily Closing / Report
    Route::get('/daily-report', [TellerController::class, 'dailyReport'])->name('daily-report');
    Route::post('/daily-report', [TellerController::class, 'storeDailyReport'])->name('daily-report.store');
});

use App\Http\Controllers\Web\SupervisorController;

// Supervisor Routes
Route::middleware(['auth:web', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorController::class, 'index'])->name('dashboard');
    Route::get('/reports', [SupervisorController::class, 'reports'])->name('reports');
    Route::get('/reports/{dailyReport}', [SupervisorController::class, 'show'])->name('reports.show');
    Route::post('/reports/{dailyReport}/approve', [SupervisorController::class, 'approveReport'])->name('reports.approve');
    Route::post('/reports/{dailyReport}/reject', [SupervisorController::class, 'rejectReport'])->name('reports.reject');
    Route::get('/journals', [SupervisorController::class, 'journals'])->name('journals');
});

use App\Http\Controllers\Web\CustomerController;

// Customer / Nasabah Routes
Route::middleware(['auth:web,customer_web', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/transactions', [CustomerController::class, 'transactions'])->name('transactions');
    Route::get('/qr-code', [CustomerController::class, 'qrCode'])->name('qr-code');
    Route::get('/security', [CustomerController::class, 'security'])->name('security');
    Route::post('/security/password', [CustomerController::class, 'updatePassword'])->name('security.password');
    Route::post('/security/pin', [CustomerController::class, 'updatePin'])->name('security.pin');

    // Withdrawal Authorization & Rejection (BR-039, BR-040, DD-006)
    Route::post('/withdrawal-requests/{withdrawalRequest}/authorize', [CustomerController::class, 'authorizeWithdrawal'])->name('withdrawal.authorize');
    Route::post('/withdrawal-requests/{withdrawalRequest}/reject', [CustomerController::class, 'rejectWithdrawal'])->name('withdrawal.reject');
});