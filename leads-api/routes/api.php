<?php

use App\Http\Api\Auth\Controllers\AuthController;
use App\Http\Api\Leads\Controllers\LeadController;
use Illuminate\Support\Facades\Route;


// Public Auth Routes
Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function (): void {

    // Auth
    Route::prefix('auth')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/send-verification-email', [AuthController::class, 'sendVerificationEmail']);
    });

    // Leads
    Route::get('leads/search', [LeadController::class, 'search']);
    Route::apiResource('leads', LeadController::class);
});
