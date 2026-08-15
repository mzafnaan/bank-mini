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

    // Bank Account Management (Read-only list & details)
    Route::get('/accounts', [AdminController::class, 'accounts'])->name('accounts');

    // Accounting Audit
    Route::get('/journals', [AdminController::class, 'journals'])->name('journals');
});

// Teller & Supervisor Placeholders
Route::middleware(['auth:web', 'role:teller'])->get('/teller/dashboard', function () {
    return 'Dashboard Teller - ' . auth()->user()->name;
})->name('teller.dashboard');

Route::middleware(['auth:web', 'role:supervisor'])->get('/supervisor/dashboard', function () {
    return 'Dashboard Supervisor - ' . auth()->user()->name;
})->name('supervisor.dashboard');

Route::middleware(['auth:customer_web', 'role:customer'])->get('/customer/dashboard', function () {
    return 'Dashboard Customer - ' . auth()->user()->username;
})->name('customer.dashboard');
