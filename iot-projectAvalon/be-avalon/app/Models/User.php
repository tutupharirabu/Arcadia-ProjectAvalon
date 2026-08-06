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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuids;

    /**
     * Register boot hooks untuk model User.
     *
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();

        static::created(function ($model) {
            $model->generateOtpCodeData($model);
        });
    }

    /**
     * Membuat kode OTP untuk user dan menyimpannya ke tabel OTP_codes.
     *
     * @param User $user
     * @return void
     */
    public function generateOtpCodeData($user): void
    {
        $randomNumber = mt_rand(100000, 999999);
        $now = Carbon::now();

        $otp = OTP_codes::updateOrCreate(
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

    /**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Relasi ke Role yang dimiliki user.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'roles_id');
    }

    /**
     * Relasi ke kode OTP milik user.
     *
     * @return HasOne<OTP_codes, $this>
     */
    public function otpCode(): HasOne
    {
        return $this->hasOne(OTP_codes::class, 'users_id');
    }

    // Relasi ke notifikasi yang dikirim oleh pengguna (kolom aktual: notifications.admin_id)
    /**
     * @return HasMany<Notification, $this>
     */
    public function sentNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'admin_id', 'users_id');
    }

    // Relasi ke notifikasi yang diterima oleh pengguna
    // (melalui notification_recipients, di-scope ke users_id penerima)
    /**
     * @return HasManyThrough<Notification, NotificationRecipient, $this>
     */
    public function receivedNotifications(): HasManyThrough
    {
        return $this->hasManyThrough(
            Notification::class,
            NotificationRecipient::class,
            'users_id', // FK di notification_recipients yang menunjuk users
            'notifications_id', // FK di notifications yang dirujuk notification_recipients
            'users_id', // Local key di users
            'notifications_id' // Local key di notification_recipients
        );
    }
}
