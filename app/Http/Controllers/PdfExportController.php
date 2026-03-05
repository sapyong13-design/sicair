<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\PdfExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdfExportController extends Controller
{
    /**
     * Export single leave request
     */
    public function leaveRequest(LeaveRequest $leaveRequest)
    {
        // Check authorization — admin dan ketua boleh akses semua
        if (Auth::id() !== $leaveRequest->user_id && !Auth::user()->isAdmin() && !Auth::user()->isKetua()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        return PdfExportService::exportLeaveRequest($leaveRequest);
    }

    /**
     * Export all leave requests (admin only)
     */
    public function allLeaveRequests(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        $query = LeaveRequest::query();

        // Filter by date range if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $leaveRequests = $query->with('user')->latest()->limit(100)->get();

        return PdfExportService::exportLeaveRequestSummary($leaveRequests);
    }

    /**
     * Export user balance report
     */
    public function balanceReport(User $user)
    {
        // Check authorization
        if (Auth::id() !== $user->id && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        return PdfExportService::exportBalanceReport($user);
    }

    /**
     * Export leave statistics for period (admin only)
     */
    public function statistics(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        return PdfExportService::exportLeaveStatistics($startDate, $endDate);
    }
}
