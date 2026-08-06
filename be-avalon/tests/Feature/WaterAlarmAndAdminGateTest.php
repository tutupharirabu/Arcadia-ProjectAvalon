<?php

use App\Models\Device;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Routing\Middleware\ThrottleRequests;

/*
|--------------------------------------------------------------------------
| Alarm route (regresi #3 audit: bind controller salah → 500) + admin gate
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->withoutMiddleware(ThrottleRequests::class);
    Http::fake();

    $this->petaniRole = Role::create(['title' => 'petani']);
    Role::create(['title' => 'admin']);

    $this->petani = User::create([
        'name' => 'Petani Alarm',
        'email' => 'alarm@test.com',
        'password' => Hash::make('password123'),
        'roles_id' => $this->petaniRole->roles_id,
    ]);

    $this->device = Device::create([
        'devices_id' => (string) Str::uuid(),
        'device_name' => 'Pompa Alarm',
        'device_type' => 'water-pump',
        'status' => 'Active',
        'users_id' => $this->petani->users_id,
    ]);

    $this->token = JWTAuth::fromUser($this->petani);
});

it('GET /v1/water-alarm dengan device milik sendiri tidak error 500 dan mengembalikan array', function () {
    // Regresi #3: sebelumnya route di-bind ke WaterPumpController yang tidak punya
    // method index → 500. Sekarang harus 200 dengan daftar alarm (JSON array polos).
    $response = $this->withToken($this->token)
        ->getJson('/api/v1/water-alarm?devices_id=' . $this->device->devices_id);

    $response->assertStatus(200);
    $this->assertIsArray($response->json());
});

it('store notifikasi oleh user petani ditolak (admin-gate)', function () {
    // Route POST /v1/notification pakai middleware isAdmin → non-admin dapat 401
    $this->withToken($this->token)
        ->postJson('/api/v1/notification', [
            'source' => 'admin',
            'title' => 'Notifikasi Ilegal',
            'message' => 'Dikirim oleh petani',
            'type' => 'info',
        ])
        ->assertStatus(401);

    $this->assertDatabaseCount('notifications', 0);
});
