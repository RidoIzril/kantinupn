<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detailorders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('orders_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('produks_id')
                ->constrained('produks');

            $table->integer('jumlah');
            $table->decimal('total_harga',12,2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detailorders');
    }
};