<?php

namespace App\Http\Controllers;

use App\Models\CutiRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\CutiTahunanCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->adminDashboard($user);
        }

        if ($user->isKetua()) {
            return $this->ketuaDashboard($user);
        }

        if ($user->isAtasan()) {
            return $this->atasanDashboard($user, $request);
        }

        return $this->pegawaiDashboard($user, $request);
    }

    private function adminDashboard(User $user)
    {
        $analyticsService = new AnalyticsService();
        $year = date('Y');
        $today = \Carbon\Carbon::today();

        $totalPegawai = User::count();
        $pendingRequests = LeaveRequest::with('user')
            ->whereIn('status', [
                LeaveRequest::STATUS_DIAJUKAN,
                LeaveRequest::STATUS_PERTIMBANGAN,
                LeaveRequest::STATUS_PENDING,
            ])
            ->latest()
            ->get();

        $recentDecisions = LeaveRequest::with('user')
            ->whereIn('status', [
                LeaveRequest::STATUS_DISETUJUI,
                LeaveRequest::STATUS_DITOLAK,
                LeaveRequest::STATUS_APPROVED,
                LeaveRequest::STATUS_REJECTED,
            ])
            ->latest()
            ->take(10)
            ->get();

        // #10 Widget: Who's on leave today
        $todayOnLeave = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();

        // #11 Widget: Upcoming leaves in 7 days
        $upcomingLeaves7Days = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->where('start_date', '>', $today)
            ->where('start_date', '<=', $today->copy()->addDays(7))
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // Get comprehensive analytics
        $analytics = $analyticsService->getDashboardAnalytics($year);
        $leaveBalances = $analyticsService->getLeaveBalanceOverview($year);

        $chartByDepartment = $analytics['charts']['by_department']['values'] ?? [];

        // Sprint 3 #16: Pegawai with low leave balance (≤ 3)
        $saldoRendah = User::where('leave_balance', '<=', 3)->orderBy('leave_balance')->take(5)->get();

        // Sprint 3 #17: Carry-over akan hangus (only show Oct-Dec)
        $carryOverHangus = [];
        if ((int) date('m') >= 10) {
            $carryOverHangus = \App\Models\CutiRecord::where('tahun', $year - 1)
                ->where('carry_over', '>', 0)
                ->with('user')
                ->get();
        }

        // Sprint 3 #18: Trend indicator (this month vs last month)
        $monthTrend = $analyticsService->getMonthTrend();

        // Sprint 3 #19: Top 5 pegawai paling banyak cuti tahun ini
        $top5Cuti = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
            ->selectRaw("users.id, users.name, SUM(COALESCE(total_hari_kerja, CAST((julianday(end_date) - julianday(start_date)) AS INTEGER) + 1)) as total_hari")
            ->whereIn('leave_requests.status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('leave_requests.start_date', $year)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_hari')
            ->take(5)
            ->get();

        // Sprint 3 #21: Recent activity feed (5 latest leave requests)
        $recentActivity = LeaveRequest::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'user', 'pendingRequests', 'recentDecisions', 'totalPegawai',
            'analytics', 'leaveBalances', 'year', 'chartByDepartment',
            'todayOnLeave', 'upcomingLeaves7Days',
            'saldoRendah', 'carryOverHangus', 'monthTrend', 'top5Cuti', 'recentActivity'
        ));
    }

    private function ketuaDashboard(User $user)
    {
        // Semua pengajuan yang perlu keputusan Ketua:
        // - Dari bawahan langsung (Hakim/Panitera/Sekretaris) → langsung STATUS_PERTIMBANGAN
        // - Dari staff yang sudah di-review atasan → juga STATUS_PERTIMBANGAN
        $needsDecision = LeaveRequest::with(['user', 'atasanReviewer'])
            ->where('status', LeaveRequest::STATUS_PERTIMBANGAN)
            ->distinct()
            ->latest()
            ->get();

        $recentDecisions = LeaveRequest::with('user')
            ->where('pejabat_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $leaveRequests = $user->leaveRequests()->latest()->get();

        // Widget: Siapa yang cuti hari ini
        $today = \Carbon\Carbon::today();
        $todayOnLeave = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();

        return view('dashboard', compact('user', 'needsDecision', 'recentDecisions', 'leaveRequests', 'todayOnLeave'));
    }

    private function atasanDashboard(User $user, Request $request)
    {
        $pendingReview = LeaveRequest::with('user')
            ->where('status', LeaveRequest::STATUS_DIAJUKAN)
            ->whereHas('user', function ($q) use ($user) {
                $q->where('atasan_id', $user->id);
            })
            ->distinct()
            ->latest()
            ->get();

        $reviewedByMe = LeaveRequest::with('user')
            ->where('atasan_reviewer_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        // Fitur 6: Search & filter for atasan's own leave
        $leaveQuery = $user->leaveRequests()->latest();

        if ($request->filled('search')) {
            $leaveQuery->where('reason', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $leaveQuery->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $leaveQuery->where('status', $request->status);
        }

        $leaveRequests = $leaveQuery->get();

        // Widget: Siapa yang cuti hari ini
        $today = \Carbon\Carbon::today();
        $todayOnLeave = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();

        return view('dashboard', compact('user', 'pendingReview', 'reviewedByMe', 'leaveRequests', 'todayOnLeave'));
    }

    private function pegawaiDashboard(User $user, Request $request)
    {
        // Fitur 6: Search & Filter
        $leaveQuery = $user->leaveRequests()->latest();

        if ($request->filled('search')) {
            $leaveQuery->where('reason', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $leaveQuery->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $leaveQuery->where('status', $request->status);
        }

        $leaveRequests = $leaveQuery->get();

        $cutiInfo = null;
        if ($user->sudahBekerjaSatuTahun()) {
            $calculator = new CutiTahunanCalculator($user);
            $cutiInfo = $calculator->hitung();
        }

        // Siapa yang cuti & dinas luar hari ini (seluruh pegawai, bukan hanya diri sendiri)
        $today = \Carbon\Carbon::today()->toDateString();
        $todayOnLeave = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->orderBy('start_date')
            ->get();

        $todayDinasLuar = \App\Models\DinasLuar::with('user')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->orderBy('start_date')
            ->get();

        return view('dashboard', compact('user', 'leaveRequests', 'cutiInfo', 'todayOnLeave', 'todayDinasLuar'));
    }
}
