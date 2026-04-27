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

            

        ]);
    }
}