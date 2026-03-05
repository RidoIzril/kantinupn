<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Penjual;

class PenjualSeeder extends Seeder
{
    public function run(): void
    {
        Penjual::updateOrCreate(
            ['penjual_username' => 'penjual1'],
            [
                'penjual_fullname'   => 'Budi Santoso',
                'penjual_notenant'   => 'T01',
                'penjual_tenantname' => 'Warung Budi',
                'foto_tenant'        => null,
                'penjual_nohp'       => '081234567890',
                'penjual_gender'     => 'Laki-laki',
                'penjual_status'     => 'aktif',
                'penjual_password'   => Hash::make('12345'),
            ]
        );
    }
}