<?php

namespace Database\Seeders;

use App\Models\CustomerCategory;
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
        $botolPet = Waste::whereLike('name', '%bersih%')->first();
        $kantong = Waste::whereLike('name', '%Kantong%')->first();
        $botolKaca = Waste::whereLike('name', '%kaca%')->first();
        $buku = Waste::whereLike('name', '%buku%')->first();
        $kardus = Waste::whereLike('name', 'Kardus Bekas')->first();
        $kaleng = Waste::whereLike('name', '%Kaleng%')->first();
        $botolktr = Waste::whereLike('name', '%kotor%')->first();

        $perorangan = CustomerCategory::whereLike('name', 'perorangan')->first();
        $pabrik = CustomerCategory::whereLike('name', 'pabrik')->first();
        $pengepul = CustomerCategory::whereLike('name', '%pengepul%')->first();

        $priceApril = Carbon::create(2025, 4, 1, 14, 0, 0);
        /**
         * Botol PET Bening Bersih
         */
        WastePrice::create([
            'waste_id' => $botolPet->id,
            'purchase_per_kg' => 1500,
            'selling_per_kg' => 2000,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $perorangan->id
        ]);
        WastePrice::create([
            'waste_id' => $botolPet->id,
            'purchase_per_kg' => 1000,
            'selling_per_kg' => 1500,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $pengepul->id
        ]);

        /**
         * Kantong Kertas
         */
        WastePrice::create([
            'waste_id' => $kantong->id,
            'purchase_per_kg' => 1200,
            'selling_per_kg' => 1700,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $perorangan->id
        ]);
        WastePrice::create([
            'waste_id' => $kantong->id,
            'purchase_per_kg' => 1000,
            'selling_per_kg' => 1500,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $pengepul->id
        ]);

        /**
         * Botol Kaca
         */
        WastePrice::create([
            'waste_id' => $botolKaca->id,
            'purchase_per_kg' => 3000,
            'selling_per_kg' => 3500,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $perorangan->id
        ]);
        WastePrice::create([
            'waste_id' => $botolKaca->id,
            'purchase_per_kg' => 2500,
            'selling_per_kg' => 3000,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $pengepul->id
        ]);

        /**
         * Buku Bekas
         */
        WastePrice::create([
            'waste_id' => $buku->id,
            'purchase_per_kg' => 1800,
            'selling_per_kg' => 2200,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $perorangan->id
        ]);
        WastePrice::create([
            'waste_id' => $buku->id,
            'purchase_per_kg' => 1200,
            'selling_per_kg' => 1600,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $pengepul->id
        ]);

        /**
         *  Kardus Bekas
         */
        WastePrice::create([
            'waste_id' => $kardus->id,
            'purchase_per_kg' => 1300,
            'selling_per_kg' => 1700,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $perorangan->id
        ]);
        WastePrice::create([
            'waste_id' => $kardus->id,
            'purchase_per_kg' => 800,
            'selling_per_kg' => 1200,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $pengepul->id
        ]);

        /**
         * Kaleng
         */
        WastePrice::create([
            'waste_id' => $kaleng->id,
            'purchase_per_kg' => 5000,
            'selling_per_kg' => 6000,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $perorangan->id
        ]);
        WastePrice::create([
            'waste_id' => $kaleng->id,
            'purchase_per_kg' => 4000,
            'selling_per_kg' => 5000,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $pengepul->id
        ]);

        /**
         * Botol PET Kotor
         */
        WastePrice::create([
            'waste_id' => $botolktr->id,
            'purchase_per_kg' => 1000,
            'selling_per_kg' => 1200,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $perorangan->id
        ]);
        WastePrice::create([
            'waste_id' => $botolktr->id,
            'purchase_per_kg' => 800,
            'selling_per_kg' => 1000,
            'effective_start_date' => $priceApril,
            'customer_category_id' => $pengepul->id
        ]);
    }
}
