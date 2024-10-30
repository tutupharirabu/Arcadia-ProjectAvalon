<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/status', function () {
    // Retrieve data from Redis
    $data = [
        'status' => Redis::hget('lastStatus', 'status') ?? 'No Data',
        'temperature' => Redis::hget('lastStatus', 'temperature') ?? 'N/A',
        'humidity' => Redis::hget('lastStatus', 'humidity') ?? 'N/A',
        'soilMoisture' => Redis::hget('lastStatus', 'soilMoisture') ?? 'N/A',
        'timestamp' => Redis::hget('lastStatus', 'timestamp')
    ];

    // Check if any data is missing or outdated
    if (!$data['status'] || !$data['temperature'] || !$data['humidity'] || !$data['soilMoisture']) {
        return response()->json([
            'status' => 'Data Lost',
            'temperature' => null,
            'humidity' => null,
            'soilMoisture' => null,
            'message' => 'No recent data available'
        ]);
    }

    return response()->json($data);
});
