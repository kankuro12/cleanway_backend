<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\PersonnelController;
use App\Http\Controllers\Web\TeamController;
use Illuminate\Support\Facades\Route;

// Public routes — no auth, no permission middleware.
Route::get('/', fn () => redirect()->route('login'));

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Forgot / reset password (guest only).
Route::middleware('guest')->group(function (): void {
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

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

    Route::middleware('permission:2.1')->group(function (): void {
        Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel');
        Route::get('/personnel/create', [PersonnelController::class, 'create'])->name('personnel.create');
        Route::get('/personnel/{user}/edit', [PersonnelController::class, 'edit'])->name('personnel.edit');
    });

    Route::middleware('permission:2.2')->post('/personnel', [PersonnelController::class, 'store'])->name('personnel.store');
    Route::middleware('permission:2.3')->put('/personnel/{user}', [PersonnelController::class, 'update'])->name('personnel.update');
    Route::middleware('permission:2.4')->delete('/personnel/{user}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');

    Route::middleware('permission:2')->group(function (): void {
        Route::get('/branches', [BranchController::class, 'index'])->name('branches');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::get('/teams', [TeamController::class, 'index'])->name('teams');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.store');
        Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('teams.members.destroy');
    });

    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings')
        ->middleware('permission:1');
    Route::get('/settings/users', [DashboardController::class, 'users'])->name('settings.users')
        ->middleware('permission:1.1');
});

// Combo: permission AND role on one route.
Route::get('/supervisor-only-approvals', [DashboardController::class, 'approvals'])->name('approvals')
    ->middleware(['auth', 'permission:4.5', 'role:1']);
