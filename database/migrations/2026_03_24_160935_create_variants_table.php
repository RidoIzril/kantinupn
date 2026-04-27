<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('produks_id')
                ->constrained('produks')
                ->cascadeOnDelete();

            $table->string('nama_variant');
            $table->decimal('harga_variant',12,2)->default(0);
            $table->boolean('status_variant')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};