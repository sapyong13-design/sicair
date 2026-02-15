<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
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

    // === Notifikasi ===
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // === Kalender Cuti ===
    Route::get('/kalender', [KalenderController::class, 'index'])->name('kalender');

    // === Pengajuan Cuti (semua pegawai termasuk atasan/ketua) ===
    Route::get('/leave/select-type', [LeaveRequestController::class, 'selectType'])->name('leave.select-type');
    Route::get('/leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
    Route::post('/leave', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::get('/leave/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave.show');
    Route::get('/leave/{leaveRequest}/export-pdf', [LeaveRequestController::class, 'exportPdf'])->name('leave.export-pdf');
    Route::get('/leave/export/summary', [LeaveRequestController::class, 'exportSummaryPdf'])->name('leave.export-summary');

    // === Dokumen ===
    Route::get('/documents/{leaveRequest}', [DocumentController::class, 'list'])->name('document.list');
    Route::get('/documents/{leaveRequest}/api', [DocumentController::class, 'getDocuments'])->name('document.api');
    Route::get('/documents/{leaveRequest}/view/{documentType?}', [DocumentController::class, 'view'])->name('document.view');
    Route::get('/documents/{leaveRequest}/download/{documentType?}', [DocumentController::class, 'download'])->name('document.download');

    // === Approval Workflow ===
    // Atasan: pertimbangan level 1
    Route::middleware('role:atasan,ketua,admin')->group(function () {
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
        Route::get('/create', [PegawaiController::class, 'create'])->name('create');
        Route::post('/', [PegawaiController::class, 'store'])->name('store');
        Route::get('/{pegawai}', [PegawaiController::class, 'show'])->name('show');
        Route::get('/{pegawai}/edit', [PegawaiController::class, 'edit'])->name('edit');
        Route::put('/{pegawai}', [PegawaiController::class, 'update'])->name('update');
        Route::delete('/{pegawai}', [PegawaiController::class, 'destroy'])->name('destroy');
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

    // === Audit Log (admin only) ===
    Route::middleware('role:admin')->prefix('admin/audit-logs')->name('admin.audit-logs.')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
    });
});
