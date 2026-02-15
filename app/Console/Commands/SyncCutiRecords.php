<?php

namespace App\Console\Commands;

use App\Models\CutiRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Console\Command;

class SyncCutiRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-cuti-records
                            {--year= : Year to sync (default: current year)}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync CutiRecord with actual approved leave requests (reconciliation)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ?? date('Y');
        $dryRun = $this->option('dry-run');

        $this->info("Syncing CutiRecords for year {$year}...");

        if ($dryRun) {
            $this->warn('DRY RUN - No changes will be made');
        }

        $syncedCount = 0;
        $errorsCount = 0;

        // Get all active users
        $users = User::where('status', 'aktif')->get();

        foreach ($users as $user) {
            try {
                $synced = $this->syncUserCutiRecords($user, $year, $dryRun);

                if ($synced) {
                    $syncedCount++;
                    $this->line("  ✓ {$user->name}");
                }
            } catch (\Exception $e) {
                $errorsCount++;
                $this->error("  ✗ {$user->name}: {$e->getMessage()}");
            }
        }

        $this->info('---');
        $this->info("Total users synced: {$syncedCount}");
        if ($errorsCount > 0) {
            $this->error("Errors: {$errorsCount}");
        }

        if ($dryRun) {
            $this->warn('DRY RUN - No actual changes were made');
        } else {
            $this->info('Sync completed successfully!');
        }
    }

    /**
     * Sync cuti records for a user
     */
    private function syncUserCutiRecords(User $user, int $year, bool $dryRun): bool
    {
        // Get or create cuti record for the year
        $cutiRecord = CutiRecord::firstOrCreate(
            [
                'user_id' => $user->id,
                'tahun' => $year,
                'jenis_cuti' => 'Cuti Tahunan',
            ],
            [
                'alokasi_awal' => 12, // Default allocation
            ]
        );

        // Count approved leave requests for this user in this year
        $approvedLeaves = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', [
                LeaveRequest::STATUS_DISETUJUI,
                LeaveRequest::STATUS_APPROVED,
            ])
            ->whereYear('created_at', $year)
            ->get();

        // Calculate total days used
        $totalDaysUsed = 0;
        foreach ($approvedLeaves as $leave) {
            // Only count cuti tahunan (regular leave)
            if ($leave->type === LeaveRequest::TYPE_TAHUNAN) {
                $totalDaysUsed += $leave->total_hari_kerja ?? $leave->number_of_days;
            }
        }

        // Check if sync is needed
        $currentUsed = $cutiRecord->digunakan ?? 0;
        if ($currentUsed === $totalDaysUsed) {
            return false; // No sync needed
        }

        // Update cuti record
        if (!$dryRun) {
            $cutiRecord->update([
                'digunakan' => $totalDaysUsed,
                'sisa' => ($cutiRecord->alokasi_awal ?? 12) + ($cutiRecord->carry_over ?? 0) - $totalDaysUsed,
            ]);

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'sync_cuti_records',
                'description' => "Reconcile cuti records: {$currentUsed} → {$totalDaysUsed} days used",
                'ip_address' => 'console-command',
                'user_agent' => 'Laravel Command',
            ]);
        }

        return true;
    }
}
