<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;

class NotificationRecipientController extends Controller
{
    /**
     * Update status "dibaca" untuk penerima
     */
    public function markAsRead($id)
    {
        // Cari penerima notifikasi berdasarkan notifications_id
        $recipient = NotificationRecipient::where('notifications_id', $id)->first();

        // Jika tidak ditemukan, kembalikan respon 404
        if (!$recipient) {
            return response()->json([
                'message' => 'Pesan notifikasi tidak ditemukan.',
            ], 404);
        }

        // Tandai notifikasi sebagai dibaca
        $recipient->is_read = true;
        $recipient->save(); // Simpan perubahan

        // Kembalikan respon sukses
        return response()->json([
            'message' => 'Status notifikasi berhasil diperbarui.',
            'recipient' => $recipient,
        ]);
    }

    /**
     * Ambil daftar notifikasi untuk penerima tertentu (berdasarkan users_id atau roles_id)
     */
    public function getNotificationsForRecipient(Request $request)
    {
        $validated = $request->validate([
            'users_id' => 'nullable|uuid',
            'roles_id' => 'nullable|uuid',
        ]);

        $query = NotificationRecipient::with(['notification', 'user', 'role']);

        if ($validated['users_id']) {
            $query->where('users_id', $validated['users_id']);
        }

        if ($validated['roles_id']) {
            $query->where('roles_id', $validated['roles_id']);
        }

        $recipients = $query->paginate(10);

        return response()->json($recipients);
    }
}
