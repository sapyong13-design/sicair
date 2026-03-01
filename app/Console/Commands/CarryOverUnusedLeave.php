<?php

namespace App\Console\Commands;

use App\Models\CutiRecord;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CarryOverUnusedLeave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:carry-over-unused-leave
                            {--year= : Year to carry over from (default: current year - 1)}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto carry over unused leave days from previous year (max 5 days)';

    const CARRY_OVER_LIMIT = 5;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ?? (date('Y') - 1);
        $dryRun = $this->option('dry-run');

        $this->info("Processing carry-over for year {$year}...");

        if ($dryRun) {
            $this->warn('DRY RUN - No changes will be made');
        }

        $processed = 0;
        $totalCarried = 0;

        // Get all users with employee status
        $users = User::where('status', 'aktif')->get();

        foreach ($users as $user) {
            $carryOverDays = $this->calculateCarryOver($user, $year);

            if ($carryOverDays <= 0) {
                continue;
            }

            $processed++;
            $totalCarried += $carryOverDays;

            if (!$dryRun) {
                // Create carry-over record for next year
                $nextYear = $year + 1;
                CutiRecord::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'tahun' => $nextYear,
                        'jenis_cuti' => 'Cuti Tahunan',
                    ],
                    [
                        'carry_over' => $carryOverDays,
                    ]
                );

                // Create audit log
                \App\Models\AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'carry_over_unused_leave',
                    'description' => "Carry over {$carryOverDays} hari cuti tahunan dari tahun {$year}",
                    'ip_address' => 'console-command',
                    'user_agent' => 'Laravel Command',
                ]);

                // Send notification
                Notification::kirim(
                    $user->id,
                    'Carry Over Cuti Tahunan',
                    "Sisa cuti tahunan Anda sebanyak {$carryOverDays} hari telah dibawa ke tahun {$nextYear}.",
                    Notification::TYPE_CUTI_DISETUJUI,
                    '/dashboard'
                );
            }

            $this->line("  ✓ {$user->name}: {$carryOverDays} hari");
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
