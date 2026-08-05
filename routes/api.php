<?php

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PersonnelController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TaskGpsController;
use Illuminate\Support\Facades\Route;

// Public.
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Authenticated.
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('permission:2.1')->group(function (): void {
        Route::get('/personnel', [PersonnelController::class, 'index']);
        Route::get('/personnel/{user}', [PersonnelController::class, 'show']);
    });

    Route::middleware('permission:2.2')->post('/personnel', [PersonnelController::class, 'store']);
    Route::middleware('permission:2.3')->put('/personnel/{user}', [PersonnelController::class, 'update']);
    Route::middleware('permission:2.4')->delete('/personnel/{user}', [PersonnelController::class, 'destroy']);

    Route::middleware('permission:2.1')->get('/supervisor/team-status', [PersonnelController::class, 'teamStatus']);

    Route::middleware('permission:3.1')->group(function (): void {
        Route::get('/properties', [PropertyController::class, 'index']);
        Route::get('/properties/search', [PropertyController::class, 'search']);
        Route::get('/properties/{property}', [PropertyController::class, 'show']);
        Route::get('/property-categories', [PropertyController::class, 'categories']);
        Route::get('/property-tags', [PropertyController::class, 'tags']);
    });

    Route::middleware('permission:3.2')->post('/properties', [PropertyController::class, 'store']);
    Route::middleware('permission:3.3')->group(function (): void {
        Route::put('/properties/{property}', [PropertyController::class, 'update']);
        Route::delete('/properties/{property}', [PropertyController::class, 'destroy']);
        Route::post('/properties/{property}/retry-geocode', [PropertyController::class, 'retryGeocode']);
    });

    // Own tasks + own notifications — any authenticated user.
    Route::get('/me/tasks', [TaskController::class, 'meTasks']);
    Route::get('/me/shifts', [AttendanceController::class, 'meShifts']);
    // All-tasks list (Task List) — mirror of the web register, permission:4.9.
    Route::get('/tasks', [TaskController::class, 'index'])->middleware('permission:4.9');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->middleware('permission:4.1');
    Route::post('/tasks/{task}/transition', [TaskController::class, 'transition'])->middleware('permission:4.4');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    // FCM device registration (own devices only).
    Route::post('/me/devices', [DeviceController::class, 'store']);
    Route::delete('/me/devices/{token}', [DeviceController::class, 'destroy']);

    // Attendance clock (6.1 view; clock events require an active user only).
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart']);
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/attendance/corrections', [AttendanceController::class, 'requestCorrection']);

    // Task GPS + evidence + incidents (4.4).
    Route::middleware('permission:4.4')->group(function (): void {
        Route::post('/tasks/{task}/check-in', [TaskGpsController::class, 'checkIn']);
        Route::post('/tasks/{task}/check-out', [TaskGpsController::class, 'checkOut']);
        Route::post('/tasks/{task}/evidence', [TaskGpsController::class, 'evidence']);
        Route::post('/tasks/{task}/complete', [TaskGpsController::class, 'complete']);
        Route::post('/tasks/{task}/incidents', [TaskGpsController::class, 'incidents']);
    });
});
