<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequestRequest;
use App\Mail\LeaveRequestApproved;
use App\Mail\LeaveRequestNeedsConsideration;
use App\Mail\LeaveRequestRejected;
use App\Mail\LeaveRequestSubmitted;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Services\BalanceAuditService;
use App\Services\CutiTahunanCalculator;
use App\Services\WhatsAppService;
use App\Services\DocxExportService;
use App\Services\HariKerjaCalculator;
use App\Services\PdfExportService;
use App\Support\CacheKeys;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LeaveRequestController extends Controller
{
    /**
     * Pilih jenis cuti
     * Fix #16: CPNS type-aware — tampilkan halaman select-type dengan info cuti terbatas
     */
    public function selectType(Request $request)
    {
        $user = Auth::user();
        // Fix #16: Jangan blokir total, biarkan CPNS memilih jenis cuti yang diperbolehkan
        if (!$user->bolehCuti() && $user->status_pegawai !== 'cpns') {
            $pesan = 'PPPK yang baru dilantik (masa kerja < 1 tahun) belum berhak mengajukan cuti.';
            return redirect('/dashboard')->with('error', $pesan);
        }

        $prefillStart = $request->query('start');
        if ($prefillStart && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $prefillStart)) {
            $prefillStart = null;
        }
        return view('leave.select-type', compact('prefillStart'));
    }

    /**
     * Form pengajuan cuti (semua jenis)
     * Fix #16: type-aware bolehCuti check
     */
    public function create(Request $request)
    {
        $type = $request->query('type', LeaveRequest::TYPE_TAHUNAN);
        $prefillStart = $request->query('start');
        if ($prefillStart && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $prefillStart)) {
            $prefillStart = null;
        }
        $user = Auth::user();

        // Fix #16: CPNS type-aware check — boleh cuti sakit/melahirkan/alasan penting
        if (!$user->bolehCuti($type)) {
            $pesan = $user->status_pegawai === 'cpns'
                ? 'CPNS hanya berhak mengajukan Cuti Sakit, Cuti Melahirkan, atau Cuti Karena Alasan Penting.'
                : 'Anda belum berhak mengajukan jenis cuti ini.';
            return redirect()->route('leave.select-type')->with('error', $pesan);
        }

        // #21 Re-apply: pre-fill from rejected leave request
        // Supports both legacy ?reapply=X and new ?reapply_from=X (via leave.reapply route)
        $reapplyData = null;
        $reapplyParam = $request->filled('reapply_from') ? $request->query('reapply_from') : ($request->filled('reapply') ? $request->query('reapply') : null);
        if ($reapplyParam) {
            $reapplySource = LeaveRequest::where('user_id', $user->id)
                ->where('id', $reapplyParam)
                ->whereIn('status', [
                    LeaveRequest::STATUS_DITOLAK,
                    LeaveRequest::STATUS_REJECTED,
                    LeaveRequest::STATUS_DIUBAH,
                ])
                ->first();
            if ($reapplySource) {
                $type = $reapplySource->type;
                $reapplyData = $reapplySource;
            }
        }

        // Validasi tipe cuti
        if (!array_key_exists($type, LeaveRequest::typeLabels())) {
            return redirect()->route('leave.select-type')->with('error', 'Jenis cuti tidak valid.');
        }

        // Hitung sisa cuti tahunan
        $cutiInfo = null;
        if ($type === LeaveRequest::TYPE_TAHUNAN) {
            $calculator = new CutiTahunanCalculator($user);
            $cutiInfo = $calculator->hitung();
        }

        // #22 Personal usage summary
        $thisYearDays = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('created_at', date('Y'))
            ->sum('total_hari_kerja');
        $lastYearDays = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('created_at', date('Y') - 1)
            ->sum('total_hari_kerja');

        return view('leave.create', compact('type', 'cutiInfo', 'reapplyData', 'thisYearDays', 'lastYearDays', 'prefillStart'));
    }

    /**
     * Simpan pengajuan cuti
     */
    public function store(StoreLeaveRequestRequest $request)
    {
        $user = Auth::user();

        $type = $request->input('type', LeaveRequest::TYPE_TAHUNAN);

        // Fix #16: CPNS type-aware check
        if (!$user->bolehCuti($type)) {
            $pesan = $user->status_pegawai === 'cpns'
                ? 'CPNS hanya berhak mengajukan Cuti Sakit, Cuti Melahirkan, atau Cuti Karena Alasan Penting.'
                : 'Anda belum berhak mengajukan jenis cuti ini.';
            return redirect('/dashboard')->with('error', $pesan);
        }

        // Validasi umum
        $rules = [
            'type' => 'required|in:' . implode(',', array_keys(LeaveRequest::typeLabels())),
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'alamat_cuti' => 'nullable|string|max:500',
            'telepon_cuti' => 'nullable|string|max:20',
        ];

        // Validasi per jenis
        $this->addTypeSpecificRules($rules, $type);

        $request->validate($rules);

        // Validasi bisnis per jenis cuti
        $error = $this->validateBusinessRules($request, $user, $type);
        if ($error) {
            return back()->withErrors(['reason' => $error])->withInput();
        }

        // Cek tumpang tindih dengan cuti yang aktif (server-level conflict detection)
        $conflict = LeaveRequest::where('user_id', $user->id)
            ->whereNotIn('status', [
                LeaveRequest::STATUS_DITOLAK,
                LeaveRequest::STATUS_REJECTED,
            ])
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('start_date', '<=', $request->start_date)
                         ->where('end_date', '>=', $request->end_date);
                  });
            })
            ->first();

        if ($conflict) {
            $conflictStart = \Carbon\Carbon::parse($conflict->start_date)->format('d/m/Y');
            $conflictEnd   = \Carbon\Carbon::parse($conflict->end_date)->format('d/m/Y');
            return back()->withErrors([
                'start_date' => 'Anda sudah memiliki pengajuan cuti pada periode ' .
                    $conflictStart . ' s/d ' . $conflictEnd .
                    ' (status: ' . $conflict->status . ').',
            ])->withInput();
        }

        // Hitung hari kerja
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $hariKerja = HariKerjaCalculator::hitungHariKerja($startDate, $endDate);

        // FIX #18: Validate hariKerja > 0 (prevent all-holiday ranges)
        if ($hariKerja < 1) {
            return back()->withErrors(['start_date' => 'Rentang tanggal yang dipilih tidak mengandung hari kerja. Semua tanggal adalah hari libur atau akhir pekan.'])->withInput();
        }

        // Upload dokumen jika ada
        $dokumenPath = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $dokumenPath = $request->file('dokumen_pendukung')->store('dokumen-cuti', 'public');
        }

        // Tentukan alur approval berdasarkan posisi pemohon:
        // - Hakim/Panitera/Sekretaris/Ketua (atasan = Ketua) → langsung ke Ketua
        // - Staff Kepaniteraan (atasan = Panitera) → Panitera → Ketua
        // - Staff Kesekretariatan (atasan = Sekretaris) → Sekretaris → Ketua
        $skipAtasan = $user->skipAtasanReview();
        $initialStatus = $skipAtasan
            ? LeaveRequest::STATUS_PERTIMBANGAN
            : LeaveRequest::STATUS_DIAJUKAN;

        try {
            $leaveRequest = DB::transaction(function () use ($request, $user, $type, $dokumenPath, $hariKerja, $initialStatus) {
                return LeaveRequest::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'reason' => $request->reason,
                    'alamat_cuti' => $request->alamat_cuti,
                    'telepon_cuti' => $request->telepon_cuti,
                    'alasan_cap' => $request->alasan_cap,
                    'kelahiran_ke' => $request->kelahiran_ke,
                    'dokumen_pendukung' => $dokumenPath,
                    'total_hari_kerja' => $hariKerja,
                    'status' => $initialStatus,
                ]);
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return back()->withErrors(['reason' => 'Anda sudah memiliki pengajuan aktif yang sedang diproses. Tunggu keputusan sebelum mengajukan ulang.'])->withInput();
        }

        // Kirim email ke pemohon (try-catch for OpenWrt sync queue compatibility)
        try {
            Mail::queue(new LeaveRequestSubmitted($leaveRequest));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Email gagal dikirim: ' . $e->getMessage());
        }

        // WA: konfirmasi ke pemohon
        if ($user->wantsWhatsAppNotification()) {
            $typeLabel = LeaveRequest::typeLabels()[$type] ?? $type;
            WhatsAppService::send($user->telepon,
                "SiCAIR: Pengajuan {$typeLabel} Anda\n"
                . $leaveRequest->start_date->format('d/m/Y') . " s/d " . $leaveRequest->end_date->format('d/m/Y')
                . " ({$leaveRequest->total_hari_kerja} hari kerja)\nberhasil diajukan dan sedang menunggu persetujuan."
            );
        }

        if ($skipAtasan) {
            // Langsung ke Ketua/Admin (tanpa review atasan)
            // Kirim email ke pejabat berwenang
            try {
                Mail::queue(new LeaveRequestNeedsConsideration($leaveRequest));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Email gagal dikirim: ' . $e->getMessage());
            }

            // Notifikasi ke semua pejabat (ketua + admin)
            $pejabatIds = \App\Models\User::whereIn('role', ['ketua', 'admin'])->pluck('id');
            foreach ($pejabatIds as $pejabatId) {
                Notification::kirim(
                    $pejabatId,
                    'Pengajuan Cuti Baru — Perlu Keputusan',
                    "{$user->name} ({$user->jabatan}) mengajukan " . LeaveRequest::typeLabels()[$type],
                    Notification::TYPE_CUTI_PERTIMBANGAN,
                    '/dashboard#needs-decision'
                );
            }
        } else {
            // Alur normal: kirim ke atasan langsung (Panitera/Sekretaris)
            if ($user->atasan_id) {
                Notification::kirim(
                    $user->atasan_id,
                    'Pengajuan Cuti Baru',
                    "{$user->name} mengajukan " . LeaveRequest::typeLabels()[$type],
                    Notification::TYPE_CUTI_DIAJUKAN,
                    '/dashboard#pending-review'
                );

                // WA: notif ke atasan
                $atasan = \App\Models\User::find($user->atasan_id);
                if ($atasan && $atasan->wantsWhatsAppNotification()) {
                    $typeLabel = LeaveRequest::typeLabels()[$type] ?? $type;
                    WhatsAppService::send($atasan->telepon,
                        "SiCAIR: {$user->name} mengajukan {$typeLabel}\n"
                        . $leaveRequest->start_date->format('d/m/Y') . " - " . $leaveRequest->end_date->format('d/m/Y')
                        . "\nSilakan login ke SiCAIR untuk memberikan pertimbangan."
                    );
                }
            }
        }

        return redirect('/dashboard')->with('success', 'Pengajuan ' . LeaveRequest::typeLabels()[$type] . ' berhasil dikirim.');
    }

    /**
     * Pertimbangan oleh Atasan Langsung (Level 1)
     */
    public function reviewAtasan(Request $request, LeaveRequest $leaveRequest)
    {
        $reviewer = Auth::user();
        $isAjax = $request->header('X-Requested-With') === 'XMLHttpRequest';

        if (!$leaveRequest->needsAtasanReview()) {
            $message = 'Pengajuan ini tidak dalam status menunggu pertimbangan atasan.';
            return $isAjax ? response()->json(['error' => $message], 400) : back()->with('error', $message);
        }

        $request->validate([
            'pertimbangan' => 'required|in:setuju,ubah,tangguhkan,tolak',
            'catatan_atasan' => 'nullable|string|max:500',
            'rejection_reason' => 'nullable|in:tanggal_konflik,kuota_habis,alasan_tidak_jelas,dokumen_kurang,lainnya',
        ]);

        $pertimbangan = $request->pertimbangan;

        if ($pertimbangan === 'tolak') {
            $request->validate([
                'catatan_atasan' => 'required|string|max:500',
                'rejection_reason' => 'required|in:tanggal_konflik,kuota_habis,alasan_tidak_jelas,dokumen_kurang,lainnya',
            ]);
            $adminNote = $request->rejection_reason
                ? '[ALASAN: ' . $request->rejection_reason . '] ' . $request->catatan_atasan
                : $request->catatan_atasan;
            $leaveRequest->update([
                'atasan_reviewer_id' => $reviewer->id,
                'pertimbangan_atasan' => $pertimbangan,
                'catatan_atasan' => $request->catatan_atasan,
                'admin_note' => $adminNote,
                'reviewed_at' => now(),
                'status' => LeaveRequest::STATUS_DITOLAK,
            ]);

            AuditLog::log(
                'reject',
                LeaveRequest::class,
                $leaveRequest->id,
                null,
                ['status' => LeaveRequest::STATUS_DITOLAK, 'catatan_atasan' => $request->catatan_atasan],
                "Atasan {$reviewer->name} menolak pengajuan cuti #{$leaveRequest->id} milik {$leaveRequest->user?->name}"
            );

            // Kirim email penolakan
            try {
                Mail::queue(new LeaveRequestRejected(
                    $leaveRequest,
                    $reviewer->name,
                    $request->catatan_atasan
                ));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Email gagal dikirim: ' . $e->getMessage());
            }

            Notification::kirim(
                $leaveRequest->user_id,
                'Pengajuan Cuti Ditolak',
                "Pengajuan {$leaveRequest->type_label} Anda ditolak oleh {$reviewer->name}.",
                Notification::TYPE_CUTI_DITOLAK,
                route('leave.show', $leaveRequest)
            );

            // WA: notif ditolak atasan
            $pemohon = $leaveRequest->user;
            if ($pemohon && $pemohon->wantsWhatsAppNotification()) {
                WhatsAppService::send($pemohon->telepon,
                    "SiCAIR: Maaf, pengajuan {$leaveRequest->type_label} Anda\n"
                    . $leaveRequest->start_date->format('d/m/Y') . " - " . $leaveRequest->end_date->format('d/m/Y')
                    . "\nDITOLAK oleh {$reviewer->name}."
                    . ($request->catatan_atasan ? "\nCatatan: {$request->catatan_atasan}" : '')
                );
            }

            return $isAjax ? response()->json(['success' => true, 'message' => 'Pengajuan cuti ditolak.']) : back()->with('success', 'Pengajuan cuti ditolak.');
        }

        // Setuju / ubah / tangguhkan → lanjut ke Pejabat Berwenang
        $leaveRequest->update([
            'atasan_reviewer_id' => $reviewer->id,
            'pertimbangan_atasan' => $pertimbangan,
            'catatan_atasan' => $request->catatan_atasan,
            'reviewed_at' => now(),
            'status' => LeaveRequest::STATUS_PERTIMBANGAN,
        ]);

        // Kirim email ke pejabat untuk pertimbangan lanjutan
        try {
            Mail::queue(new LeaveRequestNeedsConsideration($leaveRequest));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Email gagal dikirim: ' . $e->getMessage());
        }

        // Notifikasi ke pemohon
        Notification::kirim(
            $leaveRequest->user_id,
            'Cuti Sedang Dipertimbangkan',
            "Pengajuan {$leaveRequest->type_label} Anda telah dipertimbangkan oleh {$reviewer->name}.",
            Notification::TYPE_CUTI_PERTIMBANGAN,
            route('leave.show', $leaveRequest)
        );

        // WA: notif diteruskan ke pejabat
        $pemohon = $leaveRequest->user;
        if ($pemohon && $pemohon->wantsWhatsAppNotification()) {
            WhatsAppService::send($pemohon->telepon,
                "SiCAIR: Pengajuan {$leaveRequest->type_label} Anda\n"
                . $leaveRequest->start_date->format('d/m/Y') . " - " . $leaveRequest->end_date->format('d/m/Y')
                . "\ntelah dipertimbangkan oleh {$reviewer->name} dan diteruskan ke Pejabat Berwenang."
            );
        }

        return $isAjax ? response()->json(['success' => true, 'message' => 'Pertimbangan berhasil dikirim ke Pejabat Berwenang.']) : back()->with('success', 'Pertimbangan berhasil dikirim ke Pejabat Berwenang.');
    }

    /**
     * Keputusan oleh Pejabat Berwenang / Ketua PN (Level 2 - Final)
     */
    public function decidePejabat(Request $request, LeaveRequest $leaveRequest)
    {
        $pejabat = Auth::user();

        if (!$leaveRequest->needsPejabatDecision()) {
            return back()->with('error', 'Pengajuan ini tidak dalam status menunggu keputusan pejabat.');
        }

        $request->validate([
            'keputusan' => 'required|in:setuju,ubah,tangguhkan,tolak',
            'catatan_pejabat' => 'nullable|string|max:500',
            'rejection_reason' => 'nullable|in:tanggal_konflik,kuota_habis,alasan_tidak_jelas,dokumen_kurang,lainnya',
        ]);

        $keputusan = $request->keputusan;

        if ($keputusan === 'tolak') {
            $request->validate([
                'catatan_pejabat' => 'required|string|max:500',
                'rejection_reason' => 'required|in:tanggal_konflik,kuota_habis,alasan_tidak_jelas,dokumen_kurang,lainnya',
            ]);
        }

        $statusMap = [
            'setuju' => LeaveRequest::STATUS_DISETUJUI,
            'ubah' => LeaveRequest::STATUS_DIUBAH,
            'tangguhkan' => LeaveRequest::STATUS_DITANGGUHKAN,
            'tolak' => LeaveRequest::STATUS_DITOLAK,
        ];

        // Fix: Wrap status update + balance deduction in a single DB::transaction()
        // with lockForUpdate() on the LeaveRequest row to prevent race conditions.
        DB::transaction(function () use ($request, $leaveRequest, $pejabat, $keputusan, $statusMap) {
            $leaveRequest = LeaveRequest::lockForUpdate()->findOrFail($leaveRequest->id);

            $adminNote = null;
            if ($keputusan === 'tolak' && $request->rejection_reason) {
                $adminNote = '[ALASAN: ' . $request->rejection_reason . '] ' . $request->catatan_pejabat;
            }

            $leaveRequest->update([
                'pejabat_id' => $pejabat->id,
                'keputusan_pejabat' => $keputusan,
                'catatan_pejabat' => $request->catatan_pejabat,
                'admin_note' => $adminNote,
                'decided_at' => now(),
                'status' => $statusMap[$keputusan],
            ]);

            if ($keputusan === 'setuju' && in_array($leaveRequest->type, [LeaveRequest::TYPE_TAHUNAN, LeaveRequest::TYPE_BERSAMA])) {
                $lockedUser = \App\Models\User::lockForUpdate()->find($leaveRequest->user_id);
                $totalDays = $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days;
                $previousBalance = $lockedUser->leave_balance;
                $lockedUser->decrement('leave_balance', $totalDays);

                BalanceAuditService::logBalanceChange(
                    $lockedUser,
                    $previousBalance,
                    $previousBalance - $totalDays,
                    "Pengajuan {$leaveRequest->type_label} disetujui",
                    $leaveRequest->id
                );
            }
        });

        // Invalidate analytics cache so dashboard reflects the new decision immediately
        CacheKeys::forgetAnalytics(now()->year);

        $auditAction = match ($keputusan) {
            'setuju'     => 'approve',
            'tolak'      => 'reject',
            default      => $keputusan,
        };
        AuditLog::log(
            $auditAction,
            LeaveRequest::class,
            $leaveRequest->id,
            null,
            ['status' => $statusMap[$keputusan], 'keputusan_pejabat' => $keputusan, 'catatan_pejabat' => $request->catatan_pejabat],
            "Pejabat {$pejabat->name} memutuskan '{$keputusan}' pada pengajuan cuti #{$leaveRequest->id} milik {$leaveRequest->user?->name}"
        );

        // Kirim email sesuai keputusan (try-catch for OpenWrt sync queue compatibility)
        try {
            if ($keputusan === 'setuju') {
                Mail::queue(new LeaveRequestApproved($leaveRequest, $pejabat->name));
            } elseif ($keputusan === 'tolak') {
                Mail::queue(new LeaveRequestRejected(
                    $leaveRequest,
                    $pejabat->name,
                    $request->catatan_pejabat
                ));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Email gagal dikirim: ' . $e->getMessage());
        }

        $label = match ($keputusan) {
            'setuju' => 'disetujui',
            'ubah' => 'diubah',
            'tangguhkan' => 'ditangguhkan',
            'tolak' => 'ditolak',
        };

        // Notifikasi ke pemohon
        $notifType = $keputusan === 'setuju' ? Notification::TYPE_CUTI_DISETUJUI : ($keputusan === 'tolak' ? Notification::TYPE_CUTI_DITOLAK : Notification::TYPE_CUTI_PERTIMBANGAN);
        Notification::kirim(
            $leaveRequest->user_id,
            'Keputusan Cuti: ' . ucfirst($label),
            "Pengajuan {$leaveRequest->type_label} Anda telah {$label} oleh {$pejabat->name}.",
            $notifType,
            route('leave.show', $leaveRequest)
        );

        // WA: notif keputusan final
        $pemohon = $leaveRequest->user;
        if ($pemohon && $pemohon->wantsWhatsAppNotification()) {
            $tgl = $leaveRequest->start_date->format('d/m/Y') . " - " . $leaveRequest->end_date->format('d/m/Y');
            $pesan = match ($keputusan) {
                'setuju' => "SiCAIR: Selamat! Pengajuan {$leaveRequest->type_label} Anda\n{$tgl}\ntelah DISETUJUI oleh {$pejabat->name}.\nSisa cuti: " . $pemohon->fresh()->leave_balance . " hari.",
                'tolak'  => "SiCAIR: Maaf, pengajuan {$leaveRequest->type_label} Anda\n{$tgl}\nDITOLAK oleh {$pejabat->name}."
                            . ($request->catatan_pejabat ? "\nCatatan: {$request->catatan_pejabat}" : ''),
                default  => "SiCAIR: Pengajuan {$leaveRequest->type_label} Anda\n{$tgl}\ntelah {$label} oleh {$pejabat->name}.",
            };
            WhatsAppService::send($pemohon->telepon, $pesan);
        }

        return back()->with('success', "Pengajuan cuti {$leaveRequest->user->name} {$label}.");
    }

    /**
     * Pengajuan ulang dari cuti yang ditolak atau diubah
     * Redirect ke form create dengan data pre-filled dari pengajuan lama
     */
    public function reapply(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        // Hanya owner yang boleh mengajukan ulang
        if ($leaveRequest->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengajukan ulang cuti ini.');
        }

        // Hanya jika status ditolak atau diubah
        if (!in_array($leaveRequest->status, [LeaveRequest::STATUS_DITOLAK, LeaveRequest::STATUS_REJECTED, LeaveRequest::STATUS_DIUBAH])) {
            return redirect()->route('leave.show', $leaveRequest)
                ->with('error', 'Pengajuan ulang hanya bisa dilakukan untuk cuti yang ditolak atau diminta diubah.');
        }

        return redirect()->route('leave.create', [
            'type'         => $leaveRequest->type,
            'reapply_from' => $leaveRequest->id,
        ]);
    }

    /**
     * Bulk approve/reject/tangguhkan oleh Ketua/Pejabat Berwenang
     */
    public function bulkDecide(Request $request)
    {
        $request->validate([
            'ids'      => 'required|array|min:1',
            'ids.*'    => 'exists:leave_requests,id',
            'decision' => 'required|in:setuju,tolak,tangguhkan',
            'catatan'  => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        if (!$user->canApproveAsPejabat() && !$user->isAdmin()) {
            abort(403);
        }

        $statusMap = [
            'setuju'     => LeaveRequest::STATUS_DISETUJUI,
            'tolak'      => LeaveRequest::STATUS_DITOLAK,
            'tangguhkan' => LeaveRequest::STATUS_DITANGGUHKAN,
        ];

        $newStatus = $statusMap[$request->decision];
        $count = 0;

        foreach ($request->ids as $id) {
            $leave = LeaveRequest::with('user')->find($id);
            if (!$leave || !in_array($leave->status, [LeaveRequest::STATUS_PERTIMBANGAN, LeaveRequest::STATUS_DIAJUKAN])) {
                continue;
            }

            $leave->update([
                'status'            => $newStatus,
                'pejabat_id'        => $user->id,
                'keputusan_pejabat' => $request->decision,
                'catatan_pejabat'   => $request->catatan,
                'decided_at'        => now(),
            ]);

            if ($request->decision === 'setuju' && in_array($leave->type, [LeaveRequest::TYPE_TAHUNAN, LeaveRequest::TYPE_BERSAMA])) {
                DB::transaction(function () use ($leave) {
                    $lockedUser = \App\Models\User::lockForUpdate()->find($leave->user_id);
                    $totalDays = $leave->total_hari_kerja ?? $leave->total_days ?? 0;
                    $previousBalance = $lockedUser->leave_balance;
                    $lockedUser->decrement('leave_balance', $totalDays);

                    BalanceAuditService::logBalanceChange(
                        $lockedUser,
                        $previousBalance,
                        $previousBalance - $totalDays,
                        "Pengajuan {$leave->type_label} disetujui (bulk)",
                        $leave->id
                    );
                });
            }

            AuditLog::log(
                $request->decision === 'setuju' ? 'approve' : ($request->decision === 'tolak' ? 'reject' : $request->decision),
                LeaveRequest::class,
                $leave->id,
                null,
                ['status' => $newStatus, 'keputusan_pejabat' => $request->decision, 'catatan_pejabat' => $request->catatan],
                "Pejabat {$user->name} bulk-{$request->decision} pengajuan cuti #{$leave->id} milik {$leave->user?->name}"
            );

            $notifType = $request->decision === 'setuju'
                ? Notification::TYPE_CUTI_DISETUJUI
                : ($request->decision === 'tolak' ? Notification::TYPE_CUTI_DITOLAK : Notification::TYPE_CUTI_PERTIMBANGAN);

            $notifTitle = match ($request->decision) {
                'setuju'     => 'Cuti Disetujui',
                'tolak'      => 'Cuti Ditolak',
                'tangguhkan' => 'Cuti Ditangguhkan',
            };

            $notifMessage = "Pengajuan {$leave->type_label} Anda telah " .
                match ($request->decision) {
                    'setuju'     => 'disetujui',
                    'tolak'      => 'ditolak',
                    'tangguhkan' => 'ditangguhkan',
                } .
                " oleh {$user->name}" .
                ($request->catatan ? '. Catatan: ' . $request->catatan : '.');

            Notification::kirim(
                $leave->user_id,
                $notifTitle,
                $notifMessage,
                $notifType,
                route('leave.show', $leave->id)
            );

            // WA: notif bulk decision
            if ($leave->user && $leave->user->wantsWhatsAppNotification()) {
                $statusLabel = match ($request->decision) {
                    'setuju'     => 'DISETUJUI',
                    'tolak'      => 'DITOLAK',
                    'tangguhkan' => 'DITANGGUHKAN',
                };
                $tgl = $leave->start_date->format('d/m/Y') . " - " . $leave->end_date->format('d/m/Y');
                WhatsAppService::send($leave->user->telepon,
                    "SiCAIR: Pengajuan {$leave->type_label} Anda\n{$tgl}\ntelah {$statusLabel} oleh {$user->name}."
                    . ($request->catatan ? "\nCatatan: {$request->catatan}" : '')
                );
            }

            $count++;
        }

        // Invalidate analytics cache
        CacheKeys::forgetAnalytics(now()->year);

        return back()->with('success', "{$count} pengajuan berhasil diproses.");
    }

    /**
     * Bulk teruskan ke ketua (pertimbangan_atasan) oleh Atasan
     */
    public function bulkPertimbangan(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:leave_requests,id',
        ]);

        $user  = auth()->user();
        $count = 0;

        foreach ($request->ids as $id) {
            $leave = LeaveRequest::where('id', $id)
                ->where('atasan_reviewer_id', $user->id)
                ->whereIn('status', [
                    LeaveRequest::STATUS_DIAJUKAN,
                    LeaveRequest::STATUS_PENDING,
                ])
                ->first();

            if (!$leave) continue;

            $leave->status = LeaveRequest::STATUS_PERTIMBANGAN;
            $leave->save();
            $count++;
        }

        return redirect()->route('keputusan.index', ['tab' => 'review'])
            ->with('success', "{$count} pengajuan berhasil diteruskan ke ketua.");
    }

    /**
     * Detail pengajuan cuti
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $authUser = auth()->user();

        // Fix: Only the owner or an authorized role may view a leave request.
        if (
            $leaveRequest->user_id !== $authUser->id
            && !$authUser->isAdmin()
            && !$authUser->isAtasan()
            && !$authUser->canApproveAsPejabat()
        ) {
            abort(403);
        }

        $leaveRequest->load(['user', 'atasanReviewer', 'pejabat']);
        return view('leave.show', compact('leaveRequest'));
    }

    // ===== Backward compat methods (old admin approve/reject) =====

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $pejabat = Auth::user();
        $adminNote = $request->input('admin_note');

        // FIX #5: Use DB transaction with row-level lock to prevent race conditions
        $error = DB::transaction(function () use ($leaveRequest, $pejabat, $adminNote) {
            $lockedUser = \App\Models\User::lockForUpdate()->find($leaveRequest->user_id);
            $totalDays = $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days;

            if (in_array($leaveRequest->type, [LeaveRequest::TYPE_TAHUNAN, LeaveRequest::TYPE_BERSAMA]) && $totalDays > $lockedUser->leave_balance) {
                return "Sisa cuti pegawai tidak mencukupi ({$lockedUser->leave_balance} hari tersisa).";
            }

            $leaveRequest->update([
                'status' => LeaveRequest::STATUS_DISETUJUI,
                'admin_note' => $adminNote,
                'pejabat_id' => $pejabat->id,
                'keputusan_pejabat' => 'setuju',
                'decided_at' => now(),
            ]);

            if (in_array($leaveRequest->type, [LeaveRequest::TYPE_TAHUNAN, LeaveRequest::TYPE_BERSAMA])) {
                $previousBalance = $lockedUser->leave_balance;
                $lockedUser->decrement('leave_balance', $totalDays);

                BalanceAuditService::logBalanceChange(
                    $lockedUser,
                    $previousBalance,
                    $previousBalance - $totalDays,
                    "Pengajuan {$leaveRequest->type_label} disetujui",
                    $leaveRequest->id
                );
            }

            return null;
        });

        if ($error) {
            return back()->with('error', $error);
        }

        // Invalidate analytics cache so dashboard reflects the approval immediately
        CacheKeys::forgetAnalytics(now()->year);

        AuditLog::log(
            'approve',
            LeaveRequest::class,
            $leaveRequest->id,
            null,
            ['status' => LeaveRequest::STATUS_DISETUJUI],
            "Admin/pejabat {$pejabat->name} menyetujui pengajuan cuti #{$leaveRequest->id} milik {$leaveRequest->user?->name}"
        );

        // Kirim email persetujuan
        try {
            Mail::queue(new LeaveRequestApproved($leaveRequest, $pejabat->name));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Email gagal dikirim: ' . $e->getMessage());
        }

        $totalDays = $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days;
        return back()->with('success', "Cuti {$leaveRequest->user->name} disetujui ($totalDays hari).");
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $pejabat = Auth::user();
        $adminNote = $request->input('admin_note');

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_DITOLAK,
            'admin_note' => $adminNote,
            'pejabat_id' => $pejabat->id,
            'keputusan_pejabat' => 'tolak',
            'catatan_pejabat' => $adminNote,
            'decided_at' => now(),
        ]);

        AuditLog::log(
            'reject',
            LeaveRequest::class,
            $leaveRequest->id,
            null,
            ['status' => LeaveRequest::STATUS_DITOLAK, 'admin_note' => $adminNote],
            "Admin/pejabat {$pejabat->name} menolak pengajuan cuti #{$leaveRequest->id} milik {$leaveRequest->user?->name}"
        );

        // Kirim email penolakan
        try {
            Mail::queue(new LeaveRequestRejected($leaveRequest, $pejabat->name, $adminNote));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Email gagal dikirim: ' . $e->getMessage());
        }

        return back()->with('success', "Pengajuan cuti {$leaveRequest->user->name} ditolak.");
    }

    /**
     * Export single leave request to PDF
     */
    public function exportPdf(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        // Check authorization
        if ($leaveRequest->user_id !== $user->id && !$user->isAdmin() && !$user->isKetua()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk export dokumen ini.');
        }

        $pdf = PdfExportService::exportLeaveRequest($leaveRequest);
        return $pdf;
    }

    /**
     * Export Surat Permohonan Cuti (formal letter PDF)
     */
    public function exportSuratPermohonan(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        // Pemohon, atasan langsung, panitera, sekretaris, ketua, atau admin
        if (
            $leaveRequest->user_id !== $user->id
            && !$user->isAdmin()
            && !$user->isKetua()
            && !$user->isPanitera()
            && !$user->isSekretaris()
            && $leaveRequest->user->atasan_id !== $user->id
        ) {
            return back()->with('error', 'Anda tidak memiliki akses untuk export surat ini.');
        }

        return PdfExportService::exportSuratPermohonan($leaveRequest);
    }

    /**
     * Export Surat Permohonan Cuti sebagai DOCX (folio)
     */
    public function exportSuratPermohonanDocx(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        if (
            $leaveRequest->user_id !== $user->id
            && !$user->isAdmin()
            && !$user->isKetua()
            && !$user->isPanitera()
            && !$user->isSekretaris()
            && $leaveRequest->user->atasan_id !== $user->id
        ) {
            return back()->with('error', 'Anda tidak memiliki akses untuk export surat ini.');
        }

        return DocxExportService::exportSuratPermohonanDocx($leaveRequest);
    }

    /**
     * Export Form Permintaan dan Pemberian Cuti (SEMA No. 13/2019) sebagai DOCX
     */
    public function exportFormPermintaanCuti(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        if (
            $leaveRequest->user_id !== $user->id
            && !$user->isAdmin()
            && !$user->isKetua()
            && !$user->isPanitera()
            && !$user->isSekretaris()
            && $leaveRequest->user->atasan_id !== $user->id
        ) {
            return back()->with('error', 'Anda tidak memiliki akses untuk export form ini.');
        }

        return DocxExportService::exportFormPermintaanCutiTemplate($leaveRequest);
    }

    /**
     * Export all leave requests as PDF report
     */
    public function exportAllPdf(Request $request)
    {
        $user = Auth::user();

        // Only admin and ketua can export all
        if (!$user->isAdmin() && !$user->isKetua()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk export laporan ini.');
        }

        $query = LeaveRequest::query();

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        $leaveRequests = $query->latest()->get();

        $pdf = PdfExportService::exportLeaveRequestSummary($leaveRequests);
        return $pdf;
    }

    /**
     * Export leave summary for current user
     */
    public function exportSummaryPdf(Request $request)
    {
        $user = Auth::user();
        $year = $request->input('year', date('Y'));

        $pdf = PdfExportService::exportBalanceReport($user);
        return $pdf;
    }

    /**
     * Sprint 1 #2: Check if requested dates conflict with existing active leaves.
     * Returns JSON: { conflict: bool, message: string? }
     */
    public function checkConflict(Request $request)
    {
        $user = Auth::user();
        $start = $request->query('start');
        $end   = $request->query('end');

        if (!$start || !$end) {
            return response()->json(['conflict' => false]);
        }

        $conflict = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', [
                LeaveRequest::STATUS_DIAJUKAN,
                LeaveRequest::STATUS_PERTIMBANGAN,
                LeaveRequest::STATUS_DISETUJUI,
                LeaveRequest::STATUS_APPROVED,
            ])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start)
                         ->where('end_date', '>=', $end);
                  });
            })
            ->first();

        if ($conflict) {
            $startFmt = \Carbon\Carbon::parse($conflict->start_date)->format('d/m/Y');
            $endFmt   = \Carbon\Carbon::parse($conflict->end_date)->format('d/m/Y');
            return response()->json([
                'conflict' => true,
                'message'  => "Anda sudah memiliki pengajuan {$conflict->type_label} pada {$startFmt} – {$endFmt} (status: {$conflict->status}).",
            ]);
        }

        return response()->json(['conflict' => false]);
    }

    /**
     * Riwayat cuti lengkap milik user yang sedang login
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        $filterStatus = $request->input('status', '');
        $filterType   = $request->input('type', '');
        $filterYear   = $request->input('year', '');

        $query = LeaveRequest::where('user_id', $user->id)
            ->when($filterStatus, fn($q) => $q->where('status', $filterStatus))
            ->when($filterType,   fn($q) => $q->where('type', $filterType))
            ->when($filterYear,   fn($q) => $q->whereRaw("strftime('%Y', start_date) = ?", [(string)$filterYear]))
            ->with(['pejabat', 'atasanReviewer'])
            ->latest();

        $leaves = $query->paginate(15)->withQueryString();

        $statusLabels = LeaveRequest::statusLabels();
        $typeLabels   = LeaveRequest::typeLabels();

        $years = LeaveRequest::where('user_id', $user->id)
            ->selectRaw("strftime('%Y', start_date) as yr")
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr');

        return view('leave.history', compact(
            'leaves', 'filterStatus', 'filterType', 'filterYear',
            'statusLabels', 'typeLabels', 'years'
        ));
    }

    /**
     * Ringkasan & statistik cuti milik user yang sedang login
     */
    public function saya(Request $request)
    {
        $user = Auth::user();
        $year = (int) $request->input('year', date('Y'));

        $leaveBalance = $user->leave_balance ?? 0;

        $upcoming = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', [
                LeaveRequest::STATUS_DISETUJUI,
                LeaveRequest::STATUS_APPROVED,
                LeaveRequest::STATUS_DIAJUKAN,
                LeaveRequest::STATUS_PERTIMBANGAN,
            ])
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->get();

        $pendingCount = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', [LeaveRequest::STATUS_DIAJUKAN, LeaveRequest::STATUS_PERTIMBANGAN])
            ->count();

        $usedThisYear = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('start_date', $year)
            ->get()
            ->sum(fn($r) => $r->total_hari_kerja ?? $r->total_days ?? 0);

        $recentLeaves = LeaveRequest::where('user_id', $user->id)
            ->with(['pejabat', 'atasanReviewer'])
            ->latest()
            ->take(5)
            ->get();

        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->with(['pejabat', 'atasanReviewer'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('leave.saya', compact(
            'year', 'leaveBalance', 'upcoming', 'pendingCount', 'usedThisYear', 'recentLeaves', 'leaveRequests'
        ));
    }

    // ===== Private helpers =====

    private function addTypeSpecificRules(array &$rules, string $type): void
    {
        // FIX #25: Determine if document is required BEFORE Laravel file validation runs
        // by checking the alasan_cap from the incoming request directly
        $alasanCap = request()->input('alasan_cap');
        $capRequiresDocument = in_array($alasanCap, [
            LeaveRequest::CAP_SAKIT_KERAS,
            LeaveRequest::CAP_ISTRI_MELAHIRKAN,
            LeaveRequest::CAP_MUSIBAH,
        ]);

        switch ($type) {
            case LeaveRequest::TYPE_SAKIT:
                $rules['dokumen_pendukung'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
                break;
            case LeaveRequest::TYPE_MELAHIRKAN:
                $rules['kelahiran_ke'] = 'required|integer|min:1|max:10';
                break;
            case LeaveRequest::TYPE_ALASAN_PENTING:
                $rules['alasan_cap'] = 'required|in:' . implode(',', array_keys(LeaveRequest::capLabels()));
                // If the CAP type requires a document, make it required at the validation layer
                $rules['dokumen_pendukung'] = $capRequiresDocument
                    ? 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
                    : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
                break;
            case LeaveRequest::TYPE_BESAR:
                $rules['dokumen_pendukung'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
                $rules['kelahiran_ke'] = 'nullable|integer|min:1|max:10';
                break;
        }
    }

    private function validateBusinessRules(Request $request, $user, string $type): ?string
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $hariKerja = HariKerjaCalculator::hitungHariKerja($startDate, $endDate);

        switch ($type) {
            case LeaveRequest::TYPE_TAHUNAN:
                // Syarat: bekerja min 1 tahun
                if (!$user->sudahBekerjaSatuTahun()) {
                    return 'Anda belum bekerja minimal 1 tahun. Belum berhak mengajukan cuti tahunan.';
                }
                // Min 5 hari kerja sebelum pelaksanaan
                $hariSebelum = HariKerjaCalculator::hitungHariKerja(now(), $startDate->copy()->subDay());
                if ($hariSebelum < 5) {
                    return 'Pengajuan cuti tahunan minimal 5 hari kerja sebelum pelaksanaan.';
                }
                // Cek sisa cuti
                if ($hariKerja > $user->leave_balance) {
                    return "Jumlah hari kerja ($hariKerja hari) melebihi sisa cuti Anda ($user->leave_balance hari).";
                }
                // Batas consecutive: max 12 hari kerja per pengajuan
                if ($hariKerja > 12) {
                    return 'Cuti tahunan tidak boleh lebih dari 12 hari kerja dalam satu pengajuan. Ajukan terpisah untuk periode berbeda.';
                }
                // Kuota 30%
                $persen = HariKerjaCalculator::hitungPersentaseCutiSaatIni($startDate, $endDate, $user);
                if ($persen >= 30) {
                    return 'Kuota cuti bersamaan sudah mencapai 30% di unit kerja Anda. Silakan pilih tanggal lain.';
                }
                break;

            case LeaveRequest::TYPE_BESAR:
                // Min 5 tahun (kecuali haji/anak ke-4+)
                $isHaji = str_contains(strtolower($request->reason), 'haji');
                $isAnak4 = ($request->kelahiran_ke ?? 0) >= 4;
                if (!$user->sudahBekerjaLimaTahun() && !$isHaji && !$isAnak4) {
                    return 'Cuti besar memerlukan masa kerja minimal 5 tahun.';
                }
                // Min 14 hari sebelum pelaksanaan
                if (now()->diffInDays($startDate) < 14) {
                    return 'Pengajuan cuti besar minimal 14 hari sebelum pelaksanaan.';
                }
                // Max 3 bulan kalender
                if ($startDate->diffInMonths($endDate) > 3) {
                    return 'Cuti besar maksimal 3 bulan kalender.';
                }
                break;

            case LeaveRequest::TYPE_SAKIT:
                // Hakim: SEMA 13/2019 — cuti sakit diizinkan, tapi tetap ada peringatan via flash message
                // Tidak lagi memblokir pengajuan (sebelumnya return error)
                // Max 1 tahun
                if ($startDate->diffInDays($endDate) > 365) {
                    return 'Cuti sakit maksimal 1 tahun (dapat diperpanjang 6 bulan).';
                }
                // > 14 hari wajib surat keterangan dokter pemerintah
                if ($startDate->diffInDays($endDate) > LeaveRequest::CUTI_SAKIT_SURAT_DOKTER_PEMERINTAH_DAYS) {
                    if (!$request->hasFile('dokumen_pendukung')) {
                        return 'Cuti sakit lebih dari 14 hari wajib melampirkan surat keterangan dokter pemerintah.';
                    }
                }
                break;

            case LeaveRequest::TYPE_MELAHIRKAN:
                // FIX #30: Only female employees can request maternity leave
                if ($user->jenis_kelamin !== 'P') {
                    return 'Cuti Melahirkan hanya dapat diajukan oleh pegawai perempuan.';
                }
                $kelahiranKe = $request->kelahiran_ke;
                // Hanya anak ke-1,2,3 saat PNS
                if ($kelahiranKe > 3) {
                    return 'Kelahiran anak ke-4 dan seterusnya menggunakan Cuti Besar, bukan Cuti Melahirkan.';
                }
                // Durasi harus 3 bulan kalender
                if ($startDate->diffInMonths($endDate) > 3) {
                    return 'Cuti melahirkan adalah 3 bulan kalender.';
                }
                break;

            case LeaveRequest::TYPE_ALASAN_PENTING:
                // Tolak jika alasan umroh
                if (str_contains(strtolower($request->reason), 'umroh') || str_contains(strtolower($request->reason), 'umrah')) {
                    return 'Cuti Karena Alasan Penting TIDAK dapat digunakan untuk ibadah umroh.';
                }
                // Max 1 bulan
                if ($startDate->diffInDays($endDate) > 30) {
                    return 'Cuti karena alasan penting maksimal 1 bulan.';
                }
                // Validasi lampiran wajib untuk jenis tertentu
                $capType = $request->alasan_cap;
                if (in_array($capType, [LeaveRequest::CAP_SAKIT_KERAS, LeaveRequest::CAP_ISTRI_MELAHIRKAN, LeaveRequest::CAP_MUSIBAH])) {
                    if (!$request->hasFile('dokumen_pendukung')) {
                        return 'Dokumen pendukung wajib dilampirkan untuk alasan ini.';
                    }
                }
                // Istri melahirkan hanya untuk laki-laki
                if ($capType === LeaveRequest::CAP_ISTRI_MELAHIRKAN && $user->jenis_kelamin !== 'L') {
                    return 'Alasan "Istri melahirkan/caesar" hanya berlaku untuk pegawai laki-laki.';
                }
                break;

            case LeaveRequest::TYPE_LUAR_TANGGUNGAN:
                if (!$user->sudahBekerjaLimaTahun()) {
                    return 'CLTN memerlukan masa kerja minimal 5 tahun.';
                }
                if (now()->diffInDays($startDate) < 90) {
                    return 'Pengajuan CLTN minimal 3 bulan sebelum pelaksanaan.';
                }
                // SEMA 13/2019: Maks 3 tahun, perpanjangan maks 1 tahun
                $durasiTahun = $startDate->diffInYears($endDate);
                $maxCLTN = LeaveRequest::MAX_CLTN_YEARS + LeaveRequest::MAX_CLTN_EXTENSION_YEARS;
                if ($durasiTahun > $maxCLTN) {
                    return "CLTN maksimal {$maxCLTN} tahun (3 tahun + 1 tahun perpanjangan).";
                }
                break;

            case LeaveRequest::TYPE_BERSAMA:
                // Cuti bersama deduct dari saldo tahunan — cek saldo
                if ($hariKerja > $user->leave_balance) {
                    return "Jumlah hari cuti bersama ($hariKerja hari) melebihi sisa cuti tahunan Anda ($user->leave_balance hari). Cuti bersama deduct dari saldo tahunan.";
                }
                break;
        }

        return null;
    }
}
