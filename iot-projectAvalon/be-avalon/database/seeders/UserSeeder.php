<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'users_id' => '9d850160-70c1-4229-ba4c-ccf1a069aab6',
                'name' => 'Super-Admin',
                'email' => 'super-admin@gmail.com',
                'password' => Hash::make('password123'),
                'roles_id' => '9d850118-64b8-4bf0-8898-73172f0d431a',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'users_id' => '9d85037b-1e24-4c09-ba85-e09719f3b626',
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password123'),
                'roles_id' => '9d850108-cc58-4a24-a687-d0155245e38d',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'users_id' => '9d8503d8-04dc-4162-b1d5-1a545229fc00',
                'name' => 'Petugas-IoT',
                'email' => 'petugas-iot@gmail.com',
                'password' => Hash::make('password123'),
                'roles_id' => '9d850113-4074-45e6-af76-535afe276b53',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
