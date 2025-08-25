<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerTypeSeeder extends Seeder
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
            CustomerType::create([
                'name' => $name
            ]);
        }
    }
}
