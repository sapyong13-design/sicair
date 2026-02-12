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
            <div class="text-muted" style="font-size: 0.9rem;">
                Selamat datang kembali, <strong class="text-dark">{{ $user->name }}</strong>
                <span class="sh-badge sh-badge-{{ $user->isAdmin() ? 'approved' : 'pending' }} ms-1" style="font-size: 0.7rem;">
                    <i class="ti ti-{{ $user->isAdmin() ? 'shield-check' : 'user' }}"></i>
                    {{ ucfirst($user->role) }}
                </span>
            </div>
        </div>
        @if(!$user->isAdmin())
        <a href="{{ route('leave.create') }}" class="btn btn-primary sh-btn-primary">
            <i class="ti ti-file-plus me-1"></i> Ajukan Cuti
        </a>
        @endif
    </div>
</div>

@if($user->isAdmin())
    {{-- ============================= --}}
    {{--       ADMIN DASHBOARD         --}}
    {{-- ============================= --}}

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Menunggu Persetujuan</div>
                            <div class="sh-stat-number" style="color: var(--sh-warning);">{{ $pendingRequests->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">pengajuan aktif</div>
                        </div>
                        <div class="sh-stat-icon icon-warning">
                            <i class="ti ti-clock-hour-4"></i>
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
                            <div class="sh-stat-label mb-2">Disetujui</div>
                            <div class="sh-stat-number" style="color: var(--sh-success);">{{ $recentDecisions->where('status', 'approved')->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">terbaru diproses</div>
                        </div>
                        <div class="sh-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-danger">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Ditolak</div>
                            <div class="sh-stat-number" style="color: var(--sh-danger);">{{ $recentDecisions->where('status', 'rejected')->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">terbaru diproses</div>
                        </div>
                        <div class="sh-stat-icon icon-danger">
                            <i class="ti ti-circle-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Requests --}}
    <div class="card sh-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-clock-hour-4 me-2" style="color: var(--sh-warning);"></i>
                Pengajuan Menunggu Persetujuan
            </h3>
            @if($pendingRequests->isNotEmpty())
            <span class="sh-badge sh-badge-pending">{{ $pendingRequests->count() }} antrian</span>
            @endif
        </div>

        @if($pendingRequests->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sh-empty-icon">
                    <i class="ti ti-mood-happy"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Semua Beres!</h4>
                <p class="text-muted mb-0">Tidak ada pengajuan cuti yang menunggu persetujuan saat ini.</p>
            </div>
        </div>
        @else
        {{-- Mobile: cards --}}
        <div class="card-body d-md-none">
            @foreach($pendingRequests as $req)
            <div class="card sh-history-card status-pending mb-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <div class="fw-bold" style="font-size: 0.95rem;">{{ $req->user->name }}</div>
                            <div class="text-muted" style="font-size: 0.78rem;">NIP: {{ $req->user->nip }}</div>
                        </div>
                        <span class="sh-badge sh-badge-pending">
                            <i class="ti ti-clock"></i> {{ $req->total_days }} hari
                        </span>
                    </div>
                    <div class="text-muted mb-2" style="font-size: 0.82rem;">
                        <i class="ti ti-calendar me-1"></i>
                        {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                    </div>
                    <div class="mb-3" style="font-size: 0.85rem; color: #475569;">
                        {{ Str::limit($req->reason, 80) }}
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm sh-btn-success flex-fill" data-bs-toggle="modal" data-bs-target="#approveModal{{ $req->id }}">
                            <i class="ti ti-check me-1"></i> Setujui
                        </button>
                        <button class="btn btn-sm sh-btn-danger flex-fill" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                            <i class="ti ti-x me-1"></i> Tolak
                        </button>
                    </div>
                </div>
            </div>
            @include('partials.admin-modals', ['req' => $req])
            @endforeach
        </div>

        {{-- Desktop: table --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table sh-table mb-0">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Periode Cuti</th>
                        <th>Durasi</th>
                        <th>Alasan</th>
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
                                    <div class="text-muted" style="font-size: 0.75rem;">NIP: {{ $req->user->nip }} &middot; Sisa: {{ $req->user->leave_balance }} hari</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.88rem;">{{ $req->start_date->format('d M Y') }}</div>
                            <div class="text-muted" style="font-size: 0.78rem;">s.d. {{ $req->end_date->format('d M Y') }}</div>
                        </td>
                        <td>
                            <span class="sh-badge sh-badge-pending">
                                <i class="ti ti-clock"></i> {{ $req->total_days }} hari
                            </span>
                        </td>
                        <td style="max-width: 200px;">
                            <span style="font-size: 0.85rem; color: #475569;">{{ Str::limit($req->reason, 60) }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm sh-btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $req->id }}" title="Setujui">
                                    <i class="ti ti-check me-1"></i> Setujui
                                </button>
                                <button class="btn btn-sm sh-btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}" title="Tolak">
                                    <i class="ti ti-x me-1"></i> Tolak
                                </button>
                            </div>
                            @include('partials.admin-modals', ['req' => $req])
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Recent Decisions --}}
    @if($recentDecisions->isNotEmpty())
    <div class="card sh-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-history me-2" style="color: #64748b;"></i>
                Riwayat Keputusan Terbaru
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table sh-table mb-0">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Periode</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentDecisions as $req)
                    <tr>
                        <td>
                            <div class="fw-semibold" style="font-size: 0.9rem;">{{ $req->user->name }}</div>
                        </td>
                        <td style="font-size: 0.85rem;">
                            {{ $req->start_date->format('d/m/Y') }} - {{ $req->end_date->format('d/m/Y') }}
                        </td>
                        <td>
                            <span class="badge bg-blue-lt" style="border-radius: 50px;">{{ $req->total_days }} hari</span>
                        </td>
                        <td>
                            @if($req->status === 'approved')
                                <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @else
                                <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                            @endif
                        </td>
                        <td style="max-width: 200px;">
                            <span class="text-muted" style="font-size: 0.85rem;">{{ $req->admin_note ?? '-' }}</span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
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
                            <i class="ti ti-calendar-stats me-1"></i> Sisa Cuti Anda
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <span class="sh-hero-number">{{ $user->leave_balance }}</span>
                            <span style="font-size: 1.1rem; opacity: 0.8;">/ 12 hari</span>
                        </div>
                        <div class="sh-hero-progress mb-2" style="max-width: 280px;">
                            <div class="sh-hero-progress-bar" style="width: {{ ($user->leave_balance / 12) * 100 }}%;"></div>
                        </div>
                        <div style="font-size: 0.82rem; opacity: 0.7;">
                            Terpakai {{ 12 - $user->leave_balance }} hari dari total 12 hari jatah cuti tahunan
                        </div>
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
                            <div class="sh-stat-label mb-1">Pending</div>
                            <div class="sh-stat-number" style="color: var(--sh-warning);">{{ $leaveRequests->where('status', 'pending')->count() }}</div>
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
                            <div class="sh-stat-number" style="color: var(--sh-success);">{{ $leaveRequests->where('status', 'approved')->count() }}</div>
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
                            <div class="sh-stat-number" style="color: var(--sh-danger);">{{ $leaveRequests->where('status', 'rejected')->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-danger">
                            <i class="ti ti-circle-x"></i>
                        </div>
                    </div>
                </div>
            </div>
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
                <div class="sh-empty-icon">
                    <i class="ti ti-calendar-off"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Belum Ada Pengajuan</h4>
                <p class="text-muted mb-3">Anda belum pernah mengajukan cuti. Mulai dengan klik tombol di bawah.</p>
                <a href="{{ route('leave.create') }}" class="btn btn-primary sh-btn-primary">
                    <i class="ti ti-file-plus me-1"></i> Ajukan Cuti Pertama Anda
                </a>
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
                                <i class="ti ti-calendar me-1" style="color: var(--sh-primary);"></i>
                                {{ $req->start_date->format('d M Y') }}
                            </div>
                            <div class="text-muted" style="font-size: 0.78rem;">
                                s.d. {{ $req->end_date->format('d M Y') }} &middot; {{ $req->total_days }} hari
                            </div>
                        </div>
                        @if($req->status === 'pending')
                            <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> Pending</span>
                        @elseif($req->status === 'approved')
                            <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                        @else
                            <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                        @endif
                    </div>
                    <div style="font-size: 0.85rem; color: #475569; margin-bottom: 0.5rem;">
                        {{ Str::limit($req->reason, 80) }}
                    </div>
                    @if($req->admin_note)
                    <div style="font-size: 0.8rem; background: var(--sh-gray-100); border-radius: 8px; padding: 0.5rem 0.75rem; margin-top: 0.5rem;">
                        <i class="ti ti-message me-1" style="color: var(--sh-primary);"></i>
                        <span class="text-muted">{{ $req->admin_note }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Desktop: table --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table sh-table mb-0">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Durasi</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Catatan Admin</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($leaveRequests as $req)
                    <tr>
                        <td>
                            <div class="fw-semibold" style="font-size: 0.88rem;">{{ $req->start_date->format('d M Y') }}</div>
                            <div class="text-muted" style="font-size: 0.78rem;">s.d. {{ $req->end_date->format('d M Y') }}</div>
                        </td>
                        <td>
                            <span class="badge bg-blue-lt" style="border-radius: 50px;">{{ $req->total_days }} hari</span>
                        </td>
                        <td style="max-width: 200px;">
                            <span style="font-size: 0.85rem; color: #475569;">{{ Str::limit($req->reason, 50) }}</span>
                        </td>
                        <td>
                            @if($req->status === 'pending')
                                <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> Pending</span>
                            @elseif($req->status === 'approved')
                                <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @else
                                <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                            @endif
                        </td>
                        <td style="max-width: 180px;">
                            @if($req->admin_note)
                            <span style="font-size: 0.82rem; color: #64748b;">
                                <i class="ti ti-message me-1"></i> {{ Str::limit($req->admin_note, 40) }}
                            </span>
                            @else
                            <span class="text-muted" style="font-size: 0.82rem;">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endif
@endsection
