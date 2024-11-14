<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricalData extends Model
{
    protected $primaryKey = 'history_id';
    protected $table = 'historical_data';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'parameters',
        'waktu_diambil',
        'devices_id',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'devices_id');
    }

    protected $casts = [
        'parameters' => 'array',
    ];
}
