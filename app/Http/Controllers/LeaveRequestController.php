<?php

namespace App\Http\Controllers;

use App\Mail\LeaveRequestApproved;
use App\Mail\LeaveRequestNeedsConsideration;
use App\Mail\LeaveRequestRejected;
use App\Mail\LeaveRequestSubmitted;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Services\CutiTahunanCalculator;
use App\Services\HariKerjaCalculator;
use App\Services\PdfExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LeaveRequestController extends Controller
{
    /**
     * Pilih jenis cuti
     */
    public function selectType()
    {
        return view('leave.select-type');
    }

    /**
     * Form pengajuan cuti (semua jenis)
     */
    public function create(Request $request)
    {
        $type = $request->query('type', LeaveRequest::TYPE_TAHUNAN);
        $user = Auth::user();

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

        return view('leave.create', compact('type', 'cutiInfo'));
    }

    /**
     * Simpan pengajuan cuti
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $type = $request->input('type', LeaveRequest::TYPE_TAHUNAN);

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

        // Hitung hari kerja
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $hariKerja = HariKerjaCalculator::hitungHariKerja($startDate, $endDate);

        // Upload dokumen jika ada
        $dokumenPath = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $dokumenPath = $request->file('dokumen_pendukung')->store('dokumen-cuti', 'public');
        }

        $leaveRequest = LeaveRequest::create([
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
            'status' => LeaveRequest::STATUS_DIAJUKAN,
        ]);

        // Kirim email ke pemohon
        Mail::queue(new LeaveRequestSubmitted($leaveRequest));

        // Kirim notifikasi ke atasan
        if ($user->atasan_id) {
            Notification::kirim(
                $user->atasan_id,
                'Pengajuan Cuti Baru',
                "{$user->name} mengajukan " . LeaveRequest::typeLabels()[$type],
                Notification::TYPE_CUTI_DIAJUKAN,
                '/dashboard#pending-review'
            );
        }

        return redirect('/dashboard')->with('success', 'Pengajuan ' . LeaveRequest::typeLabels()[$type] . ' berhasil dikirim.');
    }

    /**
     * Pertimbangan oleh Atasan Langsung (Level 1)
     */
    public function reviewAtasan(Request $request, LeaveRequest $leaveRequest)
    {
        $reviewer = Auth::user();

        if (!$leaveRequest->needsAtasanReview()) {
            return back()->with('error', 'Pengajuan ini tidak dalam status menunggu pertimbangan atasan.');
        }

        $request->validate([
            'pertimbangan' => 'required|in:setuju,ubah,tangguhkan,tolak',
            'catatan_atasan' => 'nullable|string|max:500',
        ]);

        $pertimbangan = $request->pertimbangan;

        if ($pertimbangan === 'tolak') {
            $request->validate(['catatan_atasan' => 'required|string|max:500']);
            $leaveRequest->update([
                'atasan_reviewer_id' => $reviewer->id,
                'pertimbangan_atasan' => $pertimbangan,
                'catatan_atasan' => $request->catatan_atasan,
                'reviewed_at' => now(),
                'status' => LeaveRequest::STATUS_DITOLAK,
            ]);

            // Kirim email penolakan
            Mail::queue(new LeaveRequestRejected(
                $leaveRequest,
                $reviewer->name,
                $request->catatan_atasan
            ));

            Notification::kirim(
                $leaveRequest->user_id,
                'Pengajuan Cuti Ditolak',
                "Pengajuan {$leaveRequest->type_label} Anda ditolak oleh {$reviewer->name}.",
                Notification::TYPE_CUTI_DITOLAK,
                route('leave.show', $leaveRequest)
            );

            return back()->with('success', 'Pengajuan cuti ditolak.');
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
        Mail::queue(new LeaveRequestNeedsConsideration($leaveRequest));

        // Notifikasi ke pemohon
        Notification::kirim(
            $leaveRequest->user_id,
            'Cuti Sedang Dipertimbangkan',
            "Pengajuan {$leaveRequest->type_label} Anda telah dipertimbangkan oleh {$reviewer->name}.",
            Notification::TYPE_CUTI_PERTIMBANGAN,
            route('leave.show', $leaveRequest)
        );

        return back()->with('success', 'Pertimbangan berhasil dikirim ke Pejabat Berwenang.');
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
        ]);

        $keputusan = $request->keputusan;

        if ($keputusan === 'tolak') {
            $request->validate(['catatan_pejabat' => 'required|string|max:500']);
        }

        $statusMap = [
            'setuju' => LeaveRequest::STATUS_DISETUJUI,
            'ubah' => LeaveRequest::STATUS_DIUBAH,
            'tangguhkan' => LeaveRequest::STATUS_DITANGGUHKAN,
            'tolak' => LeaveRequest::STATUS_DITOLAK,
        ];

        $leaveRequest->update([
            'pejabat_id' => $pejabat->id,
            'keputusan_pejabat' => $keputusan,
            'catatan_pejabat' => $request->catatan_pejabat,
            'decided_at' => now(),
            'status' => $statusMap[$keputusan],
        ]);

        // Jika disetujui, kurangi leave_balance untuk cuti tahunan
        if ($keputusan === 'setuju' && $leaveRequest->type === LeaveRequest::TYPE_TAHUNAN) {
            $leaveRequest->user->decrement('leave_balance', $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days);
        }

        // Kirim email sesuai keputusan
        if ($keputusan === 'setuju') {
            Mail::queue(new LeaveRequestApproved($leaveRequest, $pejabat->name));
        } elseif ($keputusan === 'tolak') {
            Mail::queue(new LeaveRequestRejected(
                $leaveRequest,
                $pejabat->name,
                $request->catatan_pejabat
            ));
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

        return back()->with('success', "Pengajuan cuti {$leaveRequest->user->name} {$label}.");
    }

    /**
     * Detail pengajuan cuti
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user', 'atasanReviewer', 'pejabat']);
        return view('leave.show', compact('leaveRequest'));
    }

    // ===== Backward compat methods (old admin approve/reject) =====

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $user = $leaveRequest->user;
        $totalDays = $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days;

        if ($leaveRequest->type === LeaveRequest::TYPE_TAHUNAN && $totalDays > $user->leave_balance) {
            return back()->with('error', "Sisa cuti pegawai tidak mencukupi ($user->leave_balance hari tersisa).");
        }

        $pejabat = Auth::user();
        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_DISETUJUI,
            'admin_note' => $request->input('admin_note'),
            'pejabat_id' => $pejabat->id,
            'keputusan_pejabat' => 'setuju',
            'decided_at' => now(),
        ]);

        if ($leaveRequest->type === LeaveRequest::TYPE_TAHUNAN) {
            $user->decrement('leave_balance', $totalDays);
        }

        // Kirim email persetujuan
        Mail::queue(new LeaveRequestApproved($leaveRequest, $pejabat->name));

        return back()->with('success', "Cuti {$user->name} disetujui ($totalDays hari).");
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

        // Kirim email penolakan
        Mail::queue(new LeaveRequestRejected($leaveRequest, $pejabat->name, $adminNote));

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

        $pdf = (new PdfExportService())->exportLeaveRequest($leaveRequest);
        return $pdf->download("leave-request-{$leaveRequest->id}.pdf");
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

        $pdf = (new PdfExportService())->exportLeaveRequests($leaveRequests);
        return $pdf->download("leave-requests-report-" . now()->format('Y-m-d') . ".pdf");
    }

    /**
     * Export leave summary for current user
     */
    public function exportSummaryPdf(Request $request)
    {
        $user = Auth::user();
        $year = $request->input('year', date('Y'));

        $pdf = (new PdfExportService())->exportLeaveSummary($user, $year);
        return $pdf->download("leave-summary-{$year}.pdf");
    }

    // ===== Private helpers =====

    private function addTypeSpecificRules(array &$rules, string $type): void
    {
        switch ($type) {
            case LeaveRequest::TYPE_SAKIT:
                $rules['dokumen_pendukung'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
                break;
            case LeaveRequest::TYPE_MELAHIRKAN:
                $rules['kelahiran_ke'] = 'required|integer|min:1';
                break;
            case LeaveRequest::TYPE_ALASAN_PENTING:
                $rules['alasan_cap'] = 'required|in:' . implode(',', array_keys(LeaveRequest::capLabels()));
                $rules['dokumen_pendukung'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
                break;
            case LeaveRequest::TYPE_BESAR:
                $rules['dokumen_pendukung'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
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
                // Kuota 30%
                $persen = HariKerjaCalculator::hitungPersentaseCutiSaatIni($startDate, $endDate, $user->unit_kerja);
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
                // Hakim: tampilkan peringatan (diatur Perma 7/2016)
                if ($user->isHakim()) {
                    return 'Cuti sakit untuk Hakim diatur dalam Perma No. 7/2016. Silakan konsultasikan dengan admin.';
                }
                // Max 1 tahun
                if ($startDate->diffInDays($endDate) > 365) {
                    return 'Cuti sakit maksimal 1 tahun (dapat diperpanjang 6 bulan).';
                }
                break;

            case LeaveRequest::TYPE_MELAHIRKAN:
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
                break;
        }

        return null;
    }
}
