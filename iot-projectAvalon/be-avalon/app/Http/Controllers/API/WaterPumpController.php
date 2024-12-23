<?php

namespace App\Http\Controllers\API;

use App\Models\WaterPumpLog;
use Carbon\Carbon;
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
        $nodeServerUrl = env('NODE_API_URL') . '/api/water-pump/control';
        $response = Http::post($nodeServerUrl, [
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
            // Perbarui log saat pompa dimatikan
            $log = WaterPumpLog::where('devices_id', $deviceId)
                ->whereNull('end_time') // Cari log yang belum memiliki end_time
                ->latest('start_time')  // Ambil log terbaru berdasarkan waktu mulai
                ->first();

            if ($log) {
                $endTime = Carbon::now();
                $duration = $log->start_time->diffInSeconds($endTime); // Hitung durasi dalam detik

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
}
