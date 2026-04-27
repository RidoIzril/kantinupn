<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('penjuals_id')
                ->unique()
                ->constrained('penjuals')
                ->cascadeOnDelete();

            $table->string('tenant_name')->unique();
            $table->string('desk_tenant')->nullable();
            $table->string('no_tenant');
            $table->enum('kantin',['1','2']);
            $table->string('foto_tenant')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};