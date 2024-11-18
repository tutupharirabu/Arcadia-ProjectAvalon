<?php

use App\Http\Middleware\isAdmin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/me', [AuthController::class, 'getUser'])->middleware('auth:api');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

        Route::post('/generate-otp-code', [AuthController::class, 'generateOtpCodeData']);
        Route::post('/verification-email', [AuthController::class, 'verificationEmail'])->middleware('auth:api');
    });

    // Role
    Route::middleware(['auth:api', isAdmin::class])->group(function () {
        Route::get('/role', [RoleController::class, 'index']);
        Route::post('/role', [RoleController::class, 'store']);
        Route::get('/role/{id}', [RoleController::class, 'show']);
        Route::put('/role/{id}', [RoleController::class, 'update']);
        Route::delete('/role/{id}', [RoleController::class, 'destroy']);
    });
});
