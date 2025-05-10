<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionWaste;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = [
            [
                'type' => 'purchase',
                'total_price' => 60000,
                'status' => 'new',
                'customer_id' => 1
            ],
            [
                'type' => 'purchase',
                'total_price' => 30000,
                'status' => 'new',
                'customer_id' => 1
            ],
            [
                'type' => 'sell',
                'total_price' => 22500,
                'status' => 'new',
                'customer_id' => 1
            ],
        ];

        foreach ($transactions as $transaction) {
            Transaction::create([
                'type' => $transaction['type'],
                'total_price' => $transaction['total_price'],
                'status' => $transaction['status'],
                'customer_id' => $transaction['customer_id']
            ]);
        }

        $transactionWastes = [
            [
                'waste_id' => 1,
                'transaction_id' => 1,
                'qty_in_kg' => 40,
                'sub_total_price' => 60000
            ],
            [
                'waste_id' => 1,
                'transaction_id' => 2,
                'qty_in_kg' => 20,
                'sub_total_price' => 30000
            ],
            [
                'waste_id' => 1,
                'transaction_id' => 3,
                'qty_in_kg' => 15,
                'sub_total_price' => 22500
            ],
        ];

        foreach ($transactionWastes as $transactionWaste) {
            TransactionWaste::create([
                'waste_id' => $transactionWaste['waste_id'],
                'transaction_id' => $transactionWaste['transaction_id'],
                'qty_in_kg' => $transactionWaste['qty_in_kg'],
                'sub_total_price' => $transactionWaste['sub_total_price']
            ]);
        }
    }
}
