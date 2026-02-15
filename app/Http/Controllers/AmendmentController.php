<?php

namespace App\Http\Controllers;

use App\Models\LeaveAmendment;
use App\Models\LeaveRequest;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AmendmentController extends Controller
{
    /**
     * Request amendment for a leave request
     */
    public function create(LeaveRequest $leaveRequest)
    {
        // Only the employee who submitted can request amendment
        if (Auth::id() !== $leaveRequest->user_id) {
            return back()->with('error', 'Anda hanya dapat mengajukan perubahan untuk pengajuan cuti Anda sendiri.');
        }

        // Can only amend if in certain statuses
        if (!in_array($leaveRequest->status, ['diajukan', 'pertimbangan_atasan'])) {
            return back()->with('error', 'Pengajuan cuti tidak dapat diubah pada status ini.');
        }

        // Check if there's already a pending amendment
        $pendingAmendment = $leaveRequest->amendments()
            ->where('status', LeaveAmendment::STATUS_PENDING)
            ->first();

        if ($pendingAmendment) {
            return back()->with('error', 'Sudah ada perubahan yang menunggu persetujuan.');
        }

        return view('amendments.create', compact('leaveRequest'));
    }

    /**
     * Store amendment request
     */
    public function store(Request $request, LeaveRequest $leaveRequest)
    {
        // Authorization
        if (Auth::id() !== $leaveRequest->user_id) {
            abort(403);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Create amendment request
        $amendment = LeaveAmendment::create([
            'leave_request_id' => $leaveRequest->id,
            'requested_by' => Auth::id(),
            'original_start_date' => $leaveRequest->start_date,
            'original_end_date' => $leaveRequest->end_date,
            'requested_start_date' => $startDate,
            'requested_end_date' => $endDate,
            'reason' => $request->reason,
            'status' => LeaveAmendment::STATUS_PENDING,
        ]);

        // Notify atasan
        if ($leaveRequest->user->atasan_id) {
            Notification::kirim(
                $leaveRequest->user->atasan_id,
                'Perubahan Pengajuan Cuti',
                "{$leaveRequest->user->name} meminta perubahan tanggal pada pengajuan cuti {$leaveRequest->type}",
                Notification::TYPE_CUTI_PERTIMBANGAN,
                route('amendment.show', $amendment)
            );
        }

        return redirect()->route('leave.show', $leaveRequest)
            ->with('success', 'Permohonan perubahan cuti berhasil dikirim.');
    }

    /**
     * Show amendment detail
     */
    public function show(LeaveAmendment $amendment)
    {
        $this->authorize($amendment);

        $amendment->load(['leaveRequest', 'requester', 'approver']);

        return view('amendments.show', compact('amendment'));
    }

    /**
     * Approve amendment
     */
    public function approve(Request $request, LeaveAmendment $amendment)
    {
        $this->authorize($amendment);

        if (!$amendment->isPending()) {
            return back()->with('error', 'Perubahan ini sudah diproses.');
        }

        $request->validate([
            'approval_note' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        // Update amendment status
        $amendment->update([
            'status' => LeaveAmendment::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approval_note' => $request->approval_note,
            'approved_at' => now(),
        ]);

        // Update leave request dates
        $leaveRequest = $amendment->leaveRequest;
        $leaveRequest->update([
            'start_date' => $amendment->requested_start_date,
            'end_date' => $amendment->requested_end_date,
        ]);

        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'approve_amendment',
            'description' => "Persetujuan perubahan tanggal cuti untuk {$leaveRequest->user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Notify requester
        Notification::kirim(
            $amendment->requester->id,
            'Perubahan Cuti Disetujui',
            'Permohonan perubahan cuti Anda telah disetujui oleh ' . $user->name,
            Notification::TYPE_CUTI_DISETUJUI,
            route('leave.show', $leaveRequest)
        );

        return back()->with('success', 'Perubahan cuti telah disetujui.');
    }

    /**
     * Reject amendment
     */
    public function reject(Request $request, LeaveAmendment $amendment)
    {
        $this->authorize($amendment);

        if (!$amendment->isPending()) {
            return back()->with('error', 'Perubahan ini sudah diproses.');
        }

        $request->validate([
            'approval_note' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        // Update amendment status
        $amendment->update([
            'status' => LeaveAmendment::STATUS_REJECTED,
            'approved_by' => $user->id,
            'approval_note' => $request->approval_note,
            'approved_at' => now(),
        ]);

        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'reject_amendment',
            'description' => "Penolakan perubahan tanggal cuti untuk {$amendment->leaveRequest->user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Notify requester
        Notification::kirim(
            $amendment->requester->id,
            'Perubahan Cuti Ditolak',
            'Permohonan perubahan cuti Anda ditolak. Alasan: ' . $request->approval_note,
            Notification::TYPE_CUTI_DITOLAK,
            route('leave.show', $amendment->leaveRequest)
        );

        return back()->with('success', 'Perubahan cuti telah ditolak.');
    }

    /**
     * Authorization check
     */
    private function authorize(LeaveAmendment $amendment): void
    {
        $user = Auth::user();

        // Can be approved by atasan, ketua, or admin
        if (!$user->isAdmin() && !$user->isKetua() &&
            $amendment->leaveRequest->user->atasan_id !== $user->id) {
            abort(403, 'Unauthorized to approve this amendment');
        }
    }
}
