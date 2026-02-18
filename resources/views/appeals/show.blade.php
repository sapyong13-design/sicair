@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Detail Banding Pengajuan Cuti</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Appeal Details Card -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ $appeal->appellant->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $appeal->appellant->email }}</small>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge bg-{{ $appeal->getStatusBadgeClass() }} fs-6">
                                {{ ucfirst($appeal->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">No. Pengajuan Cuti</label>
                                <p class="fs-5">
                                    <a href="{{ route('leave.show', $appeal->leaveRequest) }}" class="text-decoration-none">
                                        #{{ str_pad($appeal->leave_request_id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Jenis Cuti</label>
                                <p class="fs-5">{{ $appeal->leaveRequest->jenis_cuti }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Periode Cuti</label>
                                <p class="fs-5">
                                    {{ \Carbon\Carbon::parse($appeal->leaveRequest->tanggal_mulai)->format('d M Y') }}
                                    s/d
                                    {{ \Carbon\Carbon::parse($appeal->leaveRequest->tanggal_selesai)->format('d M Y') }}
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Jumlah Hari</label>
                                <p class="fs-5">{{ $appeal->leaveRequest->jumlah_hari }} hari</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label text-muted">Alasan Penolakan Awal</label>
                        <div class="alert alert-warning mb-0">
                            {{ $appeal->leaveRequest->catatan_pejabat ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Alasan Banding</label>
                        <p>{{ $appeal->reason }}</p>
                    </div>

                    @if($appeal->additional_info)
                        <div class="mb-3">
                            <label class="form-label text-muted">Informasi Tambahan</label>
                            <p>{{ $appeal->additional_info }}</p>
                        </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Diajukan Pada</label>
                                <p>{{ $appeal->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($appeal->decided_by)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Diputuskan Oleh</label>
                                    <p>{{ $appeal->decider->name ?? '-' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tanggal Keputusan</label>
                                    <p>{{ $appeal->decided_at?->format('d M Y, H:i') ?? '-' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($appeal->decision_note)
                        <div class="mb-0">
                            <label class="form-label text-muted">Catatan Keputusan</label>
                            <div class="alert alert-{{ $appeal->decision === 'approved' ? 'success' : 'danger' }}">
                                {{ $appeal->decision_note }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Decision Card (for admins/ketua) -->
            @if($appeal->isPending() && (Auth::user()->isAdmin() || Auth::user()->isKetua()))
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <strong>Butuh Keputusan</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form action="{{ route('appeal.approve', $appeal) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Catatan Persetujuan (Opsional)</label>
                                        <textarea name="decision_note" class="form-control" rows="4"
                                                  placeholder="Berikan catatan jika diperlukan..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check-circle"></i> Setujui Banding - Cuti Disetujui
                                    </button>
                                    <small class="text-muted d-block mt-2">
                                        Dengan mengsetujui, pengajuan cuti akan diubah status menjadi DISETUJUI
                                    </small>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('appeal.deny', $appeal) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                        <textarea name="decision_note" class="form-control @error('decision_note') is-invalid @enderror"
                                                  rows="4" placeholder="Jelaskan alasan menolak banding..." required>{{ old('decision_note') }}</textarea>
                                        @error('decision_note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-times-circle"></i> Tolak Banding - Tetap Ditolak
                                    </button>
                                    <small class="text-muted d-block mt-2">
                                        Dengan menolak, pengajuan cuti tetap dalam status DITOLAK
                                    </small>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">Status Timeline</h5>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <strong>Banding Diajukan</strong>
                                <small class="text-muted d-block">
                                    {{ $appeal->created_at->format('d M Y, H:i') }}
                                </small>
                            </div>
                        </div>

                        @if($appeal->decided_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $appeal->isApproved() ? 'success' : 'danger' }}"></div>
                                <div class="timeline-content">
                                    <strong>{{ $appeal->isApproved() ? 'Banding Disetujui' : 'Banding Ditolak' }}</strong>
                                    <small class="text-muted d-block">
                                        {{ $appeal->decided_at->format('d M Y, H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ route('appeal.index') }}" class="btn btn-outline-secondary w-100 mt-3">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 10px 0;
}
.timeline-item {
    display: flex;
    margin-bottom: 15px;
}
.timeline-marker {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin-right: 15px;
    flex-shrink: 0;
    margin-top: 2px;
}
.timeline-content {
    flex: 1;
}
.timeline-content small {
    font-size: 0.85rem;
}
</style>
@endsection
