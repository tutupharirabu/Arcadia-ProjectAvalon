<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\WaterPumpLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\API\Traits\HasOwnershipChecks;

class WaterPumpController extends Controller
{
    use HasOwnershipChecks;

    public function controlPump(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'device_id' => 'required|uuid',
            'action' => 'required|in:ON,OFF',
        ]);

        $deviceId = $validated['device_id'];
        $action = $validated['action'];

        // Pastikan perangkat milik pemanggil (cegah IDOR)
        $device = $this->ensureDeviceOwnedByUser($deviceId);
        if ($device instanceof JsonResponse) {
            return $device;
        }

        // Kirimkan perintah ke Node.js
        $response = Http::withHeaders(['x-shared-secret' => config('nodeserver.shared_secret')])
            ->post(config('nodeserver.url_2') . '/api/water-pump/control', [
                'device_id' => $deviceId,
                'action' => $action,
            ]);

        if ($response->failed()) {
            Log::error('Gagal mengontrol pompa air via Node.js untuk device ' . $deviceId . ': ' . $response->body());

            return response()->json(['error' => 'Gagal mengontrol pompa air'], 500);
        }

        if ($action === 'ON') {

            // Idempotensi: jika masih ada log aktif untuk device ini, jangan buat duplikat
            $activeLog = WaterPumpLog::getLastActiveLog($deviceId);
            if ($activeLog) {
                return response()->json([
                    'message' => 'Pompa air sudah dalam keadaan menyala',
                    'water_pump_log_id' => $activeLog->water_pump_log_id,
                ]);
            }

            // Simpan log baru saat pompa dinyalakan
            $log = WaterPumpLog::create([
                'devices_id' => $deviceId,
                'start_time' => Carbon::now(),
                'end_time' => null,
                'duration' => null, // Durasi belum diketahui
            ]);

            return response()->json([
                'message' => 'Pompa air berhasil dinyalakan',
                'water_pump_log_id' => $log->water_pump_log_id,
            ]);
        } elseif ($action === 'OFF') {
            // Validasi input tambahan untuk log ID
            $validated = $request->validate([
                'water_pump_log_id' => 'required|uuid',
            ]);

            $logId = $validated['water_pump_log_id'];

            // Perbarui log berdasarkan log ID, validasi silang device_id milik pemanggil
            $log = WaterPumpLog::where('water_pump_log_id', $logId)
                ->where('devices_id', $deviceId)
                ->where('is_on', true) // Pastikan log aktif
                ->first();

            if ($log) {
                $endTime = Carbon::now();
                $startTime = Carbon::parse($log->start_time); // Pastikan start_time diubah ke Carbon
                $duration = $startTime->diffInSeconds($endTime); // Hitung durasi dalam detik

                $log->update([
                    'end_time' => $endTime,
                    'duration' => $duration, // Simpan durasi
                    'is_on' => false, // Tandai log sebagai tidak aktif
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
            // Pastikan perangkat milik pemanggil (cegah IDOR)
            $device = $this->ensureDeviceOwnedByUser($deviceId);
            if ($device instanceof JsonResponse) {
                return $device;
            }

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
            Log::error('Gagal mengambil log pompa air untuk device ' . $deviceId . ': ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil log pompa air.',
            ], 500);
        }
    }

    public function showWaterPumpLog($logId)
    {
        $log = WaterPumpLog::where('water_pump_log_id', $logId)->first();

        if (!$log) {
            return response()->json([
                'error' => 'Log tidak ditemukan',
            ], 404);
        }

        // Pastikan pemanggil memiliki device pemilik log (cegah IDOR)
        $device = $this->ensureDeviceOwnedByUser($log->devices_id);
        if ($device instanceof JsonResponse) {
            return $device;
        }

        return response()->json([
            'message' => 'Log ditemukan',
            'log' => $log,
        ]);
    }
}
