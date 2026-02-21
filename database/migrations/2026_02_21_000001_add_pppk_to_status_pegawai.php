<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Tambah status PPPK (Pegawai Pemerintah dengan Perjanjian Kerja).
     * Alur persetujuan: PPPK → Sekretaris (Level 1) → Ketua PN Natuna (Level 2)
     *
     * Kolom status_pegawai bertipe string (bukan enum), sehingga tidak perlu
     * ALTER TABLE. Migration ini hanya sebagai dokumentasi perubahan bisnis.
     *
     * Status valid: hakim | aparatur | cpns | cakim | pppk
     */
    public function up(): void
    {
        // Kolom status_pegawai sudah bertipe string — langsung menerima nilai 'pppk'
        // tanpa perubahan skema. Validasi diperbarui di PegawaiController.
    }

    public function down(): void
    {
        // Tidak ada perubahan skema yang perlu di-rollback.
    }
};
