<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('carts_id')
                ->constrained('carts')
                ->cascadeOnDelete();

            $table->foreignId('produks_id')
                ->constrained('produks');

            $table->foreignId('variants_id')
                ->nullable()
                ->constrained('variants');

            
            $table->integer('jumlah');
            $table->decimal('harga_per_item',12,2);
            $table->decimal('subtotal',12,2);

            $table->timestamps();

            $table->unique(['carts_id','produks_id','variants_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};