<?php

namespace App\Http\Controllers;

use App\Models\LeaveAppeal;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Services\BalanceAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppealController extends Controller
{
    /**
     * Show create form for appeal
     */
    public function create(LeaveRequest $leaveRequest)
    {
        // Only rejected leaves can be appealed
        if (!$leaveRequest->isRejected()) {
            return back()->with('error', 'Hanya pengajuan cuti yang ditolak yang dapat diajukan banding.');
        }

        // Only the requester can appeal
        if (Auth::id() !== $leaveRequest->user_id) {
            return back()->with('error', 'Anda hanya dapat mengajukan banding untuk pengajuan cuti Anda sendiri.');
        }

        // Check if appeal already exists
        $existingAppeal = $leaveRequest->appeals()->where('status', LeaveAppeal::STATUS_PENDING)->first();
        if ($existingAppeal) {
            return back()->with('error', 'Sudah ada banding yang menunggu pertimbangan untuk pengajuan ini.');
        }

        return view('appeals.create', compact('leaveRequest'));
    }

    /**
     * Store appeal request
     */
    public function store(Request $request, LeaveRequest $leaveRequest)
    {
        // Validation
        if (!$leaveRequest->isRejected()) {
            abort(422, 'Hanya pengajuan yang ditolak yang dapat diajukan banding.');
        }

        if (Auth::id() !== $leaveRequest->user_id) {
            abort(403);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
            'additional_info' => 'nullable|string|max:1000',
        ]);

        // Create appeal
        $appeal = LeaveAppeal::create([
            'leave_request_id' => $leaveRequest->id,
            'appealed_by' => Auth::id(),
            'reason' => $request->reason,
            'additional_info' => $request->additional_info,
            'status' => LeaveAppeal::STATUS_PENDING,
        ]);

        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'file_leave_appeal',
            'description' => "File appeal for rejected leave request #{$leaveRequest->id}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Notify admin/ketua
        $notifyUsers = \App\Models\User::whereIn('role', ['admin', 'ketua'])->pluck('id')->toArray();
        foreach ($notifyUsers as $userId) {
            Notification::kirim(
                $userId,
                'Banding Pengajuan Cuti Baru',
                "{$leaveRequest->user->name} mengajukan banding untuk pengajuan cuti yang ditolak.",
                Notification::TYPE_CUTI_PERTIMBANGAN,
                route('appeal.show', $appeal)
            );
        }

        return redirect()->route('leave.show', $leaveRequest)
            ->with('success', 'Banding pengajuan cuti berhasil dikirim.');
    }

    /**
     * Show appeal detail
     */
    public function show(LeaveAppeal $appeal)
    {
        $user = Auth::user();

        // Check authorization
        if ($appeal->appealed_by !== $user->id && !$user->isAdmin() && !$user->isKetua()) {
            abort(403);
        }

        return view('appeals.show', compact('appeal'));
    }

    /**
     * Approve appeal (grant leave)
     */
    public function approve(Request $request, LeaveAppeal $appeal)
    {
        $this->authorizePejabat();

        if (!$appeal->isPending()) {
            return back()->with('error', 'Banding ini sudah diproses.');
        }

        $request->validate([
            'decision_note' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $leaveRequest = $appeal->leaveRequest;

        DB::transaction(function () use ($appeal, $leaveRequest, $user, $request) {
            // Update appeal
            $appeal->update([
                'status' => LeaveAppeal::STATUS_APPROVED,
                'decided_by' => $user->id,
                'decision' => LeaveAppeal::DECISION_APPROVED,
                'decision_note' => $request->decision_note,
                'decided_at' => now(),
            ]);

            // Update leave request - mark as approved after appeal
            $leaveRequest->update([
                'status' => LeaveRequest::STATUS_DISETUJUI,
                'pejabat_id' => $user->id,
                'keputusan_pejabat' => 'setuju',
                'catatan_pejabat' => "Persetujuan hasil banding: {$request->decision_note}",
                'decided_at' => now(),
            ]);

            // FIX #1: Deduct leave_balance for annual leave when appeal is approved
            if ($leaveRequest->type === LeaveRequest::TYPE_TAHUNAN) {
                $leaveUser = \App\Models\User::lockForUpdate()->find($leaveRequest->user_id);
                $totalDays = $leaveRequest->total_hari_kerja ?? 0;
                $previousBalance = $leaveUser->leave_balance;
                $leaveUser->decrement('leave_balance', $totalDays);

                BalanceAuditService::logBalanceChange(
                    $leaveUser,
                    $previousBalance,
                    $previousBalance - $totalDays,
                    "Banding disetujui — pengajuan {$leaveRequest->type_label}",
                    $leaveRequest->id
                );
            }

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'approve_appeal',
                'description' => "Approve appeal for {$leaveRequest->user->name} - leave now approved",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        // Notify appellant
        Notification::kirim(
            $appeal->appealed_by,
            'Banding Disetujui - Cuti Disetujui',
            'Banding Anda disetujui. Pengajuan cuti Anda sekarang disetujui.',
            Notification::TYPE_CUTI_DISETUJUI,
            route('leave.show', $leaveRequest)
        );

        return back()->with('success', 'Banding disetujui dan pengajuan cuti disetujui.');
    }

    /**
     * Deny appeal (reject remains)
     */
    public function deny(Request $request, LeaveAppeal $appeal)
    {
        $this->authorizePejabat();

        if (!$appeal->isPending()) {
            return back()->with('error', 'Banding ini sudah diproses.');
        }

        $request->validate([
            'decision_note' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        // Update appeal
        $appeal->update([
            'status' => LeaveAppeal::STATUS_REJECTED,
            'decided_by' => $user->id,
            'decision' => LeaveAppeal::DECISION_DENIED,
            'decision_note' => $request->decision_note,
            'decided_at' => now(),
        ]);

        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'deny_appeal',
            'description' => "Deny appeal for {$appeal->leaveRequest->user->name} - rejection stands",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Notify appellant
        Notification::kirim(
            $appeal->appealed_by,
            'Banding Ditolak',
            'Banding Anda ditolak. Pengajuan cuti tetap ditolak. Alasan: ' . $request->decision_note,
            Notification::TYPE_CUTI_DITOLAK,
            route('leave.show', $appeal->leaveRequest)
        );

        return back()->with('success', 'Banding ditolak. Pengajuan tetap ditolak.');
    }

    /**
     * List all appeals (admin only)
     */
    public function index(Request $request)
    {
        $this->authorizePejabat();

        $query = LeaveAppeal::with('leaveRequest', 'appellant', 'decider');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // FIX #28: Add search by appellant name or NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('appellant', fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
            );
        }

        $appeals = $query->latest()->paginate(20);

        return view('appeals.index', compact('appeals'));
    }

    /**
     * Check if user is pejabat/admin
     */
    private function authorizePejabat(): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isKetua()) {
            abort(403, 'Unauthorized');
        }
    }
}
