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
                'name' => 'Nicolas Noel Christianto',
                'phone' => '09782316893',
                'email' => '',
                'address' => 'Jl. Kediri Ke Dara',
                'descriptions' => '',
            ],
            [
                'name' => 'Rizfi Ferdiansyah',
                'phone' => '08582316111',
                'email' => 'wiwi@gmail.com',
                'address' => 'Jl. Anomali sahur',
                'descriptions' => '',
            ],
            [
                'name' => 'Abram Widi Firmanto',
                'phone' => '08582316111',
                'email' => 'kan@gmail.com',
                'address' => 'Jl. Anomali Lirili',
                'descriptions' => '',
            ],
            [
                'name' => 'Cakra Kusuma Erlangga Ramdani',
                'phone' => '08582316112',
                'email' => 'kan2@gmail.com',
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
