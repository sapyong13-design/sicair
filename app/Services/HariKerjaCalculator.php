<?php

namespace App\Services;

use App\Models\HariLibur;
use App\Models\LeaveRequest;
use App\Models\User;
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
     * Ambil user ID berdasarkan bagian menggunakan query DB langsung.
     */
    private static function getUserIdsByBagian(string $bagian): array
    {
        $base = User::whereIn('role', ['pegawai', 'atasan', 'panitera', 'sekretaris', 'hakim', 'hakim_ad_hoc']);

        if ($bagian === 'hakim') {
            return $base->where('role', 'hakim')->pluck('id')->toArray();
        }
        if ($bagian === 'hakim_ad_hoc') {
            return $base->where('role', 'hakim_ad_hoc')->pluck('id')->toArray();
        }
        if ($bagian === 'kepaniteraan') {
            return $base->where(function ($q) {
                $q->where('role', 'panitera')
                  ->orWhere(function ($q2) {
                      $q2->whereIn('role', ['pegawai', 'atasan'])
                         ->where(function ($q3) {
                             $q3->whereRaw("LOWER(unit_kerja) LIKE '%panitera%'")
                                ->orWhereRaw("LOWER(unit_kerja) LIKE '%kepaniteraan%'");
                         });
                  });
            })->pluck('id')->toArray();
        }
        if ($bagian === 'kesekretariatan') {
            return $base->where(function ($q) {
                $q->where('role', 'sekretaris')
                  ->orWhere(function ($q2) {
                      $q2->whereIn('role', ['pegawai', 'atasan'])
                         ->where(function ($q3) {
                             $q3->whereRaw("LOWER(unit_kerja) LIKE '%sekretariat%'")
                                ->orWhereRaw("LOWER(unit_kerja) LIKE '%subbagian%'");
                         });
                  });
            })->pluck('id')->toArray();
        }
        // Fallback: filter di PHP untuk 'umum' dan lainnya
        return User::whereIn('role', ['pegawai', 'atasan', 'panitera', 'sekretaris', 'hakim', 'hakim_ad_hoc'])
            ->get()->filter(fn($u) => $u->getBagian() === $bagian)->pluck('id')->toArray();
    }

    /**
     * Hitung persentase pegawai yang cuti pada rentang tanggal tertentu
     * untuk validasi kuota 30% per bagian (hakim/kepaniteraan/kesekretariatan).
     */
    public static function hitungPersentaseCutiSaatIni(Carbon $start, Carbon $end, User $user): float
    {
        $bagian = $user->getBagian();

        // Bangun query berdasarkan bagian menggunakan DB (hindari memuat semua user ke PHP)
        $userIdsBagian = self::getUserIdsByBagian($bagian);

        $totalPegawai = count($userIdsBagian);

        if ($totalPegawai === 0) {
            return 0;
        }

        $pegawaiCuti = LeaveRequest::whereIn('user_id', $userIdsBagian)
            ->whereIn('status', ['disetujui', 'approved'])
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
