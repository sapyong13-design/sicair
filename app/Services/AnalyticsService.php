<?php

namespace App\Services;

use App\Models\CutiRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\CacheKeys;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * Get dashboard analytics data for admin
     */
    public function getDashboardAnalytics(int $year = null): array
    {
        $year = $year ?? date('Y');

        return Cache::remember(CacheKeys::analyticsDashboard($year), 3600, function () use ($year) {
            return [
                'summary' => $this->getSummaryStats($year),
                'charts' => $this->getChartData($year),
                'topUsers' => $this->getTopUsersWithLeave($year),
                'upcomingLeaves' => $this->getUpcomingLeaves(),
            ];
        });
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
            ->sum(fn($lr) => $lr->total_hari_kerja ?? $lr->total_days ?? 0);

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

        $typeDisplayNames = LeaveRequest::typeLabels();
        foreach ($data as $item) {
            $labels[] = $typeDisplayNames[$item->type] ?? ucfirst(str_replace('_', ' ', $item->type));
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
     * Get monthly trend — uses a single query grouped by month instead of 36 queries.
     */
    private function getMonthlyTrend(int $year): array
    {
        $rows = LeaveRequest::selectRaw('CAST(strftime(\'%m\', created_at) AS INTEGER) as month, status, count(*) as total')
            ->whereYear('created_at', $year)
            ->groupBy('month', 'status')
            ->get()
            ->groupBy('month');

        $approvedStatuses = [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED];
        $pendingStatuses  = [LeaveRequest::STATUS_DIAJUKAN, LeaveRequest::STATUS_PERTIMBANGAN, LeaveRequest::STATUS_PENDING];
        $rejectedStatuses = [LeaveRequest::STATUS_DITOLAK, LeaveRequest::STATUS_REJECTED];

        $months = [];
        $approved = [];
        $pending = [];
        $rejected = [];

        for ($m = 1; $m <= 12; $m++) {
            $months[] = Carbon::createFromDate($year, $m, 1)->locale('id')->isoFormat('MMM');
            $monthRows = $rows->get($m, collect());

            $approved[] = $monthRows->whereIn('status', $approvedStatuses)->sum('total');
            $pending[]  = $monthRows->whereIn('status', $pendingStatuses)->sum('total');
            $rejected[] = $monthRows->whereIn('status', $rejectedStatuses)->sum('total');
        }

        return compact('months', 'approved', 'pending', 'rejected');
    }

    /**
     * Get leaves by department — group by unit_kerja via join.
     */
    private function getLeavesByDepartment(int $year): array
    {
        $data = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
            ->selectRaw('COALESCE(users.unit_kerja, \'Tidak Diketahui\') as dept, count(*) as total')
            ->whereYear('leave_requests.created_at', $year)
            ->groupBy('dept')
            ->orderByDesc('total')
            ->pluck('total', 'dept');

        $labels = $data->keys()->toArray();
        $values = $data->values()->toArray();

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
                // FIX #16: Use nip instead of email (email may not exist in users table)
                'nip' => $lr->user->nip,
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
                // FIX #21: Use correct column/accessor
                'days' => $lr->total_hari_kerja ?? $lr->total_days ?? 0,

            ])
            ->toArray();
    }

    /**
     * Get leave balance overview for all users
     */
    public function getLeaveBalanceOverview(int $year = null): array
    {
        $year = $year ?? date('Y');

        return Cache::remember(CacheKeys::analyticsBalanceOverview($year), 3600, function () use ($year) {
            $users = User::where('status_pegawai', '!=', 'cpns')
                ->with(['cutiRecords' => fn($q) => $q->where('tahun', $year)])
                ->get()
                ->map(function ($user) use ($year) {
                    $cutiRecord = $user->cutiRecords->first();

                    if (!$cutiRecord) {
                        return null;
                    }

                    $allocated = $cutiRecord->hak_cuti ?? 12;
                    $carryOver = $cutiRecord->carry_over ?? 0;
                    $used = $cutiRecord->cuti_diambil ?? 0;
                    $total = $allocated + $carryOver;
                    $remaining = $total - $used;

                    return [
                        'name' => $user->name,
                        'nip' => $user->nip,
                        'allocated' => $allocated,
                        'carry_over' => $carryOver,
                        'total' => $total,
                        'used' => $used,
                        'remaining' => $remaining,
                        'usage_percentage' => $total > 0 ? round(($used / $total) * 100) : 0,
                    ];
                })
                ->filter(fn($item) => $item !== null)
                ->values()
                ->toArray();

            return $users;
        });
    }

    /**
     * Sprint 7 #38: Rolling 12-month leave trend
     */
    public function getMonthlyTrend12(): array
    {
        $result = [];
        $now    = Carbon::now();

        for ($i = 11; $i >= 0; $i--) {
            $d     = $now->copy()->subMonths($i);
            $count = LeaveRequest::whereIn('status', [
                    LeaveRequest::STATUS_DISETUJUI,
                    LeaveRequest::STATUS_APPROVED,
                ])
                ->whereYear('start_date', $d->year)
                ->whereMonth('start_date', $d->month)
                ->count();
            $result[] = ['label' => $d->format('M Y'), 'count' => $count];
        }

        return $result;
    }

    /**
     * Sprint 7 #39: Comparison by Bagian (Kepaniteraan / Kesekretariatan / Hakim)
     * Uses User::getBagian() logic via SQL CASE WHEN
     */
    public function getByBagian(int $year): array
    {
        $rows = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
            ->selectRaw("
                CASE
                    WHEN users.role IN ('hakim','hakim_ad_hoc') THEN 'Hakim'
                    WHEN users.role = 'panitera' OR users.unit_kerja LIKE '%paniter%' THEN 'Kepaniteraan'
                    ELSE 'Kesekretariatan'
                END AS bagian,
                COUNT(*) as total_pengajuan,
                SUM(COALESCE(total_hari_kerja, CAST((julianday(end_date) - julianday(start_date)) AS INTEGER) + 1)) as total_hari
            ")
            ->whereIn('leave_requests.status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('leave_requests.start_date', $year)
            ->groupBy('bagian')
            ->get();

        $map = [
            'Kepaniteraan'    => ['bagian' => 'Kepaniteraan',    'total_requests' => 0, 'total_days' => 0],
            'Kesekretariatan' => ['bagian' => 'Kesekretariatan', 'total_requests' => 0, 'total_days' => 0],
            'Hakim'           => ['bagian' => 'Hakim',           'total_requests' => 0, 'total_days' => 0],
        ];
        foreach ($rows as $row) {
            $key = $row->bagian;
            if (!isset($map[$key])) continue;
            $map[$key]['total_requests'] = (int) $row->total_pengajuan;
            $map[$key]['total_days']     = (int) $row->total_hari;
        }

        return array_values($map);
    }

    /**
     * Sprint 7 #18: Monthly trend comparison — this month vs last month
     */
    public function getMonthTrend(): array
    {
        $thisMonth  = (int) date('m');
        $thisYear   = (int) date('Y');
        $lastMonth  = $thisMonth === 1 ? 12 : $thisMonth - 1;
        $lastYear   = $thisMonth === 1 ? $thisYear - 1 : $thisYear;

        $thisCount = LeaveRequest::whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('start_date', $thisYear)
            ->whereMonth('start_date', $thisMonth)
            ->count();

        $last = LeaveRequest::whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('start_date', $lastYear)
            ->whereMonth('start_date', $lastMonth)
            ->count();

        $diff = $last > 0 ? round((($thisCount - $last) / $last) * 100) : 0;

        return [
            'this_month' => $thisCount,
            'last_month' => $last,
            'diff_pct'   => $diff,
            'trending'   => $diff >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Sprint 7 #41: Heatmap of approved leaves per unit per month
     */
    public function getHeatmapByUnit(int $year): array
    {
        return Cache::remember(CacheKeys::analyticsHeatmapByUnit($year), 3600, function () use ($year) {
            $rows = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
                ->selectRaw("
                    users.unit_kerja,
                    CAST(strftime('%m', leave_requests.start_date) AS INTEGER) as month,
                    COUNT(*) as count
                ")
                ->whereIn('leave_requests.status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
                ->whereYear('leave_requests.start_date', $year)
                ->whereNotNull('users.unit_kerja')
                ->groupBy('users.unit_kerja', 'month')
                ->orderBy('users.unit_kerja')
                ->get();

            $units = $rows->pluck('unit_kerja')->unique()->values()->all();
            $data  = [];
            foreach ($units as $unit) {
                $months = array_fill(1, 12, 0);
                foreach ($rows->where('unit_kerja', $unit) as $row) {
                    $months[(int) $row->month] = (int) $row->count;
                }
                $data[] = [
                    'unit'   => $unit,
                    'months' => array_values($months),
                    'total'  => array_sum($months),
                ];
            }

            return $data;
        });
    }
}
