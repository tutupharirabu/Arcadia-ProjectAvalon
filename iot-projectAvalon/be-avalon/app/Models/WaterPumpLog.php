<?php

namespace App\Models;

use App\Models\Device;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WaterPumpLog extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'water_pump_log_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'devices_id',
        'start_time',
        'end_time',
        'duration',
    ];

    /**
     * Relasi ke model Device
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'devices_id', 'devices_id');
    }
}
