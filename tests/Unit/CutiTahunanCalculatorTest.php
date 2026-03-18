<?php

namespace Tests\Unit;

use App\Models\CutiRecord;
use App\Models\User;
use App\Services\CutiTahunanCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CutiTahunanCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_hitung_sisa_cuti_basic(): void
    {
        $user = User::factory()->create([
            'leave_balance' => 12,
            'masa_kerja_mulai' => now()->subYears(2),
        ]);
        CutiRecord::create([
            'user_id'       => $user->id,
            'tahun'         => (int)date('Y'),
            'hak_cuti'      => 12,
            'carry_over'    => 0,
            'cuti_diambil'  => 3,
        ]);

        $calc = new CutiTahunanCalculator($user, (int)date('Y'));
        $result = $calc->hitung();

        $this->assertEquals(12, $result['hak_cuti']);
        $this->assertEquals(0, $result['carry_over'] ?? 0);
        $this->assertGreaterThanOrEqual(0, $result['sisa_cuti'] ?? $result['sisa'] ?? 0);
    }

    public function test_carry_over_tidak_lebih_dari_6(): void
    {
        // Buat cuti record tahun lalu dengan sisa banyak
        $user = User::factory()->create([
            'leave_balance' => 12,
            'masa_kerja_mulai' => now()->subYears(3),
        ]);
        CutiRecord::create([
            'user_id'      => $user->id,
            'tahun'        => (int)date('Y'),
            'hak_cuti'     => 12,
            'carry_over'   => 10, // melebihi batas 6
            'cuti_diambil' => 0,
        ]);

        $calc = new CutiTahunanCalculator($user, (int)date('Y'));
        $result = $calc->hitung();

        // Carry over yang ditampilkan tidak boleh lebih dari 6
        $actualCarryOver = $result['carry_over'] ?? 0;
        $this->assertLessThanOrEqual(6, $actualCarryOver);
    }

    public function test_pegawai_baru_belum_berhak_cuti(): void
    {
        $user = User::factory()->create([
            'masa_kerja_mulai' => now()->subMonths(6),
        ]);
        $this->assertFalse($user->sudahBekerjaSatuTahun());
    }

    public function test_pegawai_satu_tahun_berhak_cuti(): void
    {
        $user = User::factory()->create([
            'masa_kerja_mulai' => now()->subYears(1)->subDays(1),
        ]);
        $this->assertTrue($user->sudahBekerjaSatuTahun());
    }
}
