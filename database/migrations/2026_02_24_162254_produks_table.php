<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');

            // RELASI KE PENJUAL
            $table->foreignId('penjual_id')
                  ->constrained('penjuals', 'penjual_id')
                  ->cascadeOnDelete();

            $table->string('product_code');
            $table->string('product_name');

            $table->foreignId('category_id')
                  ->constrained('categories', 'category_id');

            $table->decimal('product_price', 10, 2);
            $table->integer('product_stock');
            $table->string('product_image');
            $table->text('product_description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};