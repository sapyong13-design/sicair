<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Jenis cuti (6 jenis sesuai SE MA 13/2019)
            $table->string('type')->default('cuti_tahunan')->after('user_id');
            // cuti_tahunan, cuti_besar, cuti_sakit, cuti_melahirkan, cuti_alasan_penting, cuti_luar_tanggungan

            // Data tambahan pengajuan
            $table->string('alamat_cuti')->nullable()->after('reason');
            $table->string('telepon_cuti', 20)->nullable()->after('alamat_cuti');
            $table->string('alasan_cap')->nullable()->after('telepon_cuti'); // sub-alasan CAP
            $table->integer('kelahiran_ke')->nullable()->after('alasan_cap'); // untuk cuti melahirkan
            $table->string('dokumen_pendukung')->nullable()->after('kelahiran_ke'); // file path
            $table->integer('total_hari_kerja')->nullable()->after('dokumen_pendukung'); // hari kerja (bukan kalender)

            // Workflow approval berjenjang
            // Status: draft, diajukan, pertimbangan_atasan, disetujui, diubah, ditangguhkan, ditolak
            // (kolom 'status' sudah ada, kita ubah nilainya)

            // Level 1: Pertimbangan Atasan Langsung
            $table->foreignId('atasan_reviewer_id')->nullable()->after('admin_note')
                  ->constrained('users')->nullOnDelete();
            $table->string('pertimbangan_atasan')->nullable()->after('atasan_reviewer_id');
            // setuju, ubah, tangguhkan, tolak
            $table->text('catatan_atasan')->nullable()->after('pertimbangan_atasan');
            $table->timestamp('reviewed_at')->nullable()->after('catatan_atasan');

            // Level 2: Keputusan Pejabat Berwenang (Ketua PN)
            $table->foreignId('pejabat_id')->nullable()->after('reviewed_at')
                  ->constrained('users')->nullOnDelete();
            $table->string('keputusan_pejabat')->nullable()->after('pejabat_id');
            // setuju, ubah, tangguhkan, tolak
            $table->text('catatan_pejabat')->nullable()->after('keputusan_pejabat');
            $table->timestamp('decided_at')->nullable()->after('catatan_pejabat');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['atasan_reviewer_id']);
            $table->dropForeign(['pejabat_id']);
            $table->dropColumn([
                'type', 'alamat_cuti', 'telepon_cuti', 'alasan_cap',
                'kelahiran_ke', 'dokumen_pendukung', 'total_hari_kerja',
                'atasan_reviewer_id', 'pertimbangan_atasan', 'catatan_atasan', 'reviewed_at',
                'pejabat_id', 'keputusan_pejabat', 'catatan_pejabat', 'decided_at',
            ]);
        });
    }
};
