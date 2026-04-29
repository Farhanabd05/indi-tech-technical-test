<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Administrator', 'slug' => 'administrator', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Agent', 'slug' => 'agent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Customer', 'slug' => 'customer', 'created_at' => now(), 'updated_at' => now()],
        ];

        // Menggunakan Query Builder tetap sah, namun wajib menyertakan timestamps manual
        DB::table('roles')->insert($roles);
    }
}