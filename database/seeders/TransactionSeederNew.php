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
         * Transaksi & Detail Agustus
         */
        $tahun = 2025;
        $bulan = 8;

        $tanggal = Carbon::create($tahun, $bulan, 2, 10, 0);
        $sampah = $kaleng;
        $customer = $abram;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah, $customer);
        $qty = 4;

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




        /**
         * Transaksi & Detail September
         */
        $tahun = 2025;
        $bulan = 9;

        $tanggal = Carbon::create($tahun, $bulan, 1, 10, 0);
        $sampah = $botolPet;
        $customer = $noel;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah, $customer);
        $qty = 10;

        $sampah2 = $buku;
        $harga2 = $this->getPrice($tipe, $sampah2, $customer);
        $qty2 = 5;

        $transaksi = Transaction::create([
            'type' => $tipe,
            'status' => $statusTransaksi,
            'payment_status' => $statusPembayaran,
            'total_price' => $harga * $qty + $harga2 * $qty2,
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
        TransactionWaste::create([
            'transaction_id' => $transaksi->id,
            'waste_id' => $sampah2->id,
            'qty_in_kg' => $qty2,
            'unit_price' => $harga2,
            'sub_total_price' => $qty2 * $harga2,
            'created_at' => $tanggal,
            'updated_at' => $tanggal
        ]);

        $tanggal = Carbon::create($tahun, $bulan, 2, 10, 0);
        $sampah = $kaleng;
        $customer = $abram;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $tipe = TransactionType::PURCHASE;
        $harga = $this->getPrice($tipe, $sampah, $customer);
        $qty = 4;

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

        $tanggal = Carbon::create($tahun, $bulan, 3, 10, 0);
        $sampah = $kaleng;
        $customer = $noel;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $tipe = TransactionType::SELL;
        $harga = $this->getPrice($tipe, $sampah, $customer);
        $qty = 4;

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


        // ----------------------------------------------------
        $tanggal = Carbon::create($tahun, $bulan, 8, 10, 0);
        $sampah = $kantong;
        $customer = $abram;
        $tipe = TransactionType::PURCHASE;
        $qty = 5;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $harga = $this->getPrice($tipe, $sampah, $customer);

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

        $tanggal = Carbon::create($tahun, $bulan, 9, 10, 0);
        $sampah = $botolKaca;
        $customer = $cakra;
        $tipe = TransactionType::PURCHASE;
        $qty = 10;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $harga = $this->getPrice($tipe, $sampah, $customer);

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

        $tanggal = Carbon::create($tahun, $bulan, 10, 10, 0);
        $tipe = TransactionType::SELL;
        $customer = $noel;
        $sampah = $botolPet;
        $qty = 10;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $harga = $this->getPrice($tipe, $sampah, $customer);

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

        // ----------------------------------------------------
        $tanggal = Carbon::create($tahun, $bulan, 15, 10, 0);
        $tipe = TransactionType::SELL;
        $customer = $noel;
        $sampah = $buku;
        $qty = 5;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $harga = $this->getPrice($tipe, $sampah, $customer);

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

        $tanggal = Carbon::create($tahun, $bulan, 16, 10, 0);
        $tipe = TransactionType::SELL;
        $customer = $cakra;
        $sampah = $kantong;
        $qty = 5;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $harga = $this->getPrice($tipe, $sampah, $customer);

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

        // ----------------------------------------------------
        $tanggal = Carbon::create($tahun, $bulan, 22, 10, 0);
        $tipe = TransactionType::SELL;
        $customer = $noel;
        $sampah = $botolKaca;
        $qty = 4.5;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $harga = $this->getPrice($tipe, $sampah, $customer);

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

        $tanggal = Carbon::create($tahun, $bulan, 23, 10, 0);
        $tipe = TransactionType::SELL;
        $customer = $abram;
        $sampah = $botolKaca;
        $qty = 5.5;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $harga = $this->getPrice($tipe, $sampah, $customer);

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

        $tanggal = Carbon::create($tahun, $bulan, 24, 10, 0);
        $tipe = TransactionType::PURCHASE;
        $customer = $abram;
        $sampah = $kardus;
        $qty = 8;
        $statusTransaksi = TransactionStatus::COMPLETE;
        $statusPembayaran = PaymentStatus::PAID;
        $harga = $this->getPrice($tipe, $sampah, $customer);

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
