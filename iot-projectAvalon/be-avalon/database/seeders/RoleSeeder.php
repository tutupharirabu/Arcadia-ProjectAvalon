<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'roles_id' => '9d850101-2ca3-43a1-b14e-51fd09390c42',
                'title' => 'super-admin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'roles_id' => '9d850108-cc58-4a24-a687-d0155245e38d',
                'title' => 'admin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'roles_id' => '9d850113-4074-45e6-af76-535afe276b53',
                'title' => 'petugas-iot',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'roles_id' => '9d850118-64b8-4bf0-8898-73172f0d431a',
                'title' => 'petani',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
