<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeputusanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isKetua()) {
            return $this->ketuaView($user, $request);
        }

        if ($user->isAtasan()) {
            return $this->atasanView($user, $request);
        }

        return $this->pegawaiView($user, $request);
    }

    private function ketuaView($user, Request $request)
    {
        $tab = $request->get('tab', 'menunggu');

        $menunggQuery = LeaveRequest::with(['user', 'atasanReviewer'])
            ->where('status', LeaveRequest::STATUS_PERTIMBANGAN)
            ->latest();

        $riwayatQuery = LeaveRequest::with(['user'])
            ->where('pejabat_id', $user->id)
            ->latest();

        if ($tab === 'menunggu') {
            $this->applyTypeAndDateFilters($menunggQuery, $request);
            $menunggu = $menunggQuery->paginate(15)->withQueryString();
            $riwayat  = collect();
        } else {
            $this->applyStatusFilter($riwayatQuery, $request);
            $this->applyTypeAndDateFilters($riwayatQuery, $request);
            $riwayat  = $riwayatQuery->paginate(15)->withQueryString();
            $menunggu = collect();
        }

        return view('keputusan.index', compact('user', 'tab', 'menunggu', 'riwayat'));
    }

    private function atasanView($user, Request $request)
    {
        $tab = $request->get('tab', 'review');

        $reviewQuery   = LeaveRequest::with(['user'])
            ->where('atasan_reviewer_id', $user->id)
            ->latest();

        $pengajuanQuery = $user->leaveRequests()->with(['user'])->latest();

        if ($tab === 'review') {
            $this->applyStatusFilter($reviewQuery, $request);
            $this->applyTypeAndDateFilters($reviewQuery, $request);
            $review    = $reviewQuery->paginate(15)->withQueryString();
            $pengajuan = collect();
        } else {
            $this->applyStatusFilter($pengajuanQuery, $request);
            $this->applyTypeAndDateFilters($pengajuanQuery, $request);
            $pengajuan = $pengajuanQuery->paginate(15)->withQueryString();
            $review    = collect();
        }

        return view('keputusan.index', compact('user', 'tab', 'review', 'pengajuan'));
    }

    private function pegawaiView($user, Request $request)
    {
        $pengajuanQuery = $user->leaveRequests()->with(['user'])->latest();

        $this->applyStatusFilter($pengajuanQuery, $request);
        $this->applyTypeAndDateFilters($pengajuanQuery, $request);

        $pengajuan = $pengajuanQuery->paginate(15)->withQueryString();

        return view('keputusan.index', compact('user', 'pengajuan'));
    }

    private function applyStatusFilter($query, Request $request): void
    {
        if (!$request->filled('status')) return;

        match ($request->status) {
            'disetujui'  => $query->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED]),
            'ditolak'    => $query->whereIn('status', [LeaveRequest::STATUS_DITOLAK, LeaveRequest::STATUS_REJECTED]),
            default      => $query->where('status', $request->status),
        };
    }

    private function applyTypeAndDateFilters($query, Request $request): void
    {
        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('start_date')) $query->where('start_date', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->where('end_date', '<=', $request->end_date);
    }
}
