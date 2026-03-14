<?php

namespace App\Console\Commands;

use App\Models\HariLibur;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportHariLiburNasional extends Command
{
    protected $signature = 'harilibur:import {year? : Tahun yang akan diimport (default: tahun ini)}';
    protected $description = 'Import hari libur nasional Indonesia dari API publik';

    public function handle(): int
    {
        $year = (int) ($this->argument('year') ?? date('Y'));
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $this->info("Mengimport hari libur nasional tahun {$year}...");

        for ($month = 1; $month <= 12; $month++) {
            try {
                $response = Http::timeout(15)->get('https://api-harilibur.vercel.app/api', [
                    'month' => $month,
                    'year'  => $year,
                ]);

                if (!$response->successful()) {
                    $errors[] = "Bulan {$month}: HTTP " . $response->status();
                    continue;
                }

                $holidays = $response->json();
                if (!is_array($holidays)) {
                    $errors[] = "Bulan {$month}: Response bukan array";
                    continue;
                }

                foreach ($holidays as $h) {
                    if (empty($h['holiday_date']) || empty($h['is_national_holiday'])) {
                        continue;
                    }

                    if (HariLibur::where('tanggal', $h['holiday_date'])->exists()) {
                        $skipped++;
                        continue;
                    }

                    HariLibur::create([
                        'tanggal'         => $h['holiday_date'],
                        'keterangan'      => $h['holiday_name'] ?? 'Hari Libur Nasional',
                        'tahun'           => $year,
                        'is_cuti_bersama' => false,
                    ]);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "Bulan {$month}: " . $e->getMessage();
            }
        }

        $this->info("Selesai: {$imported} diimport, {$skipped} sudah ada.");

        if ($errors) {
            $this->warn('Ada error: ' . implode('; ', $errors));
        }

        return self::SUCCESS;
    }
}
