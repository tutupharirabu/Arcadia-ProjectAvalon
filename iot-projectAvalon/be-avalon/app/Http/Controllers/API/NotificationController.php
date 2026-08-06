<?php

namespace App\Http\Controllers\API;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;
use App\Http\Controllers\API\Traits\HasOwnershipChecks;

class NotificationController extends Controller
{
    use HasOwnershipChecks;

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
            'admin_id' => 'required_if:source,admin|nullable|uuid',
            'devices_id' => 'nullable|uuid',
            'recipients' => 'nullable|array', // Tambahkan validasi untuk recipients
            'recipients.*.users_id' => 'nullable|uuid|exists:users,users_id',
            'recipients.*.roles_id' => 'nullable|uuid|exists:roles,roles_id',
        ]);

        // Cegah pemalsuan sumber: admin_id harus identitas pemanggil yang sedang login
        if ($validated['source'] === 'admin') {
            $adminId = auth('api')->user()->users_id;

            if (!empty($validated['admin_id']) && $validated['admin_id'] !== $adminId) {
                return response()->json([
                    'status' => false,
                    'pesan' => 'admin_id tidak sesuai dengan akun Anda.',
                ], 403);
            }

            $validated['admin_id'] = $adminId;
        }

        DB::beginTransaction();

        try {
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

            Log::error('Gagal membuat notifikasi: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'pesan' => 'Gagal membuat notifikasi.',
            ], 500);
        }
    }

    /**
     * Ambil daftar notifikasi yang terlihat oleh pemanggil
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();

        $notifications = $this->applyNotificationVisibilityScope(
            Notification::with(['user', 'device', 'recipients']),
            $user
        )->paginate(10); // Tambahkan pagination

        return response()->json($notifications);
    }

    /**
     * Ambil detail notifikasi
     */
    public function show($id)
    {
        $notification = $this->ensureNotificationVisibleToUser($id);

        if ($notification instanceof \Illuminate\Http\JsonResponse) {
            return $notification;
        }

        return response()->json($notification->load(['user', 'device', 'recipients']));
    }
}
