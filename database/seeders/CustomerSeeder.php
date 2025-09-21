<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perorangan = CustomerCategory::whereLike('name', 'perorangan')->first();
        $pabrik = CustomerCategory::whereLike('name', 'pabrik')->first();
        $pengepul = CustomerCategory::whereLike('name', '%pengepul%')->first();

        $customers = [
            [
                'name' => 'Nicolas Noel Christianto',
                'phone' => '09782316893',
                'email' => '',
                'address' => 'Jl. Sulawesi',
                'descriptions' => '',
                'customer_category_id' => $perorangan->id
            ],
            [
                'name' => 'Rizfi Ferdiansyah',
                'phone' => '08582316111',
                'email' => 'wiwi@gmail.com',
                'address' => 'Jl. Jemursari',
                'descriptions' => '',
                'customer_category_id' => $pabrik->id
            ],
            [
                'name' => 'Abram Widi Firmanto',
                'phone' => '08582316111',
                'email' => 'kan@gmail.com',
                'address' => 'Jl. Kediri',
                'descriptions' => '',
                'customer_category_id' => $pengepul->id
            ],
            [
                'name' => 'Cakra Kusuma Erlangga Ramdani',
                'phone' => '08582316112',
                'email' => 'kan2@gmail.com',
                'address' => 'Jl. Kedung Cowek',
                'descriptions' => '',
                'customer_category_id' => $perorangan->id
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create([
                'name' => $customer['name'],
                'phone' => $customer['phone'],
                'email' => $customer['email'],
                'address' => $customer['address'],
                'descriptions' => $customer['descriptions'],
                'customer_category_id' => $customer['customer_category_id']
            ]);
        }
    }
}
