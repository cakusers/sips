<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Waste;
use App\Models\Customer;
use App\Models\Transaction;
use App\Enums\PaymentStatus;
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
        $botol = Waste::find(1);
        $kardus = Waste::find(2);
        $kaleng = Waste::find(3);
        $customer = Customer::all();

        /**
         * Tahun lalu
         */
        $tanggal = Carbon::create(2024, 5, 5);
        $sampah = $botol;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah); // 1500
        $qty = 8;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2024, 5, 6);
        $sampah = $botol;
        $tipe = TransactionType::PURCHASE;
        $harga = 1000; // 1000
        $qty = 8;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2024, 5, 7);
        $sampah = $botol;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah); // 2000
        $qty = 16;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);


        /**
         * Minggu ke-1
         */
        $tanggal = Carbon::create(2025, 5, 5);
        $sampah = $botol;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah); // 1500
        $qty = 8;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 5, 6);
        $sampah = $botol;
        $tipe = TransactionType::PURCHASE;
        $harga = 1000; // 1000
        $qty = 8;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 5, 7);
        $sampah = $botol;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah); // 2000
        $qty = 8;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);


        /**
         * Minggu ke-2
         */
        $tanggal = Carbon::create(2025, 5, 12);
        $sampah = $botol;
        $tipe = TransactionType::SELL;
        $harga = 2200; // 2200
        $qty = 8;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 5, 13);
        $sampah = $kardus;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah); // 800
        $qty = 10;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 5, 14);
        $sampah = $kardus;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah); // 1200
        $qty = 10;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        /**
         * Minggu Ke-3
         */
        $tanggal = Carbon::create(2025, 5, 19);
        $sampah = $kaleng;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah); // 1200
        $qty = 15;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 5, 20);
        $sampah = $botol;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah); // 1200
        $qty = 5;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 5, 21);
        $sampah = $kaleng;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah); // 1200
        $qty = 15;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 5, 21);
        $sampah = $botol;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah); // 1200
        $qty = 5;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        /**
         * Minggu ke-4
         */
        $tanggal = Carbon::create(2025, 5, 26);
        $sampah = $kaleng;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah); // 1200
        $qty = 10;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 5, 27);
        $sampah = $kaleng;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah); // 1200
        $qty = 10;

        $transaksiMei1 = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksiMei1->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
    }

    private function getPrice(TransactionType $type, Waste $waste): int
    {
        return $type === TransactionType::SELL ? $waste->latestPrice->selling_per_kg : $waste->latestPrice->purchase_per_kg;
    }
}
