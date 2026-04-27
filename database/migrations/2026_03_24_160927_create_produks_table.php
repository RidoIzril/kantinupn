<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kategoris_id')->constrained('kategoris');
            $table->foreignId('tenants_id')->constrained('tenants')->cascadeOnDelete();

            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->integer('stok');
            $table->decimal('harga',12,2);
            $table->string('foto_produk')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};