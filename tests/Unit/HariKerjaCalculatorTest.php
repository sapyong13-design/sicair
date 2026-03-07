<?php

namespace Tests\Unit;

use App\Models\HariLibur;
use App\Services\HariKerjaCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HariKerjaCalculatorTest extends TestCase
{
    use RefreshDatabase;

    // ===== hitungHariKerja =====

    /**
     * Senin sampai Jumat (5 hari) semuanya dihitung sebagai hari kerja.
     */
    public function test_hitungHariKerja_weekdays_counted(): void
    {
        // Senin 2025-01-06 s/d Jumat 2025-01-10 = 5 hari kerja
        $start = Carbon::parse('2025-01-06'); // Senin
        $end   = Carbon::parse('2025-01-10'); // Jumat

        $result = HariKerjaCalculator::hitungHariKerja($start, $end);

        $this->assertSame(5, $result);
    }

    /**
     * Sabtu dan Minggu tidak dihitung sebagai hari kerja.
     */
    public function test_hitungHariKerja_weekends_not_counted(): void
    {
        // Sabtu 2025-01-11 s/d Minggu 2025-01-12 = 0 hari kerja
        $start = Carbon::parse('2025-01-11'); // Sabtu
        $end   = Carbon::parse('2025-01-12'); // Minggu

        $result = HariKerjaCalculator::hitungHariKerja($start, $end);

        $this->assertSame(0, $result);
    }

    /**
     * Hari libur nasional yang jatuh di hari kerja dikecualikan.
     */
    public function test_hitungHariKerja_national_holiday_not_counted(): void
    {
        // Senin 2025-01-06 s/d Jumat 2025-01-10 seharusnya 5 hari kerja.
        // Buat hari libur di Rabu 2025-01-08 → hasilnya harus 4.
        HariLibur::create([
            'tanggal'         => '2025-01-08',
            'keterangan'      => 'Libur Nasional Test',
            'tahun'           => 2025,
            'is_cuti_bersama' => false,
        ]);

        $start = Carbon::parse('2025-01-06'); // Senin
        $end   = Carbon::parse('2025-01-10'); // Jumat

        $result = HariKerjaCalculator::hitungHariKerja($start, $end);

        $this->assertSame(4, $result);
    }

    /**
     * Dua hari libur dalam rentang yang sama dikecualikan semua.
     */
    public function test_hitungHariKerja_multiple_holidays_excluded(): void
    {
        HariLibur::create([
            'tanggal'         => '2025-01-06',
            'keterangan'      => 'Libur A',
            'tahun'           => 2025,
            'is_cuti_bersama' => false,
        ]);
        HariLibur::create([
            'tanggal'         => '2025-01-07',
            'keterangan'      => 'Libur B',
            'tahun'           => 2025,
            'is_cuti_bersama' => false,
        ]);

        // Senin-Jumat minus 2 hari libur = 3 hari kerja
        $start = Carbon::parse('2025-01-06');
        $end   = Carbon::parse('2025-01-10');

        $result = HariKerjaCalculator::hitungHariKerja($start, $end);

        $this->assertSame(3, $result);
    }

    /**
     * Rentang satu hari yang merupakan hari kerja = 1.
     */
    public function test_hitungHariKerja_single_working_day(): void
    {
        $start = Carbon::parse('2025-01-06'); // Senin
        $end   = Carbon::parse('2025-01-06'); // Senin (sama)

        $result = HariKerjaCalculator::hitungHariKerja($start, $end);

        $this->assertSame(1, $result);
    }

    /**
     * Rentang satu hari yang jatuh di akhir pekan = 0.
     */
    public function test_hitungHariKerja_single_weekend_day(): void
    {
        $start = Carbon::parse('2025-01-11'); // Sabtu
        $end   = Carbon::parse('2025-01-11'); // Sabtu (sama)

        $result = HariKerjaCalculator::hitungHariKerja($start, $end);

        $this->assertSame(0, $result);
    }

    /**
     * Seminggu penuh (Senin-Minggu) menghasilkan 5 hari kerja.
     */
    public function test_hitungHariKerja_full_week(): void
    {
        $start = Carbon::parse('2025-01-06'); // Senin
        $end   = Carbon::parse('2025-01-12'); // Minggu

        $result = HariKerjaCalculator::hitungHariKerja($start, $end);

        $this->assertSame(5, $result);
    }

    // ===== isHariKerja =====

    /**
     * Hari Senin adalah hari kerja.
     */
    public function test_isHariKerja_monday_is_working_day(): void
    {
        $date = Carbon::parse('2025-01-06'); // Senin

        $this->assertTrue(HariKerjaCalculator::isHariKerja($date));
    }

    /**
     * Hari Sabtu bukan hari kerja.
     */
    public function test_isHariKerja_saturday_is_not_working_day(): void
    {
        $date = Carbon::parse('2025-01-11'); // Sabtu

        $this->assertFalse(HariKerjaCalculator::isHariKerja($date));
    }

    /**
     * Hari Minggu bukan hari kerja.
     */
    public function test_isHariKerja_sunday_is_not_working_day(): void
    {
        $date = Carbon::parse('2025-01-12'); // Minggu

        $this->assertFalse(HariKerjaCalculator::isHariKerja($date));
    }

    /**
     * Hari kerja yang ditetapkan sebagai hari libur nasional bukan hari kerja.
     */
    public function test_isHariKerja_holiday_returns_false(): void
    {
        HariLibur::create([
            'tanggal'         => '2025-01-06',
            'keterangan'      => 'Hari Libur Test',
            'tahun'           => 2025,
            'is_cuti_bersama' => false,
        ]);

        $date = Carbon::parse('2025-01-06'); // Senin tapi libur

        $this->assertFalse(HariKerjaCalculator::isHariKerja($date));
    }

    // ===== tambahHariKerja =====

    /**
     * Menambah 5 hari kerja dari Senin menghasilkan Jumat.
     */
    public function test_tambahHariKerja_skips_weekends(): void
    {
        $start = Carbon::parse('2025-01-06'); // Senin

        $result = HariKerjaCalculator::tambahHariKerja($start, 5);

        // 5 hari kerja dari Senin → Jumat
        $this->assertSame('2025-01-10', $result->format('Y-m-d'));
    }

    /**
     * Menambah hari kerja melompati hari libur nasional.
     */
    public function test_tambahHariKerja_skips_national_holiday(): void
    {
        // Libur di Selasa 2025-01-07
        HariLibur::create([
            'tanggal'         => '2025-01-07',
            'keterangan'      => 'Libur Test',
            'tahun'           => 2025,
            'is_cuti_bersama' => false,
        ]);

        $start = Carbon::parse('2025-01-06'); // Senin

        // 2 hari kerja dari Senin: Senin (1), Selasa libur → Rabu (2)
        $result = HariKerjaCalculator::tambahHariKerja($start, 2);

        $this->assertSame('2025-01-08', $result->format('Y-m-d'));
    }

    /**
     * Menambah 1 hari kerja dari hari kerja normal = hari itu sendiri.
     */
    public function test_tambahHariKerja_one_day_from_weekday(): void
    {
        $start = Carbon::parse('2025-01-06'); // Senin

        $result = HariKerjaCalculator::tambahHariKerja($start, 1);

        $this->assertSame('2025-01-06', $result->format('Y-m-d'));
    }
}
