@extends('layouts.app')

@section('title', 'Ajukan Perubahan Cuti')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square"></i> Permohonan Perubahan Tanggal Cuti
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Informasi Pengajuan Asal --}}
                    <div class="alert alert-info" role="alert">
                        <h6 class="mb-3"><i class="bi bi-info-circle"></i> Pengajuan Asal</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Jenis Cuti:</strong> {{ $leaveRequest->type }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Status:</strong>
                                    <span class="badge bg-{{ $leaveRequest->status_badge_class }}">
                                        {{ ucfirst(str_replace('_', ' ', $leaveRequest->status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-0">
                                    <strong>Tanggal Awal:</strong><br>
                                    <span class="text-muted">
                                        {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-0">
                                    <strong>Jumlah Hari:</strong><br>
                                    <span class="text-muted">{{ $leaveRequest->number_of_days }} hari kerja</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Form Perubahan --}}
                    <form action="{{ route('amendment.store', $leaveRequest) }}" method="POST">
                        @csrf

                        <h6 class="mb-4">Tanggal Pengajuan Baru</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                       id="start_date" name="start_date"
                                       value="{{ old('start_date', $leaveRequest->start_date->format('Y-m-d')) }}"
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                       id="end_date" name="end_date"
                                       value="{{ old('end_date', $leaveRequest->end_date->format('Y-m-d')) }}"
                                       required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Alasan Perubahan</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror"
                                      id="reason" name="reason" rows="4" required
                                      placeholder="Jelaskan alasan mengapa tanggal perlu diubah...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Maksimal 500 karakter</small>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <a href="{{ route('leave.show', $leaveRequest) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-send"></i> Ajukan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Penting</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Perubahan tanggal hanya dapat dilakukan saat status pengajuan masih <strong>Diajukan</strong> atau <strong>Menunggu Pertimbangan Atasan</strong></li>
                        <li>Perubahan akan diperiksa oleh atasan dan pejabat yang bertanggung jawab</li>
                        <li>Hanya dapat mengajukan <strong>satu perubahan sekaligus</strong></li>
                        <li>Tunggu persetujuan sebelum mengajukan perubahan lagi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('start_date').addEventListener('change', function() {
    const endDate = document.getElementById('end_date');
    if (new Date(endDate.value) < new Date(this.value)) {
        endDate.value = this.value;
    }
});
</script>
@endsection
