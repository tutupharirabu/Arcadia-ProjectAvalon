<?php

namespace App\Http\Controllers\API;

use App\Models\Device;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $devices = Device::with('user')->get();

        return response()->json([
            'pesan' => 'Data perangkat berhasil diambil.',
            'perangkat' => $devices,
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'devices_id' => 'required|uuid|unique:devices,devices_id', // Pastikan devices_id adalah UUID unik
            'device_type' => 'required|string|max:255',
        ]);

        // Simpan data perangkat tanpa users_id
        $device = Device::create([
            'devices_id' => $request->devices_id,
            'device_type' => $request->device_type,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Perangkat berhasil disimpan.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function showDevice(Request $request, $devices_id)
    {
        // Cari perangkat berdasarkan devices_id
        $device = Device::find($devices_id);

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat tidak ditemukan.',
            ], 404);
        }

        // Ambil users_id dari user yang sedang terautentikasi
        $userId = $request->user()->users_id; // Pastikan user() berasal dari autentikasi

        // Tambahkan users_id ke perangkat jika belum diisi
        if (!$device->users_id) {
            $device->users_id = $userId;
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
    public function update(Request $request, $id)
    {
        $device = Device::find($id);

        if (!$device) {
            return response()->json(['pesan' => 'Perangkat tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'device_name' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:Active,Inactive',
            'location' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $device->update($validated);

        return response()->json([
            'pesan' => 'Data perangkat berhasil diperbarui.',
            'perangkat' => $device,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $device = Device::find($id);

        if (!$device) {
            return response()->json(['pesan' => 'Perangkat tidak ditemukan.'], 404);
        }

        $device->delete();

        return response()->json([
            'pesan' => 'Perangkat berhasil dihapus.',
        ], 200);
    }

    public function removeShowDevice(Request $request, $devices_id)
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
        $userId = $request->user()->users_id;
        if ($device->users_id !== $userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk perangkat ini.',
            ], 403);
        }

        // Kosongkan users_id dari perangkat
        $device->users_id = null;
        $device->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Perangkat berhasil dihapus dari dashboard Anda.',
            'data' => $device,
        ], 200);
    }
}
