<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'owner',
            'role' => UserRole::OWNER,
            'email' => 'owner@gmail.com',
            'password' => Hash::make('12345678')
        ]);
        DB::table('users')->insert([
            'name' => 'admin',
            'role' => UserRole::ADMIN,
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678')
        ]);
        DB::table('users')->insert([
            'name' => 'sorter',
            'role' => UserRole::SORTER,
            'email' => 'sorter@gmail.com',
            'password' => Hash::make('12345678')
        ]);
    }
}
