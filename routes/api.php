<?php

use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\UserAuthController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function (): void {
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login',    [UserAuthController::class, 'login']);
});

// Protected routes
Route::middleware('jwt')->group(function (): void {
    Route::get('auth/profile', [UserAuthController::class, 'profile']);  
    Route::apiResource('books', BookController::class);
});