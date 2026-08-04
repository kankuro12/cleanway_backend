<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Public routes — no auth, no permission middleware.
Route::get('/', fn () => redirect()->route('login'));

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Authenticated + permission-protected routes.
Route::middleware('auth')->prefix('admin')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard')
        ->middleware('permission:4.1');

    Route::middleware('permission:4.1,7.1')->group(function (): void {
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    });

    Route::middleware('permission:3.1')->group(function (): void {
        Route::get('/properties', [DashboardController::class, 'properties'])->name('properties');
        Route::get('/properties/create', [DashboardController::class, 'propertyCreate'])->name('properties.create');
    });

    Route::middleware('role:1,2')->get('/cleaner-tools', [DashboardController::class, 'cleanerTools'])->name('cleaner-tools');

    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings')
        ->middleware('permission:1');
    Route::get('/settings/users', [DashboardController::class, 'users'])->name('settings.users')
        ->middleware('permission:1.1');
    Route::get('/personnel', [DashboardController::class, 'personnel'])->name('personnel')
        ->middleware('permission:2.1');
});

// Combo: permission AND role on one route.
Route::get('/supervisor-only-approvals', [DashboardController::class, 'approvals'])->name('approvals')
    ->middleware(['auth', 'permission:4.5', 'role:1']);