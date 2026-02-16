@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Detail Perubahan Saldo Cuti</h2>
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
            <div class="card">
                <div class="card-header bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ $adjustment->user->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $adjustment->user->email }}</small>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge bg-{{ $adjustment->getStatusBadgeClass() }} fs-6">
                                {{ ucfirst($adjustment->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Tahun</label>
                                <p class="fs-5">{{ $adjustment->year }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Jenis Perubahan</label>
                                <p class="fs-5">
                                    @switch($adjustment->type)
                                        @case('addition')
                                            <span class="badge bg-success">Penambahan</span>
                                            @break
                                        @case('deduction')
                                            <span class="badge bg-warning">Pengurangan</span>
                                            @break
                                        @case('correction')
                                            <span class="badge bg-info">Koreksi</span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Jumlah Hari</label>
                                <p class="fs-5">
                                    <strong class="{{ $adjustment->adjustment_days > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $adjustment->adjustment_days > 0 ? '+' : '' }}{{ $adjustment->adjustment_days }} hari
                                    </strong>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Jenis Cuti</label>
                                <p class="fs-5">{{ $adjustment->jenis_cuti }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Alasan</label>
                        <p>{{ $adjustment->reason }}</p>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Dibuat Oleh</label>
                                <p>Sistem (Admin)</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Tanggal Dibuat</label>
                                <p>{{ $adjustment->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($adjustment->approved_by)
                                <div class="mb-3">
                                    <label class="form-label text-muted">Disetujui/Ditolak Oleh</label>
                                    <p>{{ $adjustment->approver->name ?? '-' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tanggal Keputusan</label>
                                    <p>{{ $adjustment->approved_at?->format('d M Y, H:i') ?? '-' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($adjustment->approval_note)
                        <div class="mb-3">
                            <label class="form-label text-muted">Catatan Keputusan</label>
                            <p>{{ $adjustment->approval_note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($adjustment->isPending())
                <div class="card mt-3">
                    <div class="card-header bg-warning text-dark">
                        <strong>Perlu Persetujuan</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form action="{{ route('balance-adjustment.approve', $adjustment) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Catatan (Opsional)</label>
                                        <textarea name="approval_note" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check"></i> Setujui Perubahan
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('balance-adjustment.reject', $adjustment) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Alasan Penolakan</label>
                                        <textarea name="approval_note" class="form-control @error('approval_note') is-invalid @enderror"
                                                  rows="3" placeholder="Jelaskan alasan penolakan..." required>{{ old('approval_note') }}</textarea>
                                        @error('approval_note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-times"></i> Tolak Perubahan
                                    </button>
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
                                <strong>Dibuat</strong>
                                <small class="text-muted d-block">
                                    {{ $adjustment->created_at->format('d M Y, H:i') }}
                                </small>
                            </div>
                        </div>

                        @if($adjustment->approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $adjustment->isApproved() ? 'success' : 'danger' }}"></div>
                                <div class="timeline-content">
                                    <strong>{{ $adjustment->isApproved() ? 'Disetujui' : 'Ditolak' }}</strong>
                                    <small class="text-muted d-block">
                                        {{ $adjustment->approved_at->format('d M Y, H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ route('balance-adjustment.index') }}" class="btn btn-outline-secondary w-100 mt-3">
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
