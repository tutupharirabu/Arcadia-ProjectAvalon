<?php

use App\Http\Middleware\isAdmin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\DeviceController;
use App\Http\Middleware\VerifyPasswordResetToken;
use App\Http\Controllers\API\HistoricalDataController;

Route::prefix('v1')->group(function () {

    // Register - Login
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/me', [AuthController::class, 'getUser'])->middleware('auth:api');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

        // Generate OTP Code - Verification Email
        Route::post('/generate-otp-code', [AuthController::class, 'generateOtpCode']);
        Route::post('/verification-email', [AuthController::class, 'verificationEmail'])->middleware('auth:api');

        // Forgot Password
        Route::prefix('forgot-password')->group(function () {
            Route::post('/send-email', [AuthController::class, 'forgotPassword']);
            Route::post('/verify-otp-code', [AuthController::class, 'verifyOtpForgotPassword']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware(VerifyPasswordResetToken::class);
        });
    });

    // Role
    Route::middleware(['auth:api', isAdmin::class])->group(function () {
        Route::get('/role', [RoleController::class, 'index']);
        Route::post('/role', [RoleController::class, 'store']);
        Route::get('/role/{id}', [RoleController::class, 'show']);
        Route::put('/role/{id}', [RoleController::class, 'update']);
        Route::delete('/role/{id}', [RoleController::class, 'destroy']);
    });

    // Device
    Route::prefix('device')->group(function () {

        Route::post('/', [DeviceController::class, 'store']);
        Route::get('/check/{devices_id}', [DeviceController::class, 'checkDeviceExist']);

        // Menampilkan perangkat dan menghubungkan dengan pengguna jika belum ada
        Route::middleware('auth:api')->group(function () {
            Route::put('/{devices_id}', [DeviceController::class, 'update']);
            Route::delete('/{devices_id}', [DeviceController::class, 'destroy']);

            Route::get('/{devices_id}', [DeviceController::class, 'showDevice']);
            Route::delete('/unlink/{devices_id}', [DeviceController::class, 'removeShowDevice']);

        });
    });

    // Historical Data
    Route::prefix('historical-data')->middleware('auth:api')->group(function () {
        Route::post('/', [HistoricalDataController::class, 'store']);
        Route::get('/{id}', [HistoricalDataController::class, 'show']);
        Route::delete('/{id}', [HistoricalDataController::class, 'destroy']);
    });
});
