<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kolom pegawai sesuai SE MA 13/2019
            $table->string('jabatan')->nullable()->after('name');
            $table->string('golongan_ruang')->nullable()->after('jabatan');
            $table->string('unit_kerja')->default('Pengadilan Negeri Natuna')->after('golongan_ruang');
            $table->date('masa_kerja_mulai')->nullable()->after('unit_kerja');
            $table->string('status_pegawai')->default('aparatur')->after('masa_kerja_mulai'); // hakim, aparatur, cpns, cakim
            $table->string('jenis_kelamin', 1)->nullable()->after('status_pegawai'); // L atau P
            $table->integer('jumlah_anak')->default(0)->after('jenis_kelamin');
            $table->boolean('lokasi_terpencil')->default(false)->after('jumlah_anak');
            $table->foreignId('atasan_id')->nullable()->after('lokasi_terpencil')
                  ->constrained('users')->nullOnDelete();
            $table->string('telepon', 20)->nullable()->after('atasan_id');
            $table->text('alamat')->nullable()->after('telepon');
        });

        // Update role comment: sekarang 4 role
        // admin, ketua, atasan, pegawai
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['atasan_id']);
            $table->dropColumn([
                'jabatan', 'golongan_ruang', 'unit_kerja',
                'masa_kerja_mulai', 'status_pegawai', 'jenis_kelamin',
                'jumlah_anak', 'lokasi_terpencil', 'atasan_id',
                'telepon', 'alamat',
            ]);
        });
    }
};
