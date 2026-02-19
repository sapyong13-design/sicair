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
        $year  = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('n'));

        $user = Auth::user();

        $startOfMonth = sprintf('%04d-%02d-01', $year, $month);
        $endOfMonth   = date('Y-m-t', strtotime($startOfMonth));

        $query = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth);

        // Admin dan Ketua melihat SEMUA cuti
        if ($user->isAdmin() || $user->isKetua()) {
            // no additional filter
        } elseif ($user->isAtasan() || $user->isPanitera() || $user->isSekretaris()) {
            // Atasan/Panitera/Sekretaris: lihat milik sendiri + bawahan langsung
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', fn($q2) => $q2->where('atasan_id', $user->id));
            });
        } else {
            // Pegawai biasa, Hakim, Hakim Ad Hoc: hanya milik sendiri
            $query->where('user_id', $user->id);
        }

        $leaves   = $query->get();
        $holidays = HariLibur::where('tahun', $year)->orderBy('tanggal')->get();

        return view('kalender.index', compact('year', 'month', 'leaves', 'holidays', 'user', 'startOfMonth', 'endOfMonth'));
    }

    /**
     * JSON endpoint: cuti yang disetujui pada tanggal tertentu (untuk modal kalender)
     */
    public function leavesForDay(Request $request)
    {
        $date = $request->input('date'); // format: Y-m-d
        if (!$date) {
            return response()->json(['leaves' => [], 'holiday' => null]);
        }

        $user = Auth::user();

        $query = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);

        if ($user->isAdmin() || $user->isKetua()) {
            // no filter — lihat semua
        } elseif ($user->isAtasan() || $user->isPanitera() || $user->isSekretaris()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', fn($q2) => $q2->where('atasan_id', $user->id));
            });
        } else {
            $query->where('user_id', $user->id);
        }

        $leaves  = $query->get();
        $holiday = HariLibur::whereDate('tanggal', $date)->first();

        return response()->json([
            'date'    => $date,
            'holiday' => $holiday ? [
                'keterangan'      => $holiday->keterangan,
                'is_cuti_bersama' => (bool) $holiday->is_cuti_bersama,
            ] : null,
            'leaves'  => $leaves->map(fn($lv) => [
                'name'       => $lv->user->name,
                'jabatan'    => $lv->user->jabatan ?? '-',
                'unit_kerja' => $lv->user->unit_kerja ?? '-',
                'type_label' => $lv->type_label,
                'start_date' => $lv->start_date->format('d M Y'),
                'end_date'   => $lv->end_date->format('d M Y'),
                'total_hari' => $lv->total_hari_kerja,
                'initials'   => strtoupper(substr($lv->user->name, 0, 2)),
            ]),
        ]);
    }
}
