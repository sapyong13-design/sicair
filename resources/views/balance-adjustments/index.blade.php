@extends('layouts.app')

@section('title', 'Perubahan Saldo Cuti - SiHEALING')

@section('content')
{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h2 class="sh-page-title mb-0">
                <i class="ti ti-adjustments me-2" style="color: var(--sh-primary);"></i>
                Perubahan Saldo Cuti
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">Riwayat penyesuaian saldo cuti pegawai</div>
        </div>
        <a href="{{ route('pegawai.index') }}" class="btn sh-btn-primary">
            <i class="ti ti-plus me-1"></i> Buat Perubahan
        </a>
    </div>
</div>

{{-- Filter Card --}}
<div class="card sh-card mb-4">
    <div class="card-body p-3">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label" style="font-size: 0.78rem; font-weight: 600; color: var(--sh-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Status</label>
                    <select name="status" class="form-select" style="border-radius: 10px; border: 2px solid var(--sh-border); height: 42px;">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size: 0.78rem; font-weight: 600; color: var(--sh-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Tahun</label>
                    <select name="year" class="form-select" style="border-radius: 10px; border: 2px solid var(--sh-border); height: 42px;">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size: 0.78rem; font-weight: 600; color: var(--sh-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Cari Pegawai</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama atau NIP pegawai..."
                           value="{{ request('search') }}" style="border-radius: 10px; border: 2px solid var(--sh-border); height: 42px;">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn sh-btn-primary w-100" style="height: 42px;">
                        <i class="ti ti-search me-1"></i> Cari
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('balance-adjustment.index') }}" class="btn btn-outline-secondary w-100" style="border-radius: 10px; height: 42px;">
                        <i class="ti ti-x me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table Card --}}
<div class="card sh-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table sh-table mb-0">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Tahun</th>
                        <th>Tipe</th>
                        <th>Hari</th>
                        <th>Status</th>
                        <th>Alasan</th>
                        <th style="width: 60px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adjustment)
                        <tr>
                            <td>
                                <div class="fw-semibold" style="color: var(--sh-text);">{{ $adjustment->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">{{ $adjustment->user->email }}</div>
                            </td>
                            <td>
                                <span class="fw-semibold" style="color: var(--sh-text);">{{ $adjustment->year }}</span>
                            </td>
                            <td>
                                @switch($adjustment->type)
                                    @case('addition')
                                        <span class="sh-badge sh-badge-approved">
                                            <i class="ti ti-circle-plus"></i> Penambahan
                                        </span>
                                        @break
                                    @case('deduction')
                                        <span class="sh-badge sh-badge-pending">
                                            <i class="ti ti-circle-minus"></i> Pengurangan
                                        </span>
                                        @break
                                    @case('correction')
                                        <span class="sh-badge" style="background: #e0f2fe; color: #0369a1;">
                                            <i class="ti ti-edit"></i> Koreksi
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                <span class="fw-bold fs-5 {{ $adjustment->adjustment_days > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $adjustment->adjustment_days > 0 ? '+' : '' }}{{ $adjustment->adjustment_days }}
                                </span>
                                <span class="text-muted" style="font-size: 0.8rem;"> hari</span>
                            </td>
                            <td>
                                @php
                                    $adjStatusClass = match($adjustment->status) {
                                        'approved' => 'sh-badge-approved',
                                        'rejected' => 'sh-badge-rejected',
                                        default    => 'sh-badge-pending',
                                    };
                                    $adjStatusIcon = match($adjustment->status) {
                                        'approved' => 'ti-circle-check',
                                        'rejected' => 'ti-circle-x',
                                        default    => 'ti-clock',
                                    };
                                @endphp
                                <span class="sh-badge {{ $adjStatusClass }}">
                                    <i class="ti {{ $adjStatusIcon }}"></i>
                                    {{ ucfirst($adjustment->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.85rem;" title="{{ $adjustment->reason }}">
                                    {{ Str::limit($adjustment->reason, 40) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('balance-adjustment.show', $adjustment) }}"
                                   class="btn btn-sm"
                                   style="border-radius: 8px; background: var(--sh-primary-light); color: var(--sh-primary); border: none; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"
                                   title="Lihat Detail Perubahan Saldo"
                                   aria-label="Lihat detail perubahan saldo {{ $adjustment->user->name }}">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-5">
                                <div class="text-center">
                                    <div class="sh-empty-icon mb-3">
                                        <i class="ti ti-adjustments"></i>
                                    </div>
                                    <div class="fw-semibold mb-1" style="color: var(--sh-text);">
                                        {{ request('search') || request('status') || request('year') ? 'Tidak ada hasil ditemukan' : 'Belum ada perubahan saldo' }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.85rem;">
                                        {{ request('search') || request('status') || request('year') ? 'Coba ubah atau reset filter pencarian' : 'Perubahan saldo akan muncul di sini' }}
                                    </div>
                                    @if(request('search') || request('status') || request('year'))
                                    <a href="{{ route('balance-adjustment.index') }}" class="btn btn-outline-secondary mt-3" style="border-radius: 10px;">
                                        <i class="ti ti-x me-1"></i> Reset Filter
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination with info --}}
        @if($adjustments->hasPages() || $adjustments->total() > 0)
        <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-top: 2px solid var(--sh-gray-100);">
            <div class="text-muted" style="font-size: 0.82rem;">
                Menampilkan
                <strong>{{ $adjustments->firstItem() ?? 0 }}</strong>–<strong>{{ $adjustments->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $adjustments->total() }}</strong> perubahan saldo
            </div>
            <div>{{ $adjustments->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
