<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\HistoricalData;
use App\Models\Device;
use App\Http\Controllers\Controller;

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
            // Ambil perangkat berdasarkan devices_id
            $device = Device::where('devices_id', $validated['devices_id'])->first();

            // Periksa apakah perangkat ditemukan
            if (!$device) {
                return response()->json(['pesan' => 'Perangkat tidak ditemukan.'], 404);
            }

            // Periksa apakah perangkat sudah terhubung ke pengguna
            if (!$device->users_id) {
                return response()->json(['pesan' => 'Perangkat belum terhubung dengan pengguna.'], 403);
            }

            // Simpan data historis ke database
            $historicalData = HistoricalData::create([
                'history_id' => $validated['history_id'],
                'parameters' => $validated['parameters'],
                'devices_id' => $validated['devices_id'],
            ]);

            // Respon sukses
            return response()->json([
                'pesan' => 'Data historis berhasil disimpan ke database.',
                'data' => $historicalData,
            ], 201);
        } catch (\Exception $e) {
            // Respon kesalahan
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

    }
}
