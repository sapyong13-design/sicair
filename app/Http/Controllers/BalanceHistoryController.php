<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\BalanceAuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceHistoryController extends Controller
{
    /**
     * View balance history for current user (STB-optimized)
     */
    public function index()
    {
        $user = Auth::user();
        $history = BalanceAuditService::getBalanceHistory($user, 50); // Limit untuk STB

        return view('balance-history.index', compact('history', 'user'));
    }

    /**
     * View balance history for specific user (admin only)
     */
    public function show(User $user)
    {
        $authUser = Auth::user();

        // FIX #12: Allow admin/ketua, the user themselves, or their direct atasan
        $isOwnRecord = $authUser->id === $user->id;
        $isAdmin = $authUser->isAdmin() || $authUser->isKetua();
        $isDirectAtasan = $authUser->isAtasan() && $user->atasan_id === $authUser->id;

        if (!$isOwnRecord && !$isAdmin && !$isDirectAtasan) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        $history = BalanceAuditService::getBalanceHistory($user, 50);
        $currentBalance = $user->leave_balance;

        return view('balance-history.show', compact('user', 'history', 'currentBalance'));
    }

    /**
     * Export balance history for audit (STB-optimized PDF)
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $history = $startDate && $endDate
            ? BalanceAuditService::getBalanceChangeInPeriod($user, $startDate, $endDate)
            : BalanceAuditService::getBalanceHistory($user, 50);

        // For STB: Return JSON instead of PDF for lighter processing
        return response()->json([
            'user' => [
                'name' => $user->name,
                'nip' => $user->nip,
                'current_balance' => $user->leave_balance,
            ],
            'history' => $history->map(function ($log) {
                return [
                    'date' => $log->created_at->format('d M Y H:i'),
                    'action' => $log->action_label,
                    'old_balance' => $log->old_values['balance'] ?? '-',
                    'new_balance' => $log->new_values['balance'] ?? '-',
                    'change' => ($log->new_values['balance'] ?? 0) - ($log->old_values['balance'] ?? 0),
                    'description' => $log->description,
                ];
            }),
            'exported_at' => now()->format('d M Y H:i:s'),
        ]);
    }
}
