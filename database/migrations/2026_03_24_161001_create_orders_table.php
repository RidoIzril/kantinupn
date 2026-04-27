<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customers_id')
                ->constrained('customers');

            $table->dateTime('order_tanggal');
            
            $table->enum('order_type',['Dine In','Takeaway','Delivery']);

            $table->string('nomor_meja')->nullable();
            
            $table->integer('total_produk');
            $table->decimal('total_harga',12,2);

            $table->enum('order_status',[
                'pending','diproses','siap','selesai','batal'
            ])->default('pending');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};