<?php

namespace App\Services;

use App\Models\CutiRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get dashboard analytics data for admin
     */
    public function getDashboardAnalytics(int $year = null): array
    {
        $year = $year ?? date('Y');

        return [
            'summary' => $this->getSummaryStats($year),
            'charts' => $this->getChartData($year),
            'topUsers' => $this->getTopUsersWithLeave($year),
            'upcomingLeaves' => $this->getUpcomingLeaves(),
        ];
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStats(int $year): array
    {
        $approvedCount = LeaveRequest::whereIn('status', [
            LeaveRequest::STATUS_DISETUJUI,
            LeaveRequest::STATUS_APPROVED,
        ])->whereYear('created_at', $year)->count();

        $pendingCount = LeaveRequest::whereIn('status', [
            LeaveRequest::STATUS_DIAJUKAN,
            LeaveRequest::STATUS_PERTIMBANGAN,
            LeaveRequest::STATUS_PENDING,
        ])->whereYear('created_at', $year)->count();

        $rejectedCount = LeaveRequest::whereIn('status', [
            LeaveRequest::STATUS_DITOLAK,
            LeaveRequest::STATUS_REJECTED,
        ])->whereYear('created_at', $year)->count();

        $totalDaysApproved = LeaveRequest::whereIn('status', [
            LeaveRequest::STATUS_DISETUJUI,
            LeaveRequest::STATUS_APPROVED,
        ])
            ->whereYear('created_at', $year)
            ->get()
            ->sum(fn($lr) => $lr->total_hari_kerja ?? $lr->number_of_days);

        return [
            'total_requests' => $approvedCount + $pendingCount + $rejectedCount,
            'approved' => $approvedCount,
            'pending' => $pendingCount,
            'rejected' => $rejectedCount,
            'total_days_approved' => $totalDaysApproved,
            'approval_rate' => $approvedCount > 0 ? round(($approvedCount / ($approvedCount + $rejectedCount)) * 100) : 0,
        ];
    }

    /**
     * Get chart data
     */
    private function getChartData(int $year): array
    {
        return [
            'by_type' => $this->getLeavesByType($year),
            'by_status' => $this->getLeavesByStatus($year),
            'monthly_trend' => $this->getMonthlyTrend($year),
            'by_department' => $this->getLeavesByDepartment($year),
        ];
    }

    /**
     * Get leaves by type
     */
    private function getLeavesByType(int $year): array
    {
        $data = LeaveRequest::selectRaw('type, count(*) as total')
            ->whereYear('created_at', $year)
            ->groupBy('type')
            ->get();

        $labels = [];
        $values = [];
        $colors = [];
        $colorMap = [
            'cuti_tahunan' => '#3b82f6',
            'cuti_besar' => '#10b981',
            'cuti_sakit' => '#ef4444',
            'cuti_melahirkan' => '#f59e0b',
            'cuti_alasan_penting' => '#8b5cf6',
            'cuti_luar_tanggungan' => '#06b6d4',
        ];

        foreach ($data as $item) {
            $labels[] = str_replace('cuti_', '', $item->type);
            $values[] = $item->total;
            $colors[] = $colorMap[$item->type] ?? '#6366f1';
        }

        return compact('labels', 'values', 'colors');
    }

    /**
     * Get leaves by status
     */
    private function getLeavesByStatus(int $year): array
    {
        $data = LeaveRequest::selectRaw('status, count(*) as total')
            ->whereYear('created_at', $year)
            ->groupBy('status')
            ->get();

        $labels = [];
        $values = [];
        $colors = [];
        $colorMap = [
            'diajukan' => '#60a5fa',
            'pertimbangan_atasan' => '#fbbf24',
            'disetujui' => '#34d399',
            'ditolak' => '#f87171',
            'diubah' => '#a78bfa',
            'ditangguhkan' => '#fb923c',
        ];

        foreach ($data as $item) {
            $labels[] = ucfirst(str_replace('_', ' ', $item->status));
            $values[] = $item->total;
            $colors[] = $colorMap[$item->status] ?? '#6366f1';
        }

        return compact('labels', 'values', 'colors');
    }

    /**
     * Get monthly trend
     */
    private function getMonthlyTrend(int $year): array
    {
        $months = [];
        $approved = [];
        $pending = [];
        $rejected = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[] = Carbon::createFromDate($year, $month, 1)->format('M');

            $approvedCount = LeaveRequest::whereIn('status', [
                LeaveRequest::STATUS_DISETUJUI,
                LeaveRequest::STATUS_APPROVED,
            ])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $pendingCount = LeaveRequest::whereIn('status', [
                LeaveRequest::STATUS_DIAJUKAN,
                LeaveRequest::STATUS_PERTIMBANGAN,
            ])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $rejectedCount = LeaveRequest::whereIn('status', [
                LeaveRequest::STATUS_DITOLAK,
                LeaveRequest::STATUS_REJECTED,
            ])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $approved[] = $approvedCount;
            $pending[] = $pendingCount;
            $rejected[] = $rejectedCount;
        }

        return compact('months', 'approved', 'pending', 'rejected');
    }

    /**
     * Get leaves by department
     */
    private function getLeavesByDepartment(int $year): array
    {
        $data = LeaveRequest::with('user')
            ->whereYear('created_at', $year)
            ->get()
            ->groupBy(fn($lr) => $lr->user->department ?? 'Unknown')
            ->map(fn($group) => $group->count());

        $labels = array_keys($data->toArray());
        $values = array_values($data->toArray());

        return compact('labels', 'values');
    }

    /**
     * Get top users with most leave requests
     */
    private function getTopUsersWithLeave(int $year, int $limit = 5): array
    {
        return LeaveRequest::with('user')
            ->whereYear('created_at', $year)
            ->select('user_id')
            ->selectRaw('count(*) as leave_count')
            ->selectRaw('sum(COALESCE(total_hari_kerja, CAST((julianday(end_date) - julianday(start_date)) AS INTEGER))) as total_days')
            ->groupBy('user_id')
            ->orderByDesc('leave_count')
            ->take($limit)
            ->with('user')
            ->get()
            ->map(fn($lr) => [
                'name' => $lr->user->name,
                'email' => $lr->user->email,
                'leave_count' => $lr->leave_count,
                'total_days' => (int)$lr->total_days,
            ])
            ->toArray();
    }

    /**
     * Get upcoming leaves (next 30 days)
     */
    private function getUpcomingLeaves(int $limit = 5): array
    {
        $today = Carbon::today();
        $thirtyDaysFromNow = $today->copy()->addDays(30);

        return LeaveRequest::with('user')
            ->whereIn('status', [
                LeaveRequest::STATUS_DISETUJUI,
                LeaveRequest::STATUS_APPROVED,
            ])
            ->whereBetween('start_date', [$today, $thirtyDaysFromNow])
            ->orderBy('start_date')
            ->take($limit)
            ->get()
            ->map(fn($lr) => [
                'name' => $lr->user->name,
                'type' => $lr->type,
                'start_date' => $lr->start_date->format('d M Y'),
                'end_date' => $lr->end_date->format('d M Y'),
                'days' => $lr->number_of_days,
            ])
            ->toArray();
    }

    /**
     * Get leave balance overview for all users
     */
    public function getLeaveBalanceOverview(int $year = null): array
    {
        $year = $year ?? date('Y');

        $users = User::where('status', 'aktif')
            ->with('cutiRecords')
            ->get()
            ->map(function ($user) use ($year) {
                $cutiRecord = $user->cutiRecords()
                    ->where('tahun', $year)
                    ->where('jenis_cuti', 'Cuti Tahunan')
                    ->first();

                if (!$cutiRecord) {
                    return null;
                }

                $allocated = $cutiRecord->alokasi_awal ?? 12;
                $carryOver = $cutiRecord->carry_over ?? 0;
                $used = $cutiRecord->digunakan ?? 0;
                $total = $allocated + $carryOver;
                $remaining = $total - $used;

                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'allocated' => $allocated,
                    'carry_over' => $carryOver,
                    'total' => $total,
                    'used' => $used,
                    'remaining' => $remaining,
                    'usage_percentage' => round(($used / $total) * 100),
                ];
            })
            ->filter(fn($item) => $item !== null)
            ->values()
            ->toArray();

        return $users;
    }
}
