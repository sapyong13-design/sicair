<?php

namespace Database\Seeders;

use App\Models\HariLibur;
use Illuminate\Database\Seeder;

/**
 * Hari Libur Nasional & Cuti Bersama 2025-2026
 * Sumber: SKB 3 Menteri (Menteri Agama, Menteri Ketenagakerjaan,
 * Menteri PANRB) tentang Hari Libur Nasional dan Cuti Bersama.
 */
class HariLiburSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================
        // HARI LIBUR NASIONAL 2025
        // =====================================================
        $libur2025 = [
            ['tanggal' => '2025-01-01', 'keterangan' => 'Tahun Baru Masehi 2025'],
            ['tanggal' => '2025-01-29', 'keterangan' => 'Tahun Baru Imlek 2576 Kongzili'],
            ['tanggal' => '2025-03-28', 'keterangan' => 'Hari Suci Nyepi (Tahun Baru Saka 1947)'],
            ['tanggal' => '2025-03-31', 'keterangan' => 'Hari Raya Idul Fitri 1 Syawal 1446H'],
            ['tanggal' => '2025-04-01', 'keterangan' => 'Hari Raya Idul Fitri 2 Syawal 1446H'],
            ['tanggal' => '2025-04-18', 'keterangan' => 'Wafat Isa Al-Masih'],
            ['tanggal' => '2025-05-01', 'keterangan' => 'Hari Buruh Internasional'],
            ['tanggal' => '2025-05-12', 'keterangan' => 'Hari Raya Waisak 2569 BE'],
            ['tanggal' => '2025-05-29', 'keterangan' => 'Kenaikan Isa Al-Masih'],
            ['tanggal' => '2025-06-01', 'keterangan' => 'Hari Lahir Pancasila'],
            ['tanggal' => '2025-06-06', 'keterangan' => 'Hari Raya Idul Adha 1446H'],
            ['tanggal' => '2025-06-27', 'keterangan' => 'Tahun Baru Islam 1447H'],
            ['tanggal' => '2025-08-17', 'keterangan' => 'Hari Kemerdekaan Republik Indonesia'],
            ['tanggal' => '2025-09-05', 'keterangan' => 'Maulid Nabi Muhammad SAW 1447H'],
            ['tanggal' => '2025-12-25', 'keterangan' => 'Hari Raya Natal'],
        ];

        foreach ($libur2025 as $hl) {
            HariLibur::updateOrCreate(
                ['tanggal' => $hl['tanggal']],
                ['keterangan' => $hl['keterangan'], 'tahun' => 2025, 'is_cuti_bersama' => false]
            );
        }

        // CUTI BERSAMA 2025
        $cutiBersama2025 = [
            // Seputar Imlek (29 Jan)
            ['tanggal' => '2025-01-27', 'keterangan' => 'Cuti Bersama Tahun Baru Imlek 2576'],
            ['tanggal' => '2025-01-28', 'keterangan' => 'Cuti Bersama Tahun Baru Imlek 2576'],

            // Seputar Nyepi (28 Mar - Jumat): tidak ada cuti bersama karena libur sudah Jumat
            // Seputar Idul Fitri (31 Mar, 1 Apr)
            ['tanggal' => '2025-04-02', 'keterangan' => 'Cuti Bersama Idul Fitri 1446H'],
            ['tanggal' => '2025-04-03', 'keterangan' => 'Cuti Bersama Idul Fitri 1446H'],
            ['tanggal' => '2025-04-04', 'keterangan' => 'Cuti Bersama Idul Fitri 1446H'],
            ['tanggal' => '2025-04-07', 'keterangan' => 'Cuti Bersama Idul Fitri 1446H'],

            // Seputar Waisak (12 May - Senin)
            ['tanggal' => '2025-05-13', 'keterangan' => 'Cuti Bersama Hari Raya Waisak 2569 BE'],

            // Seputar Kenaikan Isa Al-Masih (29 May - Kamis)
            ['tanggal' => '2025-05-30', 'keterangan' => 'Cuti Bersama Kenaikan Isa Al-Masih'],

            // Seputar Natal (25 Des - Kamis)
            ['tanggal' => '2025-12-26', 'keterangan' => 'Cuti Bersama Hari Raya Natal'],
        ];

        foreach ($cutiBersama2025 as $cb) {
            HariLibur::updateOrCreate(
                ['tanggal' => $cb['tanggal']],
                ['keterangan' => $cb['keterangan'], 'tahun' => 2025, 'is_cuti_bersama' => true]
            );
        }

        // =====================================================
        // HARI LIBUR NASIONAL 2026
        // =====================================================
        $libur2026 = [
            ['tanggal' => '2026-01-01', 'keterangan' => 'Tahun Baru Masehi 2026'],
            // Isra Miraj dan Tahun Baru Imlek jatuh bersamaan: 17 Februari 2026
            ['tanggal' => '2026-02-17', 'keterangan' => 'Isra Miraj Nabi Muhammad SAW & Tahun Baru Imlek 2577'],
            ['tanggal' => '2026-03-19', 'keterangan' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)'],
            ['tanggal' => '2026-04-03', 'keterangan' => 'Wafat Isa Al-Masih'],
            ['tanggal' => '2026-04-20', 'keterangan' => 'Hari Raya Idul Fitri 1 Syawal 1447H'],
            ['tanggal' => '2026-04-21', 'keterangan' => 'Hari Raya Idul Fitri 2 Syawal 1447H'],
            ['tanggal' => '2026-05-01', 'keterangan' => 'Hari Buruh Internasional'],
            ['tanggal' => '2026-05-14', 'keterangan' => 'Kenaikan Isa Al-Masih'],
            ['tanggal' => '2026-05-31', 'keterangan' => 'Hari Raya Waisak 2570 BE'],
            ['tanggal' => '2026-06-01', 'keterangan' => 'Hari Lahir Pancasila'],
            ['tanggal' => '2026-06-27', 'keterangan' => 'Hari Raya Idul Adha 1447H'],
            ['tanggal' => '2026-07-17', 'keterangan' => 'Tahun Baru Islam 1448H'],
            ['tanggal' => '2026-08-17', 'keterangan' => 'Hari Kemerdekaan Republik Indonesia'],
            ['tanggal' => '2026-09-25', 'keterangan' => 'Maulid Nabi Muhammad SAW 1448H'],
            ['tanggal' => '2026-12-25', 'keterangan' => 'Hari Raya Natal'],
        ];

        foreach ($libur2026 as $hl) {
            HariLibur::updateOrCreate(
                ['tanggal' => $hl['tanggal']],
                ['keterangan' => $hl['keterangan'], 'tahun' => 2026, 'is_cuti_bersama' => false]
            );
        }

        // CUTI BERSAMA 2026
        $cutiBersama2026 = [
            // Seputar Imlek/Isra Miraj (17 Feb - Selasa)
            ['tanggal' => '2026-02-16', 'keterangan' => 'Cuti Bersama Tahun Baru Imlek 2577'],

            // Seputar Nyepi (19 Mar - Kamis)
            ['tanggal' => '2026-03-20', 'keterangan' => 'Cuti Bersama Nyepi'],

            // Seputar Idul Fitri (20-21 Apr - Senin-Selasa)
            ['tanggal' => '2026-04-17', 'keterangan' => 'Cuti Bersama Idul Fitri 1447H'],
            ['tanggal' => '2026-04-22', 'keterangan' => 'Cuti Bersama Idul Fitri 1447H'],
            ['tanggal' => '2026-04-23', 'keterangan' => 'Cuti Bersama Idul Fitri 1447H'],
            ['tanggal' => '2026-04-24', 'keterangan' => 'Cuti Bersama Idul Fitri 1447H'],

            // Seputar Natal (25 Des - Jumat)
            ['tanggal' => '2026-12-24', 'keterangan' => 'Cuti Bersama Hari Raya Natal'],
            ['tanggal' => '2026-12-26', 'keterangan' => 'Cuti Bersama Hari Raya Natal'],
        ];

        foreach ($cutiBersama2026 as $cb) {
            HariLibur::updateOrCreate(
                ['tanggal' => $cb['tanggal']],
                ['keterangan' => $cb['keterangan'], 'tahun' => 2026, 'is_cuti_bersama' => true]
            );
        }
    }
}
