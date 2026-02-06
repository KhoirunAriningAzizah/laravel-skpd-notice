<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengeluaran_batal_rusak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengeluaran_id')
                ->constrained('pengeluaran_notices')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('nomor_notice');
            $table->timestamps();

            $table->unique(['pengeluaran_id', 'nomor_notice']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_batal_rusak');
    }
};
