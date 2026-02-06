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
        // Drop old foreign key from layanan table if exists
        if (Schema::hasColumn('layanan', 'lokasi_id')) {
            Schema::table('layanan', function (Blueprint $table) {
                $table->dropForeign(['lokasi_id']);
                $table->dropColumn('lokasi_id');
            });
        }

        // Add layanan_id to lokasi table
        Schema::table('lokasi', function (Blueprint $table) {
            $table->foreignId('layanan_id')->nullable()->after('nama')->constrained('layanan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove layanan_id from lokasi
        Schema::table('lokasi', function (Blueprint $table) {
            $table->dropForeign(['layanan_id']);
            $table->dropColumn('layanan_id');
        });

        // Restore lokasi_id to layanan
        Schema::table('layanan', function (Blueprint $table) {
            $table->foreignId('lokasi_id')->nullable()->after('id')->constrained('lokasi')->cascadeOnDelete();
        });
    }
};
