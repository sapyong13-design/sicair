<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function create()
    {
        return view('leave.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        if ($totalDays > $user->leave_balance) {
            return back()->withErrors([
                'end_date' => "Jumlah hari cuti ($totalDays hari) melebihi sisa cuti Anda ($user->leave_balance hari).",
            ])->withInput();
        }

        LeaveRequest::create([
            'user_id' => $user->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
        ]);

        return redirect('/dashboard')->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $user = $leaveRequest->user;
        $totalDays = $leaveRequest->total_days;

        if ($totalDays > $user->leave_balance) {
            return back()->with('error', "Sisa cuti pegawai tidak mencukupi ($user->leave_balance hari tersisa).");
        }

        $leaveRequest->update([
            'status' => 'approved',
            'admin_note' => $request->input('admin_note'),
        ]);

        $user->decrement('leave_balance', $totalDays);

        return back()->with('success', "Cuti {$user->name} disetujui ($totalDays hari).");
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note'),
        ]);

        return back()->with('success', "Pengajuan cuti {$leaveRequest->user->name} ditolak.");
    }
}
