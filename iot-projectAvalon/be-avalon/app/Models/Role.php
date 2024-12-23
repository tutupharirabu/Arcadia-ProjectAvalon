<?php

namespace App\Models;

use App\Models\User;
use App\Models\NotificationRecipient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{

    use HasFactory, HasUuids;
    protected $primaryKey = 'roles_id';
    protected $table = 'roles';

    protected $fillable = [
        'title',
    ];

    public function list_user()
    {
        return $this->hasMany(User::class, 'roles_id', 'roles_id');
    }

    /**
     * Relasi ke tabel notification_recipients (notifikasi yang diterima oleh role ini).
     */
    public function notifications()
    {
        return $this->hasMany(NotificationRecipient::class, 'roles_id', 'roles_id');
    }
}
