<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            $this->applyNameFilter($menunggQuery, $request);
            $menunggu = $menunggQuery->paginate(15)->withQueryString();
            $riwayat  = collect();
        } else {
            $this->applyStatusFilter($riwayatQuery, $request);
            $this->applyTypeAndDateFilters($riwayatQuery, $request);
            $this->applyNameFilter($riwayatQuery, $request);
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
            $this->applyNameFilter($reviewQuery, $request);
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

    private function applyNameFilter($query, Request $request): void
    {
        if (!$request->filled('q')) return;
        $q = $request->q;
        $query->whereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
    }

    public function bulkKeputusan(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'ids'       => 'required|array|min:1|max:50',
            'ids.*'     => 'integer|exists:leave_requests,id',
            'keputusan' => 'required|in:disetujui,ditolak,ditangguhkan',
        ]);

        // Akses sudah dijamin middleware route
        $user = Auth::user();

        $statusMap = [
            'disetujui'    => LeaveRequest::STATUS_DISETUJUI,
            'ditolak'      => LeaveRequest::STATUS_DITOLAK,
            'ditangguhkan' => LeaveRequest::STATUS_DITANGGUHKAN,
        ];

        $leaves = LeaveRequest::whereIn('id', $request->ids)
            ->where('status', LeaveRequest::STATUS_PERTIMBANGAN)
            ->get();

        $processed = 0;
        foreach ($leaves as $leave) {
            DB::transaction(function () use ($leave, $user, $request, $statusMap) {
                $leave->update([
                    'status'            => $statusMap[$request->keputusan],
                    'pejabat_id'        => $user->id,
                    'keputusan_pejabat' => $request->keputusan,
                    'decided_at'        => now(),
                ]);

                if ($request->keputusan === 'disetujui') {
                    if (in_array($leave->type, [
                        LeaveRequest::TYPE_TAHUNAN,
                        LeaveRequest::TYPE_BERSAMA,
                    ])) {
                        $leave->user->decrement('leave_balance', $leave->total_hari_kerja ?? 1);
                    }
                }
            });
            $processed++;
        }

        return redirect()->route('keputusan.index')
            ->with('success', "Bulk keputusan berhasil: $processed pengajuan diproses.");
    }
}
