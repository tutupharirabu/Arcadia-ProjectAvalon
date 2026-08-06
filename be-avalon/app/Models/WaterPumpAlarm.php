<?php

namespace App\Models;

use App\Models\Device;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WaterPumpAlarm extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'water_pump_alarm_id';

    protected $table = 'water_pump_alarms';

    protected $fillable = [
        'water_pump_alarm_id',
        'devices_id',
        'start_time',
        'end_time',
        'is_active'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'devices_id', 'devices_id');
    }

    public function lastExecutedLog()
    {
        return $this->belongsTo(WaterPumpLog::class, 'last_executed_log_id', 'water_pump_log_id');
    }
}
