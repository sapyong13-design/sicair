@extends('layouts.app')

@section('title', 'Dinas Luar — SiCAIR')

@section('content')
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Dinas Luar</span>
</nav>

<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="sc-page-title mb-0">
            <i class="ti ti-briefcase me-1" style="color: #ea580c;" aria-hidden="true"></i> Dinas Luar
        </h2>
        <a href="{{ route('dinas-luar.create') }}" class="btn sc-btn-primary">
            <i class="ti ti-plus me-1" aria-hidden="true"></i> Tambah Dinas Luar
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="card sc-card mb-3">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <label class="form-label mb-1" style="font-size: 0.82rem; font-weight: 600;">Pegawai</label>
                <select name="user_id" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0;">
                    <option value="">Semua Pegawai</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label mb-1" style="font-size: 0.82rem; font-weight: 600;">Bulan</label>
                <select name="bulan" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0;">
                    <option value="">Semua Bulan</option>
                    @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $num => $nama)
                    <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label mb-1" style="font-size: 0.82rem; font-weight: 600;">Tahun</label>
                <select name="tahun" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0;">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn sc-btn-primary" style="height: 40px; padding: 0 1.25rem;">
                    <i class="ti ti-search me-1"></i> Filter
                </button>
                @if(request()->hasAny(['user_id','bulan','tahun']))
                <a href="{{ route('dinas-luar.index') }}" class="btn btn-outline-secondary ms-1" style="height: 40px; padding: 0 1rem; border-radius: 10px;">
                    <i class="ti ti-x me-1"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none; background: var(--sc-success-light); color: var(--sc-success);">
    <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card sc-card">
    @if($dinasLuarList->isEmpty())
    <div class="card-body py-5 text-center">
        <div class="sc-empty-icon"><i class="ti ti-briefcase" aria-hidden="true"></i></div>
        <h4 class="fw-bold text-dark mb-1">Belum Ada Data</h4>
        <p class="text-muted mb-3">Belum ada data dinas luar yang tercatat.</p>
        <a href="{{ route('dinas-luar.create') }}" class="btn sc-btn-primary">
            <i class="ti ti-plus me-1" aria-hidden="true"></i> Tambah Dinas Luar
        </a>
    </div>
    @else
    <div class="table-responsive">
        <table class="table sc-table mb-0">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Nama Pegawai</th>
                    <th>Tujuan</th>
                    <th>Keperluan</th>
                    <th>Tanggal</th>
                    <th>Durasi</th>
                    <th>Dokumen</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($dinasLuarList as $i => $dl)
                <tr>
                    <td class="text-muted">{{ $dinasLuarList->firstItem() + $i }}</td>
                    <td>
                        <div class="fw-semibold">{{ $dl->user->name }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">{{ $dl->user->jabatan ?? '-' }}</div>
                    </td>
                    <td>{{ $dl->tujuan }}</td>
                    <td>
                        <span title="{{ $dl->keperluan }}">{{ Str::limit($dl->keperluan, 50) }}</span>
                    </td>
                    <td style="white-space: nowrap;">
                        {{ $dl->start_date->format('d M Y') }}
                        @if($dl->start_date->ne($dl->end_date))
                        <br><span class="text-muted" style="font-size: 0.75rem;">s.d. {{ $dl->end_date->format('d M Y') }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="sc-badge" style="background: #fff7ed; color: #ea580c; border: 1px solid #fdba74;">
                            {{ $dl->durasi }} hari
                        </span>
                    </td>
                    <td>
                        @if($dl->dokumen)
                        <a href="{{ route('dinas-luar.dokumen', $dl) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.75rem;" title="Unduh dokumen">
                            <i class="ti ti-download me-1"></i> Unduh
                        </a>
                        @else
                        <span class="text-muted" style="font-size: 0.78rem;">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('dinas-luar.edit', $dl) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;" aria-label="Edit">
                                <i class="ti ti-edit" aria-hidden="true"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $dl->id }}" aria-label="Hapus">
                                <i class="ti ti-trash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($dinasLuarList->hasPages())
    <div class="card-footer d-flex justify-content-center">
        {{ $dinasLuarList->links() }}
    </div>
    @endif
    @endif
</div>

{{-- Delete Modals --}}
@foreach($dinasLuarList as $dl)
<div class="modal modal-blur fade" id="deleteModal{{ $dl->id }}" tabindex="-1" aria-labelledby="deleteLabel{{ $dl->id }}" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <form method="POST" action="{{ route('dinas-luar.destroy', $dl) }}">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4 text-center">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--sc-danger-light); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="ti ti-alert-triangle" style="font-size: 2rem; color: var(--sc-danger);" aria-hidden="true"></i>
                    </div>
                    <h3 class="fw-bold mb-1" id="deleteLabel{{ $dl->id }}">Hapus Data Dinas Luar?</h3>
                    <p class="text-muted mb-1">Anda yakin ingin menghapus data dinas luar:</p>
                    <p class="mb-0">
                        <strong class="text-dark">{{ $dl->user->name }}</strong><br>
                        <span class="text-muted" style="font-size: 0.85rem;">{{ $dl->tujuan }} &middot; {{ $dl->start_date->format('d M Y') }}{{ $dl->start_date->ne($dl->end_date) ? ' – ' . $dl->end_date->format('d M Y') : '' }}</span>
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4" style="justify-content: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;">Batal</button>
                    <button type="submit" class="btn sc-btn-danger" style="min-width: 100px;">
                        <i class="ti ti-trash me-1" aria-hidden="true"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
