<?php

namespace App\Models;

use App\Models\User;
use App\Models\Device;
use App\Models\NotificationRecipient;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'notifications_id';
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'source',
        'title',
        'message',
        'type',
        'admin_id',
        'devices_id',
    ];

    /**
     * Relasi ke model User (admin atau user yang mengirim notifikasi).
     * Kolom aktual di tabel notifications adalah `admin_id` (bukan `users_id`).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id', 'users_id');
    }

    /**
     * Relasi ke model Device (jika notifikasi berasal dari perangkat).
     *
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'devices_id', 'devices_id');
    }

    /**
     * Relasi ke NotificationRecipient.
     *
     * @return HasMany<NotificationRecipient, $this>
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class, 'notifications_id', 'notifications_id');
    }
}
