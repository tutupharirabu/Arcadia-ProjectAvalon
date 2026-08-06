<?php

use App\Http\Middleware\isAdmin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\DeviceController;
use App\Http\Controllers\API\WaterPumpController;
use App\Http\Middleware\VerifyPasswordResetToken;
use App\Http\Controllers\API\WaterPumpAlarmController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\HistoricalDataController;
use App\Http\Controllers\API\NotificationRecipientController;

Route::prefix('v1')->group(function () {

    // Register - Login
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
        Route::get('/me', [AuthController::class, 'getUser'])->middleware('auth:api');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

        // Generate OTP Code - Verification Email
        Route::post('/generate-otp-code', [AuthController::class, 'generateOtpCode'])->middleware('throttle:auth');
        Route::post('/verification-email', [AuthController::class, 'verificationEmail'])->middleware('auth:api', 'throttle:auth');

        // Forgot Password
        Route::prefix('forgot-password')->group(function () {
            Route::post('/send-email', [AuthController::class, 'forgotPassword'])->middleware('throttle:auth');
            Route::post('/verify-otp-code', [AuthController::class, 'verifyOtpForgotPassword'])->middleware('throttle:auth');
            Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware(VerifyPasswordResetToken::class, 'throttle:auth');
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
        Route::get('/check-public/{devices_id}', [DeviceController::class, 'checkDeviceExistPublic']);

        Route::middleware('auth:api')->group(function () {
            // {userId?} dipertahankan agar FE tetap jalan, namun diabaikan oleh controller
            // (identitas selalu diambil dari token JWT)
            Route::get('/check-by-user/{userId?}', [DeviceController::class, 'getDevicesByUser']);
            Route::get('/check-private/{devices_id}', [DeviceController::class, 'checkDeviceExistPrivate']);

            Route::put('/{devices_id}', [DeviceController::class, 'update']);
            Route::delete('/{devices_id}', [DeviceController::class, 'destroy']);

            Route::post('/link/{devices_id}', [DeviceController::class, 'linkDevice']);
            Route::delete('/unlink/{devices_id}', [DeviceController::class, 'removeShowDevice']);
        });
    });

    // Water Pump
    Route::prefix('water-pump')->middleware('auth:api')->group(function () {
        Route::post('/control', [WaterPumpController::class, 'controlPump']);
        Route::get('/log/{id}', [WaterPumpController::class, 'show']);
        // PUT /log/{logId} adalah endpoint read-only (artefak copy-paste) — dipertahankan
        // agar klien lama tetap jalan. FE hanya memakai GET /water-pump/log/{devices_id}
        // (device-based, route di atas) — GET /log/{logId} ini alias REST-correct untuk
        // lookup berbasis water_pump_log_id, terdaftar ke handler yang sama dengan PUT.
        Route::get('/log/{logId}', [WaterPumpController::class, 'showWaterPumpLog']);
        Route::put('/log/{logId}', [WaterPumpController::class, 'showWaterPumpLog']);
    });

    // Water Alarm
    Route::prefix('water-alarm')->middleware('auth:api')->group(function () {
        Route::get('/', [WaterPumpAlarmController::class, 'index']);
        Route::post('/', [WaterPumpAlarmController::class, 'updateOrCreate']);
        Route::delete('/{id}', [WaterPumpAlarmController::class, 'destroy']);
    });

    // Notification
    Route::prefix('notification')->middleware('auth:api')->group(function () {
        Route::post('/', [NotificationController::class, 'store'])->middleware(isAdmin::class);
        Route::get('/', [NotificationController::class, 'index']);

        // Diletakkan sebelum '/{id}' agar tidak ternaungi (route shadowing)
        Route::prefix('recipient')->group(function () {
            Route::get('/', [NotificationRecipientController::class, 'getNotificationsForRecipient']);
            Route::put('/{id}', [NotificationRecipientController::class, 'markAsRead']);
        });

        Route::get('/{id}', [NotificationController::class, 'show']);
    });

    // Historical Data
    Route::prefix('historical-data')->group(function () {
        Route::post('/', [HistoricalDataController::class, 'store']);
        Route::middleware('auth:api')->group(function () {
            Route::get('/{id}', [HistoricalDataController::class, 'show']);
        });
    });
});
