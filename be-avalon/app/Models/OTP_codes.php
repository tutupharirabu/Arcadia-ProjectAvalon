<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP_codes extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'otp_codes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'otp_code',
        'users_id',
        'valid_until',
    ];

    // Timestamps aktif sejak migration 2026_08_06_000000 menambahkan kolom
    // created_at/updated_at (nullable — baris lama tetap kosong).
}
