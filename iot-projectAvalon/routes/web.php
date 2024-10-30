<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
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

Route::get('/api/status', function () {
    $nodeServiceUrl = env('NODE_SERVICE_URL') . '/status';

    try {
        // Melakukan GET request ke Node.js server dengan URL HTTPS
        $response = Http::get($nodeServiceUrl);

        if ($response->failed()) {
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch data from Node.js'], 500);
        }

        return response()->json($response->json());
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Could not fetch data from Node.js'], 500);
    }
});
