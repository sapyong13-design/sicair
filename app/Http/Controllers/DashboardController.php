<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
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

        // Fitur 7: Chart data - leave requests by type
        $chartByType = LeaveRequest::selectRaw('type, count(*) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // Chart data - monthly trend
        $chartMonthly = LeaveRequest::selectRaw('MONTH(created_at) as bulan, count(*) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Chart data - status distribution
        $chartByStatus = LeaveRequest::selectRaw('status, count(*) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('dashboard', compact(
            'user', 'pendingRequests', 'recentDecisions', 'totalPegawai',
            'chartByType', 'chartMonthly', 'chartByStatus'
        ));
    }

    private function ketuaDashboard(User $user)
    {
        $needsDecision = LeaveRequest::with(['user', 'atasanReviewer'])
            ->where('status', LeaveRequest::STATUS_PERTIMBANGAN)
            ->latest()
            ->get();

        $directRequests = LeaveRequest::with('user')
            ->where('status', LeaveRequest::STATUS_DIAJUKAN)
            ->whereHas('user', function ($q) use ($user) {
                $q->where('atasan_id', $user->id);
            })
            ->latest()
            ->get();

        $recentDecisions = LeaveRequest::with('user')
            ->where('pejabat_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $leaveRequests = $user->leaveRequests()->latest()->get();

        return view('dashboard', compact('user', 'needsDecision', 'directRequests', 'recentDecisions', 'leaveRequests'));
    }

    private function atasanDashboard(User $user, Request $request)
    {
        $pendingReview = LeaveRequest::with('user')
            ->where('status', LeaveRequest::STATUS_DIAJUKAN)
            ->whereHas('user', function ($q) use ($user) {
                $q->where('atasan_id', $user->id);
            })
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

        return view('dashboard', compact('user', 'pendingReview', 'reviewedByMe', 'leaveRequests'));
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

        return view('dashboard', compact('user', 'leaveRequests', 'cutiInfo'));
    }
}
