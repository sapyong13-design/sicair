<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class BalanceAuditService
{
    /**
     * Log leave balance change
     */
    public static function logBalanceChange(
        User $user,
        int $previousBalance,
        int $newBalance,
        string $reason,
        ?int $leaveRequestId = null
    ): void {
        if ($previousBalance === $newBalance) {
            return;
        }

        $change = $newBalance - $previousBalance;
        $description = $reason;

        if ($leaveRequestId) {
            $description .= " (Pengajuan ID: {$leaveRequestId})";
        }

        AuditLog::log(
            'update',
            'LeaveBalance',
            $user->id,
            ['balance' => $previousBalance],
            ['balance' => $newBalance],
            $description
        );
    }

    /**
     * Get balance history for a user (STB-optimized - limit results)
     */
    public static function getBalanceHistory(User $user, $limit = 50)
    {
        return AuditLog::where('user_id', $user->id)
            ->where('model', 'LeaveBalance')
            ->where('action', 'update')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get balance change for specific period
     */
    public static function getBalanceChangeInPeriod(User $user, $startDate, $endDate)
    {
        return AuditLog::where('user_id', $user->id)
            ->where('model', 'LeaveBalance')
            ->where('action', 'update')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest('created_at')
            ->get();
    }

    /**
     * Calculate total balance change for a user
     */
    public static function calculateTotalChange(User $user, $startDate = null, $endDate = null)
    {
        $query = AuditLog::where('user_id', $user->id)
            ->where('model', 'LeaveBalance')
            ->where('action', 'update');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $logs = $query->get();
        $totalChange = 0;

        foreach ($logs as $log) {
            if ($log->new_values && isset($log->new_values['balance'])) {
                $totalChange += $log->new_values['balance'] - ($log->old_values['balance'] ?? 0);
            }
        }

        return $totalChange;
    }
}
