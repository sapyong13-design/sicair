<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $pendingRequests = LeaveRequest::with('user')
                ->where('status', 'pending')
                ->latest()
                ->get();

            $recentDecisions = LeaveRequest::with('user')
                ->whereIn('status', ['approved', 'rejected'])
                ->latest()
                ->take(10)
                ->get();

            return view('dashboard', compact('user', 'pendingRequests', 'recentDecisions'));
        }

        // Pegawai
        $leaveRequests = $user->leaveRequests()->latest()->get();

        return view('dashboard', compact('user', 'leaveRequests'));
    }
}
