<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\HistoricalData;
use App\Models\Device;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class HistoricalDataController extends Controller
{
    /**
     * Simpan data historis ke dalam database dan Redis.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'history_id' => 'required|uuid|unique:historical_data,history_id',
            'parameters' => 'required|array',
            'devices_id' => 'required|uuid|exists:devices,devices_id',
        ]);

        try {
            // Ambil pengguna yang sedang login
            $user = auth()->user();

            // Periksa apakah devices_id terkait dengan users_id pengguna
            $device = Device::where('devices_id', $validated['devices_id'])
                ->where('users_id', $user->users_id)
                ->first();

            if (!$device) {
                return response()->json([
                    'pesan' => 'Perangkat tidak terhubung dengan pengguna ini.',
                ], 403);
            }

            // Simpan ke Redis
            $cacheKey = 'historical_data:' . $validated['history_id'];
            Redis::set($cacheKey, json_encode($validated));
            if (!Redis::expire($cacheKey, env('REDIS_CACHE_TTL', 60))) {
                Log::warning("Redis gagal menetapkan TTL untuk $cacheKey");
            }

            // Simpan ke database
            $historicalData = HistoricalData::create([
                'history_id' => $validated['history_id'],
                'parameters' => $validated['parameters'],
                'waktu_diambil' => Carbon::now(),
                'devices_id' => $validated['devices_id'],
            ]);

            return response()->json([
                'pesan' => 'Data historis berhasil disimpan.',
                'data' => $historicalData,
            ], 201);
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan data historis: " . $e->getMessage());
            return response()->json([
                'pesan' => 'Gagal menyimpan data historis.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan data historis berdasarkan ID.
     */
    public function show($id)
    {
        $cacheKey = 'historical_data:' . $id;

        try {
            // Ambil pengguna yang sedang login
            $user = auth()->user();

            // Cek di Redis
            $data = Redis::get($cacheKey);

            if ($data) {
                $decodedData = json_decode($data, true);

                // Periksa apakah devices_id pada data terhubung ke pengguna
                $device = Device::where('devices_id', $decodedData['devices_id'])
                    ->where('users_id', $user->users_id)
                    ->first();

                if (!$device) {
                    return response()->json([
                        'pesan' => 'Perangkat tidak terhubung dengan pengguna ini.',
                    ], 403);
                }

                return response()->json([
                    'pesan' => 'Data historis berhasil diambil dari Redis.',
                    'data' => $decodedData,
                ], 200);
            }

            // Fallback ke database
            $historicalData = HistoricalData::find($id);

            if (!$historicalData) {
                return response()->json(['pesan' => 'Data historis tidak ditemukan.'], 404);
            }

            // Periksa apakah devices_id pada data terhubung ke pengguna
            $device = Device::where('devices_id', $historicalData->devices_id)
                ->where('users_id', $user->users_id)
                ->first();

            if (!$device) {
                return response()->json([
                    'pesan' => 'Perangkat tidak terhubung dengan pengguna ini.',
                ], 403);
            }

            // Simpan ke Redis untuk akses berikutnya
            Redis::set($cacheKey, $historicalData->toJson());
            if (!Redis::expire($cacheKey, env('REDIS_CACHE_TTL', 60))) {
                Log::warning("Redis gagal menetapkan TTL untuk $cacheKey setelah fallback.");
            }

            return response()->json([
                'pesan' => 'Data historis berhasil diambil dari database.',
                'data' => $historicalData,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Gagal mengambil data historis: " . $e->getMessage());
            return response()->json([
                'pesan' => 'Gagal mengambil data historis.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
