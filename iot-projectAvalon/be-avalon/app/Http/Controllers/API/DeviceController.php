<?php

namespace App\Http\Controllers\API;

use App\Models\Device;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;

class DeviceController extends Controller
{

    /**
     * Cek perangkat yang terhubung dengan pengguna tertentu.
     */
    public function getDevicesByUser($userId = null)
    {
        // Ambil userId dari pengguna yang sedang login jika $userId tidak diberikan
        if (!$userId) {
            $userId = auth('api')->user()->users_id;
        }

        // Cari perangkat yang terhubung dengan users_id
        $devices = Device::where('users_id', $userId)->get();

        // Jika tidak ada perangkat, kembalikan data kosong dengan status 200
        if ($devices->isEmpty()) {
            return response()->json([
                'status' => 'no device',
                'message' => 'Tidak ada perangkat yang terhubung dengan pengguna ini.',
                'data' => [],
            ], 200);
        }

        // Jika perangkat ditemukan, kembalikan data perangkat
        return response()->json([
            'status' => 'success',
            'message' => 'Perangkat yang terhubung ditemukan.',
            'data' => $devices,
        ], 200);
    }

    /**
     * Periksa apakah perangkat dengan devices_id tertentu ada.
     */
    public function checkDeviceExistPublic($devices_id)
    {
        // Cari perangkat berdasarkan devices_id
        $device = Device::where('devices_id', $devices_id)->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Perangkat ditemukan.',
            'data' => [
                'devices_id' => $device->devices_id,
                'device_type' => $device->device_type,
                'status' => $device->status,
            ],
        ], 200);
    }

    /**
     * Periksa apakah perangkat dengan devices_id tertentu ada.
     */
    public function checkDeviceExistPrivate($devices_id)
    {
        // Cari perangkat berdasarkan devices_id
        $device = Device::where('devices_id', $devices_id)->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Perangkat ditemukan.',
            'data' => [
                'devices_id' => $device->devices_id,
                'device_name' => $device->device_name,
                'device_type' => $device->device_type,
                'status' => $device->status,
                'location' => $device->location,
                'description' => $device->description,
                'users_id' => $device->users_id,
            ],
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Simpan perangkat baru ke database tanpa membutuhkan autentikasi.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'devices_id' => 'required|uuid', // Pastikan devices_id adalah UUID unik
            'device_type' => 'required|string|max:255',
            'qrcode_url' => 'url',
        ]);

        // Simpan data perangkat tanpa users_id
        $device = Device::create([
            'devices_id' => $request->devices_id,
            'device_type' => $request->device_type,
            'qrcode_url' => $request->qrcode_url,
            'status' => 'Not Linked To User',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Perangkat berhasil disimpan.',
        ], 201);
    }

    /**
     * Tampilkan perangkat berdasarkan devices_id dan hubungkan jika belum ada users_id.
     */
    public function linkDevice($devices_id)
    {
        // Cari perangkat berdasarkan devices_id
        $device = Device::where('devices_id', $devices_id)->first();

        // Jika perangkat tidak ditemukan
        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat tidak ditemukan.',
            ], 404);
        }

        // Ambil userId dari pengguna yang sedang login
        $userId = auth('api')->user()->users_id; // Pastikan `auth()` mengambil data pengguna yang terautentikasi

        // Jika perangkat sudah memiliki users_id, kembalikan error
        if ($device->users_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat sudah ditautkan dengan pengguna lain.',
            ], 400); // 400 Bad Request
        }

        // Jika perangkat belum memiliki users_id, hubungkan dengan users_id pengguna yang sedang login
        $device->users_id = $userId;
        $device->status = "Active";
        $device->save();

        // Kirim notifikasi
        try {
            // Buat notifikasi
            $notification = Notification::create([
                'source' => 'device', // Sumber notifikasi dari perangkat
                'title' => 'Perangkat Berhasil Ditautkan',
                'message' => 'Perangkat dengan ID ' . $devices_id . ' berhasil ditautkan ke akun Anda.',
                'type' => 'info',
                'devices_id' => $devices_id, // Mengaitkan notifikasi dengan perangkat
            ]);

            // Tambahkan penerima notifikasi
            NotificationRecipient::create([
                'notifications_id' => $notification->notifications_id,
                'users_id' => $userId, // Penerima adalah pengguna yang sedang login
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Perangkat berhasil ditautkan ke akun Anda dan notifikasi berhasil dikirim.',
            ], 200);
        } catch (\Exception $e) {
            // Penanganan kesalahan
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat berhasil ditautkan, tetapi gagal mengirim notifikasi.',
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
    public function update(Request $request, $devices_id)
    {
        // Cari perangkat berdasarkan devices_id
        $device = Device::where('devices_id', $devices_id)->first();

        if (!$device) {
            return response()->json([
                'pesan' => 'Perangkat tidak ditemukan.',
            ], 404);
        }

        // Periksa apakah perangkat memiliki users_id
        if (!$device->users_id) {
            return response()->json([
                'pesan' => 'Perangkat belum terhubung dengan pengguna. Tidak dapat memperbarui data.',
            ], 403); // Forbidden
        }

        // Validasi data yang akan diupdate
        $validated = $request->validate([
            'device_name' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:Active,Inactive',
            'location' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Update data perangkat
        $device->update($validated);

        return response()->json([
            'pesan' => 'Data perangkat berhasil diperbarui.',
            'perangkat' => $device,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($devices_id)
    {
        $device = Device::find($devices_id);

        if (!$device) {
            return response()->json(['pesan' => 'Perangkat tidak ditemukan.'], 404);
        }

        $device->delete();

        return response()->json([
            'pesan' => 'Perangkat berhasil dihapus.',
        ], 200);
    }

    /**
     * Hapus relasi perangkat dengan pengguna.
     */
    public function removeShowDevice($devices_id)
    {
        // Cari perangkat berdasarkan devices_id
        $device = Device::find($devices_id);

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat tidak ditemukan.',
            ], 404);
        }

        // Pastikan pengguna yang meminta adalah pemilik perangkat
        $userId = auth('api')->user()->users_id;
        if ($device->users_id !== $userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk perangkat ini.',
            ], 403);
        }

        // Kosongkan users_id dari perangkat
        $device->users_id = null;
        $device->status = 'Not Linked To User';
        $device->save();

        // Kirim notifikasi
        try {
            // Buat notifikasi
            $notification = Notification::create([
                'source' => 'device', // Sumber notifikasi dari perangkat
                'title' => 'Perangkat Diputuskan dari Dashboard',
                'message' => 'Perangkat dengan ID ' . $devices_id . ' telah dihapus dari dashboard Anda.',
                'type' => 'info',
                'devices_id' => $devices_id,
            ]);

            // Tambahkan penerima
            NotificationRecipient::create([
                'notifications_id' => $notification->notifications_id,
                'users_id' => $userId, // Penerima adalah pengguna yang dihapuskan perangkatnya
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Perangkat berhasil dihapus dari dashboard Anda dan notifikasi berhasil dikirim.',
                'data' => $device,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat berhasil dihapus dari dashboard Anda, tetapi gagal mengirim notifikasi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
