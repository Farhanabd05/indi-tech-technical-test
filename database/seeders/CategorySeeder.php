<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Technical Support',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Account Issue',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Billing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Feature Request',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bug Report',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Infrastructure',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('categories')->insert($categories);
    }
}
