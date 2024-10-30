<?php

use Illuminate\Http\Request;
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
    $nodeServiceUrl = env('NODE_SERVICE_URL') . '/status';

    try {
        $response = Http::get($nodeServiceUrl);
        \Log::info('Response from Node.js:', ['response' => $response->json()]);

        if ($response->failed()) {
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch data from Node.js'], 500);
        }

        return response()->json($response->json());
    } catch (\Exception $e) {
        \Log::error('Error fetching data from Node.js:', ['error' => $e->getMessage()]);
        return response()->json(['status' => 'error', 'message' => 'Could not fetch data from Node.js'], 500);
    }
});
