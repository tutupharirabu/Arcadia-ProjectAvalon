<?php

namespace App\Http\Controllers\API;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        ]);

        // Simpan data perangkat tanpa users_id
        $device = Device::create([
            'devices_id' => $request->devices_id,
            'device_type' => $request->device_type,
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
    public function showDevice($devices_id)
    {
        // Cari perangkat berdasarkan devices_id
        $device = Device::where('devices_id', $devices_id)->first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat tidak ditemukan.',
            ], 404);
        }

        // Ambil userId dari pengguna yang sedang login
        $userId = auth('api')->user()->users_id; // Pastikan `auth()` mengambil data pengguna yang terautentikasi

        // Jika perangkat belum memiliki user_id, hubungkan dengan user_id pengguna yang sedang login
        if (!$device->users_id) {
            $device->users_id = $userId;
            $device->status = "Active";
            $device->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data perangkat ditemukan.',
            'data' => $device,
        ], 200);
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

        return response()->json([
            'status' => 'success',
            'message' => 'Perangkat berhasil dihapus dari dashboard Anda.',
            'data' => $device,
        ], 200);
    }
}
