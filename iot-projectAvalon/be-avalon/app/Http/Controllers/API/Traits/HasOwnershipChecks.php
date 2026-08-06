<?php

namespace App\Http\Controllers\API\Traits;

use App\Models\Device;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

trait HasOwnershipChecks
{
    /**
     * Pastikan perangkat dimiliki oleh user yang sedang login.
     *
     * @return Device|JsonResponse Device bila pemanggil adalah pemilik,
     *                             atau respons JSON 401/403/404 untuk dikembalikan langsung.
     */
    protected function ensureDeviceOwnedByUser(string $deviceId): Device|JsonResponse
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'pesan' => 'Sesi tidak valid. Silakan login kembali.',
            ], 401);
        }

        $device = Device::where('devices_id', $deviceId)->first();

        if (!$device) {
            return response()->json([
                'status' => false,
                'pesan' => 'Perangkat tidak ditemukan.',
            ], 404);
        }

        if ($device->users_id !== $user->users_id) {
            return response()->json([
                'status' => false,
                'pesan' => 'Anda tidak memiliki akses untuk perangkat ini.',
            ], 403);
        }

        return $device;
    }

    /**
     * Pastikan notifikasi terlihat oleh user yang sedang login
     * (via notification_recipients, atau device milik pemanggil).
     *
     * @return Notification|JsonResponse Notification bila terlihat,
     *                                    atau respons JSON 401/404 untuk dikembalikan langsung.
     */
    protected function ensureNotificationVisibleToUser(string $notificationId): Notification|JsonResponse
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'pesan' => 'Sesi tidak valid. Silakan login kembali.',
            ], 401);
        }

        $notification = Notification::where('notifications_id', $notificationId)->first();

        if (!$notification) {
            return response()->json([
                'status' => false,
                'pesan' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        $isRecipient = $notification->recipients()
            ->where(function ($q) use ($user) {
                $q->where('users_id', $user->users_id)
                    ->orWhere('roles_id', $user->roles_id);
            })
            ->exists();

        $isOwnerOfDevice = $notification->devices_id
            && Device::where('devices_id', $notification->devices_id)
                ->where('users_id', $user->users_id)
                ->exists();

        // 404 (bukan 403) agar tidak membocorkan keberadaan notifikasi milik user lain
        if (!$isRecipient && !$isOwnerOfDevice) {
            return response()->json([
                'status' => false,
                'pesan' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        return $notification;
    }

    /**
     * Batasi query notifikasi hanya pada notifikasi yang terlihat oleh user.
     */
    protected function applyNotificationVisibilityScope($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereHas('recipients', function ($r) use ($user) {
                $r->where('users_id', $user->users_id)
                    ->orWhere('roles_id', $user->roles_id);
            })->orWhereHas('device', function ($d) use ($user) {
                $d->where('users_id', $user->users_id);
            });
        });
    }
}
