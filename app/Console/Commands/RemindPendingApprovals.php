<?php

namespace App\Console\Commands;

use App\Mail\LeaveRequestNeedsConsiderationMail;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * #46: Reminder otomatis untuk approval pending > 2 hari kerja
 */
class RemindPendingApprovals extends Command
{
    protected $signature = 'app:remind-pending-approvals
                            {--dry-run : Print what would be sent without actually sending}';

    protected $description = 'Kirim email reminder ke reviewer jika cuti pending lebih dari 2 hari kerja';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $threshold = Carbon::now()->subDays(2);

        // Leave requests still pending at level 1 (waiting for atasan)
        $pendingAtasan = LeaveRequest::whereIn('status', [
            LeaveRequest::STATUS_DIAJUKAN,
            LeaveRequest::STATUS_PENDING,
        ])
            ->where('updated_at', '<', $threshold)
            ->with('user')
            ->get();

        // Leave requests at level 2 (waiting for ketua/pejabat)
        $pendingKetua = LeaveRequest::where('status', LeaveRequest::STATUS_PERTIMBANGAN)
            ->where('updated_at', '<', $threshold)
            ->with('user')
            ->get();

        $remindersSent = 0;

        // Remind atasan for their subordinates' pending leaves
        foreach ($pendingAtasan as $leave) {
            $atasan = $leave->user?->atasan;
            if (!$atasan || empty($atasan->email)) {
                continue;
            }

            $daysWaiting = (int) $leave->updated_at->diffInDays(now());
            $this->line("  → Pending {$daysWaiting}h: [{$leave->id}] {$leave->user->name} → atasan: {$atasan->name}");

            if (!$dryRun) {
                try {
                    Mail::to($atasan->email)->send(new LeaveRequestNeedsConsiderationMail($leave));
                    $remindersSent++;
                } catch (\Throwable $e) {
                    $this->warn("    Gagal kirim ke {$atasan->email}: {$e->getMessage()}");
                }
            } else {
                $remindersSent++;
            }
        }

        // Remind ketua for leaves waiting for final decision
        foreach ($pendingKetua as $leave) {
            $ketuaList = User::whereIn('role', ['ketua', 'admin'])->whereNotNull('email')->get();
            foreach ($ketuaList as $ketua) {
                $daysWaiting = (int) $leave->updated_at->diffInDays(now());
                $this->line("  → Menunggu putusan {$daysWaiting}h: [{$leave->id}] {$leave->user->name} → ketua: {$ketua->name}");

                if (!$dryRun) {
                    try {
                        Mail::to($ketua->email)->send(new LeaveRequestNeedsConsiderationMail($leave));
                        $remindersSent++;
                    } catch (\Throwable $e) {
                        $this->warn("    Gagal kirim ke {$ketua->email}: {$e->getMessage()}");
                    }
                } else {
                    $remindersSent++;
                }
            }
        }

        $label = $dryRun ? '(dry run) akan dikirim' : 'terkirim';
        $this->info("Reminder $label: {$remindersSent} email. ({$pendingAtasan->count()} menunggu atasan, {$pendingKetua->count()} menunggu ketua)");

        return self::SUCCESS;
    }
}
