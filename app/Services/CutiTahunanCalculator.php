<?php

namespace App\Services;

use App\Models\CutiRecord;
use App\Models\LeaveRequest;
use App\Models\User;

/**
 * Kalkulator Sisa Cuti Tahunan sesuai SE MA No. 13/2019
 *
 * Aturan carry-over:
 * - Sisa cuti 1 tahun sebelumnya → max 6 hari bisa dibawa
 * - Sisa 2 tahun berturut tidak dipakai sama sekali → max 24 hari total
 * - Sisa 2 tahun berturut sudah dipakai → max 18 hari total
 * - Sisa >2 tahun → hangus
 * - Penangguhan → tahun berikutnya max 24 hari
 * - Tambahan lokasi terpencil → +12 hari kalender
 */
class CutiTahunanCalculator
{
    protected User $user;
    protected int $tahun;

    public function __construct(User $user, ?int $tahun = null)
    {
        $this->user = $user;
        $this->tahun = $tahun ?? now()->year;
    }

    /**
     * Hitung detail hak cuti tahunan.
     */
    public function hitung(): array
    {
        $hakDasar = 12; // 12 hari kerja per tahun

        // Cek apakah sudah bekerja min 1 tahun
        if (!$this->user->sudahBekerjaSatuTahun()) {
            return [
                'hak_dasar' => 0,
                'hak_cuti' => 0, // alias untuk view compatibility
                'carry_over' => 0,
                'tambahan_terpencil' => 0,
                'total_hak' => 0,
                'cuti_diambil' => 0,
                'sisa' => 0,
                'sisa_cuti' => 0, // alias untuk view compatibility
                'pesan' => 'Belum bekerja 1 tahun, belum berhak cuti tahunan.',
            ];
        }

        $carryOver = $this->hitungCarryOver();
        $tambahanTerpencil = $this->hitungTambahanTerpencil();
        $cutiDiambil = $this->hitungCutiDiambilTahunIni();
        $totalHak = $hakDasar + $carryOver + $tambahanTerpencil;
        $sisa = max(0, $totalHak - $cutiDiambil);

        return [
            'hak_dasar' => $hakDasar,
            'hak_cuti' => $hakDasar, // alias untuk view compatibility
            'carry_over' => $carryOver,
            'tambahan_terpencil' => $tambahanTerpencil,
            'total_hak' => $totalHak,
            'cuti_diambil' => $cutiDiambil,
            'sisa' => $sisa,
            'sisa_cuti' => $sisa, // alias untuk view compatibility
            'detail_carry_over' => $this->detailCarryOver(),
            'pesan' => null,
        ];
    }

    /**
     * Hitung carry-over dari tahun sebelumnya.
     * Sesuai SE MA 13/2019:
     * - Max 6 hari dari 1 tahun sebelumnya
     * - Jika 2 tahun berturut tidak digunakan sama sekali → total max 24
     * - Jika 2 tahun berturut sudah digunakan → total max 18
     * - Lebih dari 2 tahun → hangus
     */
    protected function hitungCarryOver(): int
    {
        $recordN1 = $this->getRecord($this->tahun - 1); // tahun lalu
        $recordN2 = $this->getRecord($this->tahun - 2); // 2 tahun lalu

        // Tidak ada record tahun sebelumnya
        if (!$recordN1) {
            return 0;
        }

        $sisaN1 = $recordN1->sisa_cuti;

        // Cek apakah cuti ditangguhkan tahun lalu
        if ($recordN1->ditangguhkan) {
            // Jika ditangguhkan, max 24 hari total (termasuk hak berjalan)
            return min($sisaN1, 12); // carry max 12 sehingga total 24
        }

        // Ada record 2 tahun lalu
        if ($recordN2) {
            $sisaN2 = $recordN2->sisa_cuti;
            $diambilN1 = $recordN1->cuti_diambil;
            $diambilN2 = $recordN2->cuti_diambil;

            // 2 tahun berturut tidak digunakan sama sekali
            // Gunakan == untuk toleransi tipe data (int vs null vs string)
            if ((int) $diambilN1 === 0 && (int) $diambilN2 === 0) {
                // Total max 24: 12 hak + 6 dari N-1 + 6 dari N-2
                $fromN1 = min($sisaN1, 6);
                $fromN2 = min($sisaN2, 6);
                return $fromN1 + $fromN2;
            }

            // 2 tahun berturut ada yang digunakan → max 18 total
            // Carry max 6 dari N-1 saja, N-2 hangus
            return min($sisaN1, 6);
        }

        // Hanya ada record tahun lalu → carry max 6
        return min($sisaN1, 6);
    }

