<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Rate limit: endpoint login dibatasi 5 request/menit per IP (throttle:auth)
|--------------------------------------------------------------------------
| PERHATIAN: test ini TIDAK memanggil withoutMiddleware(ThrottleRequests::class)
| — tujuannya justru memverifikasi limiter bekerja (percobaan ke-6 → 429).
*/

beforeEach(function () {
    Http::fake();

    $role = Role::create(['title' => 'petani']);
    User::create([
        'name' => 'Rate Limit User',
        'email' => 'ratelimit@test.com',
        'password' => Hash::make('password123'),
        'roles_id' => $role->roles_id,
    ]);
});

it('percobaan login ke-6 ditolak oleh rate limiter (429)', function () {
    // 5 percobaan pertama: password salah → 401 (limiter belum tersentuh batas)
    for ($i = 1; $i <= 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'ratelimit@test.com',
            'password' => 'password-salah',
        ])->assertStatus(401);
    }

    // Percobaan ke-6 dalam menit yang sama → 429 Too Many Requests
    $this->postJson('/api/v1/auth/login', [
        'email' => 'ratelimit@test.com',
        'password' => 'password-salah',
    ])->assertStatus(429);
});
