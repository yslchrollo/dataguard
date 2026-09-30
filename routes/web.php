<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SystemFlowController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Homepage / Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');


// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/login/quick/{role}', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes (All Active Roles)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Transfer Requests & Logs
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::get('/transfers/{uuid}', [TransferController::class, 'show'])->name('transfers.show');

    // Security Alerts (User sees own alerts, Analyst/Admin sees all)
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/{alert}', [AlertController::class, 'show'])->name('alerts.show');
    Route::put('/alerts/{alert}', [AlertController::class, 'update'])->name('alerts.update')->middleware('role:admin,analyst');

    // System Flow Page (Visual presentation of DLP Process)
    Route::get('/system-flow', [SystemFlowController::class, 'index'])->name('system-flow');

    // Reports (Admin & Security Analyst)
    Route::middleware(['role:admin,analyst'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // Administrator Only Management Routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // User Information Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');

        // Security Policies
        Route::get('/policies', [PolicyController::class, 'index'])->name('policies.index');
        Route::get('/policies/create', [PolicyController::class, 'create'])->name('policies.create');
        Route::post('/policies', [PolicyController::class, 'store'])->name('policies.store');
        Route::get('/policies/{policy}/edit', [PolicyController::class, 'edit'])->name('policies.edit');
        Route::put('/policies/{policy}', [PolicyController::class, 'update'])->name('policies.update');
        Route::post('/policies/{policy}/toggle', [PolicyController::class, 'toggle'])->name('policies.toggle');
        Route::delete('/policies/{policy}', [PolicyController::class, 'destroy'])->name('policies.destroy');

        // Sensitive Data Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
    });
});
