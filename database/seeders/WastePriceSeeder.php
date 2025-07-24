<?php

namespace Database\Seeders;

use App\Models\Waste;
use App\Models\WastePrice;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WastePriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $botolPet = Waste::where('name', 'Botol PET Bening')->first();
        $kardus = Waste::where('name', 'Kardus Bekas')->first();
        $kaleng = Waste::where('name', 'Kaleng Aluminium')->first();
        $campuran = Waste::where('name', 'Campuran')->first();

        $priceApril = Carbon::create(2025, 4, 1, 14, 0, 0);
        WastePrice::create([
            'waste_id' => $botolPet->id,
            'purchase_per_kg' => 1500,
            'selling_per_kg' => 2000,
            'effective_start_date' => $priceApril
        ]);
        WastePrice::create([
            'waste_id' => $kardus->id,
            'purchase_per_kg' => 800,
            'selling_per_kg' => 1200,
            'effective_start_date' => $priceApril
        ]);
        WastePrice::create([
            'waste_id' => $kaleng->id,
            'purchase_per_kg' => 7000,
            'selling_per_kg' => 9000,
            'effective_start_date' => $priceApril
        ]);
        WastePrice::create([
            'waste_id' => $campuran->id,
            'purchase_per_kg' => 0,
            'selling_per_kg' => 0,
            'effective_start_date' => $priceApril
        ]);
    }
}
