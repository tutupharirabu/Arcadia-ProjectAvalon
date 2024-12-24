<?php

namespace App\Http\Controllers\API;

use Log;
use Carbon\Carbon;
use App\Models\WaterPumpLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class WaterPumpController extends Controller
{
    public function controlPump(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'device_id' => 'required|uuid',
            'action' => 'required|in:ON,OFF',
        ]);

        $deviceId = $validated['device_id'];
        $action = $validated['action'];

        // Kirimkan perintah ke Node.js
        $response = Http::post(env('NODE_API_URL_2') . '/api/water-pump/control', [
            'device_id' => $deviceId,
            'action' => $action,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Gagal mengontrol pompa air'], 500);
        }

        if ($action === 'ON') {
            // Simpan log saat pompa dinyalakan
            $log = WaterPumpLog::create([
                'devices_id' => $deviceId,
                'start_time' => Carbon::now(),
                'end_time' => null,
                'duration' => null, // Durasi belum diketahui
            ]);
            return response()->json([
                'message' => 'Pompa air berhasil dinyalakan',
                'log' => $log,
            ]);
        } elseif ($action === 'OFF') {
            // Validasi input tambahan untuk log ID
            $validated = $request->validate([
                'water_pump_log_id' => 'required|uuid',
            ]);

            $logId = $validated['water_pump_log_id'];

            // Perbarui log berdasarkan log ID
            $log = WaterPumpLog::where('devices_id', $deviceId)
                ->where('water_pump_log_id', $logId) // Gunakan log ID untuk memastikan log yang tepat
                ->whereNull('end_time') // Pastikan log belum selesai
                ->first();

            if ($log) {
                $endTime = Carbon::now();
                $startTime = Carbon::parse($log->start_time); // Pastikan start_time diubah ke Carbon
                $duration = $startTime->diffInSeconds($endTime); // Hitung durasi dalam detik

                $log->update([
                    'end_time' => $endTime,
                    'duration' => $duration, // Simpan durasi
                ]);

                return response()->json([
                    'message' => 'Pompa air berhasil dimatikan',
                    'log' => $log,
                ]);
            } else {
                return response()->json([
                    'error' => 'Tidak ada log aktif untuk dimatikan',
                ], 404);
            }
        }
    }

    public function show($deviceId)
    {
        try {
            // Ambil log berdasarkan devices_id
            $logs = WaterPumpLog::where('devices_id', $deviceId)
                ->orderBy('created_at', 'desc') // Urutkan berdasarkan waktu terbaru
                ->get();

            // Jika log kosong
            if ($logs->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada log pompa air untuk perangkat ini.',
                    'data' => [],
                ], 404);
            }

            // Respon sukses
            return response()->json([
                'message' => 'Log pompa air berhasil diambil.',
                'data' => $logs,
            ], 200);
        } catch (\Exception $e) {
            // Jika terjadi kesalahan
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil log pompa air.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
