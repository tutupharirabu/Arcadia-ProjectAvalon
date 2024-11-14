<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $primaryKey = 'devices_id';
    protected $table = 'devices';

    protected $fillable = [
        'device_name',
        'device_type',
        'status',
        'location',
        'description',
        'users_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
