<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
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
        $customer = Customer::all();
        $botol = Waste::find(1);
        $kardus = Waste::find(2);
        $kaleng = Waste::find(3);

        $qty = 8;
        $mei1 = Carbon::create(2025, 5, 1);
        $transaction1Mei = Transaction::create([
            'type' => TransactionType::PURCHASE,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'customer_id' => $customer->random()->id,
            'total_price' => $qty * $botol->latestPrice->purchase_per_kg, // 8 * 1500
            'created_at' => $mei1,
            'updated_at' => $mei1
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaction1Mei->id,
            'waste_id' => $botol->id,
            'qty_in_kg' => $qty,
            'unit_price' => $botol->latestPrice->purchase_per_kg, // 1500
            'sub_total_price' => $qty * $botol->latestPrice->purchase_per_kg, // 8 * 1500
            'created_at' => $mei1,
            'updated_at' => $mei1
        ]);

        $mei2 = Carbon::create(2025, 5, 2);
        $hargaBeliBaru = 1000;
        $transaction2Mei = Transaction::create([
            'type' => TransactionType::PURCHASE,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'customer_id' => $customer->random()->id,
            'total_price' => $qty * $hargaBeliBaru, // 8 * 1000
            'created_at' => $mei1,
            'updated_at' => $mei2
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaction2Mei->id,
            'waste_id' => $botol->id,
            'qty_in_kg' => $qty,
            'unit_price' => $hargaBeliBaru, // 1000
            'sub_total_price' => $qty * $hargaBeliBaru, // 8 * 1000
            'created_at' => $mei2,
            'updated_at' => $mei2
        ]);

        $mei3 = Carbon::create(2025, 5, 3);
        $transaction1Mei = Transaction::create([
            'type' => TransactionType::SELL,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'customer_id' => $customer->random()->id,
            'total_price' => $qty * $botol->latestPrice->selling_per_kg, // 8 * 2000
            'created_at' => $mei3,
            'updated_at' => $mei3
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaction1Mei->id,
            'waste_id' => $botol->id,
            'qty_in_kg' => $qty,
            'unit_price' => $botol->latestPrice->selling_per_kg, // 2000
            'sub_total_price' => $qty * $botol->latestPrice->selling_per_kg, // 8 * 2000
            'created_at' => $mei3,
            'updated_at' => $mei3
        ]);

        $mei4 = Carbon::create(2025, 5, 4);
        $hargaJualBaru = 2200;
        $transaction1Mei = Transaction::create([
            'type' => TransactionType::SELL,
            'status' => TransactionStatus::COMPLETE,
            'payment_status' => PaymentStatus::PAID,
            'customer_id' => $customer->random()->id,
            'total_price' => $qty * $hargaJualBaru, // 8 * 2200
            'created_at' => $mei4,
            'updated_at' => $mei4
        ]);
        TransactionWaste::create([
            'transaction_id' => $transaction1Mei->id,
            'waste_id' => $botol->id,
            'qty_in_kg' => $qty,
            'unit_price' => $hargaJualBaru, // 2200
            'sub_total_price' => $qty * $hargaJualBaru, // 8 * 2200
            'created_at' => $mei4,
            'updated_at' => $mei4
        ]);
    }
}
