<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PersonnelController;
use Illuminate\Support\Facades\Route;

// Public.
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Authenticated.
Route::middleware('auth:sanctum')->group(function (): void {
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
});
