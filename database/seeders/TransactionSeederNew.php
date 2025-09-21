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
use App\Models\WastePrice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TransactionSeederNew extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Waste
         */
        $botolPet = Waste::whereLike('name', '%bersih%')->first();
        $kantong = Waste::whereLike('name', '%Kantong%')->first();
        $botolKaca = Waste::whereLike('name', '%kaca%')->first();
        $buku = Waste::whereLike('name', '%buku%')->first();
        $kardus = Waste::whereLike('name', 'Kardus Bekas')->first();
        $kaleng = Waste::whereLike('name', '%Kaleng%')->first();
        $botolktr = Waste::whereLike('name', '%kotor%')->first();

        /**
         * Customer
         */
        $noel = Customer::whereLike('name', '%noel%')->first();
        $rizfi = Customer::whereLike('name', '%rizfi%')->first();
        $abram = Customer::whereLike('name', '%abram%')->first();
        $cakra = Customer::whereLike('name', '%cakra%')->first();


        /**
         * Eksekusi
         */
        $tanggal = Carbon::create(2025, 9, 17, 6, 0);
        $sampah = $botolPet;
        $customer = $noel;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah, $customer);
        $qty = 10;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => $statusTransaksi,
            'payment_status' => $statusPembayaran,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->id,
            'customer_category_id' => $customer->customer_category_id,
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

        $tanggal = Carbon::create(2025, 9, 17, 6, 1);
        $sampah = $botolktr;
        $customer = $cakra;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah, $customer);
        $qty = 10;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => $statusTransaksi,
            'payment_status' => $statusPembayaran,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->id,
            'customer_category_id' => $customer->customer_category_id,
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

        $tanggal = Carbon::create(2025, 9, 17, 6, 2);
        $sampah = $kardus;
        $customer = $cakra;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah, $customer);
        $qty = 10;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => $statusTransaksi,
            'payment_status' => $statusPembayaran,
            'total_price' => $harga * $qty,
            'customer_id' => $customer->id,
            'customer_category_id' => $customer->customer_category_id,
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

    protected function getPrice(TransactionType $transactionType, Waste $waste, Customer $customer)
    {
        $wastePriceByCustomer = WastePrice::where('waste_id', $waste->id)->where('customer_category_id', $customer->customer_category_id)->first();

        if (!$wastePriceByCustomer) {
            return 0;
        }

        $price = $transactionType === TransactionType::SELL ? $wastePriceByCustomer->selling_per_kg : $wastePriceByCustomer->purchase_per_kg;
        return $price;
    }
}
