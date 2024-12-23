<?php

namespace App\Http\Controllers\API;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;

class NotificationController extends Controller
{
    /**
     * Buat notifikasi baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|in:admin,device',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,error,alert',
            'admin_id' => 'nullable|uuid',
            'devices_id' => 'nullable|uuid',
            'recipients' => 'nullable|array', // Tambahkan validasi untuk recipients
            'recipients.*.users_id' => 'nullable|uuid',
            'recipients.*.roles_id' => 'nullable|uuid',
        ]);

        DB::beginTransaction();

        try {
            // Jika source adalah admin, pastikan `users_id` diisi
            if ($validated['source'] === 'admin' && empty($validated['admin_id'])) {
                throw new \Exception('Untuk sumber admin, users_id harus diisi.');
            }

            // Simpan notifikasi
            $notification = Notification::create($validated);

            // Tambahkan penerima jika ada (opsional)
            if ($request->has('recipients')) {
                foreach ($request->recipients as $recipient) {
                    NotificationRecipient::create([
                        'notifications_id' => $notification->notifications_id,
                        'users_id' => $recipient['users_id'] ?? null,
                        'roles_id' => $recipient['roles_id'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Notifikasi berhasil dibuat.',
                'notification' => $notification,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal membuat notifikasi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ambil daftar notifikasi
     */
    public function index(Request $request)
    {
        $notifications = Notification::with(['user', 'device', 'recipients'])
            ->paginate(10); // Tambahkan pagination

        return response()->json($notifications);
    }

    /**
     * Ambil detail notifikasi
     */
    public function show($id)
    {
        $notification = Notification::with(['user', 'device', 'recipients'])
            ->findOrFail($id);

        return response()->json($notification);
    }
}
