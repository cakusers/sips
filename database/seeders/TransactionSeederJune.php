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

class TransactionSeederJune extends Seeder
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
         * Minggu ke-1
         */
        $tanggal = Carbon::create(2025, 6, 1);
        $sampah = $kardus;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 12;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 6, 2);
        $sampah = $kardus;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 12;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 6, 3);
        $sampah = $botol;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 5;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
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
        $tanggal = Carbon::create(2025, 6, 9);
        $sampah = $botol;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 5;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 6, 10);
        $sampah = $botol;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 10;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 6, 11);
        $sampah = $kardus;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 10;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
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
        $tanggal = Carbon::create(2025, 6, 16);
        $sampah = $kardus;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 10;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 6, 17);
        $sampah = $botol;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 10;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 6, 18);
        $sampah = $kaleng;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 5;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create(2025, 6, 19);
        $sampah = $kaleng;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 5;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        /**
         * Minggu Ke-4
         */
        $tanggal = Carbon::create(2025, 6, 23);
        $sampah = $botol;
        $sampah2 = $kardus;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah);
        $harga2 = $this->getPrice($tipe, $sampah2);
        $qty = 8;
        $qty2 = 6;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'total_price' => $harga * $qty + $harga2 * $qty2,
            'customer_id' => $customer->random()->id,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah->id,
            'qty_in_kg' => $qty,
            'unit_price' => $harga,
            'sub_total_price' => $qty * $harga,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah2->id,
            'qty_in_kg' => $qty2,
            'unit_price' => $harga2,
            'sub_total_price' => $qty2 * $harga2,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);
    }

    private function getPrice(TransactionType $type, Waste $waste): int
    {
        return $type === TransactionType::SELL ? $waste->latestPrice->selling_per_kg : $waste->latestPrice->purchase_per_kg;
    }
}
