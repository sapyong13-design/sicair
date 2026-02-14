<?php

namespace App\Services;

use App\Models\HariLibur;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HariKerjaCalculator
{
    /**
     * Hitung jumlah hari kerja antara dua tanggal.
     * Hari kerja = Senin-Jumat, minus hari libur nasional.
     */
    public static function hitungHariKerja(Carbon $start, Carbon $end): int
    {
        $holidays = HariLibur::whereBetween('tanggal', [$start, $end])
            ->pluck('tanggal')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->toArray();

        $count = 0;
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            // Skip Sabtu (6) dan Minggu (0)
            if ($date->isWeekend()) {
                continue;
            }
            // Skip hari libur nasional
            if (in_array($date->format('Y-m-d'), $holidays)) {
                continue;
            }
            $count++;
        }

        return $count;
    }

    /**
     * Cek apakah tanggal adalah hari kerja.
     */
    public static function isHariKerja(Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return false;
        }
        return !HariLibur::isHoliday($date);
    }

    /**
     * Hitung tanggal akhir berdasarkan jumlah hari kerja dari tanggal mulai.
     */
    public static function tambahHariKerja(Carbon $start, int $hariKerja): Carbon
    {
        $current = $start->copy();
        $count = 0;

        while ($count < $hariKerja) {
            if (self::isHariKerja($current)) {
                $count++;
                if ($count === $hariKerja) {
                    break;
                }
            }
            $current->addDay();
        }

        return $current;
    }

    /**
     * Hitung persentase pegawai yang cuti pada rentang tanggal tertentu
     * untuk validasi kuota 30%.
     */
    public static function hitungPersentaseCutiSaatIni(Carbon $start, Carbon $end, string $unitKerja): float
    {
        $totalPegawai = \App\Models\User::where('unit_kerja', $unitKerja)
            ->whereIn('role', ['pegawai', 'atasan'])
            ->count();

        if ($totalPegawai === 0) {
            return 0;
        }

        $pegawaiCuti = \App\Models\LeaveRequest::whereHas('user', function ($q) use ($unitKerja) {
                $q->where('unit_kerja', $unitKerja);
            })
            ->whereIn('status', ['disetujui', 'approved', 'pertimbangan_atasan', 'diajukan', 'pending'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start)
                         ->where('end_date', '>=', $end);
                  });
            })
            ->distinct('user_id')
            ->count('user_id');

        return ($pegawaiCuti / $totalPegawai) * 100;
    }
}
