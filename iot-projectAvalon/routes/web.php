<?php

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

Route::get('/status', function () {
    // Lakukan GET request ke server Node.js untuk mengambil data
    $response = Http::get('http://localhost:3001/status');  // Pastikan IP dan port benar

    // Ambil data dari respons
    $data = $response->json();

    // Kirim data ke view
    return view('status', ['data' => $data]);
});
