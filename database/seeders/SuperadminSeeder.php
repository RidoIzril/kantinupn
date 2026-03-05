<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Superadmin;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Superadmin::create([
            'username' => 'superadmin',
            'password' => Hash::make('12345'),
        ]);
    }
}
