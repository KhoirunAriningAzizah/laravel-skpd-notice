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
        Schema::table('saldo_notices', function (Blueprint $table) {
            $table->foreignId('pengeluaran_id')
                ->nullable()
                ->after('penerimaan_id')
                ->constrained('pengeluaran_notices')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saldo_notices', function (Blueprint $table) {
            $table->dropForeign(['pengeluaran_id']);
            $table->dropColumn('pengeluaran_id');
        });
    }
};
