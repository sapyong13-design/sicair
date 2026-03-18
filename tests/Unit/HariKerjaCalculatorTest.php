<?php

namespace Tests\Unit;

use App\Services\HariKerjaCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HariKerjaCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_senin_jumat_adalah_5_hari_kerja(): void
    {
        // Cari Senin depan
        $senin = Carbon::parse('next monday');
        $jumat = $senin->copy()->addDays(4);
        $result = HariKerjaCalculator::hitungHariKerja($senin, $jumat);
        $this->assertEquals(5, $result);
    }

    public function test_sabtu_minggu_tidak_terhitung(): void
    {
        $senin = Carbon::parse('next monday');
        $minggu = $senin->copy()->addDays(6); // Senin s/d Minggu = 5 hari kerja
        $result = HariKerjaCalculator::hitungHariKerja($senin, $minggu);
        $this->assertEquals(5, $result);
    }

    public function test_satu_hari_senin_adalah_1_hari_kerja(): void
    {
        $senin = Carbon::parse('next monday');
        $result = HariKerjaCalculator::hitungHariKerja($senin, $senin);
        $this->assertEquals(1, $result);
    }

    public function test_dua_minggu_penuh_adalah_10_hari_kerja(): void
    {
        $senin = Carbon::parse('next monday');
        $jumat2 = $senin->copy()->addDays(11); // 2 minggu penuh Senin-Jumat
        $result = HariKerjaCalculator::hitungHariKerja($senin, $jumat2);
        $this->assertEquals(10, $result);
    }
}
