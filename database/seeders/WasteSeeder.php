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
        $botolCategory = WasteCategory::whereLike('name', '%botol%')->first();
        $plastikCategory = WasteCategory::whereLike('name', '%plastik%')->first();
        $kacaCategory = WasteCategory::whereLike('name', '%kaca%')->first();
        $kertasCategory = WasteCategory::whereLike('name', '%kertas%')->first();
        $kardusCategory = WasteCategory::whereLike('name', '%kardus%')->first();
        $logamCategory = WasteCategory::whereLike('name', '%logam%')->first();


        Waste::create([
            'name' => 'Botol PET Bening Bersih',
            'stock_in_kg' => 0,
            'waste_category_id' => $botolCategory->id,
        ]);
        Waste::create([
            'name' => 'Kantong Plastik',
            'stock_in_kg' => 0,
            'waste_category_id' => $plastikCategory->id,
        ]);
        Waste::create([
            'name' => 'Botol Kaca',
            'stock_in_kg' => 0,
            'waste_category_id' => $kacaCategory->id,
        ]);
        Waste::create([
            'name' => 'Buku Bekas',
            'stock_in_kg' => 0,
            'waste_category_id' => $kertasCategory->id,
        ]);
        Waste::create([
            'name' => 'Kardus Bekas',
            'stock_in_kg' => 0,
            'waste_category_id' => $kardusCategory->id,
        ]);
        Waste::create([
            'name' => 'Kaleng Aluminium',
            'stock_in_kg' => 0,
            'waste_category_id' => $logamCategory->id,
        ]);
        Waste::create([
            'name' => 'Botol PET Bening Kotor',
            'stock_in_kg' => 0,
            'waste_category_id' => $botolCategory->id,
            'can_sorted' => true,
        ]);
    }
}
