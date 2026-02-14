<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('tahun');
            $table->integer('hak_cuti')->default(12);
            $table->integer('cuti_diambil')->default(0);
            $table->integer('sisa_cuti')->default(12);
            $table->integer('carry_over')->default(0); // dari tahun lalu (max 6)
            $table->integer('tambahan_terpencil')->default(0); // +12 jika lokasi terpencil
            $table->boolean('ditangguhkan')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_records');
    }
};
