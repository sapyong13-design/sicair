<?php

namespace Database\Seeders;

use App\Models\CutiRecord;
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
            'role' => 'panitera',
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
            'role' => 'sekretaris',
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

        // ===== HARI LIBUR NASIONAL 2025 & 2026 =====
        $this->call(HariLiburSeeder::class);

        // ===== CUTI RECORDS 2025 (riwayat tahun lalu, untuk carry-over) =====
        $cutiRecords2025 = [
            ['user_id' => $ketua->id,      'cuti_diambil' => 5,  'sisa_cuti' => 7,  'carry_over' => 0, 'tambahan_terpencil' => 0],
            ['user_id' => $panitera->id,    'cuti_diambil' => 6,  'sisa_cuti' => 6,  'carry_over' => 0, 'tambahan_terpencil' => 0],
            ['user_id' => $sekretaris->id,  'cuti_diambil' => 4,  'sisa_cuti' => 8,  'carry_over' => 0, 'tambahan_terpencil' => 0],
            ['user_id' => 4,  'cuti_diambil' => 3,  'sisa_cuti' => 9,  'carry_over' => 0, 'tambahan_terpencil' => 0], // Admin
            ['user_id' => 5,  'cuti_diambil' => 7,  'sisa_cuti' => 5,  'carry_over' => 0, 'tambahan_terpencil' => 0], // Hakim 1
            ['user_id' => 6,  'cuti_diambil' => 2,  'sisa_cuti' => 10, 'carry_over' => 0, 'tambahan_terpencil' => 0], // Hakim 2
            ['user_id' => 7,  'cuti_diambil' => 8,  'sisa_cuti' => 4,  'carry_over' => 0, 'tambahan_terpencil' => 0], // Panitera Pengganti
            ['user_id' => 8,  'cuti_diambil' => 10, 'sisa_cuti' => 2,  'carry_over' => 0, 'tambahan_terpencil' => 0], // Jurusita
            ['user_id' => 9,  'cuti_diambil' => 1,  'sisa_cuti' => 11, 'carry_over' => 0, 'tambahan_terpencil' => 0], // Staf TU
            ['user_id' => 10, 'cuti_diambil' => 6,  'sisa_cuti' => 6,  'carry_over' => 0, 'tambahan_terpencil' => 0], // Staf Keuangan
            ['user_id' => 12, 'cuti_diambil' => 3,  'sisa_cuti' => 9,  'carry_over' => 0, 'tambahan_terpencil' => 0], // Cakim
        ];

        foreach ($cutiRecords2025 as $rec) {
            CutiRecord::create(array_merge($rec, ['tahun' => 2025, 'hak_cuti' => 12]));
        }

        // ===== CUTI RECORDS 2026 (tahun berjalan) =====
        // carry_over = min(sisa_cuti_2025, 6) sesuai SE MA 13/2019
        $cutiRecords2026 = [
            ['user_id' => $ketua->id,      'cuti_diambil' => 0, 'carry_over' => 6, 'tambahan_terpencil' => 0], // sisa 2025: 7 → carry 6
            ['user_id' => $panitera->id,    'cuti_diambil' => 0, 'carry_over' => 6, 'tambahan_terpencil' => 0], // sisa 2025: 6 → carry 6
            ['user_id' => $sekretaris->id,  'cuti_diambil' => 0, 'carry_over' => 6, 'tambahan_terpencil' => 0], // sisa 2025: 8 → carry 6
            ['user_id' => 4,  'cuti_diambil' => 0, 'carry_over' => 6, 'tambahan_terpencil' => 0], // Admin, sisa 2025: 9 → carry 6
            ['user_id' => 5,  'cuti_diambil' => 0, 'carry_over' => 5, 'tambahan_terpencil' => 0], // Hakim 1, sisa 2025: 5 → carry 5
            ['user_id' => 6,  'cuti_diambil' => 0, 'carry_over' => 6, 'tambahan_terpencil' => 0], // Hakim 2, sisa 2025: 10 → carry 6
            ['user_id' => 7,  'cuti_diambil' => 0, 'carry_over' => 4, 'tambahan_terpencil' => 0], // PP, sisa 2025: 4 → carry 4
            ['user_id' => 8,  'cuti_diambil' => 0, 'carry_over' => 2, 'tambahan_terpencil' => 0], // Jurusita, sisa 2025: 2 → carry 2
            ['user_id' => 9,  'cuti_diambil' => 0, 'carry_over' => 6, 'tambahan_terpencil' => 0], // Staf TU, sisa 2025: 11 → carry 6
            ['user_id' => 10, 'cuti_diambil' => 0, 'carry_over' => 6, 'tambahan_terpencil' => 0], // Staf Keuangan, sisa 2025: 6 → carry 6
            ['user_id' => 12, 'cuti_diambil' => 0, 'carry_over' => 6, 'tambahan_terpencil' => 0], // Cakim, sisa 2025: 9 → carry 6
        ];

        foreach ($cutiRecords2026 as $rec) {
            $hakCuti = 12;
            $sisa = $hakCuti + $rec['carry_over'] + $rec['tambahan_terpencil'] - $rec['cuti_diambil'];
            CutiRecord::create(array_merge($rec, [
                'tahun' => 2026,
                'hak_cuti' => $hakCuti,
                'sisa_cuti' => $sisa,
            ]));
        }
    }
}
