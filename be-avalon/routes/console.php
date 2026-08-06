<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('check:alarms', function () {
    Log::info('Cron job executed via check:alarms.');
    $controller = app(\App\Http\Controllers\API\WaterPumpAlarmController::class);
    $controller->checkAndExecuteAlarms();
})->describe('Check and execute water pump alarms');
