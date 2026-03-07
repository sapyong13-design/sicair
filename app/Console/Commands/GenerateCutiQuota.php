<?php

namespace App\Console\Commands;

use App\Models\CutiRecord;
use App\Models\User;
use App\Services\CutiTahunanCalculator;
use Illuminate\Console\Command;

class GenerateCutiQuota extends Command
{
    protected $signature = 'cuti:generate-quota {year? : Tahun target (default tahun sekarang)}';
    protected $description = 'Generate/update quota cuti tahunan untuk semua pegawai aktif';

    public function handle(): int
    {
        $year  = (int) ($this->argument('year') ?? now()->year);
        $users = User::where('is_active', true)
            ->whereNotIn('role', ['admin'])
            ->get();

        $this->info("Generating quota cuti tahun {$year} untuk {$users->count()} pegawai...");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $generated = 0;
        $skipped   = 0;

        foreach ($users as $user) {
            try {
                $calculator = new CutiTahunanCalculator($user, $year);
                $result     = $calculator->hitung();

                // Pegawai belum berhak cuti tahunan (belum 1 tahun kerja)
                if ($result['pesan'] !== null) {
                    $bar->advance();
                    $skipped++;
                    continue;
                }

                CutiRecord::updateOrCreate(
                    ['user_id' => $user->id, 'tahun' => $year],
                    [
                        'hak_cuti'           => $result['hak_dasar'],
                        'carry_over'         => $result['carry_over'],
                        'tambahan_terpencil' => $result['tambahan_terpencil'],
                        'cuti_diambil'       => $result['cuti_diambil'],
                        'sisa_cuti'          => $result['sisa'],
                        'keterangan'         => 'Auto-generated dari golongan ' . ($user->golongan_ruang ?? '-'),
                    ]
                );

                $generated++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("Skip user {$user->name}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai. {$generated} quota cuti tahun {$year} berhasil digenerate, {$skipped} dilewati (belum 1 tahun kerja).");

        return self::SUCCESS;
    }
}
