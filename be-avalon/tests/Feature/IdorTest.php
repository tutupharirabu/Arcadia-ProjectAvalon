<?php

use App\Models\Device;
use App\Models\Role;
use App\Models\User;
use App\Models\OTP_codes;
use App\Models\Notification;
use App\Models\WaterPumpAlarm;
use App\Models\NotificationRecipient;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Routing\Middleware\ThrottleRequests;

/*
|--------------------------------------------------------------------------
| IDOR: user B tidak boleh mengakses resource milik user A
|--------------------------------------------------------------------------
| Pola 2 user (petani). Token di-generate langsung via JWTAuth::fromUser
| (bukan endpoint login) agar test tidak bergantung pada rate limiter.
*/

beforeEach(function () {
    $this->withoutMiddleware(ThrottleRequests::class);
    Http::fake();

    $this->petaniRole = Role::create(['title' => 'petani']);
    Role::create(['title' => 'admin']);

    $this->userA = User::create([
        'name' => 'User A',
        'email' => 'user-a@test.com',
        'password' => Hash::make('password123'),
        'roles_id' => $this->petaniRole->roles_id,
    ]);
    $this->userB = User::create([
        'name' => 'User B',
        'email' => 'user-b@test.com',
        'password' => Hash::make('password123'),
        'roles_id' => $this->petaniRole->roles_id,
    ]);

    $this->deviceA = Device::create([
        'devices_id' => (string) Str::uuid(),
        'device_name' => 'Pompa A',
        'device_type' => 'water-pump',
        'status' => 'Active',
        'users_id' => $this->userA->users_id,
    ]);
    $this->deviceB = Device::create([
        'devices_id' => (string) Str::uuid(),
        'device_name' => 'Pompa B',
        'device_type' => 'water-pump',
        'status' => 'Active',
        'users_id' => $this->userB->users_id,
    ]);

    $this->alarmA = WaterPumpAlarm::create([
        'devices_id' => $this->deviceA->devices_id,
        'start_time' => Carbon::now()->addHour()->format('Y-m-d H:i:s'),
        'end_time' => Carbon::now()->addHours(2)->format('Y-m-d H:i:s'),
        'is_active' => false,
    ]);

    $this->otpA = OTP_codes::create([
        'users_id' => $this->userA->users_id,
        'otp_code' => 123456,
        'valid_until' => Carbon::now()->addMinutes(5),
    ]);

    $this->notificationA = Notification::create([
        'source' => 'device',
        'title' => 'Notifikasi Milik A',
        'message' => 'Hanya untuk A',
        'type' => 'info',
        'devices_id' => $this->deviceA->devices_id,
    ]);
    NotificationRecipient::create([
        'notifications_id' => $this->notificationA->notifications_id,
        'users_id' => $this->userA->users_id,
    ]);

    $this->tokenA = JWTAuth::fromUser($this->userA);
    $this->tokenB = JWTAuth::fromUser($this->userB);
});

it('user B tidak bisa kontrol pompa device milik user A', function () {
    $this->withToken($this->tokenB)
        ->postJson('/api/v1/water-pump/control', [
            'device_id' => $this->deviceA->devices_id,
            'action' => 'ON',
        ])
        ->assertStatus(403);
});

it('user B tidak bisa update device milik user A', function () {
    $this->withToken($this->tokenB)
        ->putJson('/api/v1/device/' . $this->deviceA->devices_id, [
            'device_name' => 'Pompa A Dibajak',
        ])
        ->assertStatus(403);
});

it('user B tidak bisa destroy device milik user A', function () {
    $this->withToken($this->tokenB)
        ->deleteJson('/api/v1/device/' . $this->deviceA->devices_id)
        ->assertStatus(403);

    $this->assertDatabaseHas('devices', ['devices_id' => $this->deviceA->devices_id]);
});

it('user B tidak bisa lihat detail private device milik user A', function () {
    $this->withToken($this->tokenB)
        ->getJson('/api/v1/device/check-private/' . $this->deviceA->devices_id)
        ->assertStatus(403);
});

