@extends('layouts.app')

@section('title', 'Dashboard - SiHEALING')

@section('content')
<div class="page-header d-print-none mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h2 class="page-title">
                Dashboard
            </h2>
            <div class="text-muted mt-1">
                Selamat datang, <strong>{{ $user->name }}</strong>
                <span class="badge bg-blue-lt ms-1">{{ ucfirst($user->role) }}</span>
            </div>
        </div>
    </div>
</div>

@if($user->isAdmin())
    {{-- ===== ADMIN DASHBOARD ===== --}}
    <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader text-muted">Menunggu Persetujuan</div>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="h1 mb-0 me-2 text-warning">{{ $pendingRequests->count() }}</div>
                        <div class="me-auto">
                            <span class="text-muted">pengajuan</span>
                        </div>
                        <span class="text-warning"><i class="ti ti-clock" style="font-size: 2rem;"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader text-muted">Disetujui (Terbaru)</div>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="h1 mb-0 me-2 text-success">{{ $recentDecisions->where('status', 'approved')->count() }}</div>
                        <div class="me-auto">
                            <span class="text-muted">pengajuan</span>
                        </div>
                        <span class="text-success"><i class="ti ti-circle-check" style="font-size: 2rem;"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader text-muted">Ditolak (Terbaru)</div>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="h1 mb-0 me-2 text-danger">{{ $recentDecisions->where('status', 'rejected')->count() }}</div>
                        <div class="me-auto">
                            <span class="text-muted">pengajuan</span>
                        </div>
                        <span class="text-danger"><i class="ti ti-circle-x" style="font-size: 2rem;"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Requests Table --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-clock me-2"></i>Pengajuan Menunggu Persetujuan</h3>
        </div>
        @if($pendingRequests->isEmpty())
        <div class="card-body">
            <div class="empty">
                <div class="empty-icon"><i class="ti ti-mood-smile" style="font-size: 3rem; color: #2fb344;"></i></div>
                <p class="empty-title">Tidak ada pengajuan yang menunggu</p>
                <p class="empty-subtitle text-muted">Semua pengajuan cuti sudah diproses.</p>
            </div>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Tanggal</th>
                        <th>Durasi</th>
                        <th>Alasan</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pendingRequests as $req)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $req->user->name }}</div>
                            <div class="text-muted small">NIP: {{ $req->user->nip }} &middot; Sisa: {{ $req->user->leave_balance }} hari</div>
                        </td>
                        <td>
                            <div>{{ $req->start_date->format('d/m/Y') }}</div>
                            <div class="text-muted small">s.d. {{ $req->end_date->format('d/m/Y') }}</div>
                        </td>
                        <td><span class="badge bg-blue-lt">{{ $req->total_days }} hari</span></td>
                        <td><span class="text-muted">{{ Str::limit($req->reason, 50) }}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $req->id }}">
                                    <i class="ti ti-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>

                            {{-- Approve Modal --}}
                            <div class="modal modal-blur fade" id="approveModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('leave.approve', $req) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Setujui Cuti</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Setujui cuti <strong>{{ $req->user->name }}</strong> selama <strong>{{ $req->total_days }} hari</strong>?</p>
                                                <p class="text-muted small">Sisa cuti pegawai akan dikurangi otomatis.</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Catatan (opsional)</label>
                                                    <textarea name="admin_note" class="form-control" rows="2" placeholder="Catatan untuk pegawai..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="ti ti-check me-1"></i> Setujui
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Reject Modal --}}
                            <div class="modal modal-blur fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('leave.reject', $req) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tolak Pengajuan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Tolak cuti <strong>{{ $req->user->name }}</strong>?</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Alasan penolakan <span class="text-danger">*</span></label>
                                                    <textarea name="admin_note" class="form-control" rows="2" placeholder="Alasan penolakan..." required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="ti ti-x me-1"></i> Tolak
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-history me-2"></i>Riwayat Keputusan Terbaru</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentDecisions as $req)
                    <tr>
                        <td>{{ $req->user->name }}</td>
                        <td>{{ $req->start_date->format('d/m/Y') }} - {{ $req->end_date->format('d/m/Y') }}</td>
                        <td>
                            @if($req->status === 'approved')
                                <span class="badge status-badge-approved">Disetujui</span>
                            @else
                                <span class="badge status-badge-rejected">Ditolak</span>
                            @endif
                        </td>
                        <td><span class="text-muted">{{ $req->admin_note ?? '-' }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

@else
    {{-- ===== PEGAWAI DASHBOARD ===== --}}
    <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card" style="border-left: 4px solid #1a56db;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader text-muted">Sisa Cuti Anda</div>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="h1 mb-0 me-2" style="font-size: 3rem; color: #1a56db;">{{ $user->leave_balance }}</div>
                        <div class="me-auto">
                            <span class="text-muted">hari tersisa</span>
                        </div>
                        <span style="color: #1a56db;"><i class="ti ti-calendar-stats" style="font-size: 3rem;"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader text-muted">Pengajuan Pending</div>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="h1 mb-0 me-2 text-warning">{{ $leaveRequests->where('status', 'pending')->count() }}</div>
                        <div class="me-auto"><span class="text-muted">pengajuan</span></div>
                        <span class="text-warning"><i class="ti ti-clock" style="font-size: 2rem;"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader text-muted">Cuti Disetujui</div>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <div class="h1 mb-0 me-2 text-success">{{ $leaveRequests->where('status', 'approved')->count() }}</div>
                        <div class="me-auto"><span class="text-muted">pengajuan</span></div>
                        <span class="text-success"><i class="ti ti-circle-check" style="font-size: 2rem;"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick action --}}
    <div class="d-grid d-md-flex mb-4">
        <a href="{{ route('leave.create') }}" class="btn btn-primary btn-lg">
            <i class="ti ti-file-plus me-2"></i> Ajukan Cuti Baru
        </a>
    </div>

    {{-- Leave History --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-list me-2"></i>Riwayat Pengajuan Cuti</h3>
        </div>
        @if($leaveRequests->isEmpty())
        <div class="card-body">
            <div class="empty">
                <div class="empty-icon"><i class="ti ti-file-off" style="font-size: 3rem; color: #667382;"></i></div>
                <p class="empty-title">Belum ada pengajuan</p>
                <p class="empty-subtitle text-muted">Anda belum pernah mengajukan cuti.</p>
            </div>
        </div>
        @else
        {{-- Mobile cards --}}
        <div class="card-body d-md-none">
            @foreach($leaveRequests as $req)
            <div class="card mb-2 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong>{{ $req->start_date->format('d/m/Y') }}</strong>
                            <span class="text-muted">s.d.</span>
                            <strong>{{ $req->end_date->format('d/m/Y') }}</strong>
                        </div>
                        @if($req->status === 'pending')
                            <span class="badge status-badge-pending">Pending</span>
                        @elseif($req->status === 'approved')
                            <span class="badge status-badge-approved">Disetujui</span>
                        @else
                            <span class="badge status-badge-rejected">Ditolak</span>
                        @endif
                    </div>
                    <div class="text-muted small mb-1">{{ $req->total_days }} hari &middot; {{ Str::limit($req->reason, 60) }}</div>
                    @if($req->admin_note)
                    <div class="small mt-1"><i class="ti ti-message"></i> {{ $req->admin_note }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        {{-- Desktop table --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Durasi</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Catatan Admin</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($leaveRequests as $req)
                    <tr>
                        <td>{{ $req->start_date->format('d/m/Y') }}</td>
                        <td>{{ $req->end_date->format('d/m/Y') }}</td>
                        <td><span class="badge bg-blue-lt">{{ $req->total_days }} hari</span></td>
                        <td>{{ Str::limit($req->reason, 40) }}</td>
                        <td>
                            @if($req->status === 'pending')
                                <span class="badge status-badge-pending">Pending</span>
                            @elseif($req->status === 'approved')
                                <span class="badge status-badge-approved">Disetujui</span>
                            @else
                                <span class="badge status-badge-rejected">Ditolak</span>
                            @endif
                        </td>
                        <td><span class="text-muted">{{ $req->admin_note ?? '-' }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endif
@endsection
