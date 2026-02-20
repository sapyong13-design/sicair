<?php

namespace App\Http\Controllers;

use App\Models\BalanceAdjustment;
use App\Models\CutiRecord;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // FIX #23: Search by name/nip only — 'email' column does not exist in users table
        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%")
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

        // FIX #4: Pastikan saldo tidak akan negatif jika adjustment ini disetujui
        if ($adjustmentDays < 0 && ($user->leave_balance + $adjustmentDays) < 0) {
            return back()->withErrors([
                'adjustment_days' => "Pengurangan {$request->adjustment_days} hari akan membuat saldo cuti {$user->name} menjadi negatif (saldo saat ini: {$user->leave_balance} hari).",
            ])->withInput();
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

        $admin = Auth::user();
        $approvalNote = $request->approval_note;
        $alreadyProcessed = false;

        // Use DB transaction with lock to prevent concurrent double-approval
        DB::transaction(function () use ($adjustment, $admin, $approvalNote, &$alreadyProcessed) {
            // Re-fetch inside transaction with lock to check status again
            $locked = BalanceAdjustment::lockForUpdate()->find($adjustment->id);
            if (!$locked->isPending()) {
                // FIX #10: Flag so caller can return explicit error instead of silent skip
                $alreadyProcessed = true;
                return;
            }

            // Update adjustment
            $locked->update([
                'status' => BalanceAdjustment::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approval_note' => $approvalNote,
                'approved_at' => now(),
            ]);

            // Update cuti record
            $cutiRecord = CutiRecord::firstOrCreate(
                [
                    'user_id' => $locked->user_id,
                    'tahun' => $locked->year,
                    'jenis_cuti' => 'Cuti Tahunan',
                ],
                ['alokasi_awal' => 12]
            );

            $newSisa = ($cutiRecord->sisa ?? 0) + $locked->adjustment_days;
            $cutiRecord->update(['sisa' => $newSisa]);

            // FIX KRITIKAL: Juga update users.leave_balance karena validasi pengajuan cuti
            // menggunakan $user->leave_balance, bukan cuti_records.sisa
            $lockedUser = User::lockForUpdate()->find($locked->user_id);
            if ($locked->adjustment_days > 0) {
                $lockedUser->increment('leave_balance', $locked->adjustment_days);
            } elseif ($locked->adjustment_days < 0) {
                $lockedUser->decrement('leave_balance', abs($locked->adjustment_days));
            }

            // Create audit log
            \App\Models\AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'approve_balance_adjustment',
                'description' => "Approve balance adjustment for {$locked->user->name}: {$locked->adjustment_days} days (leave_balance updated)",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        // FIX #10: Return explicit error if already processed during race condition
        if ($alreadyProcessed) {
            return back()->with('error', 'Perubahan saldo ini sudah diproses oleh admin lain.');
        }

        // Notify user
        Notification::kirim(
            $adjustment->user_id,
            'Perubahan Saldo Cuti Disetujui',
            "Perubahan saldo cuti Anda telah disetujui oleh {$admin->name}.",
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
