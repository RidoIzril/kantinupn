<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([

            // ================= SUPERADMIN =================
            [
                'username' => 'superadmin',
                'password' => Hash::make('123456'),
                'role' => 'superadmin',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // ================= PENJUAL =================
            [
                'username' => 'penjual1',
                'password' => Hash::make('123456'),
                'role' => 'penjual',
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'username' => 'penjual2',
                'password' => Hash::make('123456'),
                'role' => 'penjual',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // ================= CUSTOMER =================
            [
                'username' => 'customer1',
                'password' => Hash::make('123456'),
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'username' => 'customer2',
                'password' => Hash::make('123456'),
                'role' => 'customer',
                'created_at' => now(),
                'updated_at' => now()
            ],

        ]);
    }
}