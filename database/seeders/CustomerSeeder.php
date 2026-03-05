<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customers;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        Customers::create([
            'customer_username' => 'rido',
            'customer_password' => Hash::make('12345'),
            'customer_fullname' => 'rido izril',
            'customer_email' => 'customer@kantin.com',
            'customer_dob' => '1990-01-01',
            'customer_gender' => 'Laki-laki',
            'customer_faculty' => 'Fakultas Ilmu Komputer',
            'customer_status' => 'Mahasiswa',
            'customer_contact' => '081234567890',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}