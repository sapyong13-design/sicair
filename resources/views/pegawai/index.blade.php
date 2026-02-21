@extends('layouts.app')

@section('title', 'Kelola Pegawai - SiHEALING')

@section('content')
{{-- Breadcrumb (#10) --}}
<nav class="sh-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sh-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sh-breadcrumb-current">Kelola Pegawai</span>
</nav>

{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sh-page-title mb-1">
                <i class="ti ti-users-group me-1" style="color: var(--sh-primary);" aria-hidden="true"></i>
                Kelola Pegawai
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Menampilkan <strong class="text-dark">{{ $pegawai->total() }}</strong> pegawai terdaftar
            </div>
        </div>
        <a href="{{ route('pegawai.create') }}" class="btn btn-primary sh-btn-primary">
            <i class="ti ti-user-plus me-1"></i> Tambah Pegawai
        </a>
    </div>
</div>

{{-- Search & Filters --}}
<div class="card sh-card mb-4 animate-in">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('pegawai.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                        <i class="ti ti-search me-1" style="color: var(--sh-primary);"></i> Cari Pegawai
                    </label>
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari nama, NIP, atau jabatan..."
                           value="{{ request('search') }}"
                           style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                        <i class="ti ti-shield me-1" style="color: var(--sh-primary);"></i> Role
                    </label>
                    <select name="role" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                        <option value="">Semua Role</option>
                        <option value="pegawai" {{ request('role') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                        <option value="hakim" {{ request('role') === 'hakim' ? 'selected' : '' }}>Hakim</option>
                        <option value="hakim_ad_hoc" {{ request('role') === 'hakim_ad_hoc' ? 'selected' : '' }}>Hakim Ad Hoc</option>
                        <option value="panitera" {{ request('role') === 'panitera' ? 'selected' : '' }}>Panitera</option>
                        <option value="sekretaris" {{ request('role') === 'sekretaris' ? 'selected' : '' }}>Sekretaris</option>
                        <option value="atasan" {{ request('role') === 'atasan' ? 'selected' : '' }}>Atasan Lainnya</option>
                        <option value="ketua" {{ request('role') === 'ketua' ? 'selected' : '' }}>Ketua Pengadilan</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                        <i class="ti ti-id-badge me-1" style="color: var(--sh-primary);"></i> Status Pegawai
                    </label>
                    <select name="status_pegawai" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                        <option value="">Semua Status</option>
                        <option value="hakim" {{ request('status_pegawai') === 'hakim' ? 'selected' : '' }}>Hakim</option>
                        <option value="aparatur" {{ request('status_pegawai') === 'aparatur' ? 'selected' : '' }}>Aparatur</option>
                        <option value="cpns" {{ request('status_pegawai') === 'cpns' ? 'selected' : '' }}>CPNS</option>
                        <option value="cakim" {{ request('status_pegawai') === 'cakim' ? 'selected' : '' }}>Cakim</option>
                        <option value="pppk" {{ request('status_pegawai') === 'pppk' ? 'selected' : '' }}>PPPK</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary sh-btn-primary flex-fill" style="height: 46px;">
                            <i class="ti ti-search me-1"></i> Filter
                        </button>
                        @if(request()->hasAny(['search', 'role', 'status_pegawai']))
                        <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; height: 46px; display: flex; align-items: center; justify-content: center;" title="Reset filter">
                            <i class="ti ti-x"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Data Table Card --}}
<div class="card sh-card animate-in">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">
            <i class="ti ti-list-details me-2" style="color: var(--sh-primary);"></i>
            Daftar Pegawai
        </h3>
        <span class="text-muted" style="font-size: 0.8rem;">{{ $pegawai->total() }} data</span>
    </div>

    @if($pegawai->isEmpty())
    <div class="card-body py-5">
        <div class="text-center">
            <div class="sh-empty-icon">
                <i class="ti ti-users-minus"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Tidak Ada Data</h4>
            <p class="text-muted mb-3">
                @if(request()->hasAny(['search', 'role', 'status_pegawai']))
                    Tidak ditemukan pegawai yang sesuai dengan filter. Coba ubah kriteria pencarian.
                @else
                    Belum ada pegawai yang terdaftar. Mulai dengan menambahkan pegawai baru.
                @endif
            </p>
            @if(request()->hasAny(['search', 'role', 'status_pegawai']))
            <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">
                <i class="ti ti-arrow-back me-1"></i> Reset Filter
            </a>
            @else
            <a href="{{ route('pegawai.create') }}" class="btn btn-primary sh-btn-primary">
                <i class="ti ti-user-plus me-1"></i> Tambah Pegawai
            </a>
            @endif
        </div>
    </div>
    @else

    {{-- Mobile: Cards --}}
    <div class="card-body d-md-none">
        @foreach($pegawai as $p)
        <div class="card sh-history-card mb-3" style="border-left-color: var(--sh-primary);">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="sh-user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 10px;">
                            {{ strtoupper(substr($p->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 0.95rem;">{{ $p->name }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">NIP: {{ $p->nip }}</div>
                        </div>
                    </div>
                    @php
                        $roleBadge = match($p->role) {
                            'admin' => 'background: #f3e8ff; color: #7c3aed;',
                            'ketua' => 'background: var(--sh-primary-light); color: var(--sh-primary);',
                            'panitera' => 'background: var(--sh-success-light); color: var(--sh-success);',
                            'sekretaris' => 'background: #d1fae5; color: #065f46;',
                            'atasan' => 'background: #dcfce7; color: #166534;',
                            'hakim' => 'background: #fef3c7; color: #b45309;',
                            'hakim_ad_hoc' => 'background: #dbeafe; color: #0c4a6e;',
                            default => 'background: var(--sh-gray-100); color: #64748b;',
                        };
                        $roleLabel = match($p->role) {
                            'admin' => 'Admin',
                            'ketua' => 'Ketua PN',
                            'panitera' => 'Panitera',
                            'sekretaris' => 'Sekretaris',
                            'atasan' => 'Atasan',
                            'hakim' => 'Hakim',
                            'hakim_ad_hoc' => 'Hakim Ad Hoc',
                            'pegawai' => 'Pegawai',
                            default => ucfirst($p->role),
                        };
                    @endphp
                    <span class="sh-badge" style="{{ $roleBadge }}">
                        {{ $roleLabel }}
                    </span>
                </div>

                <div class="row g-2 mb-2" style="font-size: 0.82rem;">
                    <div class="col-6">
                        <div class="text-muted">Jabatan</div>
                        <div class="fw-semibold">{{ $p->jabatan ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Golongan</div>
                        <div class="fw-semibold">{{ $p->golongan_ruang ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Masa Kerja</div>
                        <div class="fw-semibold">{{ $p->masa_kerja_format ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Sisa Cuti</div>
                        <div class="fw-semibold" style="color: var(--sh-primary);">{{ $p->leave_balance }} hari</div>
                    </div>
                </div>

                @php
                    $statusBadge = match($p->status_pegawai) {
                        'hakim' => 'background: var(--sh-primary-light); color: var(--sh-primary);',
                        'aparatur' => 'background: var(--sh-success-light); color: var(--sh-success);',
                        'cpns' => 'background: var(--sh-warning-light); color: var(--sh-warning);',
                        'cakim' => 'background: #fef9c3; color: #a16207;',
                        default => 'background: var(--sh-gray-100); color: #64748b;',
                    };
                @endphp
                <div class="mb-3">
                    <span class="sh-badge" style="{{ $statusBadge }}">
                        {{ ucfirst($p->status_pegawai ?? '-') }}
                    </span>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('pegawai.show', $p) }}" class="btn btn-sm btn-outline-primary flex-fill" style="border-radius: 8px;">
                        <i class="ti ti-eye me-1"></i> Detail
                    </a>
                    <a href="{{ route('pegawai.edit', $p) }}" class="btn btn-sm btn-outline-secondary flex-fill" style="border-radius: 8px;">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('balance-adjustment.create', $p) }}" class="btn btn-sm btn-outline-warning" style="border-radius: 8px;" title="Ubah saldo cuti">
                        <i class="ti ti-calendar-stats"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $p->id }}">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Desktop: Table --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table sh-table mb-0">
            <thead>
                <tr>
                    <th>Pegawai</th>
                    <th>Jabatan / Golongan</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Masa Kerja</th>
                    <th class="text-center">Sisa Cuti</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($pegawai as $p)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="sh-user-avatar" style="width: 38px; height: 38px; font-size: 0.75rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($p->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.9rem;">{{ $p->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">NIP: {{ $p->nip }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold" style="font-size: 0.88rem;">{{ $p->jabatan ?? '-' }}</div>
                        <div class="text-muted" style="font-size: 0.78rem;">{{ $p->golongan_ruang ?? '-' }}</div>
                    </td>
                    <td>
                        @php
                            $roleBadge = match($p->role) {
                                'admin' => 'background: #f3e8ff; color: #7c3aed;',
                                'ketua' => 'background: var(--sh-primary-light); color: var(--sh-primary);',
                                'panitera' => 'background: var(--sh-success-light); color: var(--sh-success);',
                                'sekretaris' => 'background: #d1fae5; color: #065f46;',
                                'atasan' => 'background: #dcfce7; color: #166534;',
                                'hakim' => 'background: #fef3c7; color: #b45309;',
                                'hakim_ad_hoc' => 'background: #dbeafe; color: #0c4a6e;',
                                default => 'background: var(--sh-gray-100); color: #64748b;',
                            };
                            $roleIcon = match($p->role) {
                                'admin' => 'ti-shield-check',
                                'ketua' => 'ti-crown',
                                'panitera' => 'ti-user-star',
                                'sekretaris' => 'ti-user-edit',
                                'atasan' => 'ti-user-check',
                                'hakim' => 'ti-gavel',
                                'hakim_ad_hoc' => 'ti-scale',
                                default => 'ti-user',
                            };
                            $roleLabel = match($p->role) {
                                'admin' => 'Admin',
                                'ketua' => 'Ketua PN',
                                'panitera' => 'Panitera',
                                'sekretaris' => 'Sekretaris',
                                'atasan' => 'Atasan',
                                'hakim' => 'Hakim',
                                'hakim_ad_hoc' => 'Hakim Ad Hoc',
                                'pegawai' => 'Pegawai',
                                default => ucfirst($p->role),
                            };
                        @endphp
                        <span class="sh-badge" style="{{ $roleBadge }}">
                            <i class="ti {{ $roleIcon }}"></i> {{ $roleLabel }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusBadge = match($p->status_pegawai) {
                                'hakim' => 'background: var(--sh-primary-light); color: var(--sh-primary);',
                                'aparatur' => 'background: var(--sh-success-light); color: var(--sh-success);',
                                'cpns' => 'background: var(--sh-warning-light); color: var(--sh-warning);',
                                'cakim' => 'background: #fef9c3; color: #a16207;',
                                'pppk' => 'background: #ede9fe; color: #7c3aed;',
                                default => 'background: var(--sh-gray-100); color: #64748b;',
                            };
                        @endphp
                        <span class="sh-badge" style="{{ $statusBadge }}">
                            {{ ucfirst($p->status_pegawai ?? '-') }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.85rem;">{{ $p->masa_kerja_format ?? '-' }}</span>
                    </td>
                    <td class="text-center">
                        <span class="fw-bold" style="color: var(--sh-primary); font-size: 0.95rem;">{{ $p->leave_balance }}</span>
                        <span class="text-muted" style="font-size: 0.75rem;">hari</span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('pegawai.show', $p) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;" title="Detail {{ $p->name }}" aria-label="Lihat detail {{ $p->name }}">
                                <i class="ti ti-eye" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('pegawai.edit', $p) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;" title="Edit {{ $p->name }}" aria-label="Edit data {{ $p->name }}">
                                <i class="ti ti-edit" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('balance-adjustment.create', $p) }}" class="btn btn-sm btn-outline-warning" style="border-radius: 8px;" title="Ubah saldo cuti {{ $p->name }}" aria-label="Ubah saldo cuti {{ $p->name }}">
                                <i class="ti ti-calendar-stats" aria-hidden="true"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $p->id }}" title="Hapus {{ $p->name }}" aria-label="Hapus {{ $p->name }}">
                                <i class="ti ti-trash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pegawai->hasPages())
    <div class="card-footer d-flex align-items-center justify-content-between" style="background: #fff; border-top: 2px solid var(--sh-gray-100); padding: 0.75rem 1.25rem;">
        <div class="text-muted" style="font-size: 0.82rem;">
            Menampilkan {{ $pegawai->firstItem() }}-{{ $pegawai->lastItem() }} dari {{ $pegawai->total() }} pegawai
        </div>
        <div>
            {{ $pegawai->links() }}
        </div>
    </div>
    @endif

    @endif
</div>

{{-- Delete Modals --}}
@foreach($pegawai as $p)
<div class="modal modal-blur fade" id="deleteModal{{ $p->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $p->id }}" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <form method="POST" action="{{ route('pegawai.destroy', $p) }}">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4 text-center">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--sh-danger-light); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="ti ti-alert-triangle" style="font-size: 2rem; color: var(--sh-danger);"></i>
                    </div>
                    <h3 class="fw-bold mb-1" id="deleteModalLabel{{ $p->id }}">Hapus Pegawai?</h3>
                    <p class="text-muted mb-1">
                        Anda yakin ingin menghapus data pegawai:
                    </p>
                    <p class="mb-0">
                        <strong class="text-dark">{{ $p->name }}</strong><br>
                        <span class="text-muted" style="font-size: 0.85rem;">NIP: {{ $p->nip }}</span>
                    </p>
                    <div class="alert mt-3 mb-0" style="background: var(--sh-danger-light); border: none; border-radius: 10px; color: var(--sh-danger); font-size: 0.85rem;">
                        <i class="ti ti-alert-circle me-1"></i>
                        Tindakan ini tidak dapat dibatalkan. Semua data terkait pegawai ini akan ikut terhapus.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4" style="justify-content: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;">Batal</button>
                    <button type="submit" class="btn sh-btn-danger" style="min-width: 100px;">
                        <i class="ti ti-trash me-1"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
