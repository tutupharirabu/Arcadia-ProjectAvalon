<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\OTP_codes;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->generateOtpCodeData($model);
        });
    }

    public function generateOtpCodeData($user)
    {
        $randomNumber = mt_rand(100000, 999999);
        $now = Carbon::now();

        $otp = Otp_codes::updateOrCreate(
            ['users_id' => $user->users_id],
            [
                'otp_code' => $randomNumber,
                'valid_until' => $now->addMinutes(5),
            ]
        );
    }

    protected $primaryKey = 'users_id';
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id');
    }

    public function otpCode()
    {
        return $this->hasOne(Otp_codes::class, 'users_id');
    }

    // Relasi ke notifikasi yang dikirim oleh pengguna
    public function sentNotifications()
    {
        return $this->hasMany(Notification::class, 'sender_id', 'id');
    }

    // Relasi ke notifikasi yang diterima oleh pengguna
    public function receivedNotifications()
    {
        return $this->hasManyThrough(
            Notification::class,
            NotificationRecipient::class,
            'recipient_id', // Foreign key di notification_recipients
            'notification_id', // Foreign key di notifications
            'id', // Local key di users
            'notification_id' // Local key di notification_recipients
        );
    }
}
