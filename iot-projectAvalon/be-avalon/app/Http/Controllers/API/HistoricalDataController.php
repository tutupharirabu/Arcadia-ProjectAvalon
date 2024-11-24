<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\HistoricalData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class HistoricalDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'history_id' => 'required|uuid|unique:historical_data,history_id',
            'parameters' => 'required|array', // Data parameter dalam format array
            'devices_id' => 'required|uuid|exists:devices,devices_id',
        ]);

        try {
            // Simpan ke Redis
            $cacheKey = 'historical_data:' . $validated['history_id'];
            Redis::set($cacheKey, json_encode($validated));
            Redis::expire($cacheKey, env('REDIS_CACHE_TTL', 10)); // TTL diatur dari .env atau default 1 menit

            // Simpan ke database
            $historicalData = HistoricalData::create([
                'history_id' => $validated['history_id'],
                'parameters' => $validated['parameters'], // Disimpan sebagai JSON
                'waktu_diambil' => Carbon::now(),
                'devices_id' => $validated['devices_id'],
            ]);

            return response()->json([
                'pesan' => 'Data historis berhasil disimpan.',
                'data' => $historicalData,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'pesan' => 'Gagal menyimpan data historis.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cacheKey = 'historical_data:' . $id;

        try {
            // Cek di Redis
            $data = Redis::get($cacheKey);

            if ($data) {
                return response()->json([
                    'pesan' => 'Data historis berhasil diambil dari Redis.',
                    'data' => json_decode($data),
                ], 200);
            }

            // Fallback ke database
            $historicalData = HistoricalData::find($id);

            if (!$historicalData) {
                return response()->json(['pesan' => 'Data historis tidak ditemukan.'], 404);
            }

            // Simpan ke Redis untuk akses berikutnya
            Redis::set($cacheKey, $historicalData->toJson());
            Redis::expire($cacheKey, env('REDIS_CACHE_TTL', 10)); // TTL diatur dari .env atau default 1 menit

            return response()->json([
                'pesan' => 'Data historis berhasil diambil dari database.',
                'data' => $historicalData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'pesan' => 'Gagal mengambil data historis.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $historicalData = HistoricalData::find($id);

            if (!$historicalData) {
                return response()->json(['pesan' => 'Data historis tidak ditemukan.'], 404);
            }

            // Hapus dari Redis
            $cacheKey = 'historical_data:' . $id;
            Redis::del($cacheKey);

            // Hapus dari database
            $historicalData->delete();

            return response()->json(['pesan' => 'Data historis berhasil dihapus.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'pesan' => 'Gagal menghapus data historis.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
