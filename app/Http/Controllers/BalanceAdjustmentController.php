<?php

namespace App\Http\Controllers;

use App\Models\BalanceAdjustment;
use App\Models\CutiRecord;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceAdjustmentController extends Controller
{
    /**
     * List all balance adjustments (admin only)
     */
    public function index(Request $request)
    {
        $this->authorize('admin');

        $query = BalanceAdjustment::with('user', 'approver');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Search by user name
        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            );
        }

        $adjustments = $query->latest()->paginate(20);
        $years = range(date('Y') - 2, date('Y') + 1);

        return view('balance-adjustments.index', compact('adjustments', 'years'));
    }

    /**
     * Show create form for balance adjustment
     */
    public function create(User $user)
    {
        $this->authorize('admin');

        $currentYear = date('Y');
        $cutiRecord = CutiRecord::where('user_id', $user->id)
            ->where('tahun', $currentYear)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->first();

        return view('balance-adjustments.create', compact('user', 'cutiRecord', 'currentYear'));
    }

    /**
     * Store balance adjustment
     */
    public function store(Request $request, User $user)
    {
        $this->authorize('admin');

        $request->validate([
            'year' => 'required|integer|min:' . (date('Y') - 5) . '|max:' . (date('Y') + 2),
            'type' => 'required|in:addition,deduction,correction',
            'adjustment_days' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:500',
        ]);

        $adjustmentDays = $request->adjustment_days;

        // Validate deduction doesn't exceed current balance
        if ($request->type === BalanceAdjustment::TYPE_DEDUCTION && $adjustmentDays > 0) {
            $adjustmentDays = -$adjustmentDays; // Make it negative
        }

        // Create adjustment
        $adjustment = BalanceAdjustment::create([
            'user_id' => $user->id,
            'year' => $request->year,
            'jenis_cuti' => 'Cuti Tahunan',
            'adjustment_days' => $adjustmentDays,
            'reason' => $request->reason,
            'type' => $request->type,
            'status' => BalanceAdjustment::STATUS_PENDING,
        ]);

        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'create_balance_adjustment',
            'description' => "Create balance adjustment for {$user->name}: {$adjustmentDays} days ({$request->type})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Send notification to user
        Notification::kirim(
            $user->id,
            'Perubahan Saldo Cuti Pending',
            "Admin mengajukan perubahan saldo cuti Anda. Tunggu persetujuan dari admin lain.",
            Notification::TYPE_CUTI_PERTIMBANGAN,
            route('balance-adjustment.show', $adjustment)
        );

        return redirect()->route('balance-adjustment.index')
            ->with('success', 'Perubahan saldo cuti berhasil dibuat.');
    }

    /**
     * Show balance adjustment detail
     */
    public function show(BalanceAdjustment $adjustment)
    {
        $this->authorize('admin');

        return view('balance-adjustments.show', compact('adjustment'));
    }

    /**
     * Approve balance adjustment
     */
    public function approve(Request $request, BalanceAdjustment $adjustment)
    {
        $this->authorize('admin');

        if (!$adjustment->isPending()) {
            return back()->with('error', 'Perubahan saldo ini sudah diproses.');
        }

        $request->validate([
            'approval_note' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        // Update adjustment
        $adjustment->update([
            'status' => BalanceAdjustment::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approval_note' => $request->approval_note,
            'approved_at' => now(),
        ]);

        // Update cuti record
        $cutiRecord = CutiRecord::firstOrCreate(
            [
                'user_id' => $adjustment->user_id,
                'tahun' => $adjustment->year,
                'jenis_cuti' => 'Cuti Tahunan',
            ],
            ['alokasi_awal' => 12]
        );

        $newSisa = ($cutiRecord->sisa ?? 0) + $adjustment->adjustment_days;
        $cutiRecord->update(['sisa' => $newSisa]);

        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'approve_balance_adjustment',
            'description' => "Approve balance adjustment for {$adjustment->user->name}: {$adjustment->adjustment_days} days",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Notify user
        Notification::kirim(
            $adjustment->user_id,
            'Perubahan Saldo Cuti Disetujui',
            "Perubahan saldo cuti Anda telah disetujui oleh {$user->name}.",
            Notification::TYPE_CUTI_DISETUJUI,
            route('balance-adjustment.show', $adjustment)
        );

        return back()->with('success', 'Perubahan saldo cuti berhasil disetujui.');
    }

    /**
     * Reject balance adjustment
     */
    public function reject(Request $request, BalanceAdjustment $adjustment)
    {
        $this->authorize('admin');

        if (!$adjustment->isPending()) {
            return back()->with('error', 'Perubahan saldo ini sudah diproses.');
        }

        $request->validate([
            'approval_note' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        // Update adjustment
        $adjustment->update([
            'status' => BalanceAdjustment::STATUS_REJECTED,
            'approved_by' => $user->id,
            'approval_note' => $request->approval_note,
            'approved_at' => now(),
        ]);

        // Create audit log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'reject_balance_adjustment',
            'description' => "Reject balance adjustment for {$adjustment->user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Notify user
        Notification::kirim(
            $adjustment->user_id,
            'Perubahan Saldo Cuti Ditolak',
            "Perubahan saldo cuti Anda ditolak. Alasan: {$request->approval_note}",
            Notification::TYPE_CUTI_DITOLAK,
            route('balance-adjustment.show', $adjustment)
        );

        return back()->with('success', 'Perubahan saldo cuti berhasil ditolak.');
    }

    /**
     * Authorize admin only
     */
    private function authorize(string $role)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
    }
}
