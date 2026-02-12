<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
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

    // Pegawai: leave request
    Route::get('/leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
    Route::post('/leave', [LeaveRequestController::class, 'store'])->name('leave.store');

    // Admin: approve/reject
    Route::middleware('admin')->group(function () {
        Route::post('/leave/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave.approve');
        Route::post('/leave/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave.reject');
    });
});
