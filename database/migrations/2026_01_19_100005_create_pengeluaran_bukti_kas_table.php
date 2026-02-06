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
        Schema::create('pengeluaran_bukti_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengeluaran_id')
                ->constrained('pengeluaran_notices')
                ->cascadeOnDelete();

            $table->integer('lokal')->default(0);
            $table->integer('link')->default(0);
            $table->integer('jumlah');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_bukti_kas');
    }
};
