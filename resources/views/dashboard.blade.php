@extends('layouts.app')

@section('title', 'Dashboard - SiHEALING')

@section('content')
{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sh-page-title mb-1">
                <i class="ti ti-layout-dashboard me-1" style="color: var(--sh-primary);"></i>
                Dashboard
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Selamat datang kembali, <strong class="text-dark">{{ $user->name }}</strong>
@php
    $roleLabels = [
        'admin' => 'Admin', 'ketua' => 'Ketua', 'atasan' => 'Atasan',
        'panitera' => 'Panitera', 'sekretaris' => 'Sekretaris',
        'pegawai' => 'Pegawai', 'hakim' => 'Hakim', 'hakim_ad_hoc' => 'Hakim Ad Hoc',
    ];
    $roleBadge = $user->isAdmin() ? 'approved' : ($user->isKetua() ? 'pending' : ($user->isAtasan() ? 'pending' : 'approved'));
    $roleIcon  = $user->isAdmin() ? 'shield-check' : ($user->isKetua() ? 'gavel' : ($user->isAtasan() ? 'user-check' : 'user'));
@endphp
                <span class="sh-badge sh-badge-{{ $roleBadge }} ms-1" style="font-size: 0.7rem;">
                    <i class="ti ti-{{ $roleIcon }}"></i>
                    {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                </span>
            </div>
        </div>
        @if(!$user->isAdmin() && $user->bolehCuti())
        <a href="{{ route('leave.select-type') }}" class="btn btn-primary sh-btn-primary">
            <i class="ti ti-file-plus me-1"></i> Ajukan Cuti
        </a>
        @endif
    </div>
</div>

@if(session('success'))
<div class="alert mb-4" style="background: var(--sh-success-light); color: var(--sh-success); border-radius: 12px; border: none;">
    <i class="ti ti-circle-check me-2"></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert mb-4" style="background: var(--sh-danger-light); color: var(--sh-danger); border-radius: 12px; border: none;">
    <i class="ti ti-alert-circle me-2"></i> {{ session('error') }}
</div>
@endif

@if($user->isAdmin())
    {{-- ============================= --}}
    {{--       ADMIN DASHBOARD         --}}
    {{-- ============================= --}}

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sh-stat-card stat-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Total Pegawai</div>
                            <div class="sh-stat-number" style="color: var(--sh-primary);">{{ $totalPegawai }}</div>
                        </div>
                        <div class="sh-stat-icon icon-primary">
                            <i class="ti ti-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sh-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Menunggu Proses</div>
                            <div class="sh-stat-number" style="color: var(--sh-warning);">{{ $pendingRequests->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-warning">
                            <i class="ti ti-clock-hour-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sh-stat-card stat-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Disetujui</div>
                            <div class="sh-stat-number" style="color: var(--sh-success);">{{ $recentDecisions->filter(fn($r) => $r->isApproved())->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sh-stat-card stat-danger">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Ditolak</div>
                            <div class="sh-stat-number" style="color: var(--sh-danger);">{{ $recentDecisions->filter(fn($r) => $r->isRejected())->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-danger">
                            <i class="ti ti-circle-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- #10 & #11: Widgets Cuti Hari Ini + Mendatang --}}
    <div class="row g-3 mb-4">
        {{-- #10: Siapa Cuti Hari Ini --}}
        <div class="col-md-6 animate-in">
            <div class="card sh-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-beach me-2" style="color: var(--sh-success);"></i>
                        Cuti Hari Ini
                    </h3>
                    <span class="sh-badge sh-badge-{{ $todayOnLeave->isNotEmpty() ? 'pending' : 'approved' }}">
                        {{ $todayOnLeave->count() }} pegawai
                    </span>
                </div>
                <div class="card-body p-3" style="max-height: 220px; overflow-y: auto;">
                    @if($todayOnLeave->isEmpty())
                    <div class="text-center py-3">
                        <i class="ti ti-users" style="font-size: 2rem; color: var(--sh-text-muted); opacity: 0.3;"></i>
                        <p class="text-muted mb-0 mt-2" style="font-size: 0.85rem;">Tidak ada pegawai yang cuti hari ini</p>
                    </div>
                    @else
                    @foreach($todayOnLeave as $req)
                    <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sh-user-avatar" style="width:32px;height:32px;font-size:0.7rem;background:var(--sh-primary-light);color:var(--sh-primary);border:none;border-radius:8px;flex-shrink:0;">
                            {{ strtoupper(substr($req->user->name, 0, 2)) }}
                        </div>
                        <div class="flex-fill" style="min-width:0;">
                            <div class="fw-semibold" style="font-size:0.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req->user->name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $req->type_label }} &bull; s.d. {{ $req->end_date->format('d M') }}</div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- #11: Cuti Mendatang 7 Hari --}}
        <div class="col-md-6 animate-in">
            <div class="card sh-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-calendar-event me-2" style="color: var(--sh-accent);"></i>
                        Cuti Mendatang (7 Hari)
                    </h3>
                    <span class="sh-badge sh-badge-pending">{{ $upcomingLeaves7Days->count() }}</span>
                </div>
                <div class="card-body p-3" style="max-height: 220px; overflow-y: auto;">
                    @if($upcomingLeaves7Days->isEmpty())
                    <div class="text-center py-3">
                        <i class="ti ti-calendar" style="font-size: 2rem; color: var(--sh-text-muted); opacity: 0.3;"></i>
                        <p class="text-muted mb-0 mt-2" style="font-size: 0.85rem;">Tidak ada cuti disetujui dalam 7 hari ke depan</p>
                    </div>
                    @else
                    @foreach($upcomingLeaves7Days as $req)
                    <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sh-user-avatar" style="width:32px;height:32px;font-size:0.7rem;background:var(--sh-accent-light);color:var(--sh-accent);border:none;border-radius:8px;flex-shrink:0;">
                            {{ strtoupper(substr($req->user->name, 0, 2)) }}
                        </div>
                        <div class="flex-fill" style="min-width:0;">
                            <div class="fw-semibold" style="font-size:0.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req->user->name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $req->start_date->format('d M') }} &bull; {{ $req->type_label }}</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge" style="background:var(--sh-accent-light);color:var(--sh-accent);border-radius:50px;font-size:0.7rem;">{{ $req->start_date->diffInDays(\Carbon\Carbon::today()) }}h lagi</span>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Requests --}}
    <div class="card sh-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-clock-hour-4 me-2" style="color: var(--sh-warning);"></i>
                Pengajuan Menunggu Proses
            </h3>
            @if($pendingRequests->isNotEmpty())
            <span class="sh-badge sh-badge-pending">{{ $pendingRequests->count() }} antrian</span>
            @endif
        </div>

        @if($pendingRequests->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sh-empty-icon"><i class="ti ti-mood-happy"></i></div>
                <h4 class="fw-bold text-dark mb-1">Semua Beres!</h4>
                <p class="text-muted mb-0">Tidak ada pengajuan cuti yang menunggu proses saat ini.</p>
            </div>
        </div>
        @else
        {{-- Desktop Table View --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table sh-table mb-0">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Jenis Cuti</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pendingRequests as $req)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="sh-user-avatar" style="width: 36px; height: 36px; font-size: 0.75rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 10px;">
                                    {{ strtoupper(substr($req->user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size: 0.9rem;">{{ $req->user->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span style="font-size: 0.85rem;">{{ $req->type_label }}</span></td>
                        <td>
                            <div style="font-size: 0.85rem;">{{ $req->start_date->format('d M Y') }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">s.d. {{ $req->end_date->format('d M Y') }} &middot; {{ $req->total_days }} hari</div>
                        </td>
                        <td>
                            <span class="sh-badge sh-badge-pending">{{ $req->status_label }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="card-body d-md-none">
            @foreach($pendingRequests as $req)
            <div class="card sh-history-card mb-3" style="border-left-color: var(--sh-warning);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sh-user-avatar" style="width: 36px; height: 36px; font-size: 0.75rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($req->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.9rem;">{{ $req->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                            </div>
                        </div>
                        <span class="sh-badge sh-badge-pending">{{ $req->status_label }}</span>
                    </div>
                    <div class="row g-2 mb-2" style="font-size: 0.82rem;">
                        <div class="col-6">
                            <div class="text-muted">Jenis</div>
                            <div class="fw-semibold">{{ $req->type_label }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">Durasi</div>
                            <div class="fw-semibold">{{ $req->total_days }} hari</div>
                        </div>
                    </div>
                    <div class="mb-2" style="font-size: 0.82rem;">
                        <div class="text-muted">Periode</div>
                        <div class="fw-semibold">{{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }}</div>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                            <i class="ti ti-eye me-1"></i> Lihat
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Recent Decisions --}}
    @if($recentDecisions->isNotEmpty())
    <div class="card sh-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-history me-2" style="color: #64748b;"></i>
                Keputusan Terbaru
            </h3>
        </div>

        {{-- Desktop Table View --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table sh-table mb-0">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Jenis</th>
                        <th>Periode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentDecisions as $req)
                    <tr>
                        <td class="fw-semibold" style="font-size: 0.9rem;">{{ $req->user->name }}</td>
                        <td style="font-size: 0.85rem;">{{ $req->type_label }}</td>
                        <td style="font-size: 0.85rem;">{{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }}</td>
                        <td>
                            @if($req->isApproved())
                                <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @elseif($req->isRejected())
                                <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                            @else
                                <span class="sh-badge sh-badge-pending">{{ $req->status_label }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="card-body d-md-none">
            @foreach($recentDecisions as $req)
            <div class="card sh-history-card mb-3" style="border-left-color: #64748b;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;">{{ $req->user->name }}</div>
                        </div>
                        @if($req->isApproved())
                            <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i></span>
                        @elseif($req->isRejected())
                            <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i></span>
                        @else
                            <span class="sh-badge sh-badge-pending">{{ $req->status_label }}</span>
                        @endif
                    </div>
                    <div class="row g-2 mb-2" style="font-size: 0.82rem;">
                        <div class="col-6">
                            <div class="text-muted">Jenis</div>
                            <div class="fw-semibold">{{ $req->type_label }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">Status</div>
                            <div class="fw-semibold">
                                @if($req->isApproved())
                                    Disetujui
                                @elseif($req->isRejected())
                                    Ditolak
                                @else
                                    {{ $req->status_label }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div style="font-size: 0.82rem;">
                        <div class="text-muted">Periode</div>
                        <div class="fw-semibold">{{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Fitur 7: Charts & Statistik --}}
    <div class="row g-3 mt-2">
        <div class="col-lg-6 animate-in">
            <div class="card sh-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-chart-bar me-2" style="color: var(--sh-primary);"></i>
                        Pengajuan per Jenis Cuti ({{ date('Y') }})
                    </h3>
                </div>
                <div class="card-body p-3">
                    <div class="sh-chart-container">
                        <canvas id="chartByType"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 animate-in">
            <div class="card sh-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-chart-line me-2" style="color: var(--sh-accent);"></i>
                        Tren Bulanan ({{ date('Y') }})
                    </h3>
                </div>
                <div class="card-body p-3">
                    <div class="sh-chart-container">
                        <canvas id="chartMonthly"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 animate-in">
            <div class="card sh-card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-chart-pie me-2" style="color: var(--sh-success);"></i>
                        Distribusi Status ({{ date('Y') }})
                    </h3>
                </div>
                <div class="card-body p-3">
                    <div class="sh-chart-container">
                        <canvas id="chartByStatus"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

@elseif($user->isKetua())
    {{-- ============================= --}}
    {{--       KETUA DASHBOARD         --}}
    {{-- ============================= --}}

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 animate-in">
            <div class="card sh-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Perlu Keputusan</div>
                            <div class="sh-stat-number" style="color: var(--sh-warning);">{{ $needsDecision->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">menunggu keputusan Anda</div>
                        </div>
                        <div class="sh-stat-icon icon-warning">
                            <i class="ti ti-gavel"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 animate-in">
            <div class="card sh-stat-card stat-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Keputusan Saya</div>
                            <div class="sh-stat-number" style="color: var(--sh-success);">{{ $recentDecisions->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">total keputusan</div>
                        </div>
                        <div class="sh-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Needs Decision (already reviewed by atasan) --}}
    <div class="card sh-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-gavel me-2" style="color: var(--sh-warning);"></i>
                Menunggu Keputusan Anda
            </h3>
            @if($needsDecision->isNotEmpty())
            <span class="sh-badge sh-badge-pending">{{ $needsDecision->count() }} antrian</span>
            @endif
        </div>
        @if($needsDecision->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sh-empty-icon"><i class="ti ti-mood-happy"></i></div>
                <h4 class="fw-bold text-dark mb-1">Tidak Ada Antrian</h4>
                <p class="text-muted mb-0">Tidak ada pengajuan yang menunggu keputusan Anda saat ini.</p>
            </div>
        </div>
        @else
        <div class="card-body p-3">
            @foreach($needsDecision as $req)
            <div class="card sh-history-card status-{{ $req->status }} mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sh-user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($req->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.95rem;">{{ $req->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                            </div>
                        </div>
                        <span class="sh-badge sh-badge-pending">{{ $req->type_label }}</span>
                    </div>
                    <div class="row g-2 mb-2" style="font-size: 0.82rem;">
                        <div class="col-sm-6">
                            <i class="ti ti-calendar me-1 text-muted"></i>
                            {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                        </div>
                        <div class="col-sm-6">
                            <i class="ti ti-clock me-1 text-muted"></i>
                            {{ $req->total_days }} hari
                            @if($req->total_hari_kerja)
                                ({{ $req->total_hari_kerja }} hari kerja)
                            @endif
                        </div>
                    </div>
                    @if($req->atasanReviewer)
                    <div class="mb-2" style="background: var(--sh-primary-light); border-radius: 8px; padding: 0.5rem 0.75rem; font-size: 0.82rem;">
                        <i class="ti ti-user-check me-1" style="color: var(--sh-primary);"></i>
                        <strong>Pertimbangan {{ $req->atasanReviewer->name }}:</strong>
                        @if($req->pertimbangan_atasan === 'setuju')
                            <span style="color: var(--sh-success);">Disetujui</span>
                        @elseif($req->pertimbangan_atasan === 'ubah')
                            <span style="color: var(--sh-primary);">Perubahan</span>
                        @elseif($req->pertimbangan_atasan === 'tangguhkan')
                            <span style="color: var(--sh-warning);">Ditangguhkan</span>
                        @endif
                        @if($req->catatan_atasan)
                            &mdash; {{ Str::limit($req->catatan_atasan, 80) }}
                        @endif
                    </div>
                    @else
                    <div class="mb-2" style="background: #ecfdf5; border-radius: 8px; padding: 0.5rem 0.75rem; font-size: 0.82rem;">
                        <i class="ti ti-arrow-forward me-1" style="color: var(--sh-success);"></i>
                        <strong>Pengajuan Langsung</strong> &mdash; tanpa pertimbangan atasan
                    </div>
                    @endif
                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-sm sh-btn-primary flex-fill" data-bs-toggle="modal" data-bs-target="#decisionModal{{ $req->id }}">
                            <i class="ti ti-gavel me-1"></i> Beri Keputusan
                        </button>
                        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @include('partials.pejabat-decision-modal', ['req' => $req])
            @endforeach
        </div>
        @endif
    </div>

    {{-- Ketua's Own Leave --}}
    @if(isset($leaveRequests) && $leaveRequests->isNotEmpty())
    <div class="card sh-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-list-details me-2" style="color: #64748b;"></i>
                Pengajuan Cuti Saya
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table sh-table mb-0">
                <thead>
                    <tr><th>Jenis</th><th>Periode</th><th>Durasi</th><th>Status</th></tr>
                </thead>
                <tbody>
                @foreach($leaveRequests as $req)
                    <tr>
                        <td style="font-size: 0.85rem;">{{ $req->type_label }}</td>
                        <td style="font-size: 0.85rem;">{{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }}</td>
                        <td><span class="badge bg-blue-lt" style="border-radius: 50px;">{{ $req->total_days }} hari</span></td>
                        <td>
                            @if($req->isApproved())
                                <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @elseif($req->isRejected())
                                <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                            @else
                                <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

@elseif($user->isAtasan())
    {{-- ============================= --}}
    {{--      ATASAN DASHBOARD         --}}
    {{-- ============================= --}}

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Perlu Pertimbangan</div>
                            <div class="sh-stat-number" style="color: var(--sh-warning);">{{ $pendingReview->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">dari bawahan Anda</div>
                        </div>
                        <div class="sh-stat-icon icon-warning">
                            <i class="ti ti-checklist"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Sudah Dipertimbangkan</div>
                            <div class="sh-stat-number" style="color: var(--sh-success);">{{ $reviewedByMe->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">riwayat pertimbangan</div>
                        </div>
                        <div class="sh-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Cuti Saya</div>
                            <div class="sh-stat-number" style="color: var(--sh-primary);">{{ $user->leave_balance }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">hari sisa cuti</div>
                        </div>
                        <div class="sh-stat-icon icon-primary">
                            <i class="ti ti-calendar-stats"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Review --}}
    <div class="card sh-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-checklist me-2" style="color: var(--sh-warning);"></i>
                Menunggu Pertimbangan Anda
            </h3>
            @if($pendingReview->isNotEmpty())
            <span class="sh-badge sh-badge-pending">{{ $pendingReview->count() }} antrian</span>
            @endif
        </div>
        @if($pendingReview->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sh-empty-icon"><i class="ti ti-mood-happy"></i></div>
                <h4 class="fw-bold text-dark mb-1">Tidak Ada Antrian</h4>
                <p class="text-muted mb-0">Tidak ada pengajuan bawahan yang menunggu pertimbangan Anda.</p>
            </div>
        </div>
        @else
        <div class="card-body p-3">
            @foreach($pendingReview as $req)
            <div class="card sh-history-card status-{{ $req->status }} mb-3" data-request-id="{{ $req->id }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sh-user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($req->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.95rem;">{{ $req->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                            </div>
                        </div>
                        <span class="sh-badge sh-badge-pending">{{ $req->type_label }}</span>
                    </div>
                    <div class="row g-2 mb-2" style="font-size: 0.82rem;">
                        <div class="col-sm-6">
                            <i class="ti ti-calendar me-1 text-muted"></i>
                            {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                        </div>
                        <div class="col-sm-6">
                            <i class="ti ti-clock me-1 text-muted"></i>
                            {{ $req->total_days }} hari
                        </div>
                    </div>
                    @if($req->reason)
                    <div class="mb-2" style="font-size: 0.82rem; color: #475569;">
                        {{ Str::limit($req->reason, 100) }}
                    </div>
                    @endif
                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-sm sh-btn-primary flex-fill" onclick="openReviewModal{{ $req->id }}()">
                            <i class="ti ti-checklist me-1"></i> Beri Pertimbangan
                        </button>
                        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @include('partials.atasan-review-modal', ['req' => $req])
            @endforeach
        </div>
        @endif
    </div>

    {{-- Reviewed by Me --}}
    @if($reviewedByMe->isNotEmpty())
    <div class="card sh-card mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-history me-2" style="color: #64748b;"></i>
                Riwayat Pertimbangan Saya
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table sh-table mb-0">
                <thead>
                    <tr><th>Pemohon</th><th>Jenis</th><th>Periode</th><th>Pertimbangan</th><th>Status Akhir</th></tr>
                </thead>
                <tbody>
                @foreach($reviewedByMe as $req)
                    <tr>
                        <td class="fw-semibold" style="font-size: 0.88rem;">{{ $req->user->name }}</td>
                        <td style="font-size: 0.85rem;">{{ $req->type_label }}</td>
                        <td style="font-size: 0.82rem;">{{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }}</td>
                        <td>
                            @if($req->pertimbangan_atasan === 'setuju')
                                <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Setuju</span>
                            @elseif($req->pertimbangan_atasan === 'ubah')
                                <span class="sh-badge" style="background: var(--sh-primary-light); color: var(--sh-primary);"><i class="ti ti-edit"></i> Ubah</span>
                            @elseif($req->pertimbangan_atasan === 'tangguhkan')
                                <span class="sh-badge sh-badge-pending"><i class="ti ti-clock-pause"></i> Tangguhkan</span>
                            @elseif($req->pertimbangan_atasan === 'tolak')
                                <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Tolak</span>
                            @endif
                        </td>
                        <td>
                            @if($req->isApproved())
                                <span class="sh-badge sh-badge-approved">Disetujui</span>
                            @elseif($req->isRejected())
                                <span class="sh-badge sh-badge-rejected">Ditolak</span>
                            @else
                                <span class="sh-badge sh-badge-pending">{{ $req->status_label }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Atasan's Own Leave --}}
    @if(isset($leaveRequests) && $leaveRequests->isNotEmpty())
    <div class="card sh-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-list-details me-2" style="color: var(--sh-primary);"></i>
                Riwayat Cuti Saya
            </h3>
        </div>
        <div class="card-body p-3">
            @foreach($leaveRequests as $req)
            <div class="card sh-history-card status-{{ $req->status }} mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="fw-bold" style="font-size: 0.9rem;">{{ $req->type_label }}</span>
                            <div class="text-muted" style="font-size: 0.78rem;">
                                {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }} &middot; {{ $req->total_days }} hari
                            </div>
                        </div>
                        @if($req->isApproved())
                            <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                        @elseif($req->isRejected())
                            <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                        @else
                            <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

@else
    {{-- ============================= --}}
    {{--      PEGAWAI DASHBOARD        --}}
    {{-- ============================= --}}

    {{-- Hero Balance Card --}}
    <div class="card sh-hero-balance mb-4 animate-in">
        <div class="card-body p-4">
            <div class="hero-content">
                <div class="row align-items-center">
                    <div class="col">
                        <div style="font-size: 0.85rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 0.5rem;">
                            <i class="ti ti-calendar-stats me-1"></i> Sisa Cuti Tahunan
                        </div>
                        @if($cutiInfo)
                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <span class="sh-hero-number">{{ $cutiInfo['sisa_cuti'] ?? $user->leave_balance }}</span>
                            <span style="font-size: 1.1rem; opacity: 0.8;">/ {{ $cutiInfo['total_hak'] ?? 12 }} hari</span>
                        </div>
                        <div class="sh-hero-progress mb-2" style="max-width: 280px;">
                            @php $hakTotal = $cutiInfo['total_hak'] ?? 12; @endphp
                            <div class="sh-hero-progress-bar" style="width: {{ $hakTotal > 0 ? (($cutiInfo['sisa_cuti'] ?? $user->leave_balance) / $hakTotal) * 100 : 0 }}%;"></div>
                        </div>
                        <div style="font-size: 0.82rem; opacity: 0.7;">
                            Hak: {{ $cutiInfo['hak_cuti'] ?? 12 }} hari
                            @if(($cutiInfo['carry_over'] ?? 0) > 0)
                                + Carry Over: {{ $cutiInfo['carry_over'] }} hari
                            @endif
                            @if(($cutiInfo['tambahan_terpencil'] ?? 0) > 0)
                                + Terpencil: {{ $cutiInfo['tambahan_terpencil'] }} hari
                            @endif
                            &mdash; Terpakai: {{ $cutiInfo['cuti_diambil'] ?? 0 }} hari
                        </div>
                        @else
                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <span class="sh-hero-number">{{ $user->leave_balance }}</span>
                            <span style="font-size: 1.1rem; opacity: 0.8;">/ 12 hari</span>
                        </div>
                        <div class="sh-hero-progress mb-2" style="max-width: 280px;">
                            <div class="sh-hero-progress-bar" style="width: {{ ($user->leave_balance / 12) * 100 }}%;"></div>
                        </div>
                        <div style="font-size: 0.82rem; opacity: 0.7;">
                            @if(!$user->sudahBekerjaSatuTahun())
                                <i class="ti ti-alert-triangle me-1"></i> Anda belum bekerja 1 tahun. Belum berhak cuti tahunan.
                            @else
                                Terpakai {{ 12 - $user->leave_balance }} hari dari total 12 hari jatah cuti tahunan
                            @endif
                        </div>
                        @endif
                    </div>
                    <div class="col-auto d-none d-sm-block">
                        <i class="ti ti-beach" style="font-size: 5rem; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mini Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-1">Diproses</div>
                            <div class="sh-stat-number" style="color: var(--sh-warning);">{{ $leaveRequests->filter(fn($r) => $r->isPending())->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-warning">
                            <i class="ti ti-clock-hour-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-1">Disetujui</div>
                            <div class="sh-stat-number" style="color: var(--sh-success);">{{ $leaveRequests->filter(fn($r) => $r->isApproved())->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-danger">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-1">Ditolak</div>
                            <div class="sh-stat-number" style="color: var(--sh-danger);">{{ $leaveRequests->filter(fn($r) => $r->isRejected())->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-danger">
                            <i class="ti ti-circle-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fitur 6: Search & Filter --}}
    <div class="card sh-card mb-4 animate-in">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('dashboard') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                            <i class="ti ti-search me-1" style="color: var(--sh-primary);"></i> Cari Alasan
                        </label>
                        <input type="text" name="search" class="form-control" placeholder="Cari alasan cuti..."
                               value="{{ request('search') }}"
                               style="border-radius: 10px; border: 2px solid #e2e8f0; height: 42px;">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                            <i class="ti ti-category me-1" style="color: var(--sh-primary);"></i> Jenis
                        </label>
                        <select name="type" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0; height: 42px;">
                            <option value="">Semua Jenis</option>
                            @foreach(\App\Models\LeaveRequest::typeLabels() as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                            <i class="ti ti-filter me-1" style="color: var(--sh-primary);"></i> Status
                        </label>
                        <select name="status" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0; height: 42px;">
                            <option value="">Semua</option>
                            <option value="diajukan" {{ request('status') === 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                            <option value="pertimbangan_atasan" {{ request('status') === 'pertimbangan_atasan' ? 'selected' : '' }}>Pertimbangan</option>
                            <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary sh-btn-primary flex-fill" style="height: 42px;">
                                <i class="ti ti-search me-1"></i> Filter
                            </button>
                            @if(request()->hasAny(['search', 'type', 'status']))
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" style="border-radius: 10px; height: 42px; display: flex; align-items: center; justify-content: center;" title="Reset">
                                <i class="ti ti-x"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Leave History --}}
    <div class="card sh-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-list-details me-2" style="color: var(--sh-primary);"></i>
                Riwayat Pengajuan Cuti
            </h3>
            <span class="text-muted" style="font-size: 0.8rem;">{{ $leaveRequests->count() }} total</span>
        </div>

        @if($leaveRequests->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sh-empty-icon"><i class="ti ti-calendar-off"></i></div>
                <h4 class="fw-bold text-dark mb-1">Belum Ada Pengajuan</h4>
                @if($user->bolehCuti())
                <p class="text-muted mb-3">Anda belum pernah mengajukan cuti. Mulai dengan klik tombol di bawah.</p>
                <a href="{{ route('leave.select-type') }}" class="btn btn-primary sh-btn-primary">
                    <i class="ti ti-file-plus me-1"></i> Ajukan Cuti Pertama Anda
                </a>
                @else
                <p class="text-muted mb-3">
                    @if($user->status_pegawai === 'cpns')
                        CPNS belum berhak mengajukan cuti.
                    @else
                        PPPK yang baru dilantik (masa kerja &lt; 1 tahun) belum berhak mengajukan cuti.
                    @endif
                </p>
                @endif
            </div>
        </div>
        @else

        {{-- Mobile: cards --}}
        <div class="card-body d-md-none">
            @foreach($leaveRequests as $req)
            <div class="card sh-history-card status-{{ $req->status }} mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;">
                                {{ $req->type_label }}
                            </div>
                            <div class="text-muted" style="font-size: 0.78rem;">
                                <i class="ti ti-calendar me-1"></i>
                                {{ $req->start_date->format('d M Y') }} s.d. {{ $req->end_date->format('d M Y') }} &middot; {{ $req->total_days }} hari
                            </div>
                        </div>
                        @if($req->isApproved())
                            <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                        @elseif($req->isRejected())
                            <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                        @else
                            <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
                        @endif
                    </div>

                    {{-- Show rejection reason if rejected --}}
                    @if($req->isRejected() && ($req->catatan_atasan || $req->catatan_pejabat))
                    <div class="alert mb-2" style="background: var(--sh-danger-light); color: var(--sh-danger); border: 1px solid rgba(220, 38, 38, 0.2); border-radius: 8px; padding: 0.75rem; font-size: 0.85rem;">
                        <div class="d-flex gap-2">
                            <i class="ti ti-alert-circle" style="flex-shrink: 0; margin-top: 2px;"></i>
                            <div>
                                <div class="fw-semibold mb-1">Alasan Penolakan:</div>
                                <div>{{ $req->catatan_pejabat ?? $req->catatan_atasan }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($req->reason)
                    <div style="font-size: 0.85rem; color: #475569;">{{ Str::limit($req->reason, 80) }}</div>
                    @endif
                    <div class="mt-2">
                        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.78rem;">
                            <i class="ti ti-eye me-1"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Desktop: table --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table sh-table mb-0">
                <thead>
                    <tr>
                        <th>Jenis Cuti</th>
                        <th>Periode</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($leaveRequests as $req)
                    <tr>
                        <td>
                            <span class="fw-semibold" style="font-size: 0.88rem;">{{ $req->type_label }}</span>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">{{ $req->start_date->format('d M Y') }}</div>
                            <div class="text-muted" style="font-size: 0.78rem;">s.d. {{ $req->end_date->format('d M Y') }}</div>
                        </td>
                        <td>
                            <span class="badge bg-blue-lt" style="border-radius: 50px;">{{ $req->total_days }} hari</span>
                        </td>
                        <td>
                            @if($req->isApproved())
                                <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @elseif($req->isRejected())
                                <div>
                                    <span class="sh-badge sh-badge-rejected" title="{{ $req->catatan_pejabat ?? $req->catatan_atasan ?? 'Tidak ada catatan' }}" style="cursor: help;"><i class="ti ti-circle-x"></i> Ditolak</span>
                                    @if($req->catatan_pejabat || $req->catatan_atasan)
                                    <div class="small text-muted mt-1" style="font-size: 0.75rem; max-width: 200px; white-space: normal;">
                                        <i class="ti ti-info-circle" style="font-size: 0.7rem;"></i> {{ Str::limit($req->catatan_pejabat ?? $req->catatan_atasan, 60) }}
                                    </div>
                                    @endif
                                </div>
                            @else
                                <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endif

@if($user->isAdmin())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// #12 Count-up animation for stat numbers
(function() {
    function countUp(el, target, duration) {
        var start = 0;
        var startTime = null;
        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(start + (target - start) * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.sh-stat-number').forEach(function(el) {
            var val = parseInt(el.textContent.trim());
            if (!isNaN(val) && val > 0) {
                el.textContent = '0';
                countUp(el, val, 900);
            }
        });
    });
})();

document.addEventListener('DOMContentLoaded', function() {
    var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    var textColor = isDark ? '#94a3b8' : '#64748b';
    var gridColor = isDark ? '#334155' : '#f0f4f0';

    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;

    // Chart by Type
    var typeLabels = @json(array_map(fn($t) => \App\Models\LeaveRequest::typeLabels()[$t] ?? $t, array_keys($chartByType)));
    var typeData = @json(array_values($chartByType));
    if (typeLabels.length > 0 && typeData.length > 0 && document.getElementById('chartByType')) {
        new Chart(document.getElementById('chartByType'), {
            type: 'bar',
            data: {
                labels: typeLabels,
                datasets: [{
                    label: 'Jumlah',
                    data: typeData,
                    backgroundColor: ['#166534','#059669','#d97706','#dc2626','#7c3aed','#64748b'],
                    borderRadius: 8,
                    barThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Monthly Trend
    var monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
    var monthlyData = new Array(12).fill(0);
    var rawMonthly = @json($chartMonthly);
    if (rawMonthly && Object.keys(rawMonthly).length > 0) {
        for (var m in rawMonthly) { monthlyData[parseInt(m) - 1] = rawMonthly[m]; }
    }
    if (document.getElementById('chartMonthly')) {
        new Chart(document.getElementById('chartMonthly'), {
            type: 'line',
            data: {
                labels: monthNames,
                datasets: [{
                    label: 'Pengajuan',
                    data: monthlyData,
                    borderColor: '#b8860b',
                    backgroundColor: 'rgba(184,134,11,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#b8860b',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    // Status Distribution
    var statusMap = @json(\App\Models\LeaveRequest::statusLabels());
    var rawStatus = @json($chartByStatus);
    var statusLabels = [], statusData = [], statusColors = [];
    var colorMap = {
        'diajukan': '#d97706', 'pertimbangan_atasan': '#f59e0b',
        'disetujui': '#059669', 'approved': '#059669',
        'ditolak': '#dc2626', 'rejected': '#dc2626',
        'diubah': '#166534', 'ditangguhkan': '#b8860b', 'pending': '#d97706'
    };
    for (var s in rawStatus) {
        statusLabels.push(statusMap[s] || s);
        statusData.push(rawStatus[s]);
        statusColors.push(colorMap[s] || '#64748b');
    }
    if (statusLabels.length > 0 && document.getElementById('chartByStatus')) {
        new Chart(document.getElementById('chartByStatus'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors,
                    borderWidth: 2,
                    borderColor: isDark ? '#1e293b' : '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 16 } }
                }
            }
        });
    }
});
</script>
@endpush
@endif
@endsection
