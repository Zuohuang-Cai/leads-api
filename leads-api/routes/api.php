<?php

use App\Http\Api\Auth\Controllers\AuthController;
use App\Http\Api\Leads\Controllers\LeadController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function (): void {

    Route::prefix('auth')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::apiResource('leads', LeadController::class);
});
