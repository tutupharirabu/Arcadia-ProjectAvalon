<?php

namespace App\Models;

use App\Models\User;
use App\Models\Device;
use App\Models\NotificationRecipient;
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
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'users_id');
    }

    /**
     * Relasi ke model Device (jika notifikasi berasal dari perangkat).
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'devices_id', 'devices_id');
    }

    /**
     * Relasi ke NotificationRecipient.
     */
    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class, 'notifications_id', 'notifications_id');
    }
}
