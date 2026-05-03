<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TrainerController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
    });
});

// Public reads
Route::get('classes', [ClassController::class, 'index']);
Route::get('classes/{id}', [ClassController::class, 'show']);
Route::get('trainers', [TrainerController::class, 'index']);
Route::get('trainers/{id}', [TrainerController::class, 'show']);
Route::get('sessions', [SessionController::class, 'index']);
Route::get('sessions/{id}', [SessionController::class, 'show']);

// Admin only - Classes write
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('classes', [ClassController::class, 'store']);
    Route::post('classes/{id}', [ClassController::class, 'update']); // accepts _method=PUT for upload
    Route::put('classes/{id}', [ClassController::class, 'update']);
    Route::delete('classes/{id}', [ClassController::class, 'destroy']);

    Route::post('trainers', [TrainerController::class, 'store']);
    Route::delete('trainers/{id}', [TrainerController::class, 'destroy']);

    Route::post('sessions', [SessionController::class, 'store']);
    Route::put('sessions/{id}', [SessionController::class, 'update']);
    Route::delete('sessions/{id}', [SessionController::class, 'destroy']);
});

// Admin & Trainer - Trainer update (own profile for trainer)
Route::middleware(['auth:api', 'role:admin,trainer'])->group(function () {
    Route::put('trainers/{id}', [TrainerController::class, 'update']);
});

// Bookings - logic by role inside controller
Route::middleware('auth:api')->group(function () {
    Route::get('bookings', [BookingController::class, 'index']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::put('bookings/{id}', [BookingController::class, 'update']);
    Route::delete('bookings/{id}', [BookingController::class, 'destroy']);
});

Route::middleware(['auth:api', 'role:member'])->group(function () {
    Route::post('bookings', [BookingController::class, 'store']);
});