    protected function detailCarryOver(): array
    {
        $recordN1 = $this->getRecord($this->tahun - 1);
        $recordN2 = $this->getRecord($this->tahun - 2);

        $detail = [];

        if ($recordN1) {
            $detail['tahun_n1'] = [
                'tahun' => $this->tahun - 1,
                'hak' => $recordN1->total_hak,
                'diambil' => $recordN1->cuti_diambil,
                'sisa' => $recordN1->sisa_cuti,
                'carry' => min($recordN1->sisa_cuti, 6),
                'ditangguhkan' => $recordN1->ditangguhkan,
            ];
        }

        if ($recordN2) {
            $diambilN1 = $recordN1 ? $recordN1->cuti_diambil : 0;
            $diambilN2 = $recordN2->cuti_diambil;
            $bisa_carry_n2 = ((int) $diambilN1 === 0 && (int) $diambilN2 === 0);

            $detail['tahun_n2'] = [
                'tahun' => $this->tahun - 2,
                'hak' => $recordN2->total_hak,
                'diambil' => $recordN2->cuti_diambil,
                'sisa' => $recordN2->sisa_cuti,
                'carry' => $bisa_carry_n2 ? min($recordN2->sisa_cuti, 6) : 0,
                'hangus' => !$bisa_carry_n2,
            ];
        }

        return $detail;
    }

    protected function hitungTambahanTerpencil(): int
    {
        // SEMA 13/2019: PN Natuna ADALAH lokasi terpencil → berhak +12 hari kalender
        // Cek berdasarkan flag lokasi_terpencil ATAU unit_kerja PN Natuna
        $unitKerja = strtolower(trim($this->user->unit_kerja ?? ''));
        $lokasiTerpencil = [
            'pengadilan negeri natuna',
        ];

        // PN Natuna selalu dianggap terpencil sesuai SEMA 13/2019
        if (in_array($unitKerja, $lokasiTerpencil)) {
            return 12;
        }

        if ($this->user->lokasi_terpencil) {
            return 12; // +12 hari kalender
        }
        return 0;
    }

    protected function hitungCutiDiambilTahunIni(): int
    {
        // FIX #29: Use DB-level SUM for performance; avoid loading all rows to PHP
        return (int) LeaveRequest::where('user_id', $this->user->id)
            ->where('type', LeaveRequest::TYPE_TAHUNAN)
            ->whereIn('status', [
                LeaveRequest::STATUS_APPROVED,
                LeaveRequest::STATUS_DISETUJUI,
            ])
            ->whereYear('start_date', $this->tahun)
            ->sum('total_hari_kerja');
    }

    protected function getRecord(int $tahun): ?CutiRecord
    {
        return CutiRecord::where('user_id', $this->user->id)
            ->where('tahun', $tahun)
            ->first();
    }

    /**
     * Simpan/update record cuti untuk tahun berjalan.
     */
    public function updateRecord(): CutiRecord
    {
        $data = $this->hitung();

        return CutiRecord::updateOrCreate(
            ['user_id' => $this->user->id, 'tahun' => $this->tahun],
            [
                'hak_cuti' => $data['hak_dasar'],
                'cuti_diambil' => $data['cuti_diambil'],
                'sisa_cuti' => $data['sisa'],
                'carry_over' => $data['carry_over'],
                'tambahan_terpencil' => $data['tambahan_terpencil'],
            ]
        );
    }
}
