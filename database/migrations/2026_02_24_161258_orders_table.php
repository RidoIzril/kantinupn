<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');

            // CUSTOMER
            $table->foreignId('customer_id')
                  ->constrained('customers', 'customer_id')
                  ->cascadeOnDelete();

            // PENJUAL (PENTING untuk konsep 1 toko per order)
            $table->foreignId('penjual_id')
                  ->constrained('penjuals', 'penjual_id')
                  ->cascadeOnDelete();

            $table->timestamp('order_date');
            $table->integer('total_amount');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'paid', 'shipped', 'delivered', 'cancelled']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};