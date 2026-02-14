<?php

namespace Database\Seeders;

use App\Models\CutiRecord;
use App\Models\HariLibur;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== 1. KETUA PN NATUNA (Pejabat Berwenang) =====
        $ketua = User::create([
            'name' => 'Dr. Ahmad Fauzi, S.H., M.H.',
            'nip' => '197501152000031001',
            'password' => Hash::make('ketua123'),
            'role' => 'ketua',
            'leave_balance' => 12,
            'jabatan' => 'Ketua Pengadilan',
            'golongan_ruang' => 'IV/b',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2000-03-01',
            'status_pegawai' => 'hakim',
            'jenis_kelamin' => 'L',
            'jumlah_anak' => 3,
            'lokasi_terpencil' => true,
        ]);

        // ===== 2. PANITERA (Atasan Langsung) =====
        $panitera = User::create([
            'name' => 'Hendra Wijaya, S.H., M.H.',
            'nip' => '198003202005011003',
            'password' => Hash::make('atasan123'),
            'role' => 'atasan',
            'leave_balance' => 12,
            'jabatan' => 'Panitera',
            'golongan_ruang' => 'IV/a',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2005-01-01',
            'status_pegawai' => 'aparatur',
            'jenis_kelamin' => 'L',
            'jumlah_anak' => 2,
            'lokasi_terpencil' => true,
            'atasan_id' => $ketua->id,
        ]);

        // ===== 3. SEKRETARIS (Atasan Langsung) =====
        $sekretaris = User::create([
            'name' => 'Dewi Kartika, S.H.',
            'nip' => '198507102008012004',
            'password' => Hash::make('atasan123'),
            'role' => 'atasan',
            'leave_balance' => 10,
            'jabatan' => 'Sekretaris',
            'golongan_ruang' => 'III/d',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2008-01-01',
            'status_pegawai' => 'aparatur',
            'jenis_kelamin' => 'P',
            'jumlah_anak' => 1,
            'lokasi_terpencil' => true,
            'atasan_id' => $ketua->id,
        ]);

        // ===== 4. ADMIN (Pengelola Kepegawaian) =====
        User::create([
            'name' => 'Rizki Amalia, S.E.',
            'nip' => '199201012015012005',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'leave_balance' => 12,
            'jabatan' => 'Kasubag Kepegawaian',
            'golongan_ruang' => 'III/b',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2015-01-01',
            'status_pegawai' => 'aparatur',
            'jenis_kelamin' => 'P',
            'jumlah_anak' => 0,
            'lokasi_terpencil' => true,
            'atasan_id' => $sekretaris->id,
        ]);

        // ===== 5. HAKIM =====
        User::create([
            'name' => 'Muhammad Irfan, S.H., M.H.',
            'nip' => '199005152018011006',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'leave_balance' => 12,
            'jabatan' => 'Hakim',
            'golongan_ruang' => 'III/c',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2018-01-01',
            'status_pegawai' => 'hakim',
            'jenis_kelamin' => 'L',
            'jumlah_anak' => 1,
            'lokasi_terpencil' => true,
            'atasan_id' => $ketua->id,
        ]);

        // ===== 6. HAKIM 2 =====
        User::create([
            'name' => 'Sari Indah Permata, S.H.',
            'nip' => '199203102019022007',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'leave_balance' => 8,
            'jabatan' => 'Hakim',
            'golongan_ruang' => 'III/b',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2019-02-01',
            'status_pegawai' => 'hakim',
            'jenis_kelamin' => 'P',
            'jumlah_anak' => 0,
            'lokasi_terpencil' => true,
            'atasan_id' => $ketua->id,
        ]);

        // ===== 7. PANITERA PENGGANTI =====
        User::create([
            'name' => 'Budi Santoso, S.H.',
            'nip' => '199205152021011002',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'leave_balance' => 12,
            'jabatan' => 'Panitera Pengganti',
            'golongan_ruang' => 'III/a',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2021-01-01',
            'status_pegawai' => 'aparatur',
            'jenis_kelamin' => 'L',
            'jumlah_anak' => 2,
            'lokasi_terpencil' => true,
            'atasan_id' => $panitera->id,
        ]);

        // ===== 8. JURUSITA =====
        User::create([
            'name' => 'Siti Rahayu',
            'nip' => '199308202022012003',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'leave_balance' => 12,
            'jabatan' => 'Jurusita',
            'golongan_ruang' => 'II/d',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2022-01-01',
            'status_pegawai' => 'aparatur',
            'jenis_kelamin' => 'P',
            'jumlah_anak' => 1,
            'lokasi_terpencil' => true,
            'atasan_id' => $panitera->id,
        ]);

        // ===== 9. STAF TU =====
        User::create([
            'name' => 'Andi Pratama',
            'nip' => '199506012023011009',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'leave_balance' => 12,
            'jabatan' => 'Staf Tata Usaha',
            'golongan_ruang' => 'II/c',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2023-01-01',
            'status_pegawai' => 'aparatur',
            'jenis_kelamin' => 'L',
            'jumlah_anak' => 0,
            'lokasi_terpencil' => true,
            'atasan_id' => $sekretaris->id,
        ]);

        // ===== 10. STAF KEUANGAN =====
        User::create([
            'name' => 'Nur Hidayah, S.E.',
            'nip' => '199407152020012010',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'leave_balance' => 10,
            'jabatan' => 'Staf Keuangan',
            'golongan_ruang' => 'III/a',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2020-01-01',
            'status_pegawai' => 'aparatur',
            'jenis_kelamin' => 'P',
            'jumlah_anak' => 3,
            'lokasi_terpencil' => true,
            'atasan_id' => $sekretaris->id,
        ]);

        // ===== 11. CPNS BARU =====
        User::create([
            'name' => 'Fajar Ramadhan',
            'nip' => '200001012025011011',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'leave_balance' => 0,
            'jabatan' => 'CPNS Umum',
            'golongan_ruang' => 'II/a',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2025-01-01',
            'status_pegawai' => 'cpns',
            'jenis_kelamin' => 'L',
            'jumlah_anak' => 0,
            'lokasi_terpencil' => true,
            'atasan_id' => $sekretaris->id,
        ]);

        // ===== 12. CAKIM =====
        User::create([
            'name' => 'Putri Anggraini, S.H.',
            'nip' => '199812012024012012',
            'password' => Hash::make('pegawai123'),
            'role' => 'pegawai',
            'leave_balance' => 12,
            'jabatan' => 'Calon Hakim',
            'golongan_ruang' => 'III/a',
            'unit_kerja' => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2024-01-01',
            'status_pegawai' => 'cakim',
            'jenis_kelamin' => 'P',
            'jumlah_anak' => 0,
            'lokasi_terpencil' => true,
            'atasan_id' => $ketua->id,
        ]);

        // ===== HARI LIBUR NASIONAL 2026 =====
        $hariLibur2026 = [
            ['tanggal' => '2026-01-01', 'keterangan' => 'Tahun Baru Masehi'],
            ['tanggal' => '2026-01-29', 'keterangan' => 'Tahun Baru Imlek'],
            ['tanggal' => '2026-02-17', 'keterangan' => 'Isra Miraj Nabi Muhammad SAW'],
            ['tanggal' => '2026-03-20', 'keterangan' => 'Hari Raya Nyepi'],
            ['tanggal' => '2026-03-22', 'keterangan' => 'Nuzulul Quran (perkiraan)'],
            ['tanggal' => '2026-04-03', 'keterangan' => 'Wafat Isa Al-Masih'],
            ['tanggal' => '2026-04-20', 'keterangan' => 'Hari Raya Idul Fitri 1 Syawal'],
            ['tanggal' => '2026-04-21', 'keterangan' => 'Hari Raya Idul Fitri 2 Syawal'],
            ['tanggal' => '2026-05-01', 'keterangan' => 'Hari Buruh Internasional'],
            ['tanggal' => '2026-05-14', 'keterangan' => 'Kenaikan Isa Al-Masih'],
            ['tanggal' => '2026-05-26', 'keterangan' => 'Hari Raya Waisak'],
            ['tanggal' => '2026-06-01', 'keterangan' => 'Hari Lahir Pancasila'],
            ['tanggal' => '2026-06-27', 'keterangan' => 'Hari Raya Idul Adha'],
            ['tanggal' => '2026-07-18', 'keterangan' => 'Tahun Baru Islam 1448H'],
            ['tanggal' => '2026-08-17', 'keterangan' => 'Hari Kemerdekaan RI'],
            ['tanggal' => '2026-09-27', 'keterangan' => 'Maulid Nabi Muhammad SAW'],
            ['tanggal' => '2026-12-25', 'keterangan' => 'Hari Natal'],
        ];

        foreach ($hariLibur2026 as $hl) {
            HariLibur::updateOrCreate(
                ['tanggal' => $hl['tanggal']],
                [
                    'keterangan' => $hl['keterangan'],
                    'tahun' => 2026,
                    'is_cuti_bersama' => false,
                ]
            );
        }

        // Cuti bersama 2026 (contoh)
        $cutiBersama = [
            ['tanggal' => '2026-04-17', 'keterangan' => 'Cuti Bersama Idul Fitri'],
            ['tanggal' => '2026-04-22', 'keterangan' => 'Cuti Bersama Idul Fitri'],
            ['tanggal' => '2026-04-23', 'keterangan' => 'Cuti Bersama Idul Fitri'],
            ['tanggal' => '2026-12-24', 'keterangan' => 'Cuti Bersama Natal'],
        ];

        foreach ($cutiBersama as $cb) {
            HariLibur::updateOrCreate(
                ['tanggal' => $cb['tanggal']],
                [
                    'keterangan' => $cb['keterangan'],
                    'tahun' => 2026,
                    'is_cuti_bersama' => true,
                ]
            );
        }

        // ===== CUTI RECORDS (contoh riwayat tahun lalu) =====
        // Budi Santoso - punya sisa 4 hari dari tahun lalu
        CutiRecord::create([
            'user_id' => 7, // Budi
            'tahun' => 2025,
            'hak_cuti' => 12,
            'cuti_diambil' => 8,
            'sisa_cuti' => 4,
            'carry_over' => 0,
            'tambahan_terpencil' => 12,
        ]);
    }
}
