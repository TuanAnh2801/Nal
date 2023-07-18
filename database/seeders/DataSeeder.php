<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataSeeder extends Seeder
{

    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'admin'],
            ['name' => 'user'],
        ]);

        DB::table('permissions')->insert([
            ['name' => 'view'],
            ['name' => 'update'],
            ['name' => 'delete'],
        ]);

        DB::table('role_permission')->insert([
            ['role_id' => 1, 'permission_id' => 1],
            ['role_id' => 1, 'permission_id' => 2],
            ['role_id' => 1, 'permission_id' => 3],
            ['role_id' => 2, 'permission_id' => 1],
            ['role_id' => 2, 'permission_id' => 2],
            ['role_id' => 2, 'permission_id' => 3],
        ]);
    }
}
