@extends('layouts.app')

@section('title', 'Pencarian - SiHEALING')

@section('content')
<nav class="sh-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sh-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sh-breadcrumb-current">Hasil Pencarian</span>
</nav>

<div class="sh-page-header">
    <h2 class="sh-page-title mb-2">Pencarian</h2>
    <form method="GET" action="{{ route('search') }}" class="d-flex gap-2" style="max-width: 480px;">
        <div class="input-group">
            <span class="input-group-text" style="border-radius: 10px 0 0 10px; border: 2px solid var(--sh-border);">
                <i class="ti ti-search text-muted"></i>
            </span>
            <input type="text" name="q" value="{{ $query }}"
                   class="form-control"
                   placeholder="Cari nama pegawai, NIP, jenis cuti..."
                   style="border-radius: 0 10px 10px 0; border: 2px solid var(--sh-border); border-left: none;">
        </div>
        <button type="submit" class="btn sh-btn-primary" style="border-radius: 10px; white-space: nowrap;">
            Cari
        </button>
    </form>
</div>

@if(strlen($query) < 2)
<div class="card sh-card">
    <div class="card-body text-center py-5 text-muted">
        <i class="ti ti-search" style="font-size: 3rem; opacity: 0.3;"></i>
        <p class="mt-2">Masukkan minimal 2 karakter untuk mencari.</p>
    </div>
</div>
@else

{{-- Results summary --}}
<p class="text-muted mb-4" style="font-size: 0.85rem;">
    Menampilkan hasil untuk <strong>"{{ $query }}"</strong>
    &mdash; {{ $pegawai->count() + $leaves->count() }} hasil ditemukan
</p>

<div class="row g-4">
    {{-- Pegawai results --}}
    <div class="col-12 col-md-6">
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-users me-2" style="color: var(--sh-primary);"></i>
                    Pegawai
                    <span class="badge ms-2" style="background:var(--sh-primary-light);color:var(--sh-primary);font-size:0.72rem;">{{ $pegawai->count() }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                @forelse($pegawai as $p)
                <div class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="sh-user-avatar sh-user-avatar-sm" style="width:38px;height:38px;font-size:0.85rem;flex-shrink:0;background:var(--sh-primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;">
                        {{ strtoupper(substr($p->name, 0, 2)) }}
                    </div>
                    <div class="flex-fill min-w-0">
                        <div class="fw-semibold text-truncate" style="font-size:0.9rem;">{{ $p->name }}</div>
                        <div class="text-muted" style="font-size:0.78rem;">
                            {{ $p->nip }} &bull; {{ $p->jabatan ?? '-' }}
                        </div>
                        @if($p->unit_kerja)
                        <div class="text-muted" style="font-size:0.75rem;">{{ $p->unit_kerja }}</div>
                        @endif
                    </div>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('pegawai.show', $p) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;white-space:nowrap;">
                        <i class="ti ti-eye"></i>
                    </a>
                    @endif
                </div>
                @empty
                <div class="text-center py-4 text-muted" style="font-size:0.85rem;">
                    Tidak ada pegawai ditemukan.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Leave requests results --}}
    <div class="col-12 col-md-6">
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-file-text me-2" style="color: var(--sh-primary);"></i>
                    Pengajuan Cuti
                    <span class="badge ms-2" style="background:var(--sh-primary-light);color:var(--sh-primary);font-size:0.72rem;">{{ $leaves->count() }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                @forelse($leaves as $leave)
                <div class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="flex-fill min-w-0">
                        <div class="fw-semibold text-truncate" style="font-size:0.88rem;">
                            {{ $leave->user?->name }}
                        </div>
                        <div style="font-size:0.8rem;color:var(--sh-primary);">{{ $leave->type_label }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            {{ $leave->start_date->format('d M Y') }} &mdash; {{ $leave->end_date->format('d M Y') }}
                        </div>
                    </div>
                    @php
                        $badgeClass = match(true) {
                            $leave->isApproved() => 'sh-badge-approved',
                            $leave->isRejected() => 'sh-badge-rejected',
                            default => 'sh-badge-pending',
                        };
                    @endphp
                    <span class="sh-badge {{ $badgeClass }}" style="white-space:nowrap;">{{ $leave->status_label }}</span>
                    <a href="{{ route('leave.show', $leave) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                        <i class="ti ti-eye"></i>
                    </a>
                </div>
                @empty
                <div class="text-center py-4 text-muted" style="font-size:0.85rem;">
                    Tidak ada pengajuan cuti ditemukan.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif
@endsection
