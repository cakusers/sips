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

class TransactionSeederApril extends Seeder
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
         * --- Data Bulan April ---
         */

        $apr1 = Carbon::create(2025, 4, 1, 10);
        $purchasApr1 = Transaction::create([
            'type' => TransactionType::PURCHASE->value,
            'status' => TransactionStatus::COMPLETE->value,
            'customer_id' => $customer2->id,
            'total_price' => ($hargaBotolPet->purchase_per_kg * 10) + ($hargaKardus->purchase_per_kg * 5), // 1500 * 10 + 800 * 5 = 15000 + 4000 = 19000
            'created_at' => $apr1,
            'updated_at' => $apr1,
        ]);
        TransactionWaste::create([
            'transaction_id' => $purchasApr1->id,
            'waste_id' => $botolPet->id,
            'qty_in_kg' => 10,
            'unit_price' => $hargaBotolPet->purchase_per_kg,
            'sub_total_price' => $hargaBotolPet->purchase_per_kg * 10, //15000
            'created_at' => $apr1,
            'updated_at' => $apr1,
        ]);
        TransactionWaste::create([
            'transaction_id' => $purchasApr1->id,
            'waste_id' => $kardus->id,
            'qty_in_kg' => 5,
            'unit_price' => $hargaKardus->purchase_per_kg,
            'sub_total_price' => $hargaKardus->purchase_per_kg * 5, //4000
            'created_at' => $apr1,
            'updated_at' => $apr1,
        ]);

        $apr2 = Carbon::create(2025, 4, 2, 9, 30, 0);
        $sellApr2 = Transaction::create([
            'type' => TransactionType::SELL->value,
            'status' => TransactionStatus::COMPLETE->value,
            'customer_id' => $customer1->id,
            'total_price' => ($hargaBotolPet->selling_per_kg * 10) + ($hargaKardus->selling_per_kg * 5), // 20000 + 6000 = 26000
            'created_at' => $apr2,
            'updated_at' => $apr2,
        ]);
        TransactionWaste::create([
            'transaction_id' => $sellApr2->id,
            'waste_id' => $botolPet->id,
            'qty_in_kg' => 10,
            'unit_price' => $botolPet->selling_per_kg,
            'sub_total_price' => $hargaBotolPet->selling_per_kg * 10, //20000
            'created_at' => $apr2,
            'updated_at' => $apr2,
        ]);
        TransactionWaste::create([
            'transaction_id' => $sellApr2->id,
            'waste_id' => $kardus->id,
            'qty_in_kg' => 5,
            'unit_price' => $hargaKardus->selling_per_kg,
            'sub_total_price' => $hargaKardus->selling_per_kg * 5, //6000
            'created_at' => $apr2,
            'updated_at' => $apr2,
        ]);

        // $apr7 = Carbon::create(2025, 4, 7, 14, 0, 0);
        // $purchaseApr7 = Transaction::create([
        //     'type' => TransactionType::PURCHASE->value,
        //     'status' => TransactionStatus::COMPLETE->value,
        //     'customer_id' => $customer1->id,
        //     'total_price' => $hargaKaleng->purchase_per_kg * 20, // 7000 * 20 = 140000
        //     'created_at' => $apr7,
        //     'updated_at' => $apr7,
        // ]);
        // TransactionWaste::create([
        //     'transaction_id' => $purchaseApr7->id,
        //     'waste_id' => $kaleng->id,
        //     'qty_in_kg' => 20,
        //     'sub_total_price' => $hargaKaleng->purchase_per_kg * 20, // 7000 * 20 = 140000
        //     'created_at' => $apr7,
        //     'updated_at' => $apr7,
        // ]);

        // $apr8 = Carbon::create(2025, 4, 8, 14, 0, 0);
        // $sellApr8 = Transaction::create([
        //     'type' => TransactionType::SELL->value,
        //     'status' => TransactionStatus::COMPLETE->value,
        //     'customer_id' => $customer2->id,
        //     'total_price' => $hargaKaleng->selling_per_kg * 20, // 9000 * 20 = 180000
        //     'created_at' => $apr8,
        //     'updated_at' => $apr8,
        // ]);
        // TransactionWaste::create([
        //     'transaction_id' => $sellApr8->id,
        //     'waste_id' => $kaleng->id,
        //     'qty_in_kg' => 20,
        //     'sub_total_price' => $hargaKaleng->selling_per_kg * 20, //180000
        //     'created_at' => $apr8,
        //     'updated_at' => $apr8,
        // ]);

        // $apr14 = Carbon::create(2025, 4, 14, 14, 0, 0);
        // $purchaseApr14 = Transaction::create([
        //     'type' => TransactionType::PURCHASE->value,
        //     'status' => TransactionStatus::COMPLETE->value,
        //     'customer_id' => $customer2->id,
        //     'total_price' => $hargaBotolPet->purchase_per_kg * 20, // 1500 * 20 = 30000
        //     'created_at' => $apr14,
        //     'updated_at' => $apr14,
        // ]);
        // TransactionWaste::create([
        //     'transaction_id' => $purchaseApr14->id,
        //     'waste_id' => $botolPet->id,
        //     'qty_in_kg' => 20,
        //     'sub_total_price' => $hargaBotolPet->purchase_per_kg * 20, //30000
        //     'created_at' => $apr14,
        //     'updated_at' => $apr14,
        // ]);

        // $apr15 = Carbon::create(2025, 4, 15, 14, 0, 0);
        // $sellApr15 = Transaction::create([
        //     'type' => TransactionType::SELL->value,
        //     'status' => TransactionStatus::COMPLETE->value,
        //     'customer_id' => $customer1->id,
        //     'total_price' => $hargaBotolPet->selling_per_kg * 20, // 9000 * 20 = 180000
        //     'created_at' => $apr15,
        //     'updated_at' => $apr15,
        // ]);
        // TransactionWaste::create([
        //     'transaction_id' => $sellApr15->id,
        //     'waste_id' => $kaleng->id,
        //     'qty_in_kg' => 20,
        //     'sub_total_price' => $hargaKaleng->selling_per_kg * 20, // 180000
        //     'created_at' => $apr15,
        //     'updated_at' => $apr15,
        // ]);
    }
}
