@extends('layouts.app')

@section('title', 'Detail Perubahan Cuti')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Header --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('leave.show', $amendment->leaveRequest) }}"
                   class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;">
                    <i class="ti ti-arrow-left"></i>
                </a>
                <div>
                    <h2 class="mb-0">Detail Perubahan Cuti</h2>
                    <div class="text-muted" style="font-size: 0.85rem;">
                        {{ $amendment->leaveRequest->user->name }} — {{ $amendment->leaveRequest->type_label }}
                    </div>
                </div>
            </div>

            {{-- Status Badge --}}
            <div class="mb-4">
                <span class="badge bg-{{ $amendment->getStatusBadgeClass() }} fs-5 px-3 py-2">
                    <i class="ti ti-{{ match($amendment->status) {
                        'pending' => 'clock-hour-4',
                        'approved' => 'circle-check',
                        'rejected' => 'circle-x',
                        default => 'question-mark'
                    } }}"></i>
                    {{ match($amendment->status) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst($amendment->status)
                    } }}
                </span>
            </div>

            {{-- Amendment Info Card --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-switch-horizontal"></i> Perubahan yang Diminta
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Tanggal Awal</h6>
                            <div class="alert alert-light mb-0">
                                <p class="mb-1">
                                    <strong>Mulai:</strong> {{ $amendment->original_start_date->format('d M Y') }}
                                </p>
                                <p class="mb-0">
                                    <strong>Selesai:</strong> {{ $amendment->original_end_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Tanggal Baru</h6>
                            <div class="alert alert-warning mb-0">
                                <p class="mb-1">
                                    <strong>Mulai:</strong> {{ $amendment->requested_start_date->format('d M Y') }}
                                </p>
                                <p class="mb-0">
                                    <strong>Selesai:</strong> {{ $amendment->requested_end_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-muted mb-2">Alasan Perubahan</h6>
                    <div class="alert alert-info">
                        {{ $amendment->reason }}
                    </div>

                    <div class="row text-sm">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Pemohon:</strong> {{ $amendment->requester->name }}
                            </p>
                            <p class="mb-0 text-muted" style="font-size: 0.85rem;">
                                {{ $amendment->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                        @if($amendment->approved_at)
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <strong>Diproses oleh:</strong> {{ $amendment->approver->name }}
                                </p>
                                <p class="mb-0 text-muted" style="font-size: 0.85rem;">
                                    {{ $amendment->approved_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Approval Notes (if exists) --}}
            @if($amendment->approval_note)
                <div class="card shadow-sm mb-4 border-{{ $amendment->status === 'approved' ? 'success' : 'danger' }}">
                    <div class="card-header bg-{{ $amendment->status === 'approved' ? 'success' : 'danger' }} text-white">
                        <h6 class="mb-0">
                            <i class="ti ti-{{ $amendment->status === 'approved' ? 'circle-check' : 'circle-x' }} me-1"></i>
                            {{ $amendment->status === 'approved' ? 'Catatan Persetujuan' : 'Catatan Penolakan' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        {{ $amendment->approval_note }}
                    </div>
                </div>
            @endif

            {{-- Approval Actions (for pending amendments) --}}
            @if($amendment->isPending() && (auth()->user()->isAdmin() || auth()->user()->isKetua() || auth()->user()->id === $amendment->leaveRequest->user->atasan_id))
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="ti ti-circle-check me-1"></i> Tindakan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#approveModal">
                                    <i class="ti ti-circle-check me-1"></i> Setujui Perubahan
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="ti ti-circle-x me-1"></i> Tolak Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Related Leave Request --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="ti ti-file me-1"></i> Pengajuan Cuti Terkait
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $amendment->leaveRequest->status_badge_class }}">
                            {{ ucfirst(str_replace('_', ' ', $amendment->leaveRequest->status)) }}
                        </span>
                    </p>
                    <p class="mb-0">
                        <a href="{{ route('leave.show', $amendment->leaveRequest) }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-external-link me-1"></i> Lihat Pengajuan Lengkap
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Setujui Perubahan Cuti</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('amendment.approve', $amendment) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="ti ti-info-circle me-1"></i>
                        Tanggal cuti akan diubah menjadi {{ $amendment->requested_start_date->format('d M Y') }} — {{ $amendment->requested_end_date->format('d M Y') }}
                    </div>
                    <div class="mb-3">
                        <label for="approval_note" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" id="approval_note" name="approval_note" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-circle-check me-1"></i> Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Tolak Perubahan Cuti</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('amendment.reject', $amendment) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="ti ti-alert-triangle me-1"></i>
                        Perubahan akan ditolak dan pemohon akan diberitahu.
                    </div>
                    <div class="mb-3">
                        <label for="rejection_note" class="form-label">Alasan Penolakan *</label>
                        <textarea class="form-control @error('approval_note') is-invalid @enderror"
                                  id="rejection_note" name="approval_note" rows="4" required
                                  placeholder="Jelaskan alasan penolakan..."></textarea>
                        @error('approval_note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-circle-x me-1"></i> Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
