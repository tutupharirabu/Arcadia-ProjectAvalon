<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('check:alarms')
            ->everyMinute()
            ->onSuccess(function () {
                Log::info('Scheduler ran successfully!');
            })
            ->onFailure(function () {
                Log::error('Scheduler failed!');
            });
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Terapkan throttle default untuk seluruh route API
        $middleware->api(append: [
            'throttle:api',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    })
    ->create();

// Rate limiter didefinisikan saat app boot (setelah facade root aktif).
$app->booted(function () {
    // Batas umum untuk seluruh route API: 60 request/menit per IP
    // ($request->user() null karena guard default 'web', jadi keyed by IP)
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->users_id ?: $request->ip());
    });

    // Batas ketat untuk endpoint sensitif (login, OTP, forgot-password): 5 request/menit per IP
    RateLimiter::for('auth', function (Request $request) {
        return Limit::perMinute(5)->by($request->ip());
    });
});

return $app;
