<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        $prevYear = $year - 1;

        $analyticsService = new AnalyticsService();

        // Current year analytics
        $analytics = $analyticsService->getDashboardAnalytics($year);
        $leaveBalances = $analyticsService->getLeaveBalanceOverview($year);

        // Chart data
        $chartByType = $analytics['charts']['by_type'] ?? [];
        $chartByStatus = $analytics['charts']['by_status'] ?? [];
        $chartMonthly = $analytics['charts']['monthly_trend'] ?? [];
        $chartByDepartment = $analytics['charts']['by_department'] ?? [];
        $topUsers = $analytics['topUsers'] ?? [];
        $upcomingLeaves = $analytics['upcomingLeaves'] ?? [];

        // #41 Year comparison
        $prevAnalytics = $analyticsService->getDashboardAnalytics($prevYear);
        $prevSummary = $prevAnalytics['summary'] ?? [];

        // #42 Heatmap data: per user per month
        $heatmapData = $this->getHeatmapData($year);

        // Available years for filter
        $availableYears = range(date('Y'), date('Y') - 3);

        return view('analytics.index', compact(
            'year', 'prevYear', 'analytics', 'leaveBalances',
            'chartByType', 'chartByStatus', 'chartMonthly', 'chartByDepartment',
            'topUsers', 'upcomingLeaves', 'prevSummary', 'heatmapData', 'availableYears'
        ));
    }

    /**
     * #43 Export annual report as CSV
     */
    public function exportAnnual(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));

        $leaves = LeaveRequest::with('user')
            ->whereYear('created_at', $year)
            ->orderBy('created_at')
            ->get();

        $csv = "No,Nama,NIP,Jabatan,Jenis Cuti,Tanggal Mulai,Tanggal Selesai,Hari Kerja,Status,Catatan\n";
        $no = 1;
        foreach ($leaves as $l) {
            $csv .= implode(',', [
                $no++,
                '"' . str_replace('"', '""', $l->user->name) . '"',
                $l->user->nip,
                '"' . str_replace('"', '""', $l->user->jabatan ?? '') . '"',
                '"' . $l->type_label . '"',
                $l->start_date->format('d/m/Y'),
                $l->end_date->format('d/m/Y'),
                $l->total_hari_kerja ?? $l->total_days,
                $l->status_label,
                '"' . str_replace('"', '""', $l->catatan_pejabat ?? $l->catatan_atasan ?? '') . '"',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"laporan-cuti-{$year}.csv\"",
        ]);
    }

    /**
     * #42 Heatmap: leaves per user per month
     */
    private function getHeatmapData(int $year): array
    {
        $rows = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
            ->selectRaw("users.name, users.id as user_id, CAST(strftime('%m', leave_requests.start_date) AS INTEGER) as month, sum(COALESCE(total_hari_kerja, CAST((julianday(end_date) - julianday(start_date)) AS INTEGER) + 1)) as total_days")
            ->whereIn('leave_requests.status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('leave_requests.start_date', $year)
            ->groupBy('users.id', 'users.name', 'month')
            ->orderBy('users.name')
            ->get();

        $users = $rows->pluck('name', 'user_id')->unique();
        $data = [];
        foreach ($users as $uid => $name) {
            $months = array_fill(1, 12, 0);
            foreach ($rows->where('user_id', $uid) as $row) {
                $months[(int)$row->month] = (int)$row->total_days;
            }
            $data[] = [
                'name' => $name,
                'months' => array_values($months),
                'total' => array_sum($months),
            ];
        }

        return $data;
    }
}