it('user B tidak bisa akses alarm milik user A (index/update/destroy)', function () {
    // index
    $this->withToken($this->tokenB)
        ->getJson('/api/v1/water-alarm?devices_id=' . $this->deviceA->devices_id)
        ->assertStatus(403);

    // updateOrCreate
    $this->withToken($this->tokenB)
        ->postJson('/api/v1/water-alarm', [
            'devices_id' => $this->deviceA->devices_id,
            'start_time' => Carbon::now()->addHours(3)->format('Y-m-d H:i:s'),
            'end_time' => Carbon::now()->addHours(4)->format('Y-m-d H:i:s'),
            'is_active' => true,
        ])
        ->assertStatus(403);

    // destroy
    $this->withToken($this->tokenB)
        ->deleteJson('/api/v1/water-alarm/' . $this->alarmA->water_pump_alarm_id)
        ->assertStatus(403);

    $this->assertDatabaseHas('water_pump_alarms', [
        'water_pump_alarm_id' => $this->alarmA->water_pump_alarm_id,
    ]);
});

it('OTP milik user A tidak valid untuk user B saat verifikasi email', function () {
    $this->withToken($this->tokenB)
        ->postJson('/api/v1/auth/verification-email', [
            'otp_code' => $this->otpA->otp_code,
        ])
        ->assertStatus(404);
});

it('feed notifikasi user B tidak bocor ke notifikasi milik user A', function () {
    // index: feed B tidak memuat notifikasi A
    $feedB = $this->withToken($this->tokenB)
        ->getJson('/api/v1/notification')
        ->assertStatus(200)
        ->json('data');

    $idsVisibleToB = collect($feedB)->pluck('notifications_id')->all();
    expect($idsVisibleToB)->not->toContain($this->notificationA->notifications_id);

    // show: B tidak bisa baca detail notifikasi A (404 agar tidak membocorkan eksistensi)
    $this->withToken($this->tokenB)
        ->getJson('/api/v1/notification/' . $this->notificationA->notifications_id)
        ->assertStatus(404);

    // recipient feed: B tidak melihat baris recipient milik A
    $recipientsB = $this->withToken($this->tokenB)
        ->getJson('/api/v1/notification/recipient')
        ->assertStatus(200)
        ->json('data');

    $recipientIdsB = collect($recipientsB)->pluck('notification_recipients_id')->all();
    expect($recipientIdsB)->toBe([]);
});

it('user A tetap bisa mengakses resource miliknya sendiri (kontrol positif)', function () {
    // Feed notifikasi A memuat notifikasi A
    $feedA = $this->withToken($this->tokenA)
        ->getJson('/api/v1/notification')
        ->assertStatus(200)
        ->json('data');

    expect(collect($feedA)->pluck('notifications_id')->all())
        ->toContain($this->notificationA->notifications_id);

    // Alarm A terlihat oleh A
    $this->withToken($this->tokenA)
        ->getJson('/api/v1/water-alarm?devices_id=' . $this->deviceA->devices_id)
        ->assertStatus(200);
});

it('menghapus device meng-cascade alarm dan log terkait (migration hardening)', function () {
    \App\Models\WaterPumpLog::create([
        'devices_id' => $this->deviceA->devices_id,
        'start_time' => Carbon::now()->subMinutes(10)->format('Y-m-d H:i:s'),
        'is_on' => false,
    ]);

    // Catatan: notifications.devices_id sengaja TETAP RESTRICT (di luar daftar 4 FK
    // yang di-cascade di migration hardening) — notifikasi sumber-device yang masih
    // menunjuk ke device akan tetap memblokir delete. Lepas dulu untuk menguji
    // cascade alarm/log/recipient yang memang menjadi bagian dari hardening.
    $this->notificationA->update(['devices_id' => null]);

    $this->withToken($this->tokenA)
        ->deleteJson('/api/v1/device/' . $this->deviceA->devices_id)
        ->assertStatus(200);

    // FK cascade dari migration hardening: dependensi ikut terhapus
    $this->assertDatabaseMissing('water_pump_alarms', [
        'water_pump_alarm_id' => $this->alarmA->water_pump_alarm_id,
    ]);
    $this->assertDatabaseMissing('water_pump_logs', [
        'devices_id' => $this->deviceA->devices_id,
    ]);
});

it('menghapus notifikasi meng-cascade baris recipient-nya (migration hardening)', function () {
    $this->notificationA->delete();

    $this->assertDatabaseMissing('notification_recipients', [
        'notifications_id' => $this->notificationA->notifications_id,
    ]);
});
