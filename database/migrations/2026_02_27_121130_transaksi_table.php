<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transaction_id');

            // RELASI KE PENJUAL
            $table->foreignId('penjual_id')
                  ->constrained('penjuals', 'penjual_id')
                  ->cascadeOnDelete();

            $table->foreignId('order_id')
                  ->constrained('orders', 'order_id')
                  ->cascadeOnDelete();

            $table->foreignId('payment_id')
                  ->constrained('payments', 'payment_id')
                  ->cascadeOnDelete();

            $table->timestamp('transaction_date');
            $table->enum('status', ['pending', 'success', 'failed']);
            $table->text('delivery_address');
            $table->enum('delivery_status', ['processing', 'shipped', 'delivered']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};