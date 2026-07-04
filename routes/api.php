<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\GachaLogController;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\GachaController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Gacha (User)
    Route::prefix('gacha')->group(function () {
        Route::get('/events', [GachaController::class, 'activeEvents']);
        Route::post('/roll', [GachaController::class, 'roll']);
        Route::get('/history', [GachaController::class, 'history']);
        Route::get('/stats', [GachaController::class, 'stats']);
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Events CRUD
        Route::apiResource('events', EventController::class);
        Route::get('/events-active', [EventController::class, 'activeEvents']);

        // Rewards CRUD (nested under events)
        Route::prefix('events/{event}')->group(function () {
            Route::get('/rewards', [RewardController::class, 'index']);
            Route::post('/rewards', [RewardController::class, 'store']);
            Route::get('/rewards/{reward}', [RewardController::class, 'show']);
            Route::put('/rewards/{reward}', [RewardController::class, 'update']);
            Route::delete('/rewards/{reward}', [RewardController::class, 'destroy']);
            Route::get('/validate-drop-rate', [RewardController::class, 'validateDropRate']);
        });

        // Gacha Logs
        Route::get('/gacha-logs', [GachaLogController::class, 'index']);
        Route::get('/gacha-logs/stats', [GachaLogController::class, 'stats']);
        Route::get('/gacha-logs/realtime', [GachaLogController::class, 'realtime']);
    });
});
