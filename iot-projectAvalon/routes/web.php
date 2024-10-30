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

Route::get('/iot-data-status', function () {
    try {
        // Ambil data dari API Laravel yang memanggil Node.js
        $response = Http::get(url('/api/status'));

        if ($response->failed()) {
            throw new \Exception('Failed to fetch data from Laravel API');
        }

        $data = $response->json();
        return view('status-iot-data', compact('data'));
    } catch (\Exception $e) {
        \Log::error('Error fetching data from Laravel API: ' . $e->getMessage());
        $data = [
            'status' => 'Error fetching data from Laravel API.',
            'temperature' => 'N/A',
            'humidity' => 'N/A',
            'soilMoisture' => 'N/A',
            'timestamp' => 'No Data',
        ];

        return view('status-iot-data', compact('data'));
    }
});
