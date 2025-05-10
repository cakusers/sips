<?php

namespace Database\Seeders;

use App\Models\Waste;
use App\Models\WastePrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WasteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wastes = [
            [
                'name' => 'Botol Plastik',
                'waste_category_id' => 1,
                'purchase_per_kg' => 1500,
                'selling_per_kg' => 2000,
                'stock_in_kg' => 45,
            ],
            [
                'name' => 'Botol Kaca',
                'waste_category_id' => 3,
                'purchase_per_kg' => 2000,
                'selling_per_kg' => 2500,
                'stock_in_kg' => 0,
            ],
            [
                'name' => 'Kertas Karton',
                'waste_category_id' => 2,
                'purchase_per_kg' => 800,
                'selling_per_kg' => 1000,
                'stock_in_kg' => 0,
            ],
        ];

        foreach ($wastes as $waste) {

            $currentWaste = Waste::create([
                'name' => $waste['name'],
                'waste_category_id' => $waste['waste_category_id'],
                'stock_in_kg' => $waste['stock_in_kg']
            ]);
            WastePrice::create([
                'waste_id' => $currentWaste->id,
                'purchase_per_kg' => $waste['purchase_per_kg'],
                'selling_per_kg' => $waste['selling_per_kg'],
            ]);
        }
    }
}
