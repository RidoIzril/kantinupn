<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id('delivery_id');

            $table->foreignId('orders_id')
                ->unique()
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->text('alamat');
            $table->text('catatan')->nullable();
            $table->enum('status_delivery', ['pending','dalam perjalanan','selesai'])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};