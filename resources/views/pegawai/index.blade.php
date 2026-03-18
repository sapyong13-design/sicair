@extends('layouts.app')

@section('title', 'Kelola Pegawai - SiCAIR')

@section('content')
{{-- Breadcrumb (#10) --}}
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Kelola Pegawai</span>
</nav>

{{-- Page Header --}}
<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sc-page-title mb-1">
                <i class="ti ti-users-group me-1" style="color: var(--sc-primary);" aria-hidden="true"></i>
                Kelola Pegawai
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Menampilkan <strong class="text-dark">{{ $pegawai->total() }}</strong> pegawai terdaftar
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Sprint 5 #29: Export CSV --}}
            <a href="{{ route('pegawai.export', request()->query()) }}" class="btn btn-outline-secondary sc-export-btn" style="border-radius:10px;">
                <i class="ti ti-download me-1"></i> Export CSV
            </a>
            {{-- #30: Card/Table Toggle --}}
            <button type="button" class="btn btn-outline-secondary" id="viewToggleBtn" style="border-radius:10px;" title="Ganti tampilan" onclick="togglePegawaiView()">
                <i class="ti ti-layout-grid" id="viewToggleIcon"></i>
            </button>
            {{-- #36 Import Excel --}}
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal"
                style="border-radius: 10px;">
                <i class="ti ti-file-import me-1"></i> Import Excel
            </button>
            {{-- C5: Generate Quota Cuti Otomatis --}}
            @if(auth()->user()->isAdmin())
            <form method="POST" action="{{ route('admin.generate-quota') }}" class="d-inline">
                @csrf
                <input type="hidden" name="year" value="{{ now()->year }}">
                <button type="submit" class="btn btn-outline-success btn-sm"
                    style="border-radius: 10px; height: 38px; padding: 0 0.75rem;"
                    onclick="return confirm('Generate quota cuti {{ now()->year }} untuk semua pegawai aktif?')">
                    <i class="ti ti-refresh me-1"></i>Generate Quota {{ now()->year }}
                </button>
            </form>
            @endif
            <a href="{{ route('pegawai.create') }}" class="btn btn-primary sc-btn-primary">
                <i class="ti ti-user-plus me-1"></i> Tambah Pegawai
            </a>
        </div>
    </div>
</div>

{{-- Search & Filters --}}
<div class="card sc-card mb-4 animate-in">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('pegawai.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                        <i class="ti ti-search me-1" style="color: var(--sc-primary);"></i> Cari Pegawai
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
                        <i class="ti ti-shield me-1" style="color: var(--sc-primary);"></i> Role
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
                        <i class="ti ti-id-badge me-1" style="color: var(--sc-primary);"></i> Status Pegawai
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
                <div class="col-6 col-md-2">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                        <i class="ti ti-toggle-right me-1" style="color: var(--sc-primary);"></i> Status
                    </label>
                    <select name="active" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                        <option value="">Semua</option>
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
                <div class="col-12 col-md-1">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary sc-btn-primary flex-fill" style="height: 46px;">
                            <i class="ti ti-search me-1"></i>
                        </button>
                        @if(request()->hasAny(['search', 'role', 'status_pegawai', 'active']))
                        <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; height: 46px; display: flex; align-items: center; justify-content: center;" title="Reset semua filter" aria-label="Reset semua filter">
                            <i class="ti ti-x"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Sprint 5 #28: Bulk Action Form (hidden, submitted via JS) --}}
<form method="POST" action="{{ route('pegawai.bulk-action') }}" id="bulkActionForm">
    @csrf
    <input type="hidden" name="action" id="bulkActionType">
    <input type="hidden" name="unit_kerja" id="bulkUnitKerja">
    {{-- Checkboxes submitted as ids[] via JS --}}
</form>

{{-- Sprint 5 #28: Floating Bulk Action Bar --}}
<div id="bulkActionBar" class="d-none" style="position:fixed;bottom:80px;left:50%;transform:translateX(-50%);z-index:1030;background:var(--sc-card-bg);border-radius:50px;padding:0.5rem 1rem;box-shadow:0 8px 32px rgba(0,0,0,0.2);border:2px solid var(--sc-primary);display:flex;align-items:center;gap:0.5rem;">
    <span class="fw-semibold text-primary" style="font-size:0.85rem;" id="bulkSelectedCount">0 dipilih</span>
    <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;" onclick="doBulkAction('reset-password')">
        <i class="ti ti-key me-1"></i> Reset Password
    </button>
    <button type="button" class="btn btn-sm btn-outline-warning" style="border-radius:50px;" onclick="promptPindahUnit()">
        <i class="ti ti-building me-1"></i> Pindah Unit
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger" style="border-radius:50px;" onclick="doBulkAction('nonaktifkan')">
        <i class="ti ti-user-off me-1"></i> Non-aktifkan
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:50px;" onclick="clearBulk()">
        <i class="ti ti-x"></i>
    </button>
</div>

{{-- Data Table Card --}}
<div class="card sc-card animate-in" id="pegawaiTableCard">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">
            <i class="ti ti-list-details me-2" style="color: var(--sc-primary);"></i>
            Daftar Pegawai
        </h3>
        <span class="text-muted" style="font-size: 0.8rem;">{{ $pegawai->total() }} data</span>
    </div>

    @if($pegawai->isEmpty())
    <div class="card-body py-5">
        <div class="text-center">
            <div class="sc-empty-icon">
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
            <a href="{{ route('pegawai.create') }}" class="btn btn-primary sc-btn-primary">
                <i class="ti ti-user-plus me-1"></i> Tambah Pegawai
            </a>
            @endif
        </div>
    </div>
    @else

    {{-- Mobile: Cards --}}
    <div class="card-body d-md-none">
        @foreach($pegawai as $p)
        <div class="card sc-history-card mb-3" style="border-left-color: var(--sc-primary);">
            <div class="card-body p-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="sc-user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--sc-primary-light); color: var(--sc-primary); border: none; border-radius: 10px;">
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
                            'ketua' => 'background: var(--sc-primary-light); color: var(--sc-primary);',
                            'panitera' => 'background: var(--sc-success-light); color: var(--sc-success);',
                            'sekretaris' => 'background: #d1fae5; color: #065f46;',
                            'atasan' => 'background: #dcfce7; color: #166534;',
                            'hakim' => 'background: #fef3c7; color: #b45309;',
                            'hakim_ad_hoc' => 'background: #dbeafe; color: #0c4a6e;',
                            default => 'background: var(--sc-gray-100); color: #64748b;',
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
                    <span class="sc-badge" style="{{ $roleBadge }}">
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
                        <div class="fw-semibold" style="color: var(--sc-primary);">{{ $p->leave_balance }} hari</div>
                    </div>
                </div>

                @php
                    $statusBadge = match($p->status_pegawai) {
                        'hakim' => 'background: var(--sc-primary-light); color: var(--sc-primary);',
                        'aparatur' => 'background: var(--sc-success-light); color: var(--sc-success);',
                        'cpns' => 'background: var(--sc-warning-light); color: var(--sc-warning);',
                        'cakim' => 'background: #fef9c3; color: #a16207;',
                        default => 'background: var(--sc-gray-100); color: #64748b;',
                    };
                @endphp
                <div class="mb-3">
                    <span class="sc-badge" style="{{ $statusBadge }}">
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
    <div class="table-responsive d-none d-md-block" id="tableView">
        <table class="table sc-table mb-0">
            @once
            @php
                function sortUrl($col, $currentSort, $currentDir) {
                    $dir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
                    return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $dir]);
                }
                function sortIcon($col, $currentSort, $currentDir) {
                    if ($currentSort !== $col) return 'ti-arrows-sort';
                    return $currentDir === 'asc' ? 'ti-sort-ascending' : 'ti-sort-descending';
                }
            @endphp
            @endonce
            <thead>
                <tr>
                    <th style="width:36px;"><input type="checkbox" id="checkAll" style="cursor:pointer;" tabindex="0" title="Pilih semua pegawai" aria-label="Pilih semua pegawai" onchange="toggleAllCheckboxes(this)"></th>
                    <th>
                        <a href="{{ sortUrl('name', $sortBy, $sortDir) }}" class="text-decoration-none text-dark d-flex align-items-center gap-1">
                            Pegawai <i class="ti {{ sortIcon('name', $sortBy, $sortDir) }}" style="font-size:0.75rem;"></i>
                        </a>
                    </th>
                    <th>
                        <a href="{{ sortUrl('jabatan', $sortBy, $sortDir) }}" class="text-decoration-none text-dark d-flex align-items-center gap-1">
                            Jabatan / Golongan <i class="ti {{ sortIcon('jabatan', $sortBy, $sortDir) }}" style="font-size:0.75rem;"></i>
                        </a>
                    </th>
                    <th>
                        <a href="{{ sortUrl('role', $sortBy, $sortDir) }}" class="text-decoration-none text-dark d-flex align-items-center gap-1">
                            Role <i class="ti {{ sortIcon('role', $sortBy, $sortDir) }}" style="font-size:0.75rem;"></i>
                        </a>
                    </th>
                    <th>Status</th>
                    <th>Masa Kerja</th>
                    <th class="text-center">Sisa Cuti</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($pegawai as $p)
                <tr class="{{ ($p->is_active === false) ? 'opacity-50' : '' }}">
                    <td><input type="checkbox" class="bulk-check" value="{{ $p->id }}" onchange="updateBulkBar()" style="cursor:pointer;"></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($p->photo)
                            <img src="{{ Storage::url($p->photo) }}" alt="{{ $p->name }}" style="width:38px;height:38px;border-radius:10px;object-fit:cover;flex-shrink:0;">
                            @else
                            <div class="sc-user-avatar" style="width: 38px; height: 38px; font-size: 0.75rem; background: var(--sc-primary-light); color: var(--sc-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($p->name, 0, 2)) }}
                            </div>
                            @endif
                            <div>
                                <div class="fw-bold" style="font-size: 0.9rem;">{{ $p->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">NIP: {{ $p->nip }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        {{-- #31 Inline edit jabatan --}}
                        <div class="fw-semibold sc-inline-edit" style="font-size: 0.88rem; border-radius:6px; padding:2px 4px; cursor:text;"
                             contenteditable="true"
                             data-pegawai-id="{{ $p->id }}"
                             data-field="jabatan"
                             title="Klik untuk edit jabatan"
                             spellcheck="false">{{ $p->jabatan ?? '' }}</div>
                        <div class="text-muted" style="font-size: 0.78rem;">{{ $p->golongan_ruang ?? '-' }}</div>
                        <div class="text-muted sc-inline-edit" style="font-size: 0.75rem; border-radius:6px; padding:2px 4px; cursor:text; margin-top:2px;"
                             contenteditable="true"
                             data-pegawai-id="{{ $p->id }}"
                             data-field="unit_kerja"
                             title="Klik untuk edit unit kerja"
                             spellcheck="false">{{ $p->unit_kerja ?? '' }}</div>
                    </td>
                    <td>
                        @php
                            $roleBadge = match($p->role) {
                                'admin' => 'background: #f3e8ff; color: #7c3aed;',
                                'ketua' => 'background: var(--sc-primary-light); color: var(--sc-primary);',
                                'panitera' => 'background: var(--sc-success-light); color: var(--sc-success);',
                                'sekretaris' => 'background: #d1fae5; color: #065f46;',
                                'atasan' => 'background: #dcfce7; color: #166534;',
                                'hakim' => 'background: #fef3c7; color: #b45309;',
                                'hakim_ad_hoc' => 'background: #dbeafe; color: #0c4a6e;',
                                default => 'background: var(--sc-gray-100); color: #64748b;',
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
                        <span class="sc-badge" style="{{ $roleBadge }}">
                            <i class="ti {{ $roleIcon }}"></i> {{ $roleLabel }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusBadge = match($p->status_pegawai) {
                                'hakim' => 'background: var(--sc-primary-light); color: var(--sc-primary);',
                                'aparatur' => 'background: var(--sc-success-light); color: var(--sc-success);',
                                'cpns' => 'background: var(--sc-warning-light); color: var(--sc-warning);',
                                'cakim' => 'background: #fef9c3; color: #a16207;',
                                'pppk' => 'background: #ede9fe; color: #7c3aed;',
                                default => 'background: var(--sc-gray-100); color: #64748b;',
                            };
                        @endphp
                        <span class="sc-badge" style="{{ $statusBadge }}">
                            {{ ucfirst($p->status_pegawai ?? '-') }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.85rem;">{{ $p->masa_kerja_format ?? '-' }}</span>
                    </td>
                    <td class="text-center">
                        @php $lb = $p->leave_balance ?? 0; @endphp
                        <span class="fw-bold" style="color: {{ $lb <= 0 ? 'var(--sc-danger)' : ($lb <= 3 ? 'var(--sc-warning)' : 'var(--sc-primary)') }}; font-size: 0.95rem;"
                              title="{{ $lb <= 0 ? 'Saldo habis' : ($lb <= 3 ? 'Saldo rendah' : '') }}">{{ $lb }}</span>
                        <span class="text-muted" style="font-size: 0.75rem;">hari</span>
                        @if($p->is_active === false)
                        <span class="d-block" style="font-size:0.7rem;color:var(--sc-danger);font-weight:600;margin-top:2px;">Non-aktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end flex-wrap">
                            {{-- #38 Quick View --}}
                            <button class="btn btn-sm btn-outline-primary sc-quick-view-btn" style="border-radius: 8px;"
                                title="Lihat riwayat cuti {{ $p->name }}"
                                aria-label="Riwayat cuti {{ $p->name }}"
                                data-pegawai-id="{{ $p->id }}"
                                data-pegawai-name="{{ $p->name }}">
                                <i class="ti ti-history" aria-hidden="true"></i>
                            </button>
                            <a href="{{ route('pegawai.show', $p) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;" title="Detail {{ $p->name }}">
                                <i class="ti ti-eye" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('pegawai.edit', $p) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;" title="Edit {{ $p->name }}">
                                <i class="ti ti-edit" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('balance-adjustment.create', $p) }}" class="btn btn-sm btn-outline-warning" style="border-radius: 8px;" title="Ubah saldo cuti">
                                <i class="ti ti-calendar-stats" aria-hidden="true"></i>
                            </a>
                            {{-- Sprint 5 #32: Toggle Active --}}
                            <form method="POST" action="{{ route('pegawai.toggle-active', $p) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ ($p->is_active ?? true) ? 'btn-outline-secondary' : 'btn-outline-success' }}" style="border-radius:8px;" title="{{ ($p->is_active ?? true) ? 'Non-aktifkan' : 'Aktifkan' }}">
                                    <i class="ti {{ ($p->is_active ?? true) ? 'ti-user-off' : 'ti-user-check' }}" aria-hidden="true"></i>
                                </button>
                            </form>
                            {{-- C3: Impersonate --}}
                            @if(Auth::user()->isAdmin() && !Session::has('impersonating') && !$p->isAdmin())
                            <form method="POST" action="{{ route('admin.impersonate', $p) }}" class="d-inline"
                                onsubmit="return confirm('Masuk sebagai {{ addslashes($p->name) }}?')">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;" title="Masuk Sebagai">
                                    <i class="ti ti-user-share" aria-hidden="true"></i>
                                </button>
                            </form>
                            @endif
                            <button class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $p->id }}" title="Hapus {{ $p->name }}">
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
    <div class="card-footer d-flex align-items-center justify-content-between" style="background: #fff; border-top: 2px solid var(--sc-gray-100); padding: 0.75rem 1.25rem;">
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
{{-- #38 Quick View Riwayat Cuti Modal --}}
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: var(--sc-primary); color: #fff; border: none;">
                <h5 class="modal-title" id="quickViewModalLabel">
                    <i class="ti ti-history me-2"></i>
                    Riwayat Cuti: <span id="qvPegawaiName">-</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="qvContent" class="p-4">
                    <div class="text-center py-4">
                        <div class="spinner-border text-success" style="width: 2rem; height: 2rem;"></div>
                        <p class="text-muted mt-2 mb-0">Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- #36 Import Excel Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="importModalLabel">
                    <i class="ti ti-file-import me-2" style="color: var(--sc-primary);"></i>
                    Import Pegawai dari Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('pegawai.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3 p-3" style="background: var(--sc-primary-light); border-radius: 10px;">
                        <div style="font-size: 0.85rem; color: var(--sc-primary);">
                            <i class="ti ti-info-circle me-1"></i>
                            <strong>Format file:</strong> .xlsx atau .csv<br>
                            Kolom yang diperlukan: nama, nip, jabatan, golongan_ruang, unit_kerja, role, tanggal_mulai_kerja
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" class="form-control" accept=".xlsx,.xls,.csv" required style="border-radius: 10px;">
                    </div>
                    <a href="{{ route('pegawai.import-template') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                        <i class="ti ti-download me-1"></i> Download Template
                    </a>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn sc-btn-primary">
                        <i class="ti ti-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Sprint 5 #30: Card View --}}
<div class="d-none" id="cardView">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-3">
        @foreach($pegawai as $p)
        <div class="col">
            <div class="card sc-card h-100 {{ ($p->is_active === false) ? 'opacity-50' : '' }}">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        @if($p->photo)
                        <img src="{{ Storage::url($p->photo) }}" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                        @else
                        <div class="sc-user-avatar" style="width:48px;height:48px;font-size:0.9rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:50%;flex-shrink:0;">{{ strtoupper(substr($p->name,0,2)) }}</div>
                        @endif
                        <div>
                            <div class="fw-bold" style="font-size:0.95rem;">{{ $p->name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $p->nip }}</div>
                        </div>
                    </div>
                    <div style="font-size:0.82rem;" class="mb-2">
                        <div>{{ $p->jabatan ?? '-' }} &bull; {{ $p->unit_kerja ?? '-' }}</div>
                        <div class="text-muted">Sisa Cuti: <strong style="color:var(--sc-primary);">{{ $p->leave_balance }}h</strong></div>
                    </div>
                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('pegawai.show', $p) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:0.78rem;">
                            <i class="ti ti-eye me-1"></i> Detail
                        </a>
                        <a href="{{ route('pegawai.edit', $p) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:0.78rem;">
                            <i class="ti ti-edit me-1"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
// Sprint 2 #7: NIP confirmation for delete
function checkNipConfirm(id, expectedNip) {
    var input = document.getElementById('nipConfirm' + id);
    var btn = document.getElementById('deleteBtn' + id);
    if (!input || !btn) return;
    var match = input.value.trim() === expectedNip.trim();
    btn.disabled = !match;
    btn.style.opacity = match ? '1' : '0.5';
}

// Sprint 5 #28: Bulk action handlers
var selectedIds = [];
function updateBulkBar() {
    var checks = document.querySelectorAll('.bulk-check:checked');
    selectedIds = Array.from(checks).map(function(c) { return c.value; });
    var bar = document.getElementById('bulkActionBar');
    var cnt = document.getElementById('bulkSelectedCount');
    if (selectedIds.length > 0) {
        bar.classList.remove('d-none');
        bar.style.display = 'flex';
        cnt.textContent = selectedIds.length + ' dipilih';
    } else {
        bar.classList.add('d-none');
    }
}
function clearBulk() {
    document.querySelectorAll('.bulk-check').forEach(function(c) { c.checked = false; });
    var ca = document.getElementById('checkAll'); if (ca) ca.checked = false;
    updateBulkBar();
}
function toggleAllCheckboxes(master) {
    document.querySelectorAll('.bulk-check').forEach(function(c) { c.checked = master.checked; });
    updateBulkBar();
}
function doBulkAction(action) {
    if (selectedIds.length === 0) return;
    if (!confirm('Lakukan aksi "' + action + '" untuk ' + selectedIds.length + ' pegawai?')) return;
    var form = document.getElementById('bulkActionForm');
    document.getElementById('bulkActionType').value = action;
    // Add hidden id inputs
    form.querySelectorAll('input[name="ids[]"]').forEach(function(el) { el.remove(); });
    selectedIds.forEach(function(id) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
        form.appendChild(inp);
    });
    form.submit();
}
function promptPindahUnit() {
    var unit = prompt('Masukkan nama unit kerja tujuan:');
    if (!unit) return;
    document.getElementById('bulkUnitKerja').value = unit;
    doBulkAction('pindah-unit');
}

// Sprint 5 #30: Card/Table View Toggle
function togglePegawaiView() {
    var tableView = document.getElementById('tableView');
    var cardView = document.getElementById('cardView');
    var icon = document.getElementById('viewToggleIcon');
    var tableCard = document.getElementById('pegawaiTableCard');
    var isCard = localStorage.getItem('sc-pegawai-view') === 'card';
    if (isCard) {
        // Switch to table
        if (tableView) tableView.classList.remove('d-none');
        if (cardView) cardView.classList.add('d-none');
        if (icon) icon.className = 'ti ti-layout-grid';
        localStorage.setItem('sc-pegawai-view', 'table');
    } else {
        // Switch to card
        if (tableView) tableView.classList.add('d-none');
        if (cardView) { cardView.classList.remove('d-none'); cardView.style.display = 'block'; }
        if (icon) icon.className = 'ti ti-layout-list';
        localStorage.setItem('sc-pegawai-view', 'card');
    }
}
// Apply saved view preference on load
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('sc-pegawai-view') === 'card') {
        var tableView = document.getElementById('tableView');
        var cardView = document.getElementById('cardView');
        var icon = document.getElementById('viewToggleIcon');
        if (tableView) tableView.classList.add('d-none');
        if (cardView) { cardView.classList.remove('d-none'); cardView.style.display = 'block'; }
        if (icon) icon.className = 'ti ti-layout-list';
    }
});

// #31 Inline edit jabatan / unit_kerja
(function() {
    var INLINE_URL = '/pegawai/';
    var CSRF = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';

    // Hover styles
    document.querySelectorAll('.sc-inline-edit').forEach(function(el) {
        el.addEventListener('mouseenter', function() {
            this.style.background = 'var(--sc-primary-light)';
            this.style.outline = '1px dashed var(--sc-primary)';
        });
        el.addEventListener('mouseleave', function() {
            if (document.activeElement !== this) {
                this.style.background = '';
                this.style.outline = '';
            }
        });
        el.addEventListener('focus', function() {
            this._originalValue = this.textContent.trim();
            this.style.background = 'var(--sc-primary-light)';
            this.style.outline = '2px solid var(--sc-primary)';
        });
        el.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); this.blur(); }
            if (e.key === 'Escape') {
                this.textContent = this._originalValue || '';
                this.blur();
            }
        });
        el.addEventListener('blur', function() {
            this.style.background = '';
            this.style.outline = '';
            var newVal = this.textContent.trim();
            if (newVal === (this._originalValue || '')) return;
            var pid = this.dataset.pegawaiId;
            var field = this.dataset.field;
            var self = this;
            fetch(INLINE_URL + pid + '/inline', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ field: field, value: newVal })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    self._originalValue = newVal;
                    self.style.color = 'var(--sc-success)';
                    setTimeout(function() { self.style.color = ''; }, 1200);
                } else {
                    self.textContent = self._originalValue || '';
                    if (typeof scToast === 'function') scToast('Gagal menyimpan perubahan.', 'danger');
                }
            })
            .catch(function() {
                self.textContent = self._originalValue || '';
            });
        });
    });
})();

// #38 Quick View Riwayat Cuti
document.querySelectorAll('.sc-quick-view-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var pid = this.dataset.pegawaiId;
        var name = this.dataset.pegawaiName;
        document.getElementById('qvPegawaiName').textContent = name;
        document.getElementById('qvContent').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success" style="width:2rem;height:2rem;"></div><p class="text-muted mt-2 mb-0">Memuat data...</p></div>';
        var modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
        modal.show();

        fetch('/pegawai/' + pid + '/riwayat-cuti', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var html = '';
            if (!data.leaves || data.leaves.length === 0) {
                html = '<div class="text-center py-4"><i class="ti ti-calendar-off" style="font-size:3rem;color:var(--sc-text-muted);opacity:0.3;"></i><p class="text-muted mt-2">Tidak ada riwayat cuti dalam 1 tahun terakhir.</p></div>';
            } else {
                html = '<div class="table-responsive"><table class="table sc-table mb-0"><thead><tr><th>Jenis</th><th>Periode</th><th>Durasi</th><th>Status</th></tr></thead><tbody>';
                data.leaves.forEach(function(l) {
                    var badge = l.status === 'disetujui' || l.status === 'approved'
                        ? '<span class="sc-badge sc-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>'
                        : l.status === 'ditolak' || l.status === 'rejected'
                        ? '<span class="sc-badge sc-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>'
                        : '<span class="sc-badge sc-badge-pending"><i class="ti ti-clock"></i> ' + l.status_label + '</span>';
                    html += '<tr><td style="font-size:0.85rem;">' + l.type_label + '</td><td style="font-size:0.82rem;">' + l.start_date + ' s.d. ' + l.end_date + '</td><td><span class="badge bg-blue-lt" style="border-radius:50px;">' + l.total_days + ' hari</span></td><td>' + badge + '</td></tr>';
                });
                html += '</tbody></table></div>';
            }
            document.getElementById('qvContent').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('qvContent').innerHTML = '<div class="text-center py-4 text-danger"><i class="ti ti-alert-triangle" style="font-size:2rem;"></i><p class="mt-2">Gagal memuat data.</p></div>';
        });
    });
});
</script>
@endpush

