<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('orders_id')
                ->unique()
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->enum('metode_pembayaran',['cash','qris']);

            $table->enum('status_pembayaran',[
                'pending','paid','failed','expired'
            ])->default('pending');

            $table->decimal('jumlah_bayar',12,2);
            $table->timestamp('waktu_bayar')->nullable();
            $table->string('reference_payment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};