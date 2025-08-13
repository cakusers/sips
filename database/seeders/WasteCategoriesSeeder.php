<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WasteCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Botol Plastik', 'emission_factor' => 0.28],
            ['name' => 'Plastik', 'emission_factor' => 5.51],
            ['name' => 'Kaca', 'emission_factor' => 1.46],
            ['name' => 'Kertas', 'emission_factor' => 0.609],
            ['name' => 'Kardus', 'emission_factor' => 0.46],
            ['name' => 'Logam', 'emission_factor' => 2.39],
            ['name' => 'Campuran', 'emission_factor' => 0],
        ];

        foreach ($categories as $category) {
            DB::table('waste_categories')->insert([
                'name' => $category['name'],
                'emission_factor' => $category['emission_factor'],
            ]);
        }
    }
}
