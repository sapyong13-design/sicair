@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Ajukan Banding Cuti</h2>
            <p class="text-muted">Mengajukan banding untuk pengajuan cuti yang ditolak</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Detail Pengajuan Cuti</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Jenis Cuti</label>
                                <p class="fs-5">{{ $leaveRequest->jenis_cuti }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Periode</label>
                                <p class="fs-5">
                                    {{ \Carbon\Carbon::parse($leaveRequest->tanggal_mulai)->format('d/m/Y') }}
                                    s/d
                                    {{ \Carbon\Carbon::parse($leaveRequest->tanggal_selesai)->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Jumlah Hari</label>
                                <p class="fs-5">{{ $leaveRequest->jumlah_hari }} hari</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Status</label>
                                <p class="fs-5">
                                    <span class="badge bg-danger">Ditolak</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($leaveRequest->catatan_pejabat)
                        <div class="mb-0">
                            <label class="form-label text-muted">Catatan Penolakan</label>
                            <div class="alert alert-warning mb-0">
                                {{ $leaveRequest->catatan_pejabat }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ajukan Banding</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('appeal.store', $leaveRequest) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Alasan Banding <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                                      rows="5" placeholder="Jelaskan alasan mengapa pengajuan cuti ini seharusnya disetujui..." required>{{ old('reason') }}</textarea>
                            <small class="text-muted">Minimal 10 karakter, maksimal 1000 karakter</small>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Informasi Tambahan (Opsional)</label>
                            <textarea name="additional_info" class="form-control @error('additional_info') is-invalid @enderror"
                                      rows="4" placeholder="Tambahkan dokumen pendukung atau informasi lainnya...">{{ old('additional_info') }}</textarea>
                            <small class="text-muted">Maksimal 1000 karakter</small>
                            @error('additional_info') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Catatan:</strong> Banding Anda akan direview oleh admin/ketua. Pastikan Anda memberikan alasan yang jelas dan informatif.
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('leave.show', $leaveRequest) }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Kirim Banding
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">Tips Pengajuan Banding</h5>
                    <ul class="small mb-0">
                        <li>Jelaskan alasan secara detail dan objektif</li>
                        <li>Berikan dokumen pendukung jika ada</li>
                        <li>Hindari menggunakan bahasa emosional</li>
                        <li>Fokus pada fakta dan bukti yang relevan</li>
                        <li class="mt-2">Pengajuan banding akan ditinjau dalam 3-5 hari kerja</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
