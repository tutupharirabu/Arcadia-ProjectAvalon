<?php

namespace App\Http\Controllers\API;

use App\Models\WaterPumpLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\WaterPumpAlarm;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\WaterPumpController;
use App\Http\Controllers\API\Traits\HasOwnershipChecks;

class WaterPumpAlarmController extends Controller
{
    use HasOwnershipChecks;

    protected $waterPumpController;

    public function __construct(WaterPumpController $waterPumpController)
    {
        $this->waterPumpController = $waterPumpController;
    }

    public function index(Request $request)
    {
        $request->validate([
            'devices_id' => 'required'
        ]);

        // Pastikan perangkat milik pemanggil (cegah IDOR)
        $device = $this->ensureDeviceOwnedByUser($request->devices_id);
        if ($device instanceof JsonResponse) {
            return $device;
        }

        return WaterPumpAlarm::where('devices_id', $request->devices_id)
            ->orderBy('start_time', 'desc')
            ->get();
    }

    public function updateOrCreate(Request $request)
    {
        // Validasi request
        $request->validate([
            'devices_id' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'is_active' => 'boolean'
        ], [
            'required' => 'Silahkan masukkan :attribute dengan benar!',
            'date' => 'Format :attribute harus berupa tanggal yang valid!'
        ]);

        // Pastikan perangkat milik pemanggil (cegah IDOR)
        $device = $this->ensureDeviceOwnedByUser($request->devices_id);
        if ($device instanceof JsonResponse) {
            return $device;
        }

        // Update atau Create alarm
        $alarm = WaterPumpAlarm::updateOrCreate(
            [
                'devices_id' => $request->devices_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ],
            [
                'is_active' => $request->is_active ?? false,
            ]
        );

        // Jika alarm diaktifkan dan ini adalah create baru atau update dari tidak aktif
        if ($alarm->wasRecentlyCreated && $request->is_active) {
            WaterPumpLog::create([
                'devices_id' => $alarm->devices_id,
                'start_time' => Carbon::now(),
                'is_on' => true
            ]);
        } elseif ($alarm->wasChanged('is_active') && $request->is_active) {
            WaterPumpLog::create([
                'devices_id' => $alarm->devices_id,
                'start_time' => Carbon::now(),
                'is_on' => true
            ]);
        }

        return response([
            'message' => 'Data Alarm berhasil diubah!',
            'data' => $alarm,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $alarm = WaterPumpAlarm::where('water_pump_alarm_id', $id)->first();

        if (!$alarm) {
            return response()->json([
                'message' => 'Alarm tidak ditemukan.'
            ], 404);
        }

        // Pastikan perangkat pemilik alarm adalah milik pemanggil (cegah IDOR)
        $device = $this->ensureDeviceOwnedByUser($alarm->devices_id);
        if ($device instanceof JsonResponse) {
            return $device;
        }

        $alarm->delete();
        return response()->noContent();
    }

    public function checkAndExecuteAlarms()
    {
        try {
            // Ambil semua alarm yang aktif
            $activeAlarms = WaterPumpAlarm::where('is_active', true)->get();
            $now = Carbon::now();

            foreach ($activeAlarms as $alarm) {
                $startTime = Carbon::parse($alarm->start_time);
                $endTime = Carbon::parse($alarm->end_time);

                // Cek apakah sekarang waktunya untuk menyalakan
                if ($now->format('H:i') === $startTime->format('H:i')) {
                    // Buat request untuk menyalakan pompa
                    $request = new Request([
                        'device_id' => $alarm->devices_id,
                        'action' => 'ON'
                    ]);

                    // Eksekusi perintah menyalakan
                    $response = $this->waterPumpController->controlPump($request);

                    // Jika berhasil, simpan water_pump_log_id ke alarm
                    if ($response->getStatusCode() === 200) {
                        $responseData = json_decode($response->getContent(), true);
                        $alarm->update([
                            'last_executed_log_id' => $responseData['water_pump_log_id']
                        ]);
                    }
                }

                // Cek apakah sekarang waktunya untuk mematikan
                if ($now->format('H:i') === $endTime->format('H:i')) {
                    // Pastikan ada log_id yang tersimpan
                    if ($alarm->last_executed_log_id) {
                        // Buat request untuk mematikan pompa
                        $request = new Request([
                            'device_id' => $alarm->devices_id,
                            'action' => 'OFF',
                            'water_pump_log_id' => $alarm->last_executed_log_id
                        ]);

                        // Eksekusi perintah mematikan
                        $this->waterPumpController->controlPump($request);

                        // Reset log_id
                        $alarm->update([
                            'last_executed_log_id' => null
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'Pemeriksaan alarm berhasil dijalankan'
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal menjalankan pemeriksaan alarm: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menjalankan alarm'
            ], 500);
        }
    }
}
