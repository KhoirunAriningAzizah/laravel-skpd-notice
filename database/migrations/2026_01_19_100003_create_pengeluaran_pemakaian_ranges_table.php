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
        Schema::create('pengeluaran_pemakaian_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengeluaran_id')
                ->constrained('pengeluaran_notices')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('nomor_awal');
            $table->unsignedBigInteger('nomor_akhir');
            $table->integer('jumlah');
            $table->timestamps();

            $table->index(['nomor_awal', 'nomor_akhir']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_pemakaian_ranges');
    }
};
