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
        $names = ['Plastik', 'Kertas', 'Kaca', 'Logam'];

        foreach ($names as $name) {
            DB::table('waste_categories')->insert([
                'name' => $name
            ]);
        }
    }
}
