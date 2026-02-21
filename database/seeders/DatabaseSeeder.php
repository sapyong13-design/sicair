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
        $pass      = Hash::make('123456');
        $passAdmin = Hash::make('admin123456');

        // =========================================================
        // GRUP 1 — PIMPINAN
        // =========================================================

        // No. 1 — Ketua Pengadilan Tingkat Pertama Klas II
        $ketua = User::create([
            'name'             => 'LODEWYK IVANDRIE SIMANJUNTAK, S.H., M.H.',
            'nip'              => '197511172001121003',
            'password'         => $pass,
            'role'             => 'ketua',
            'leave_balance'    => 12,
            'jabatan'          => 'Ketua Pengadilan Tingkat Pertama Klas II',
            'golongan_ruang'   => 'IV/b',
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2001-12-01',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
        ]);

        // No. 14 — Panitera Tingkat Pertama Klas II
        $panitera = User::create([
            'name'             => 'HADRY. B, S.H.',
            'nip'              => '197809302011011005',
            'password'         => $pass,
            'role'             => 'panitera',
            'leave_balance'    => 12,
            'jabatan'          => 'Panitera Tingkat Pertama Klas II',
            'golongan_ruang'   => 'III/d',
            'unit_kerja'       => 'Kepaniteraan Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2011-01-01',
            'status_pegawai'   => 'aparatur',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 15 — Sekretaris Tingkat Pertama Klas II
        $sekretaris = User::create([
            'name'             => 'MARIO TYSON NADAPDAP, S.E.',
            'nip'              => '199608172019031002',
            'password'         => $pass,
            'role'             => 'sekretaris',
            'leave_balance'    => 12,
            'jabatan'          => 'Sekretaris Tingkat Pertama Klas II',
            'golongan_ruang'   => 'III/c',
            'unit_kerja'       => 'Sekretariat Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2019-03-01',
            'status_pegawai'   => 'aparatur',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // =========================================================
        // GRUP 2 — HAKIM AD HOC PERIKANAN (tanpa golongan)
        // =========================================================

        // No. 3
        User::create([
            'name'             => 'H. M. MEISON AZIS, S.E., S.H.',
            'nip'              => '195905040220121002',
            'password'         => $pass,
            'role'             => 'hakim_ad_hoc',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Ad Hoc Perikanan',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2012-11-06',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 4
        User::create([
            'name'             => 'SUTRIYADI, S.H., M.Si',
            'nip'              => '3578162705610002',
            'password'         => $pass,
            'role'             => 'hakim_ad_hoc',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Ad Hoc Perikanan',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2025-04-29',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 5
        User::create([
            'name'             => 'SIRODJUDDIN, S.H., M.H.',
            'nip'              => '3578040206700007',
            'password'         => $pass,
            'role'             => 'hakim_ad_hoc',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Ad Hoc Perikanan',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2021-04-28',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 6
        User::create([
            'name'             => 'ENDRO BASUKI PRABOWO, A.Pi.',
            'nip'              => '1871132109620005',
            'password'         => $pass,
            'role'             => 'hakim_ad_hoc',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Ad Hoc Perikanan',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2021-02-26',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 8
        User::create([
            'name'             => 'SURIADI, S.H., M.H.',
            'nip'              => '1371040205780005',
            'password'         => $pass,
            'role'             => 'hakim_ad_hoc',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Ad Hoc Perikanan',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2021-03-08',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 13
        User::create([
            'name'             => 'Dr. HALOMOAN FREDDY SITINJAK ALEXANDRA, S.H., M.H.',
            'nip'              => '3175101312630005',
            'password'         => $pass,
            'role'             => 'hakim_ad_hoc',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Ad Hoc Perikanan',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2022-11-11',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // =========================================================
        // GRUP 3 — HAKIM TINGKAT PERTAMA
        // =========================================================

        // No. 7
        User::create([
            'name'             => 'SALIHIN ARDIANSYAH, S.H., M.H.',
            'nip'              => '199208212017121004',
            'password'         => $pass,
            'role'             => 'hakim',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Tingkat Pertama',
            'golongan_ruang'   => 'III/c',
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2017-12-01',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 9
        User::create([
            'name'             => 'GERALDO GRACELO MARIO SITUMEANG, S.H.',
            'nip'              => '199906042022031008',
            'password'         => $pass,
            'role'             => 'hakim',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Tingkat Pertama',
            'golongan_ruang'   => 'III/a',
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2022-03-01',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 10
        User::create([
            'name'             => 'HADITIO, S.H.',
            'nip'              => '199702172022031003',
            'password'         => $pass,
            'role'             => 'hakim',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Tingkat Pertama',
            'golongan_ruang'   => 'III/a',
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2022-03-01',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 11
        User::create([
            'name'             => 'ALFARIZ MAULANA REZA, S.H., M.H.',
            'nip'              => '199712262022031005',
            'password'         => $pass,
            'role'             => 'hakim',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Tingkat Pertama',
            'golongan_ruang'   => 'III/a',
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2022-03-01',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 12
        User::create([
            'name'             => 'SWANDI HUTABARAT, S.H.',
            'nip'              => '199703182022031007',
            'password'         => $pass,
            'role'             => 'hakim',
            'leave_balance'    => 12,
            'jabatan'          => 'Hakim Tingkat Pertama',
            'golongan_ruang'   => 'III/a',
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2022-03-01',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // No. 2 — Wakil Ketua (hakim, atasan = Ketua)
        User::create([
            'name'             => 'JOKO CIPTANTO, S.H., M.H.',
            'nip'              => '198006162008051001',
            'password'         => $pass,
            'role'             => 'hakim',
            'leave_balance'    => 12,
            'jabatan'          => 'Wakil Ketua Tingkat Pertama',
            'golongan_ruang'   => 'IV/a',
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2008-05-01',
            'status_pegawai'   => 'hakim',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $ketua->id,
        ]);

        // =========================================================
        // GRUP 4 — STAF KEPANITERAAN (atasan = Panitera)
        // =========================================================

        // No. 16
        User::create([
            'name'             => 'JHIVO WILANDA, S.H.',
            'nip'              => '199604232020121001',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Panitera Pengganti Tingkat Pertama',
            'golongan_ruang'   => 'III/b',
            'unit_kerja'       => 'Kepaniteraan Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2020-12-01',
            'status_pegawai'   => 'aparatur',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $panitera->id,
        ]);

        // No. 17
        User::create([
            'name'             => 'ARI PUTRA UTAMA, A.Md. A.B.',
            'nip'              => '199811252022031008',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Panitera Pengganti Tingkat Pertama',
            'golongan_ruang'   => 'II/c',
            'unit_kerja'       => 'Kepaniteraan Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2022-03-01',
            'status_pegawai'   => 'aparatur',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $panitera->id,
        ]);

        // No. 18
        User::create([
            'name'             => 'MARIHOD TUA LUBIS, S.H.',
            'nip'              => '200002282024051001',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Klerek - Analis Perkara Peradilan',
            'golongan_ruang'   => 'III/a',
            'unit_kerja'       => 'Panitera Muda Pidana Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2024-05-01',
            'status_pegawai'   => 'aparatur',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $panitera->id,
        ]);

        // No. 22
        User::create([
            'name'             => 'CANIA KIRANA, A.Md',
            'nip'              => '199602022022032010',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Klerek - Pengelola Penanganan Perkara',
            'golongan_ruang'   => 'II/c',
            'unit_kerja'       => 'Panitera Muda Pidana Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2022-03-01',
            'status_pegawai'   => 'aparatur',
            'jenis_kelamin'    => 'P',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $panitera->id,
        ]);

        // No. 23 — CPNS
        User::create([
            'name'             => 'MUHAMMAD FARIS AKBAR, A.Md.',
            'nip'              => '199412162025061006',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Klerek - Dokumentalis Hukum',
            'golongan_ruang'   => 'II/c',
            'unit_kerja'       => 'Panitera Muda Pidana Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2025-06-01',
            'status_pegawai'   => 'cpns',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $panitera->id,
        ]);

        // No. 24 — CPNS
        User::create([
            'name'             => 'ASTURI PERIYADI, A.Md.A.B.',
            'nip'              => '199705062025061012',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Klerek - Dokumentalis Hukum',
            'golongan_ruang'   => 'II/c',
            'unit_kerja'       => 'Panitera Muda Perdata Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2025-06-01',
            'status_pegawai'   => 'cpns',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $panitera->id,
        ]);

        // No. 25 — CPNS
        User::create([
            'name'             => 'DION BOY ARDITA, A.Md.A.B.',
            'nip'              => '200104212025061010',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Klerek - Dokumentalis Hukum',
            'golongan_ruang'   => 'II/c',
            'unit_kerja'       => 'Panitera Muda Khusus HAM Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2025-06-01',
            'status_pegawai'   => 'cpns',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $panitera->id,
        ]);

        // No. 26 — CPNS
        User::create([
            'name'             => 'JUPRIZAL, A.Md.A.B.',
            'nip'              => '199510102025061005',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Klerek - Dokumentalis Hukum',
            'golongan_ruang'   => 'II/c',
            'unit_kerja'       => 'Panitera Muda Khusus Perikanan Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2025-06-01',
            'status_pegawai'   => 'cpns',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $panitera->id,
        ]);

        // =========================================================
        // GRUP 5 — STAF SEKRETARIAT (atasan = Sekretaris)
        // =========================================================

        // No. 19
        User::create([
            'name'             => 'DAVID SANGGAM CHRISTOPHER LUMBANTOBING, S.T.',
            'nip'              => '198712272020121003',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Pranata Komputer Ahli Pertama',
            'golongan_ruang'   => 'III/a',
            'unit_kerja'       => 'Sekretariat Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2020-12-01',
            'status_pegawai'   => 'aparatur',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // No. 20
        User::create([
            'name'             => 'CANDRA FIRMANSYAH, S.I.Pust.',
            'nip'              => '199312102020121001',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Operator - Penata Layanan Operasional',
            'golongan_ruang'   => 'III/a',
            'unit_kerja'       => 'Subbagian Kepegawaian, Organisasi, dan Tata Laksana',
            'masa_kerja_mulai' => '2020-12-01',
            'status_pegawai'   => 'aparatur',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // No. 21 — CPNS
        User::create([
            'name'             => 'FRANS ALBERTO SIREGAR, S.T.',
            'nip'              => '199808202025061008',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Teknisi Sarana dan Prasarana',
            'golongan_ruang'   => 'III/a',
            'unit_kerja'       => 'Subbagian Umum dan Keuangan',
            'masa_kerja_mulai' => '2025-06-01',
            'status_pegawai'   => 'cpns',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // =========================================================
        // GRUP 6 — PPPK (atasan = Sekretaris, tanpa golongan)
        // =========================================================

        // No. 27
        User::create([
            'name'             => 'BAIT, S.H.',
            'nip'              => '199005052025211076',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Operator - Penata Layanan Operasional',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Subbagian Umum dan Keuangan',
            'masa_kerja_mulai' => '2025-09-01',
            'status_pegawai'   => 'pppk',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // No. 28
        User::create([
            'name'             => 'RATI PUSITA, S.Pd.I.',
            'nip'              => '198610172025212030',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Operator - Penata Layanan Operasional',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Subbagian Umum dan Keuangan',
            'masa_kerja_mulai' => '2025-09-01',
            'status_pegawai'   => 'pppk',
            'jenis_kelamin'    => 'P',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // No. 29
        User::create([
            'name'             => 'YUNINGSIH',
            'nip'              => '197906022025212014',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Operator Layanan Operasional',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Panitera Muda Pidana Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2025-09-01',
            'status_pegawai'   => 'pppk',
            'jenis_kelamin'    => 'P',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // No. 30
        User::create([
            'name'             => 'KARTINA',
            'nip'              => '199210082025212043',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Operator Layanan Operasional',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Panitera Muda Perdata Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => '2025-09-01',
            'status_pegawai'   => 'pppk',
            'jenis_kelamin'    => 'P',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // No. 31
        User::create([
            'name'             => 'KUSNAIDI',
            'nip'              => '199011202025211033',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Operator Layanan Operasional',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Subbagian Perencanaan, Teknologi Informasi, dan Pelaporan',
            'masa_kerja_mulai' => '2025-09-01',
            'status_pegawai'   => 'pppk',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // No. 32
        User::create([
            'name'             => 'NOKI SURYATNO',
            'nip'              => '199011052025211052',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Operator Layanan Operasional',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Subbagian Kepegawaian, Organisasi, dan Tata Laksana',
            'masa_kerja_mulai' => '2025-09-01',
            'status_pegawai'   => 'pppk',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // No. 33
        User::create([
            'name'             => 'ARDIANSYAH',
            'nip'              => '199001062025211027',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Operator Layanan Operasional',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Subbagian Umum dan Keuangan',
            'masa_kerja_mulai' => '2025-09-01',
            'status_pegawai'   => 'pppk',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // No. 34
        User::create([
            'name'             => 'RIKO GUSTIANTO',
            'nip'              => '200003232025211015',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Operator Layanan Operasional',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Subbagian Umum dan Keuangan',
            'masa_kerja_mulai' => '2025-09-01',
            'status_pegawai'   => 'pppk',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // No. 35
        User::create([
            'name'             => 'RIA ANGELINA BR SITOMPUL',
            'nip'              => '199007292025212038',
            'password'         => $pass,
            'role'             => 'pegawai',
            'leave_balance'    => 12,
            'jabatan'          => 'Operator Layanan Operasional',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Subbagian Umum dan Keuangan',
            'masa_kerja_mulai' => '2025-09-01',
            'status_pegawai'   => 'pppk',
            'jenis_kelamin'    => 'P',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
            'atasan_id'        => $sekretaris->id,
        ]);

        // =========================================================
        // AKUN ADMIN SISTEM (di luar 35 pegawai)
        // =========================================================
        User::create([
            'name'             => 'Administrator Sistem',
            'nip'              => '000000000000000000',
            'email'            => 'admin@pn-natuna.go.id',
            'password'         => $passAdmin,
            'role'             => 'admin',
            'leave_balance'    => 0,
            'jabatan'          => 'Administrator',
            'golongan_ruang'   => null,
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai' => null,
            'status_pegawai'   => 'aparatur',
            'jenis_kelamin'    => 'L',
            'jumlah_anak'      => 0,
            'lokasi_terpencil' => false,
        ]);

        // =========================================================
        // HARI LIBUR NASIONAL 2025 & 2026
        // =========================================================
        $this->call(HariLiburSeeder::class);

        // =========================================================
        // CUTI RECORDS 2026 — fresh, tanpa carry-over
        // Semua 35 pegawai mendapat hak_cuti=12, sisa=12
        // =========================================================
        $allPegawai = User::where('role', '!=', 'admin')->get();

        foreach ($allPegawai as $pegawai) {
            CutiRecord::create([
                'user_id'           => $pegawai->id,
                'tahun'             => 2026,
                'hak_cuti'          => 12,
                'cuti_diambil'      => 0,
                'sisa_cuti'         => 12,
                'carry_over'        => 0,
                'tambahan_terpencil'=> 0,
                'keterangan'        => 'Database awal — saldo baru',
            ]);
        }
    }
}
