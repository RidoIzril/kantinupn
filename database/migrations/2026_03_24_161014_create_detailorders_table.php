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
                ->nullable()
                ->constrained('produks')
                ->nullOnDelete();
            // Jika varian optional, bisa nullable()
            $table->foreignId('variants_id')
                ->nullable() 
                ->constrained('variants')
                ->nullOnDelete();

            $table->string('catatan_menu')->nullable();  // custom note per item/menu (opsional)

            $table->integer('jumlah');
            $table->decimal('total_harga',12,2);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detailorders');
    }
};