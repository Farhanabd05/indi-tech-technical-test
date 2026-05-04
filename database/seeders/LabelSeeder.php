<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            [
                'name' => 'Label 1',
                'color' => '#FF0000',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Label 2',
                'color' => '#00FF00',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Label 3',
                'color' => '#0000FF',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('labels')->insert($labels);
    }
}
