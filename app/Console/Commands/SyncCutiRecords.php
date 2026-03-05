<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CutiTahunanCalculator;
use Illuminate\Console\Command;

class SyncCutiRecords extends Command
{
    protected $signature = 'app:sync-cuti-records
                            {--year= : Year to sync (default: current year)}
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Sync CutiRecord with actual approved leave requests (reconciliation)';

    public function handle()
    {
        $year = (int) ($this->option('year') ?? date('Y'));
        $dryRun = $this->option('dry-run');

        $this->info("Syncing CutiRecords for year {$year}...");

        if ($dryRun) {
            $this->warn('DRY RUN - No changes will be made');
        }

        $syncedCount = 0;
        $errorsCount = 0;

        // Get all users (using role-based filter, no 'status' column)
        $users = User::whereNotIn('role', ['admin'])->get();

        foreach ($users as $user) {
            try {
                // Gunakan CutiTahunanCalculator untuk rekonsiliasi
                $calculator = new CutiTahunanCalculator($user, $year);
                $cutiData = $calculator->hitung();

                if ($cutiData['pesan'] !== null) {
                    // User belum berhak cuti tahunan, skip
                    continue;
                }

                if (!$dryRun) {
                    $calculator->updateRecord();
                }

                $syncedCount++;
                $sisa = $cutiData['sisa'] ?? 0;
                $diambil = $cutiData['cuti_diambil'] ?? 0;
                $this->line("  ✓ {$user->name}: diambil={$diambil}, sisa={$sisa}");
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
                $totalDaysUsed += $leave->total_hari_kerja ?? $leave->total_days ?? 0;
            }
        }

        // Check if sync is needed
        $currentUsed = $cutiRecord->cuti_diambil ?? 0;
        if ($currentUsed === $totalDaysUsed) {
            return false; // No sync needed
        }

        // Update cuti record
        if (!$dryRun) {
            $cutiRecord->update([
                'cuti_diambil' => $totalDaysUsed,
                'sisa_cuti' => ($cutiRecord->hak_cuti ?? 12) + ($cutiRecord->carry_over ?? 0) - $totalDaysUsed,
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
