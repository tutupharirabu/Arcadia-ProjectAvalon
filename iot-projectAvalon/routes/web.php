<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/iot-data', function () {
    try {
        // Attempt to retrieve data from Redis
        $data = [
            'status' => Redis::hget('lastStatus', 'status') ?? 'No Data',
            'temperature' => Redis::hget('lastStatus', 'temperature') ?? 'N/A',
            'humidity' => Redis::hget('lastStatus', 'humidity') ?? 'N/A',
            'soilMoisture' => Redis::hget('lastStatus', 'soilMoisture') ?? 'N/A',
            'timestamp' => Redis::hget('lastStatus', 'timestamp')
                ? Carbon::createFromTimestampMs(Redis::hget('lastStatus', 'timestamp'))->toDateTimeString()
                : 'No Data',
        ];

    } catch (\Exception $e) {
        // In case of an error, return a default response or error message
        $data = [
            'status' => 'Error fetching data',
            'temperature' => 'N/A',
            'humidity' => 'N/A',
            'soilMoisture' => 'N/A',
            'timestamp' => 'N/A',
        ];
    }

    // Pass data to the view with the updated name
    return view('status-iot-data', compact('data'));
});
