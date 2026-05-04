<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Jalankan RoleSeeder terlebih dahulu agar ID peran tersedia
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            PrioritySeeder::class,
            LabelSeeder::class
        ]);

        // 2. Buat akun demo wajib menggunakan Factory dan State
        // Menyesuaikan email admin@admin.com dan role admin
        User::factory()->admin()->create([
            'email' => 'admin@admin.com',
            'password' => 'password'
        ]);

        User::factory()->supervisor()->create([
            'email' => 'supervisor@admin.com',
            'password' => 'password'
        ]);
        User::factory()->agent()->create([
            'email' => 'agent@admin.com',
            'password' => 'password'
        ]);
        User::factory()->customer()->create([
            'email' => 'customer@demo.com',
            'password' => 'password'
        ]);

        // 3. (Opsional) Produksi ribuan data dummy lainnya
        User::factory(100)->customer()->create();
    }
}
