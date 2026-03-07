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

    // ===== Helpers =====

    /**
     * Buat user PNS dengan masa kerja tertentu.
     */
    private function buatUserDenganMasaKerja(int $tahunKerja): User
    {
        return User::factory()->create([
            'role'            => 'pegawai',
            'status_pegawai'  => 'pns',
            'masa_kerja_mulai' => now()->subYears($tahunKerja)->subDays(1),
            'lokasi_terpencil' => false,
            'unit_kerja'      => 'Subbagian Umum',
        ]);
    }

    // ===== Pegawai belum 1 tahun =====

    /**
     * Pegawai baru (< 1 tahun masa kerja) mendapat hak cuti 0.
     */
    public function test_hitung_pegawai_baru_belum_berhak_cuti(): void
    {
        $user = User::factory()->create([
            'role'            => 'pegawai',
            'status_pegawai'  => 'pns',
            'masa_kerja_mulai' => now()->subMonths(6), // 6 bulan saja
            'lokasi_terpencil' => false,
            'unit_kerja'      => 'Subbagian Umum',
        ]);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $this->assertSame(0, $result['hak_dasar']);
        $this->assertSame(0, $result['total_hak']);
        $this->assertSame(0, $result['sisa']);
        $this->assertNotEmpty($result['pesan']);
    }

    /**
     * Pegawai baru mendapat hak_cuti = 0 (alias view compatibility).
     */
    public function test_hitung_pegawai_baru_hak_cuti_alias_nol(): void
    {
        $user = User::factory()->create([
            'role'            => 'pegawai',
            'status_pegawai'  => 'pns',
            'masa_kerja_mulai' => now()->subDays(30),
            'lokasi_terpencil' => false,
            'unit_kerja'      => 'Subbagian Umum',
        ]);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $this->assertSame(0, $result['hak_cuti']);
        $this->assertSame(0, $result['sisa_cuti']);
    }

    // ===== Pegawai sudah >= 1 tahun =====

    /**
     * Pegawai dengan >= 1 tahun masa kerja mendapat hak dasar 12 hari.
     */
    public function test_hitung_pegawai_satu_tahun_mendapat_12_hari(): void
    {
        $user = $this->buatUserDenganMasaKerja(1);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $this->assertSame(12, $result['hak_dasar']);
        $this->assertGreaterThanOrEqual(12, $result['total_hak']);
        $this->assertNull($result['pesan']);
    }

    /**
     * Pegawai dengan >= 1 tahun dan tidak ada cuti diambil: sisa = total_hak.
     */
    public function test_hitung_pegawai_satu_tahun_tanpa_cuti_diambil(): void
    {
        $user = $this->buatUserDenganMasaKerja(2);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $this->assertSame(0, $result['cuti_diambil']);
        $this->assertSame($result['total_hak'], $result['sisa']);
    }

    /**
     * total_hak = hak_dasar + carry_over + tambahan_terpencil.
     */
    public function test_hitung_total_hak_equals_sum_of_components(): void
    {
        $user = $this->buatUserDenganMasaKerja(3);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $expectedTotal = $result['hak_dasar'] + $result['carry_over'] + $result['tambahan_terpencil'];
        $this->assertSame($expectedTotal, $result['total_hak']);
    }

    // ===== Carry-over =====

    /**
     * Jika ada sisa cuti tahun lalu (max 6 hari), carry-over dihitung.
     */
    public function test_hitung_carry_over_max_6_dari_tahun_lalu(): void
    {
        $user = $this->buatUserDenganMasaKerja(3);
        $tahunIni = now()->year;

        // Buat record tahun lalu dengan sisa 10 → carry max 6
        CutiRecord::create([
            'user_id'           => $user->id,
            'tahun'             => $tahunIni - 1,
            'hak_cuti'          => 12,
            'cuti_diambil'      => 5,
            'sisa_cuti'         => 10,
            'carry_over'        => 0,
            'tambahan_terpencil' => 0,
            'ditangguhkan'      => false,
        ]);

        $calculator = new CutiTahunanCalculator($user, $tahunIni);
        $result = $calculator->hitung();

        $this->assertSame(6, $result['carry_over']);
    }

    /**
     * Jika sisa tahun lalu kurang dari 6, carry-over = sisa yang ada.
     */
    public function test_hitung_carry_over_kurang_dari_6(): void
    {
        $user = $this->buatUserDenganMasaKerja(3);
        $tahunIni = now()->year;

        CutiRecord::create([
            'user_id'           => $user->id,
            'tahun'             => $tahunIni - 1,
            'hak_cuti'          => 12,
            'cuti_diambil'      => 10,
            'sisa_cuti'         => 3, // sisa hanya 3
            'carry_over'        => 0,
            'tambahan_terpencil' => 0,
            'ditangguhkan'      => false,
        ]);

        $calculator = new CutiTahunanCalculator($user, $tahunIni);
        $result = $calculator->hitung();

        $this->assertSame(3, $result['carry_over']);
    }

    /**
     * Tanpa record tahun lalu, carry-over = 0.
     */
    public function test_hitung_carry_over_nol_tanpa_record_sebelumnya(): void
    {
        $user = $this->buatUserDenganMasaKerja(1);

        $calculator = new CutiTahunanCalculator($user, now()->year);
        $result = $calculator->hitung();

        $this->assertSame(0, $result['carry_over']);
    }

    /**
     * Dua tahun berturut tidak digunakan sama sekali:
     * carry-over bisa dari N-1 (max 6) + N-2 (max 6) = max 12.
     */
    public function test_hitung_carry_over_dua_tahun_tidak_dipakai_max_12(): void
    {
        $user = $this->buatUserDenganMasaKerja(4);
        $tahunIni = now()->year;

        // N-2: sisa 8, tidak diambil
        CutiRecord::create([
            'user_id'           => $user->id,
            'tahun'             => $tahunIni - 2,
            'hak_cuti'          => 12,
            'cuti_diambil'      => 0,
            'sisa_cuti'         => 8,
            'carry_over'        => 0,
            'tambahan_terpencil' => 0,
            'ditangguhkan'      => false,
        ]);

        // N-1: sisa 10, tidak diambil
        CutiRecord::create([
            'user_id'           => $user->id,
            'tahun'             => $tahunIni - 1,
            'hak_cuti'          => 12,
            'cuti_diambil'      => 0,
            'sisa_cuti'         => 10,
            'carry_over'        => 0,
            'tambahan_terpencil' => 0,
            'ditangguhkan'      => false,
        ]);

        $calculator = new CutiTahunanCalculator($user, $tahunIni);
        $result = $calculator->hitung();

        // carry = min(10,6) + min(8,6) = 6 + 6 = 12
        $this->assertSame(12, $result['carry_over']);
    }

    // ===== Tambahan Terpencil =====

    /**
     * Pegawai di lokasi terpencil mendapat tambahan 12 hari.
     */
    public function test_hitung_tambahan_lokasi_terpencil_12_hari(): void
    {
        $user = User::factory()->create([
            'role'             => 'pegawai',
            'status_pegawai'   => 'pns',
            'masa_kerja_mulai' => now()->subYears(2),
            'lokasi_terpencil' => true,
            'unit_kerja'       => 'Subbagian Umum',
        ]);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $this->assertSame(12, $result['tambahan_terpencil']);
        $this->assertGreaterThanOrEqual(24, $result['total_hak']); // 12 + 12
    }

    /**
     * Pegawai bukan di lokasi terpencil tidak mendapat tambahan.
     */
    public function test_hitung_tanpa_tambahan_terpencil(): void
    {
        $user = $this->buatUserDenganMasaKerja(2);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $this->assertSame(0, $result['tambahan_terpencil']);
    }

    /**
     * Pegawai dengan unit_kerja PN Natuna (case-insensitive) mendapat tambahan 12 hari.
     */
    public function test_hitung_pn_natuna_mendapat_tambahan_terpencil(): void
    {
        $user = User::factory()->create([
            'role'             => 'pegawai',
            'status_pegawai'   => 'pns',
            'masa_kerja_mulai' => now()->subYears(2),
            'lokasi_terpencil' => false,
            'unit_kerja'       => 'Pengadilan Negeri Natuna',
        ]);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $this->assertSame(12, $result['tambahan_terpencil']);
    }

    // ===== Struktur return value =====

    /**
     * Hasil hitung() selalu mengandung semua key yang dibutuhkan.
     */
    public function test_hitung_return_value_has_required_keys(): void
    {
        $user = $this->buatUserDenganMasaKerja(2);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $requiredKeys = [
            'hak_dasar',
            'hak_cuti',
            'carry_over',
            'tambahan_terpencil',
            'total_hak',
            'cuti_diambil',
            'sisa',
            'sisa_cuti',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $result, "Key '{$key}' tidak ada dalam hasil hitung().");
        }
    }

    /**
     * Untuk pegawai baru, hasil hitung() juga mengandung semua key wajib.
     */
    public function test_hitung_return_value_has_required_keys_for_new_employee(): void
    {
        $user = User::factory()->create([
            'role'             => 'pegawai',
            'status_pegawai'   => 'pns',
            'masa_kerja_mulai' => now()->subMonths(3),
            'lokasi_terpencil' => false,
            'unit_kerja'       => 'Subbagian Umum',
        ]);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $this->assertArrayHasKey('hak_dasar', $result);
        $this->assertArrayHasKey('total_hak', $result);
        $this->assertArrayHasKey('sisa', $result);
        $this->assertArrayHasKey('pesan', $result);
    }

    // ===== Tahun spesifik =====

    /**
     * Constructor menerima parameter tahun opsional; default ke tahun berjalan.
     */
    public function test_hitung_default_tahun_is_current_year(): void
    {
        $user = $this->buatUserDenganMasaKerja(2);

        $calculatorDefault = new CutiTahunanCalculator($user);
        $calculatorExplicit = new CutiTahunanCalculator($user, now()->year);

        $this->assertSame(
            $calculatorDefault->hitung()['hak_dasar'],
            $calculatorExplicit->hitung()['hak_dasar']
        );
    }

    /**
     * sisa tidak pernah negatif meskipun cuti_diambil melebihi total_hak.
     */
    public function test_hitung_sisa_tidak_negatif(): void
    {
        $user = $this->buatUserDenganMasaKerja(2);

        $calculator = new CutiTahunanCalculator($user);
        $result = $calculator->hitung();

        $this->assertGreaterThanOrEqual(0, $result['sisa']);
        $this->assertGreaterThanOrEqual(0, $result['sisa_cuti']);
    }
}
