@extends('layouts.app')

@section('title', 'Detail Pegawai - SiHEALING')

@section('content')
{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;">
                <i class="ti ti-arrow-left" style="font-size: 1.2rem;"></i>
            </a>
            <div>
                <h2 class="sh-page-title mb-0">{{ $pegawai->name }}</h2>
                <div class="text-muted" style="font-size: 0.85rem;">
                    NIP: {{ $pegawai->nip }}
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.leave.create', $pegawai) }}" class="btn sh-btn-success text-white">
                <i class="ti ti-file-plus me-1"></i> Tambah Cuti
            </a>
            <a href="{{ route('pegawai.edit', $pegawai) }}" class="btn sh-btn-primary">
                <i class="ti ti-edit me-1"></i> Edit Pegawai
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left Column: Info --}}
    <div class="col-lg-5">
        {{-- Profile Card --}}
        <div class="card sh-card mb-4">
            <div class="card-body p-4 text-center">
                <div class="sh-user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 1.8rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 20px;">
                    {{ strtoupper(substr($pegawai->name, 0, 2)) }}
                </div>
                <h3 class="fw-bold mb-1">{{ $pegawai->name }}</h3>
                <div class="text-muted mb-2" style="font-size: 0.88rem;">{{ $pegawai->jabatan ?? '-' }}</div>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    @php
                        $roleBadgeClass = match($pegawai->role) {
                            'admin' => 'sh-badge-approved',
                            'ketua' => 'sh-badge-rejected',
                            'panitera', 'sekretaris', 'atasan' => 'sh-badge-pending',
                            default => 'sh-badge-approved',
                        };
                        $roleLabelShow = match($pegawai->role) {
                            'admin' => 'Admin',
                            'ketua' => 'Ketua PN',
                            'panitera' => 'Panitera',
                            'sekretaris' => 'Sekretaris',
                            'atasan' => 'Atasan',
                            'hakim' => 'Hakim',
                            'hakim_ad_hoc' => 'Hakim Ad Hoc',
                            'pegawai' => 'Pegawai',
                            default => ucfirst($pegawai->role),
                        };
                    @endphp
                    <span class="sh-badge {{ $roleBadgeClass }}" style="font-size: 0.75rem;">
                        {{ $roleLabelShow }}
                    </span>
                    <span class="badge" style="background: #f1f5f9; color: #475569; border-radius: 50px; font-weight: 600; font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                        {{ ucfirst($pegawai->status_pegawai ?? '-') }}
                    </span>
                    @if($pegawai->lokasi_terpencil)
                    <span class="badge" style="background: var(--sh-warning-light); color: var(--sh-warning); border-radius: 50px; font-weight: 600; font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                        <i class="ti ti-map-pin"></i> Terpencil
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Detail Info --}}
        <div class="card sh-card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-info-circle me-2" style="color: var(--sh-primary);"></i>
                    Informasi Lengkap
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0" style="font-size: 0.88rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 40%; padding: 0.75rem 1rem;">NIP</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->nip }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Jenis Kelamin</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Golongan/Ruang</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->golongan_ruang ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Unit Kerja</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->unit_kerja ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Masa Kerja</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->masa_kerja_format ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">TMT</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->masa_kerja_mulai ? $pegawai->masa_kerja_mulai->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Atasan Langsung</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->atasan->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Jumlah Anak</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->jumlah_anak ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Telepon</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->telepon ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Alamat</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->alamat ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Cuti Balance --}}
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-calendar-stats me-2" style="color: var(--sh-success);"></i>
                    Sisa Cuti Tahunan
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span style="font-size: 2.5rem; font-weight: 800; color: var(--sh-primary);">{{ $pegawai->leave_balance }}</span>
                    <span class="text-muted">hari tersisa</span>
                </div>
                <div style="height: 8px; border-radius: 4px; background: var(--sh-gray-100);">
                    <div style="height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--sh-primary), #22c55e); width: {{ min(100, ($pegawai->leave_balance / 12) * 100) }}%;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Records --}}
    <div class="col-lg-7">
        {{-- Cuti Records per Year --}}
        @if($pegawai->cutiRecords->isNotEmpty())
        <div class="card sh-card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-chart-bar me-2" style="color: #7c3aed;"></i>
                    Rekap Cuti Tahunan
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table sh-table mb-0">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Hak</th>
                            <th>Diambil</th>
                            <th>Sisa</th>
                            <th>Carry Over</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($pegawai->cutiRecords as $record)
                        <tr>
                            <td class="fw-bold">{{ $record->tahun }}</td>
                            <td>{{ $record->total_hak }} hari</td>
                            <td>{{ $record->cuti_diambil }} hari</td>
                            <td>
                                <span class="fw-bold" style="color: var(--sh-primary);">{{ $record->sisa_cuti }} hari</span>
                            </td>
                            <td>{{ $record->carry_over ?? 0 }} hari</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Leave History --}}
        <div class="card sh-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0">
                    <i class="ti ti-history me-2" style="color: #d97706;"></i>
                    Riwayat Pengajuan Cuti
                </h3>
                @if($pegawai->leaveRequests->isNotEmpty())
                <span class="text-muted" style="font-size: 0.8rem;">{{ $pegawai->leaveRequests->count() }} pengajuan</span>
                @endif
            </div>

            @if($pegawai->leaveRequests->isEmpty())
            <div class="card-body py-5">
                <div class="text-center">
                    <div class="sh-empty-icon">
                        <i class="ti ti-calendar-off"></i>
                    </div>
                    <p class="text-muted mb-0">Belum ada riwayat pengajuan cuti.</p>
                </div>
            </div>
            @else
            <div class="card-body p-3">
                @foreach($pegawai->leaveRequests as $req)
                <div class="card sh-history-card status-{{ $req->status }} mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fw-bold" style="font-size: 0.9rem;">{{ $req->type_label }}</span>
                                <div class="text-muted" style="font-size: 0.78rem;">
                                    <i class="ti ti-calendar me-1"></i>
                                    {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                                    &middot; {{ $req->total_days }} hari
                                </div>
                            </div>
                            @php
                                $badgeClass = match(true) {
                                    $req->isApproved() => 'sh-badge-approved',
                                    $req->isRejected() => 'sh-badge-rejected',
                                    default => 'sh-badge-pending',
                                };
                            @endphp
                            <span class="sh-badge {{ $badgeClass }}">{{ $req->status_label }}</span>
                        </div>
                        @if($req->reason)
                        <div style="font-size: 0.82rem; color: #475569;" class="mb-2">{{ Str::limit($req->reason, 100) }}</div>
                        @endif
                        {{-- Admin Actions --}}
                        <div class="d-flex gap-2 mt-1">
                            <a href="{{ route('leave.show', $req) }}"
                               class="btn btn-sm btn-outline-secondary" style="border-radius: 7px; font-size: 0.78rem; padding: 0.2rem 0.65rem;">
                                <i class="ti ti-eye me-1"></i>Detail
                            </a>
                            <a href="{{ route('admin.leave.edit', $req) }}"
                               class="btn btn-sm btn-outline-primary" style="border-radius: 7px; font-size: 0.78rem; padding: 0.2rem 0.65rem;">
                                <i class="ti ti-edit me-1"></i>Edit
                            </a>
                            <form method="POST" action="{{ route('admin.leave.destroy', $req) }}"
                                  onsubmit="return confirm('Hapus riwayat cuti ini? Saldo cuti tahunan akan dikembalikan jika status disetujui.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger" style="border-radius: 7px; font-size: 0.78rem; padding: 0.2rem 0.65rem;">
                                    <i class="ti ti-trash me-1"></i>Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
