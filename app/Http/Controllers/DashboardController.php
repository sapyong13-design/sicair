<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\CutiTahunanCalculator;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Admin: kelola pegawai, rekapitulasi, lihat semua data
        if ($user->isAdmin()) {
            return $this->adminDashboard($user);
        }

        // Ketua PN: keputusan final approval
        if ($user->isKetua()) {
            return $this->ketuaDashboard($user);
        }

        // Atasan: pertimbangan level 1
        if ($user->isAtasan()) {
            return $this->atasanDashboard($user);
        }

        // Pegawai: ajukan cuti, lihat riwayat
        return $this->pegawaiDashboard($user);
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

        return view('dashboard', compact('user', 'pendingRequests', 'recentDecisions', 'totalPegawai'));
    }

    private function ketuaDashboard(User $user)
    {
        // Pengajuan yang butuh keputusan Ketua (sudah dipertimbangkan atasan)
        $needsDecision = LeaveRequest::with(['user', 'atasanReviewer'])
            ->where('status', LeaveRequest::STATUS_PERTIMBANGAN)
            ->latest()
            ->get();

        // Pengajuan baru langsung ke ketua (jika tidak punya atasan)
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

    private function atasanDashboard(User $user)
    {
        // Pengajuan bawahan yang perlu pertimbangan
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

        $leaveRequests = $user->leaveRequests()->latest()->get();

        return view('dashboard', compact('user', 'pendingReview', 'reviewedByMe', 'leaveRequests'));
    }

    private function pegawaiDashboard(User $user)
    {
        $leaveRequests = $user->leaveRequests()->latest()->get();

        $cutiInfo = null;
        if ($user->sudahBekerjaSatuTahun()) {
            $calculator = new CutiTahunanCalculator($user);
            $cutiInfo = $calculator->hitung();
        }

        return view('dashboard', compact('user', 'leaveRequests', 'cutiInfo'));
    }
}
