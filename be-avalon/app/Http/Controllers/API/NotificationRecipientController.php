<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;

class NotificationRecipientController extends Controller
{
    /**
     * Update status "dibaca" untuk penerima
     */
    public function markAsRead($id)
    {
        $user = auth('api')->user();

        // Cari penerima notifikasi berdasarkan notifications_id, scoped ke pemanggil
        // (fix bug `->first()` pada notifikasi broadcast: baris milik pemanggil yang diupdate)
        $recipient = NotificationRecipient::where('notifications_id', $id)
            ->where(function ($q) use ($user) {
                $q->where('users_id', $user->users_id)
                    ->orWhere('roles_id', $user->roles_id);
            })
            ->first();

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
     * Ambil daftar notifikasi untuk pemanggil (identitas dari token, bukan query string)
     */
    public function getNotificationsForRecipient()
    {
        $user = auth('api')->user();

        $query = NotificationRecipient::with(['notification', 'user', 'role']);

        // Scoped ke identitas ter-autentikasi (cegah IDOR via query string)
        $query->where(function ($q) use ($user) {
            $q->where('users_id', $user->users_id)
                ->orWhere('roles_id', $user->roles_id);
        });

        $recipients = $query->paginate(10);

        return response()->json($recipients);
    }
}
