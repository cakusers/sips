<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Nowel',
                'phone' => '09782316893',
                'email' => '',
                'address' => 'Jl. Kediri Ke Dara',
                'descriptions' => '',
            ],
            [
                'name' => 'Wiwi',
                'phone' => '08582316111',
                'email' => 'wiwi@gmail.com',
                'address' => 'Jl. Anomali sahur',
                'descriptions' => '',
            ],
            [
                'name' => 'Abram',
                'phone' => '08582316111',
                'email' => 'kan@gmail.com',
                'address' => 'Jl. Anomali Lirili',
                'descriptions' => '',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create([
                'name' => $customer['name'],
                'phone' => $customer['phone'],
                'email' => $customer['email'],
                'address' => $customer['address'],
                'descriptions' => $customer['descriptions'],
            ]);
        }
    }
}
