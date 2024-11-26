<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricalData extends Model
{
    use HasFactory;

    /**
     * Primary key untuk tabel.
     *
     * @var string
     */
    protected $primaryKey = 'history_id';
    protected $table = 'historical_data';

    /**
     * Kolom yang bisa diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'history_id',
        'parameters',
        'devices_id',
    ];

    protected $casts = [
        'parameters' => 'array', // Konversi otomatis JSON ke array
    ];

    /**
     * UUID digunakan sebagai primary key.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tipe primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Relasi ke tabel Devices.
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'devices_id', 'devices_id');
    }
}
