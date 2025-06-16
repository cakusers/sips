<?php

namespace Database\Seeders;

use App\Models\Waste;
use App\Models\WastePrice;
use App\Models\WasteCategory;
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
        $plastikCategory = WasteCategory::where('name', 'Plastik')->first();
        $kertasCategory = WasteCategory::where('name', 'Kertas')->first();
        $logamCategory = WasteCategory::where('name', 'Logam')->first();

        Waste::create([
            'name' => 'Botol PET Bening',
            'stock_in_kg' => 0,
            'waste_category_id' => $plastikCategory->id,
        ]);
        Waste::create([
            'name' => 'Kardus Bekas',
            'stock_in_kg' => 0,
            'waste_category_id' => $kertasCategory->id,
        ]);
        Waste::create([
            'name' => 'Kaleng Aluminium',
            'stock_in_kg' => 0,
            'waste_category_id' => $logamCategory->id,
        ]);
    }
}
