<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KalenderController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));

        $user = Auth::user();

        // Get approved leaves for this month
        $startOfMonth = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endOfMonth = date('Y-m-t', strtotime($startOfMonth));

        $query = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth);

        // Pegawai only sees their own
        if ($user->isPegawai()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isAtasan()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', fn($q2) => $q2->where('atasan_id', $user->id));
            });
        }

        $leaves = $query->get();

        // Get holidays
        $holidays = HariLibur::where('tahun', $year)->get();

        return view('kalender.index', compact('year', 'month', 'leaves', 'holidays', 'user'));
    }
}
