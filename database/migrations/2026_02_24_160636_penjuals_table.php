<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjuals', function (Blueprint $table) {
            $table->id('penjual_id');
            $table->string('penjual_fullname');
            $table->string('penjual_notenant')->unique();
            $table->string('penjual_tenantname')->unique();
            $table->string('foto_tenant')->nullable();
            $table->string('penjual_nohp');
            $table->enum('penjual_gender', ['Laki-laki', 'Perempuan']);
            $table->enum('penjual_status', ['aktif', 'nonaktif'])->default('aktif');
            $table->string('penjual_username')->unique();
            $table->string('penjual_password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjuals');
    }
};