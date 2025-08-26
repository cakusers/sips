<?php

namespace Database\Seeders;

use App\Models\CustomerCategory;
use App\Models\CustomerType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Perorangan',
            'Pengepul Sampah',
            'Pabrik'
        ];

        foreach ($names as $name) {
            CustomerCategory::create([
                'name' => $name
            ]);
        }
    }
}
