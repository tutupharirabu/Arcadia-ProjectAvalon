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

    public $timestamps = false;
}