@foreach($pegawai as $p)
{{-- Sprint 2 #7: Typed NIP confirmation delete modal --}}
<div class="modal modal-blur fade" id="deleteModal{{ $p->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $p->id }}" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <form method="POST" action="{{ route('pegawai.destroy', $p) }}" id="deleteForm{{ $p->id }}">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4 text-center">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--sc-danger-light); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="ti ti-alert-triangle" style="font-size: 2rem; color: var(--sc-danger);"></i>
                    </div>
                    <h3 class="fw-bold mb-1" id="deleteModalLabel{{ $p->id }}">Hapus Pegawai?</h3>
                    <p class="text-muted mb-1">Konfirmasi hapus dengan mengetik NIP pegawai:</p>
                    <p class="mb-3">
                        <strong class="text-dark">{{ $p->name }}</strong><br>
                        <code style="font-size:0.85rem;">NIP: {{ $p->nip }}</code>
                    </p>
                    <input type="text" class="form-control text-center mb-3"
                           placeholder="Ketik NIP untuk konfirmasi..."
                           id="nipConfirm{{ $p->id }}"
                           oninput="checkNipConfirm('{{ $p->id }}', '{{ $p->nip }}')"
                           style="border-radius:10px;border:2px solid #e2e8f0;">
                    <div class="alert" style="background: var(--sc-danger-light); border: none; border-radius: 10px; color: var(--sc-danger); font-size: 0.82rem; text-align:left;">
                        <i class="ti ti-alert-circle me-1"></i>
                        Tindakan ini tidak dapat dibatalkan. Semua data terkait pegawai ini akan ikut terhapus.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4" style="justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;">Batal</button>
                    <button type="submit" class="btn sc-btn-danger" id="deleteBtn{{ $p->id }}" disabled style="min-width: 100px; opacity:0.5;">
                        <i class="ti ti-trash me-1"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
