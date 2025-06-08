<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Waste;
use App\Models\Customer;
use App\Models\WastePrice;
use App\Models\Transaction;
use App\Enums\TransactionType;
use Illuminate\Database\Seeder;
use App\Enums\TransactionStatus;
use App\Models\TransactionWaste;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TransactionSeederMay extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data master
        $customer1 = Customer::find(1);
        $customer2 = Customer::find(2);

        if (!$customer1 || !$customer2) {
            $this->command->error('Customer dengan ID 1 atau 2 tidak ditemukan. Jalankan CustomerSeeder terlebih dahulu.');
            return;
        }

        $botolPet = Waste::where('name', 'Botol PET Bening')->first();
        $kardus = Waste::where('name', 'Kardus Bekas')->first();
        $kaleng = Waste::where('name', 'Kaleng Aluminium')->first();

        if (!$botolPet || !$kardus || !$kaleng) {
            $this->command->error('Waste item tidak ditemukan. Jalankan WasteSeeder & WastePriceSeeder terlebih dahulu.');
            return;
        }

        $hargaBotolPet = WastePrice::where('waste_id', $botolPet->id)->first();
        $hargaKardus = WastePrice::where('waste_id', $kardus->id)->first();
        $hargaKaleng = WastePrice::where('waste_id', $kaleng->id)->first();

        if (!$hargaBotolPet || !$hargaKardus || !$hargaKaleng) {
            $this->command->error('Waste price tidak ditemukan. Jalankan WastePriceSeeder terlebih dahulu.');
            return;
        }

        /**
         * --- Data Bulan May ---
         */

        $mei1 = Carbon::create(2025, 5, 1, 10);
        $purchaseMei1 = Transaction::create([
            'type' => TransactionType::PURCHASE->value,
            'status' => TransactionStatus::COMPLETE->value,
            'customer_id' => $customer2->id,
            'total_price' => ($hargaBotolPet->purchase_per_kg * 10) + ($hargaKardus->purchase_per_kg * 5), // 20000 + 6000 = 26000
            'created_at' => $mei1,
            'updated_at' => $mei1,
        ]);
        TransactionWaste::create([
            'transaction_id' => $purchaseMei1->id,
            'waste_id' => $botolPet->id,
            'qty_in_kg' => 10,
            'sub_total_price' => $hargaBotolPet->purchase_per_kg * 10, //20000
            'created_at' => $mei1,
            'updated_at' => $mei1,
        ]);
        TransactionWaste::create([
            'transaction_id' => $purchaseMei1->id,
            'waste_id' => $kardus->id,
            'qty_in_kg' => 5,
            'sub_total_price' => $hargaKardus->purchase_per_kg * 5, //6000
            'created_at' => $mei1,
            'updated_at' => $mei1,
        ]);

        $mei2 = Carbon::create(2025, 5, 1, 10);
        $sellMei2 = Transaction::create([
            'type' => TransactionType::SELL->value,
            'status' => TransactionStatus::COMPLETE->value,
            'customer_id' => $customer2->id,
            'total_price' => ($hargaBotolPet->selling_per_kg * 10) + ($hargaKardus->selling_per_kg * 5), // 20000 + 6000 = 26000
            'created_at' => $mei2,
            'updated_at' => $mei2,
        ]);
        TransactionWaste::create([
            'transaction_id' => $sellMei2->id,
            'waste_id' => $botolPet->id,
            'qty_in_kg' => 10,
            'sub_total_price' => $hargaBotolPet->selling_per_kg * 10, //20000
            'created_at' => $mei2,
            'updated_at' => $mei2,
        ]);
        TransactionWaste::create([
            'transaction_id' => $sellMei2->id,
            'waste_id' => $kardus->id,
            'qty_in_kg' => 5,
            'sub_total_price' => $hargaKardus->selling_per_kg * 5, //6000
            'created_at' => $mei2,
            'updated_at' => $mei2,
        ]);
    }
}
