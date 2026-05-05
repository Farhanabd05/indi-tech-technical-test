<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
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
        // 1. Eksekusi data referensi utama
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            PrioritySeeder::class,
            LabelSeeder::class
        ]);

        // 2. Ciptakan 3 Departemen/Tim buatan
        $teams = Team::factory(3)->create();

        // 3. Akun Demo Utama
        User::factory()->admin()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@admin.com',
            'password' => 'password'
        ]);

        // Masukkan akun penyelia demo ke tim pertama
        User::factory()->supervisor()->create([
            'name' => 'Penyelia IT (1) ',
            'email' => 'supervisor@admin.com',
            'password' => 'password',
            'team_id' => $teams->first()->id, 
        ]);

        User::factory()->supervisor()->create([
            'name' => 'Penyelia IT (2) ',
            'email' => 'supervisor2@admin.com',
            'password' => 'password',
            'team_id' => $teams->get(2)->id,
        ]);

        // Masukkan akun agen demo ke tim pertama
        User::factory()->agent()->create([
            'name' => 'Agen IT Support (1)',
            'email' => 'agent@admin.com',
            'password' => 'password',
            'team_id' => $teams->first()->id,
        ]);

        // akun agen kedua ke tim pertama
        User::factory()->agent()->create([
            'name' => 'Pak Toto',
            'email' => 'toto@admin.com',
            'password' => 'password',
            'team_id' => $teams->first()->id,
        ]);


        // masukkan akun agen demo ke tim kedua
        User::factory()->agent()->create([
            'name' => 'Agen IT Support (2)',
            'email' => 'agent2@admin.com',
            'password' => 'password',
            'team_id' => $teams->get(2)->id,
        ]);

        User::factory()->agent()->create([
            'name' => 'Pak Tatang',
            'email' => 'tatang@admin.com',
            'password' => 'password',
            'team_id' => $teams->get(2)->id,
        ]);



        User::factory()->customer()->create([
            'name' => 'Pelanggan Demo',
            'email' => 'customer@demo.com',
            'password' => 'password'
        ]);

        // 4. Populasi Data Massal: Distribusikan agen dan penyelia ke masing-masing tim
        foreach ($teams as $team) {
            // Tiap tim punya 1 penyelia tambahan dan 4 agen acak
            User::factory(1)->supervisor()->create(['team_id' => $team->id]);
            User::factory(4)->agent()->create(['team_id' => $team->id]);
        }

        // Ciptakan 20 pelanggan acak
        User::factory(20)->customer()->create();
    }
}