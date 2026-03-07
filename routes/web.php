<?php

use App\Http\Controllers\AdminLeaveController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AmendmentController;
use App\Http\Controllers\LaporanSaldoCutiController;
use App\Http\Controllers\AppealController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceAdjustmentController;
use App\Http\Controllers\BalanceHistoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DinasLuarController;
use App\Http\Controllers\LaporanBulananController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public routes (no auth required)
Route::get('/offline', function () {
    return view('errors.offline');
})->name('offline');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email')->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', fn () => redirect('/dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // === Profil ===
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/notifications', [ProfileController::class, 'updateNotificationPreferences'])->name('profile.notifications');
    Route::post('/profile/delegate', [ProfileController::class, 'updateDelegate'])->name('profile.delegate');

    // === Notifikasi ===
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // === Kalender Cuti ===
    Route::get('/kalender', [KalenderController::class, 'index'])->name('kalender');
    Route::get('/kalender/leaves-for-day', [KalenderController::class, 'leavesForDay'])->name('kalender.leaves-for-day');
    Route::get('/kalender/export-ics', [KalenderController::class, 'exportIcs'])->name('kalender.export-ics');

    // === Pengajuan Cuti (semua pegawai termasuk atasan/ketua) ===
    Route::get('/leave/check-conflict', [LeaveRequestController::class, 'checkConflict'])->name('leave.check-conflict');
    Route::get('/leave/select-type', [LeaveRequestController::class, 'selectType'])->name('leave.select-type');
    Route::get('/leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
    Route::post('/leave', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::get('/leave/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave.show');
    Route::get('/leave/{leaveRequest}/export-pdf', [LeaveRequestController::class, 'exportPdf'])->name('leave.export-pdf');
    Route::get('/leave/{leaveRequest}/surat-permohonan', [LeaveRequestController::class, 'exportSuratPermohonan'])->name('leave.surat-permohonan');
    Route::get('/leave/{leaveRequest}/surat-permohonan-docx', [LeaveRequestController::class, 'exportSuratPermohonanDocx'])->name('leave.surat-permohonan-docx');
    Route::get('/leave/{leaveRequest}/form-cuti', [LeaveRequestController::class, 'exportFormPermintaanCuti'])->name('leave.form-cuti');
    Route::get('/leave/export/summary', [LeaveRequestController::class, 'exportSummaryPdf'])->name('leave.export-summary');

    // === Riwayat Saldo Cuti ===
    Route::prefix('balance-history')->name('balance-history.')->group(function () {
        Route::get('/', [BalanceHistoryController::class, 'index'])->name('index');
        Route::get('/{user}', [BalanceHistoryController::class, 'show'])->name('show')->middleware('auth');
        Route::get('/{user}/export', [BalanceHistoryController::class, 'export'])->name('export');
    });

    // === Dokumen ===
    Route::get('/documents/{leaveRequest}', [DocumentController::class, 'list'])->name('document.list');
    Route::get('/documents/{leaveRequest}/api', [DocumentController::class, 'getDocuments'])->name('document.api');
    Route::get('/documents/{leaveRequest}/view/{documentType?}', [DocumentController::class, 'view'])->name('document.view');
    Route::get('/documents/{leaveRequest}/download/{documentType?}', [DocumentController::class, 'download'])->name('document.download');
    Route::post('/documents/{leaveRequest}/upload', [DocumentController::class, 'upload'])->name('document.upload');

    // === Amendments ===
    Route::prefix('amendments')->name('amendment.')->group(function () {
        Route::get('/create/{leaveRequest}', [AmendmentController::class, 'create'])->name('create');
        Route::post('/{leaveRequest}', [AmendmentController::class, 'store'])->name('store');
        Route::get('/{amendment}', [AmendmentController::class, 'show'])->name('show');
        Route::post('/{amendment}/approve', [AmendmentController::class, 'approve'])->name('approve');
        Route::post('/{amendment}/reject', [AmendmentController::class, 'reject'])->name('reject');
    });

    // === Appeals ===
    Route::prefix('appeals')->name('appeal.')->group(function () {
        Route::get('/', [AppealController::class, 'index'])->name('index');
        Route::get('/create/{leaveRequest}', [AppealController::class, 'create'])->name('create');
        Route::post('/{leaveRequest}', [AppealController::class, 'store'])->name('store');
        Route::get('/{appeal}', [AppealController::class, 'show'])->name('show');
        Route::post('/{appeal}/approve', [AppealController::class, 'approve'])->name('approve');
        Route::post('/{appeal}/deny', [AppealController::class, 'deny'])->name('deny');
    });

    // === Approval Workflow ===
    // Atasan: pertimbangan level 1
    Route::middleware('role:atasan,panitera,sekretaris,ketua,admin')->group(function () {
        Route::post('/leave/{leaveRequest}/review', [LeaveRequestController::class, 'reviewAtasan'])->name('leave.review');
    });

    // Ketua/Pejabat Berwenang: keputusan final
    Route::middleware('role:ketua,admin')->group(function () {
        Route::post('/leave/{leaveRequest}/decide', [LeaveRequestController::class, 'decidePejabat'])->name('leave.decide');
    });

    // Admin backward compat: approve/reject langsung
    Route::middleware('admin')->group(function () {
        Route::post('/leave/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave.approve');
        Route::post('/leave/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave.reject');
        Route::get('/leave/export/all-pdf', [LeaveRequestController::class, 'exportAllPdf'])->name('leave.export-all-pdf');
    });

    // === Manajemen Pegawai (admin only) ===
    Route::middleware('role:admin')->prefix('pegawai')->name('pegawai.')->group(function () {
        Route::get('/', [PegawaiController::class, 'index'])->name('index');
        Route::get('/export', [PegawaiController::class, 'export'])->name('export');           // Sprint 5 #29
        Route::post('/bulk-action', [PegawaiController::class, 'bulkAction'])->name('bulk-action'); // Sprint 5 #28
        Route::get('/import-template', [PegawaiController::class, 'importTemplate'])->name('import-template');
        Route::post('/import', [PegawaiController::class, 'import'])->name('import');
        Route::get('/create', [PegawaiController::class, 'create'])->name('create');
        Route::post('/', [PegawaiController::class, 'store'])->name('store');
        Route::get('/{pegawai}/riwayat-cuti', [PegawaiController::class, 'riwayatCuti'])->name('riwayat-cuti');
        Route::post('/{pegawai}/saldo-cuti/{year}', [PegawaiController::class, 'updateSaldoCuti'])->name('saldo-cuti');
        Route::patch('/{pegawai}/inline', [PegawaiController::class, 'inlineEdit'])->name('inline');    // Sprint 5 #31
        Route::post('/{pegawai}/upload-photo', [PegawaiController::class, 'uploadPhoto'])->name('upload-photo'); // Sprint 5 #27
        Route::post('/{pegawai}/toggle-active', [PegawaiController::class, 'toggleActive'])->name('toggle-active'); // Sprint 5 #32
        Route::get('/{pegawai}', [PegawaiController::class, 'show'])->name('show');
        Route::get('/{pegawai}/edit', [PegawaiController::class, 'edit'])->name('edit');
        Route::put('/{pegawai}', [PegawaiController::class, 'update'])->name('update');
        Route::delete('/{pegawai}', [PegawaiController::class, 'destroy'])->name('destroy');
    });

    // === Admin: Manajemen Cuti Pegawai (manual entry) ===
    Route::middleware('role:admin')->prefix('admin/leave')->name('admin.leave.')->group(function () {
        Route::get('/create/{user}', [AdminLeaveController::class, 'create'])->name('create');
        Route::post('/create/{user}', [AdminLeaveController::class, 'store'])->name('store');
        Route::get('/{leaveRequest}/edit', [AdminLeaveController::class, 'edit'])->name('edit');
        Route::put('/{leaveRequest}', [AdminLeaveController::class, 'update'])->name('update');
        Route::delete('/{leaveRequest}', [AdminLeaveController::class, 'destroy'])->name('destroy');
    });

    // === Hari Libur (admin only) ===
    Route::middleware('role:admin')->prefix('hari-libur')->name('hari-libur.')->group(function () {
        Route::get('/', [HariLiburController::class, 'index'])->name('index');
        Route::get('/create', [HariLiburController::class, 'create'])->name('create');
        Route::post('/', [HariLiburController::class, 'store'])->name('store');
        Route::get('/{hariLibur}/edit', [HariLiburController::class, 'edit'])->name('edit');
        Route::put('/{hariLibur}', [HariLiburController::class, 'update'])->name('update');
        Route::delete('/{hariLibur}', [HariLiburController::class, 'destroy'])->name('destroy');
    });

    // === Dinas Luar (admin only) ===
    Route::middleware('role:admin')->prefix('dinas-luar')->name('dinas-luar.')->group(function () {
        Route::get('/', [DinasLuarController::class, 'index'])->name('index');
        Route::get('/create', [DinasLuarController::class, 'create'])->name('create');
        Route::post('/', [DinasLuarController::class, 'store'])->name('store');
        Route::get('/{dinasLuar}/edit', [DinasLuarController::class, 'edit'])->name('edit');
        Route::put('/{dinasLuar}', [DinasLuarController::class, 'update'])->name('update');
        Route::delete('/{dinasLuar}', [DinasLuarController::class, 'destroy'])->name('destroy');
    });
    // Download dokumen: accessible by admin + ketua + pemilik record
    Route::get('/dinas-luar/{dinasLuar}/dokumen', [DinasLuarController::class, 'downloadDokumen'])->name('dinas-luar.dokumen');

    // === Laporan Bulanan (admin only) ===
    Route::middleware('role:admin')->prefix('laporan-bulanan')->name('laporan-bulanan.')->group(function () {
        Route::get('/', [LaporanBulananController::class, 'index'])->name('index');
        Route::get('/export', [LaporanBulananController::class, 'export'])->name('export');
    });

    // === Audit Log (admin only) ===
    Route::middleware('role:admin')->prefix('admin/audit-logs')->name('admin.audit-logs.')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
    });

    // === Balance Adjustments (admin only) ===
    Route::middleware('role:admin')->prefix('balance-adjustments')->name('balance-adjustment.')->group(function () {
        Route::get('/', [BalanceAdjustmentController::class, 'index'])->name('index');
        Route::get('/create/{user}', [BalanceAdjustmentController::class, 'create'])->name('create');
        Route::post('/{user}', [BalanceAdjustmentController::class, 'store'])->name('store');
        Route::get('/{balanceAdjustment}', [BalanceAdjustmentController::class, 'show'])->name('show');
        Route::post('/{balanceAdjustment}/approve', [BalanceAdjustmentController::class, 'approve'])->name('approve');
        Route::post('/{balanceAdjustment}/reject', [BalanceAdjustmentController::class, 'reject'])->name('reject');
    });

    // === PDF Export ===
    Route::prefix('pdf-export')->name('pdf-export.')->group(function () {
        Route::get('/leave/{leaveRequest}', [PdfExportController::class, 'leaveRequest'])->name('leave-request');
        Route::get('/balance/{user}', [PdfExportController::class, 'balanceReport'])->name('balance-report');

        // Admin only
        Route::middleware('role:admin')->group(function () {
            Route::get('/all-leaves', [PdfExportController::class, 'allLeaveRequests'])->name('all-leaves');
            Route::get('/statistics', [PdfExportController::class, 'statistics'])->name('statistics');
        });
    });

    // === Analytics (#40, #41, #42, #43) ===
    Route::middleware('role:admin')->prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/export-annual', [AnalyticsController::class, 'exportAnnual'])->name('export-annual');
        Route::get('/export-pdf', [AnalyticsController::class, 'exportPdf'])->name('export-pdf'); // Sprint 7 #40
    });

    // === Laporan Saldo Cuti (Sprint 7 #37) ===
    Route::middleware('role:admin')->prefix('laporan-saldo-cuti')->name('laporan-saldo-cuti.')->group(function () {
        Route::get('/', [LaporanSaldoCutiController::class, 'index'])->name('index');
        Route::get('/export', [LaporanSaldoCutiController::class, 'export'])->name('export');
    });

    // FIX #3: Debug endpoint removed — was publicly accessible and leaked user data
});
