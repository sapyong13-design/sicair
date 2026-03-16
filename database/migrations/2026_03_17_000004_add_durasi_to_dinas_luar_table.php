<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add durasi (number of days) column to dinas_luar table.
     * Referenced by LaporanBulananController and KalenderController via $dl->durasi.
     * Stored as an integer so it can be set explicitly or computed on insert.
     */
    public function up(): void
    {
        Schema::table('dinas_luar', function (Blueprint $table) {
            $table->unsignedInteger('durasi')->nullable()->after('end_date')
                  ->comment('Durasi dinas luar dalam hari kerja');
        });
    }

    public function down(): void
    {
        Schema::table('dinas_luar', function (Blueprint $table) {
            $table->dropColumn('durasi');
        });
    }
};
