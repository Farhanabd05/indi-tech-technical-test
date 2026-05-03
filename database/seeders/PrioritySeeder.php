<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            [
                'name' => 'Low',
                'response_time' => 24, // 24 hours
                'resolution_time' => 120, // 5 days
                'created_at' => now(),
                'updated_at' => now(),
                'level' => 1
            ],
            [
                'name' => 'Medium',
                'response_time' => 8, // 8 hours
                'resolution_time' => 72, // 3 days
                'created_at' => now(),
                'updated_at' => now(),
                'level' => 2
            ],
            [
                'name' => 'High',
                'response_time' => 4, // 4 hours
                'resolution_time' => 24, // 1 day
                'created_at' => now(),
                'updated_at' => now(),
                'level' => 3
            ],
            [
                'name' => 'Critical',
                'response_time' => 1, // 1 hour
                'resolution_time' => 8, // 8 hours
                'created_at' => now(),
                'updated_at' => now(),
                'level' => 4
            ],
        ];

        DB::table('priorities')->insert($priorities);
    }
}