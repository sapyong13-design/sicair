@extends('layouts.app')

@section('title', 'Riwayat Cuti — SiCAIR')

@section('content')
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Riwayat Cuti</span>
</nav>

<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div>
            <h2 class="sc-page-title mb-0">Riwayat Cuti Saya</h2>
            <div class="text-muted" style="font-size: 0.85rem;">Semua pengajuan cuti yang pernah diajukan</div>
        </div>
        <a href="{{ route('leave.saya') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-size: 0.85rem;">
            <i class="ti ti-chart-bar me-1"></i> Statistik Cuti
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="card sc-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach($statusLabels as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') == $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Jenis Cuti</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    @foreach($typeLabels as $key => $label)
                        <option value="{{ $key }}" @selected(request('type') == $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Tahun</label>
                <select name="year" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn sc-btn-primary btn-sm flex-fill">
                    <i class="ti ti-filter me-1"></i> Filter
                </button>
                <a href="{{ route('leave.history') }}" class="btn btn-outline-secondary btn-sm flex-fill">
                    <i class="ti ti-x me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card sc-card">
    <div class="card-body p-0">
        @if($leaves->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="ti ti-file-off" style="font-size: 3rem; opacity: 0.4;"></i>
            <p class="mt-2">Belum ada riwayat cuti.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table sc-table mb-0">
                <thead>
                    <tr>
                        <th>Jenis Cuti</th>
                        <th>Tanggal</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Diproses Oleh</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $leave)
                    <tr>
                        <td class="fw-semibold" style="color: var(--sc-primary);">{{ $leave->type_label }}</td>
                        <td>
                            <span style="font-size: 0.85rem;">
                                {{ $leave->start_date->format('d M Y') }}
                                @if(!$leave->start_date->equalTo($leave->end_date))
                                    &mdash; {{ $leave->end_date->format('d M Y') }}
                                @endif
                            </span>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size: 0.85rem;">
                                {{ $leave->total_hari_kerja ?? $leave->total_days ?? '-' }} hari
                            </span>
                        </td>
                        <td>
                            @php
                                $badgeClass = match(true) {
                                    $leave->isApproved() => 'sc-badge-approved',
                                    $leave->isRejected() => 'sc-badge-rejected',
                                    default => 'sc-badge-pending',
                                };
                            @endphp
                            <span class="sc-badge {{ $badgeClass }}">{{ $leave->status_label }}</span>
                        </td>
                        <td class="text-muted" style="font-size: 0.85rem;">
                            {{ $leave->pejabat?->name ?? $leave->atasanReviewer?->name ?? '-' }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('leave.show', $leave) }}"
                               class="btn btn-sm btn-outline-secondary"
                               style="border-radius: 8px; font-size: 0.78rem;">
                                <i class="ti ti-eye me-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @if($leaves->hasPages())
    <div class="card-footer border-top py-3">
        {{ $leaves->links() }}
    </div>
    @endif
</div>
@endsection
