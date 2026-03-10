<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;

// Auth routes
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth', 'multi_session', 'session_timeout', 'anti_hijack', 'audit'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Superadmin + Admin
        Route::middleware('role:superadmin,admin')->group(function () {
            // Route::resource('clientes', ClienteController::class);
            // Route::resource('utilisateurs', UserController::class);
        });

        // Agent + Compta
        Route::middleware('role:superadmin,admin,agent,compta')->group(function () {
            // Route::get('/caisse', [FactureController::class, 'create'])->name('caisse');
            // Route::resource('factures', FactureController::class);
            // Route::resource('depenses', DepenseController::class);
        });

        // Comm
        Route::middleware('role:superadmin,comm')->group(function () {
            // Route::resource('communications', CommunicationController::class);
        });
    });
