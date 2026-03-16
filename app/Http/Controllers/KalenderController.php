<?php

namespace App\Http\Controllers;

use App\Models\DinasLuar;
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
            // #32 Filter per unit (Admin/Ketua only)
            if ($request->filled('unit')) {
                $query->whereHas('user', fn($q) => $q->where('unit_kerja', $request->input('unit')));
            }
        } elseif ($user->isAtasan() || $user->isPanitera() || $user->isSekretaris()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', fn($q2) => $q2->where('atasan_id', $user->id));
            });
        } else {
            $query->where('user_id', $user->id);
        }

        $leaves   = $query->get();
        $holidays = HariLibur::where('tahun', $year)->orderBy('tanggal')->get();

        // Dinas Luar: visible to all users (public announcement)
        $dinasLuarList = DinasLuar::with('user')
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth)
            ->orderBy('start_date')
            ->get();

        // #32 List of units for filter dropdown
        $unitList = ($user->isAdmin() || $user->isKetua())
            ? \App\Models\User::whereNotNull('unit_kerja')->distinct()->pluck('unit_kerja')->sort()->values()
            : collect();

        return view('kalender.index', compact('year', 'month', 'leaves', 'holidays', 'user', 'startOfMonth', 'endOfMonth', 'dinasLuarList', 'unitList'));
    }

    /**
     * #31 Export calendar as iCal (.ics)
     */
    public function exportIcs(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        $user = Auth::user();

        $leaves = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('start_date', $year)
            ->when(!$user->isAdmin() && !$user->isKetua(), fn($q) => $q->where('user_id', $user->id))
            ->get();

        $holidays = HariLibur::where('tahun', $year)->orderBy('tanggal')->get();

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//SiCAIR//PN Natuna//ID\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "X-WR-CALNAME:Kalender Cuti " . $year . "\r\n";
        $ics .= "X-WR-CALDESC:Jadwal Cuti & Hari Libur SiCAIR - PN Natuna\r\n";

        foreach ($leaves as $leave) {
            $uid = 'leave-' . $leave->id . '@sicair.pn-natuna';
            $start = $leave->start_date->format('Ymd');
            $end   = $leave->end_date->copy()->addDay()->format('Ymd'); // iCal end is exclusive
            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:{$uid}\r\n";
            $ics .= "DTSTART;VALUE=DATE:{$start}\r\n";
            $ics .= "DTEND;VALUE=DATE:{$end}\r\n";
            $ics .= "SUMMARY:[Cuti] " . $this->escapeIcs($leave->user->name) . " - " . $this->escapeIcs($leave->type_label) . "\r\n";
            $ics .= "DESCRIPTION:" . $this->escapeIcs($leave->reason ?? '') . "\r\n";
            $ics .= "CATEGORIES:CUTI\r\n";
            $ics .= "END:VEVENT\r\n";
        }

        foreach ($holidays as $holiday) {
            $uid = 'holiday-' . $holiday->id . '@sicair.pn-natuna';
            $date = \Carbon\Carbon::parse($holiday->tanggal)->format('Ymd');
            $nextDate = \Carbon\Carbon::parse($holiday->tanggal)->addDay()->format('Ymd');
            $prefix = $holiday->is_cuti_bersama ? '[Cuti Bersama]' : '[Libur Nasional]';
            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:{$uid}\r\n";
            $ics .= "DTSTART;VALUE=DATE:{$date}\r\n";
            $ics .= "DTEND;VALUE=DATE:{$nextDate}\r\n";
            $ics .= "SUMMARY:{$prefix} " . $this->escapeIcs($holiday->keterangan) . "\r\n";
            $ics .= "CATEGORIES:" . ($holiday->is_cuti_bersama ? 'CUTI BERSAMA' : 'HARI LIBUR') . "\r\n";
            $ics .= "END:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="kalender-cuti-' . $year . '.ics"',
        ]);
    }

    private function escapeIcs(string $str): string
    {
        return str_replace(["\r\n", "\n", "\r", ',', ';', '\\'], ['\\n', '\\n', '\\n', '\\,', '\\;', '\\\\'], $str);
    }

    public function tim(Request $request)
    {
        $user = auth()->user();
        if (!$user->canApproveAsAtasan() && !$user->isAdmin()) {
            abort(403);
        }

        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        // Ambil semua bawahan langsung
        $bawahanIds = \App\Models\User::where('atasan_id', $user->id)->pluck('id');

        // Jika admin atau ketua, ambil semua user
        if ($user->isAdmin()) {
            $bawahanIds = \App\Models\User::where('role', '!=', 'admin')->pluck('id');
        }

        $leaves = \App\Models\LeaveRequest::with('user')
            ->whereIn('user_id', $bawahanIds)
            ->whereIn('status', [\App\Models\LeaveRequest::STATUS_DISETUJUI, \App\Models\LeaveRequest::STATUS_APPROVED])
            ->where(function ($q) use ($year, $month) {
                $ym = sprintf('%04d-%02d', $year, $month);
                $q->whereRaw("strftime('%Y-%m', start_date) = ?", [$ym])
                  ->orWhereRaw("strftime('%Y-%m', end_date) = ?", [$ym]);
            })
            ->orderBy('start_date')
            ->get();

        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear  = $month == 1 ? $year - 1 : $year;
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear  = $month == 12 ? $year + 1 : $year;

        return view('kalender.tim', compact('leaves', 'month', 'year', 'prevMonth', 'prevYear', 'nextMonth', 'nextYear'));
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

        $dinasLuar = DinasLuar::with('user')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get();

        return response()->json([
            'date'      => $date,
            'holiday'   => $holiday ? [
                'keterangan'      => $holiday->keterangan,
                'is_cuti_bersama' => (bool) $holiday->is_cuti_bersama,
            ] : null,
            'leaves'    => $leaves->map(fn($lv) => [
                'name'       => $lv->user->name,
                'jabatan'    => $lv->user->jabatan ?? '-',
                'unit_kerja' => $lv->user->unit_kerja ?? '-',
                'type_label' => $lv->type_label,
                'start_date' => $lv->start_date->format('d M Y'),
                'end_date'   => $lv->end_date->format('d M Y'),
                'total_hari' => $lv->total_hari_kerja,
                'initials'   => strtoupper(substr($lv->user->name, 0, 2)),
            ]),
            'dinasLuar' => $dinasLuar->map(fn($dl) => [
                'name'       => $dl->user->name,
                'jabatan'    => $dl->user->jabatan ?? '-',
                'tujuan'     => $dl->tujuan,
                'keperluan'  => $dl->keperluan,
                'start_date' => $dl->start_date->format('d M Y'),
                'end_date'   => $dl->end_date->format('d M Y'),
                'durasi'     => $dl->durasi,
                'initials'   => strtoupper(substr($dl->user->name, 0, 2)),
            ]),
        ]);
    }
}
