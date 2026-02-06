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
        Schema::create('pengeluaran_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_id')
                ->constrained('penerimaan_notices')
                ->cascadeOnDelete();

            $table->date('tanggal');
            $table->integer('jumlah_total');
            $table->unsignedBigInteger('lokasi_id');
            $table->unsignedBigInteger('created_by'); // kasir
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_notices');
    }
};
