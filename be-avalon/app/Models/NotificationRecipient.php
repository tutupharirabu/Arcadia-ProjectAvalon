<?php

namespace App\Models;

use App\Models\Role;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationRecipient extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'notification_recipients';
    protected $primaryKey = 'notification_recipients_id';

    protected $fillable = [
        'notification_recipients_id',
        'notifications_id',
        'users_id',
        'roles_id',
        'is_read',
    ];

    /**
     * Relasi ke model Notification.
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notifications_id', 'notifications_id');
    }

    /**
     * Relasi ke model User (penerima notifikasi jika spesifik user).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'users_id');
    }

    /**
     * Relasi ke model Role (penerima notifikasi berdasarkan role).
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id', 'roles_id');
    }

}
