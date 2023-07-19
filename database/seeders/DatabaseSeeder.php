<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'admin'],
            ['name' => 'user'],
            ['name' => 'customer'],
            ['name' => 'editor'],
        ]);

        DB::table('permissions')->insert([
            ['name' => 'create'],
            ['name' => 'read'],
            ['name' => 'update'],
            ['name' => 'delete'],


        ]);

        DB::table('permission_role')->insert([

            ['role_id' => 3, 'permission_id' => 2],
            ['role_id' => 4, 'permission_id' => 1],
            ['role_id' => 4, 'permission_id' => 2],
            ['role_id' => 4, 'permission_id' => 3],

        ]);

    }
}
