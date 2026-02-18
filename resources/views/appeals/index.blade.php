@extends('layouts.app')

@section('title', 'Banding Cuti - SiHEALING')

@section('content')
{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h2 class="sh-page-title mb-0">
                <i class="ti ti-gavel me-2" style="color: var(--sh-primary);"></i>
                Daftar Banding Cuti
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">Kelola pengajuan banding penolakan cuti pegawai</div>
        </div>
    </div>
</div>

{{-- Filter Card --}}
<div class="card sh-card mb-4">
    <div class="card-body p-3">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" style="font-size: 0.78rem; font-weight: 600; color: var(--sh-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Status</label>
                    <select name="status" class="form-select" style="border-radius: 10px; border: 2px solid var(--sh-border); height: 42px;">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label" style="font-size: 0.78rem; font-weight: 600; color: var(--sh-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari pegawai atau nomor cuti..."
                           value="{{ request('search') }}" style="border-radius: 10px; border: 2px solid var(--sh-border); height: 42px;">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn sh-btn-primary w-100" style="height: 42px;">
                        <i class="ti ti-search me-1"></i> Cari
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('appeal.index') }}" class="btn btn-outline-secondary w-100" style="border-radius: 10px; height: 42px;">
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
                        <th>No. Cuti</th>
                        <th>Tanggal Banding</th>
                        <th>Jenis Cuti</th>
                        <th>Status</th>
                        <th>Keputusan</th>
                        <th style="width: 60px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appeals as $appeal)
                        <tr>
                            <td>
                                <div class="fw-semibold" style="color: var(--sh-text);">{{ $appeal->appellant->name }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">{{ $appeal->appellant->email }}</div>
                            </td>
                            <td>
                                <a href="{{ route('leave.show', $appeal->leaveRequest) }}" class="text-decoration-none fw-semibold" style="color: var(--sh-primary);">
                                    #{{ str_pad($appeal->leave_request_id, 6, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td style="font-size: 0.88rem;">{{ $appeal->created_at->format('d M Y') }}</td>
                            <td style="font-size: 0.88rem;">{{ $appeal->leaveRequest->jenis_cuti }}</td>
                            <td>
                                @php
                                    $statusClass = match($appeal->status) {
                                        'approved' => 'sh-badge-approved',
                                        'rejected' => 'sh-badge-rejected',
                                        default    => 'sh-badge-pending',
                                    };
                                    $statusIcon = match($appeal->status) {
                                        'approved' => 'ti-circle-check',
                                        'rejected' => 'ti-circle-x',
                                        default    => 'ti-clock',
                                    };
                                @endphp
                                <span class="sh-badge {{ $statusClass }}">
                                    <i class="ti {{ $statusIcon }}"></i>
                                    {{ ucfirst($appeal->status) }}
                                </span>
                            </td>
                            <td>
                                @if($appeal->decision)
                                    <span class="sh-badge {{ $appeal->decision === 'approved' ? 'sh-badge-approved' : 'sh-badge-rejected' }}">
                                        <i class="ti {{ $appeal->decision === 'approved' ? 'ti-circle-check' : 'ti-circle-x' }}"></i>
                                        {{ $appeal->getDecisionLabelAttribute() }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size: 0.85rem;">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('appeal.show', $appeal) }}"
                                   class="btn btn-sm"
                                   style="border-radius: 8px; background: var(--sh-primary-light); color: var(--sh-primary); border: none; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"
                                   title="Lihat Detail Banding"
                                   aria-label="Lihat detail banding pegawai {{ $appeal->appellant->name }}">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-5">
                                <div class="text-center">
                                    <div class="sh-empty-icon mb-3">
                                        <i class="ti ti-gavel"></i>
                                    </div>
                                    <div class="fw-semibold mb-1" style="color: var(--sh-text);">
                                        {{ request('search') || request('status') ? 'Tidak ada hasil ditemukan' : 'Belum ada data banding' }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.85rem;">
                                        {{ request('search') || request('status') ? 'Coba ubah atau reset filter pencarian' : 'Banding akan muncul di sini ketika ada pengajuan' }}
                                    </div>
                                    @if(request('search') || request('status'))
                                    <a href="{{ route('appeal.index') }}" class="btn btn-outline-secondary mt-3" style="border-radius: 10px;">
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
        @if($appeals->hasPages() || $appeals->total() > 0)
        <div class="d-flex align-items-center justify-content-between px-4 py-3" style="border-top: 2px solid var(--sh-gray-100);">
            <div class="text-muted" style="font-size: 0.82rem;">
                Menampilkan
                <strong>{{ $appeals->firstItem() ?? 0 }}</strong>–<strong>{{ $appeals->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $appeals->total() }}</strong> banding
            </div>
            <div>{{ $appeals->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
