@extends('layouts.app')

@section('title', 'Cuti Saya - SiHEALING')

@section('content')
<nav class="sh-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sh-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sh-breadcrumb-current">Cuti Saya</span>
</nav>

<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div>
            <h2 class="sh-page-title mb-0">Cuti Saya</h2>
            <div class="text-muted" style="font-size: 0.85rem;">Ringkasan dan statistik cuti Anda tahun {{ $year }}</div>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->bolehCuti())
            <a href="{{ route('leave.create') }}" class="btn sh-btn-primary" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="ti ti-file-plus me-1"></i> Ajukan Cuti
            </a>
            @endif
            <a href="{{ route('leave.history') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="ti ti-history me-1"></i> Riwayat Lengkap
            </a>
        </div>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card sh-stat-card" style="border-top: 3px solid var(--sh-primary);">
            <div class="card-body text-center py-3">
                <div class="sh-stat-icon icon-primary mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 12px; font-size: 1.2rem;">
                    <i class="ti ti-calendar-check"></i>
                </div>
                <div class="fw-bold" style="font-size: 1.8rem; color: var(--sh-primary);">{{ $leaveBalance }}</div>
                <div class="text-muted" style="font-size: 0.78rem;">Sisa Kuota</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sh-stat-card" style="border-top: 3px solid var(--sh-warning);">
            <div class="card-body text-center py-3">
                <div class="sh-stat-icon icon-warning mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 12px; font-size: 1.2rem;">
                    <i class="ti ti-clock-hour-4"></i>
                </div>
                <div class="fw-bold" style="font-size: 1.8rem; color: var(--sh-warning);">{{ $pendingCount }}</div>
                <div class="text-muted" style="font-size: 0.78rem;">Menunggu Proses</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sh-stat-card" style="border-top: 3px solid var(--sh-success);">
            <div class="card-body text-center py-3">
                <div class="sh-stat-icon icon-success mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 12px; font-size: 1.2rem;">
                    <i class="ti ti-circle-check"></i>
                </div>
                <div class="fw-bold" style="font-size: 1.8rem; color: var(--sh-success);">{{ $usedThisYear }}</div>
                <div class="text-muted" style="font-size: 0.78rem;">Hari Terpakai {{ $year }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sh-stat-card" style="border-top: 3px solid var(--sh-accent);">
            <div class="card-body text-center py-3">
                <div class="sh-stat-icon mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 12px; font-size: 1.2rem; background: var(--sh-accent-light); color: var(--sh-accent);">
                    <i class="ti ti-calendar-event"></i>
                </div>
                <div class="fw-bold" style="font-size: 1.8rem; color: var(--sh-accent);">{{ $upcoming->count() }}</div>
                <div class="text-muted" style="font-size: 0.78rem;">Cuti Mendatang</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Upcoming leaves --}}
    <div class="col-12 col-md-6">
        <div class="card sh-card h-100">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-calendar-time me-2" style="color: var(--sh-primary);"></i>
                    Cuti Mendatang
                </h3>
            </div>
            <div class="card-body">
                @forelse($upcoming as $leave)
                <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div style="min-width: 46px; text-align: center;">
                        <div style="font-size: 1.3rem; font-weight: 800; color: var(--sh-primary); line-height: 1;">
                            {{ $leave->start_date->format('d') }}
                        </div>
                        <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--sh-text-muted);">
                            {{ $leave->start_date->format('M') }}
                        </div>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-semibold" style="font-size: 0.88rem;">{{ $leave->type_label }}</div>
                        <div class="text-muted" style="font-size: 0.78rem;">
                            {{ $leave->start_date->format('d M') }} &mdash; {{ $leave->end_date->format('d M Y') }}
                            &bull; {{ $leave->total_hari_kerja ?? $leave->total_days }} hari
                        </div>
                    </div>
                    <a href="{{ route('leave.show', $leave) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                        <i class="ti ti-eye"></i>
                    </a>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="ti ti-calendar-off" style="font-size: 2rem; opacity: 0.4;"></i>
                    <p class="mt-2 mb-0" style="font-size: 0.85rem;">Tidak ada cuti mendatang.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent leaves --}}
    <div class="col-12 col-md-6">
        <div class="card sh-card h-100">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-history me-2" style="color: var(--sh-primary);"></i>
                    Pengajuan Terbaru
                </h3>
            </div>
            <div class="card-body">
                @forelse($recentLeaves as $leave)
                <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div>
                        @php
                            $badgeClass = match(true) {
                                $leave->isApproved() => 'sh-badge-approved',
                                $leave->isRejected() => 'sh-badge-rejected',
                                default => 'sh-badge-pending',
                            };
                        @endphp
                        <span class="sh-badge {{ $badgeClass }}">{{ $leave->status_label }}</span>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-semibold" style="font-size: 0.88rem;">{{ $leave->type_label }}</div>
                        <div class="text-muted" style="font-size: 0.78rem;">
                            {{ $leave->start_date->format('d M Y') }}
                            &bull; {{ $leave->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <a href="{{ route('leave.show', $leave) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                        <i class="ti ti-eye"></i>
                    </a>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <p class="mb-0" style="font-size: 0.85rem;">Belum ada pengajuan cuti.</p>
                </div>
                @endforelse
                <div class="text-center mt-3">
                    <a href="{{ route('leave.history') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.82rem;">
                        Lihat Semua Riwayat <i class="ti ti-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
