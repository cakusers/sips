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

class TransactionSeederApril extends Seeder
{
    /**
     * Run the database seeds.
     */
    private function getPrice(TransactionType $type, Waste $waste): int
    {
        return $type === TransactionType::SELL ? $waste->latestPrice->selling_per_kg : $waste->latestPrice->purchase_per_kg;
    }

    public function run(): void
    {
        $botol = Waste::find(1);
        $kardus = Waste::find(2);
        $kaleng = Waste::find(3);
        $customer = Customer::all();

        /**
         * Minggu ke-4
         */
        $tanggal = Carbon::create(2025, 4, 21);
        $sampah = $kardus;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 20;

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

        $tanggal = Carbon::create(2025, 4, 21);
        $sampah = $kardus;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 20;

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

        $tanggal = Carbon::create(2025, 4, 22);
        $sampah = $botol;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 15;

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

        $tanggal = Carbon::create(2025, 4, 22);
        $sampah = $botol;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah);
        $qty = 15;

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
         * Minggu ke-5
         */

        $tanggal = Carbon::create(2025, 4, 28);
        $sampah = $kardus;
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

        $tanggal = Carbon::create(2025, 4, 28);
        $sampah = $kardus;
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

        $tanggal = Carbon::create(2025, 4, 29);
        $sampah = $kaleng;
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

        $tanggal = Carbon::create(2025, 4, 29);
        $sampah = $kaleng;
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
    }
}
