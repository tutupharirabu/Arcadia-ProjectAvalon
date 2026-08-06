<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Middleware\ThrottleRequests;

/*
|--------------------------------------------------------------------------
| Auth flow: register, login sukses, login password salah
|--------------------------------------------------------------------------
| Throttle di-bypass agar test fokus pada logika auth (test rate limit
| terpisah di RateLimitTest.php).
*/

beforeEach(function () {
    $this->withoutMiddleware(ThrottleRequests::class);
    Http::fake(); // blokir panggilan nyata ke node_mqtt_server saat login

    // register() mencari role 'petani' — wajib ada
    Role::create(['title' => 'petani']);
});

it('register berhasil dan mengembalikan token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Petani Test',
        'email' => 'petani@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'user' => ['name', 'email'], 'token']);

    $this->assertDatabaseHas('users', [
        'email' => 'petani@test.com',
    ]);
});

it('login berhasil dengan password benar', function () {
    $role = Role::where('title', 'petani')->first();
    User::create([
        'name' => 'Petani Login',
        'email' => 'login@test.com',
        'password' => Hash::make('password123'),
        'roles_id' => $role->roles_id,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@test.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email', 'role'], 'token']);
});

it('login gagal dengan password salah', function () {
    $role = Role::where('title', 'petani')->first();
    User::create([
        'name' => 'Petani Gagal',
        'email' => 'gagal@test.com',
        'password' => Hash::make('password123'),
        'roles_id' => $role->roles_id,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'gagal@test.com',
        'password' => 'salah-salah',
    ]);

    $response->assertStatus(401);
});
