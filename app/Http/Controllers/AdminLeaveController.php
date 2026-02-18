<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\HariKerjaCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminLeaveController extends Controller
{
    /**
     * Form tambah cuti manual untuk pegawai tertentu (admin only)
     * Bypass semua validasi bisnis — boleh backdated, dll.
     */
    public function create(User $user)
    {
        return view('admin.leave.form', [
            'leaveRequest' => null,
            'pegawai'      => $user,
        ]);
    }

    /**
     * Simpan cuti manual oleh admin
     */
    public function store(Request $request, User $user)
    {
        $request->validate([
            'type'           => 'required|in:' . implode(',', array_keys(LeaveRequest::typeLabels())),
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'reason'         => 'required|string|max:1000',
            'status'         => 'required|in:diajukan,pertimbangan_atasan,disetujui,diubah,ditangguhkan,ditolak',
            'alamat_cuti'    => 'nullable|string|max:500',
            'telepon_cuti'   => 'nullable|string|max:20',
            'alasan_cap'     => 'nullable|in:' . implode(',', array_keys(LeaveRequest::capLabels())),
            'kelahiran_ke'   => 'nullable|integer|min:1|max:10',
            'catatan_admin'  => 'nullable|string|max:500',
        ]);

        $admin      = Auth::user();
        $startDate  = Carbon::parse($request->start_date);
        $endDate    = Carbon::parse($request->end_date);
        $hariKerja  = HariKerjaCalculator::hitungHariKerja($startDate, $endDate);

        $data = [
            'user_id'          => $user->id,
            'type'             => $request->type,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'reason'           => $request->reason,
            'status'           => $request->status,
            'total_hari_kerja' => $hariKerja,
            'alamat_cuti'      => $request->alamat_cuti,
            'telepon_cuti'     => $request->telepon_cuti,
            'alasan_cap'       => $request->alasan_cap,
            'kelahiran_ke'     => $request->kelahiran_ke,
            'admin_note'       => 'Dimasukkan admin: ' . $admin->name
                                   . ($request->filled('catatan_admin') ? '. ' . $request->catatan_admin : ''),
        ];

        // Jika status disetujui → isi data persetujuan
        if ($request->status === LeaveRequest::STATUS_DISETUJUI) {
            $data['pejabat_id']        = $admin->id;
            $data['keputusan_pejabat'] = 'setuju';
            $data['decided_at']        = now();
            // Juga isi atasan reviewer agar riwayat lengkap
            $data['atasan_reviewer_id']   = $admin->id;
            $data['pertimbangan_atasan']  = 'setuju';
            $data['reviewed_at']          = now();

            // Kurangi leave_balance jika cuti tahunan
            if ($request->type === LeaveRequest::TYPE_TAHUNAN) {
                $user->decrement('leave_balance', $hariKerja);
            }
        }

        $leaveRequest = LeaveRequest::create($data);

        AuditLog::log(
            'create',
            'LeaveRequest',
            $leaveRequest->id,
            null,
            $data,
            "Admin {$admin->name} menambahkan riwayat cuti untuk {$user->name}"
        );

        return redirect()
            ->route('pegawai.show', $user)
            ->with('success', "Riwayat cuti {$user->name} berhasil ditambahkan.");
    }

    /**
     * Form edit cuti oleh admin
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        return view('admin.leave.form', [
            'leaveRequest' => $leaveRequest,
            'pegawai'      => $leaveRequest->user,
        ]);
    }

    /**
     * Update cuti oleh admin
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'type'           => 'required|in:' . implode(',', array_keys(LeaveRequest::typeLabels())),
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'reason'         => 'required|string|max:1000',
            'status'         => 'required|in:diajukan,pertimbangan_atasan,disetujui,diubah,ditangguhkan,ditolak',
            'alamat_cuti'    => 'nullable|string|max:500',
            'telepon_cuti'   => 'nullable|string|max:20',
            'alasan_cap'     => 'nullable|in:' . implode(',', array_keys(LeaveRequest::capLabels())),
            'kelahiran_ke'   => 'nullable|integer|min:1|max:10',
            'catatan_admin'  => 'nullable|string|max:500',
        ]);

        $admin      = Auth::user();
        $user       = $leaveRequest->user;
        // FIX #9: Capture old data BEFORE update so getOriginal() works correctly
        $oldData         = $leaveRequest->toArray();
        $oldHariKerja    = $leaveRequest->total_hari_kerja ?? 0;
        $wasApprovedTahunan = $leaveRequest->status === LeaveRequest::STATUS_DISETUJUI
                              && $leaveRequest->type === LeaveRequest::TYPE_TAHUNAN;

        $startDate  = Carbon::parse($request->start_date);
        $endDate    = Carbon::parse($request->end_date);
        $hariKerja  = HariKerjaCalculator::hitungHariKerja($startDate, $endDate);

        $data = [
            'type'             => $request->type,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'reason'           => $request->reason,
            'status'           => $request->status,
            'total_hari_kerja' => $hariKerja,
            'alamat_cuti'      => $request->alamat_cuti,
            'telepon_cuti'     => $request->telepon_cuti,
            'alasan_cap'       => $request->alasan_cap,
            'kelahiran_ke'     => $request->kelahiran_ke,
            'admin_note'       => 'Diedit admin: ' . $admin->name
                                   . ($request->filled('catatan_admin') ? '. ' . $request->catatan_admin : ''),
        ];

        // Jika status berubah ke disetujui
        if ($request->status === LeaveRequest::STATUS_DISETUJUI) {
            $data['pejabat_id']           = $admin->id;
            $data['keputusan_pejabat']    = 'setuju';
            $data['decided_at']           = $leaveRequest->decided_at ?? now();
            $data['atasan_reviewer_id']   = $leaveRequest->atasan_reviewer_id ?? $admin->id;
            $data['pertimbangan_atasan']  = $leaveRequest->pertimbangan_atasan ?? 'setuju';
            $data['reviewed_at']          = $leaveRequest->reviewed_at ?? now();
        }

        // FIX #8/#5: Wrap all balance changes + update in a DB transaction
        DB::transaction(function () use ($leaveRequest, $data, $user, $request, $wasApprovedTahunan, $oldHariKerja, $hariKerja) {
            $leaveRequest->update($data);

            // Koreksi leave_balance untuk cuti tahunan (use pre-captured $oldHariKerja)
            if ($request->type === LeaveRequest::TYPE_TAHUNAN) {
                if ($wasApprovedTahunan && $request->status !== LeaveRequest::STATUS_DISETUJUI) {
                    // Status berubah dari disetujui → kembalikan saldo
                    $user->increment('leave_balance', $oldHariKerja);
                } elseif (!$wasApprovedTahunan && $request->status === LeaveRequest::STATUS_DISETUJUI) {
                    // Status berubah ke disetujui → kurangi saldo
                    $user->decrement('leave_balance', $hariKerja);
                } elseif ($wasApprovedTahunan && $request->status === LeaveRequest::STATUS_DISETUJUI) {
                    // Tetap disetujui tapi hari berubah → koreksi selisih
                    $selisih = $hariKerja - $oldHariKerja;
                    if ($selisih > 0) {
                        $user->decrement('leave_balance', $selisih);
                    } elseif ($selisih < 0) {
                        $user->increment('leave_balance', abs($selisih));
                    }
                }
            }
        });

        AuditLog::log(
            'update',
            'LeaveRequest',
            $leaveRequest->id,
            $oldData,
            $leaveRequest->fresh()->toArray(),
            "Admin {$admin->name} mengedit riwayat cuti {$user->name}"
        );

        return redirect()
            ->route('pegawai.show', $user)
            ->with('success', "Riwayat cuti {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus cuti oleh admin
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        $admin = Auth::user();
        $user  = $leaveRequest->user;

        // Kembalikan saldo jika cuti tahunan disetujui
        if ($leaveRequest->status === LeaveRequest::STATUS_DISETUJUI
            && $leaveRequest->type === LeaveRequest::TYPE_TAHUNAN) {
            $hari = $leaveRequest->total_hari_kerja ?? 0;
            $user->increment('leave_balance', $hari);
        }

        AuditLog::log(
            'delete',
            'LeaveRequest',
            $leaveRequest->id,
            $leaveRequest->toArray(),
            null,
            "Admin {$admin->name} menghapus riwayat cuti {$user->name}"
        );

        $leaveRequest->delete();

        return redirect()
            ->route('pegawai.show', $user)
            ->with('success', "Riwayat cuti {$user->name} berhasil dihapus.");
    }
}
