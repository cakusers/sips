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
                'name' => 'Ironik',
                'phone' => '09782316893',
                'email' => '',
                'address' => 'Jl. Kediri Ke Dara',
                'descriptions' => '',
            ],
            [
                'name' => 'Kupo',
                'phone' => '08582316111',
                'email' => 'kupokupo@gmail.com',
                'address' => 'Jl. Anomali sahur',
                'descriptions' => '',
            ],
            [
                'name' => 'Sora',
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
