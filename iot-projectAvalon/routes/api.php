<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
    try {
        // Lakukan GET request ke server Node.js
        $response = Http::get('http://192.168.43.122:3001/status');  // Sesuaikan dengan IP server Node.js Anda

        // Ambil data dari respons dan kirim dalam format JSON
        return response()->json($response->json());
    } catch (\Exception $e) {
        // Jika terjadi error, kirimkan pesan error
        return response()->json(['status' => 'error', 'message' => 'Could not fetch data from ESP32']);
    }
});
