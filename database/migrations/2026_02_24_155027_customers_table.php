<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id');
            $table->string('customer_username')->unique();
            $table->string('customer_password');
            $table->string('customer_fullname');
            $table->string('customer_email')->unique();
            $table->date('customer_dob')->nullable();
            $table->enum('customer_gender', ['Laki-laki', 'Perempuan',]);
            $table->string('customer_faculty');
             $table->enum('customer_status', ['Mahasiswa', 'Dosen', 'Tendik']);
            $table->string('customer_contact');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};