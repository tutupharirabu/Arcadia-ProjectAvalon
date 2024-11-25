<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'devices_id';
    protected $table = 'devices';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'devices_id',
        'device_name',
        'device_type',
        'status',
        'location',
        'description',
        'users_id',
    ];

    /**
     * Relasi ke model User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'users_id');
    }

    /**
     * Relasi ke model HistoricalData.
     */
    public function historicalData()
    {
        return $this->hasMany(HistoricalData::class, 'devices_id', 'devices_id');
    }
}
