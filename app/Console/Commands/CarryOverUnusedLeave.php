<?php

namespace App\Console\Commands;

use App\Models\CutiRecord;
use App\Models\Notification;
use App\Models\User;
use App\Services\CutiTahunanCalculator;
use Illuminate\Console\Command;

class CarryOverUnusedLeave extends Command
{
    protected $signature = 'app:carry-over-unused-leave
                            {--year= : Year to carry over from (default: current year - 1)}
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Auto carry over unused leave days from previous year (max 6 days per SEMA 13/2019)';

    /**
     * Batas carry-over sesuai SEMA 13/2019: maks 6 hari per tahun sebelumnya
     */
    const CARRY_OVER_LIMIT = 6;

    public function handle()
    {
        $year = (int) ($this->option('year') ?? (date('Y') - 1));
        $dryRun = $this->option('dry-run');
        $nextYear = $year + 1;

        $this->info("Processing carry-over from year {$year} to {$nextYear}...");

        if ($dryRun) {
            $this->warn('DRY RUN - No changes will be made');
        }

        $processed = 0;
        $totalCarried = 0;

        // Get all users (using role-based filter, no 'status' column)
        $users = User::whereNotIn('role', ['admin'])->get();

        foreach ($users as $user) {
            // Gunakan CutiTahunanCalculator untuk menghitung sisa cuti tahun $year
            $calculator = new CutiTahunanCalculator($user, $year);
            $cutiData = $calculator->hitung();

            $sisa = $cutiData['sisa'] ?? 0;

            if ($sisa <= 0) {
                continue;
            }

            // Cap at carry-over limit (6 hari per SEMA 13/2019)
            $carryOverDays = min($sisa, self::CARRY_OVER_LIMIT);

            $processed++;
            $totalCarried += $carryOverDays;

            if (!$dryRun) {
                // Simpan/update record tahun sumber
                $calculator->updateRecord();

                // Update record tahun tujuan dengan carry_over
                CutiRecord::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'tahun' => $nextYear,
                    ],
                    [
                        'carry_over' => $carryOverDays,
                        'hak_cuti' => 12,
                    ]
                );

                // Send notification
                Notification::kirim(
                    $user->id,
                    'Carry Over Cuti Tahunan',
                    "Sisa cuti tahunan Anda sebanyak {$carryOverDays} hari telah dibawa ke tahun {$nextYear}.",
                    Notification::TYPE_CUTI_DISETUJUI,
                    '/dashboard'
                );
            }

            $this->line("  ✓ {$user->name}: {$carryOverDays} hari (sisa: {$sisa})");
        }

        $this->info("---");
        $this->info("Total users processed: {$processed}");
        $this->info("Total days carried over: {$totalCarried} hari");

        if ($dryRun) {
            $this->warn('DRY RUN - No actual changes were made');
        } else {
            $this->info('Carry-over completed successfully!');
        }
    }

    /**
     * Calculate carry-over days for a user in a given year
     */
    private function calculateCarryOver(User $user, int $year): int
    {
        // Get or create cuti record for the year
        $cutiRecord = CutiRecord::firstOrCreate(
            [
                'user_id' => $user->id,
                'tahun' => $year,
            ],
            [
                'hak_cuti' => 12,
            ]
        );

        // Calculate total used leave
        $usedDays = $cutiRecord->cuti_diambil ?? 0;

        // Calculate remaining days
        $allocated = $cutiRecord->hak_cuti ?? 12;
        $carryOver = $cutiRecord->carry_over ?? 0;
        $totalAvailable = $allocated + $carryOver;
        $remaining = $totalAvailable - $usedDays;

        // Cap at carry-over limit
        $carryOverDays = min($remaining, self::CARRY_OVER_LIMIT);

        // Only carry over if there are remaining days
        return max(0, $carryOverDays);
    }
}
