@extends('layouts.app')

@section('title', 'Dokumen - ' . $leaveRequest->user->name)

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark"></i> Dokumen Pengajuan Cuti
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted">Informasi Pengajuan</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Pemohon:</strong><br>
                                    {{ $leaveRequest->user->name }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Jenis Cuti:</strong><br>
                                    {{ $leaveRequest->type }}
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Periode:</strong><br>
                                    {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Status:</strong><br>
                                    <span class="badge bg-{{ $leaveRequest->status_badge_class }}">
                                        {{ ucfirst(str_replace('_', ' ', $leaveRequest->status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    @if(count($documents) > 0)
                        <h6 class="mb-3">File Dokumen</h6>
                        <div class="list-group">
                            @foreach($documents as $document)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="document-icon">
                                                    <i class="bi bi-{{ match(strtolower($document['extension'])) {
                                                        'pdf' => 'file-pdf',
                                                        'jpg', 'jpeg', 'png', 'gif' => 'file-image',
                                                        'doc', 'docx' => 'file-word',
                                                        'xls', 'xlsx' => 'file-excel',
                                                        default => 'file-earmark'
                                                    } }} fs-5"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $document['label'] }}</h6>
                                                    <small class="text-muted">
                                                        {{ $document['size'] }} • {{ strtoupper($document['extension']) }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btn-group" role="group">
                                            @if($document['extension'] === 'pdf' || str_contains($document['mime'], 'image'))
                                                <a href="{{ route('document.view', [$leaveRequest, $document['type']]) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Lihat dokumen">
                                                    <i class="bi bi-eye"></i> Lihat
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="Tipe file tidak dapat ditampilkan">
                                                    <i class="bi bi-eye"></i> Lihat
                                                </button>
                                            @endif
                                            <a href="{{ route('document.download', [$leaveRequest, $document['type']]) }}"
                                               class="btn btn-sm btn-outline-success"
                                               title="Download dokumen">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle"></i>
                            Tidak ada dokumen pendukung yang dilampirkan untuk pengajuan cuti ini.
                        </div>
                    @endif

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="{{ route('leave.show', $leaveRequest) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Dokumen yang dilampirkan akan tersedia untuk ditinjau oleh atasan dan pejabat yang bertanggung jawab dalam proses persetujuan cuti.
                    </p>

                    <h6 class="mt-4 mb-2">Format yang Didukung</h6>
                    <ul class="small">
                        <li>PDF</li>
                        <li>JPG/JPEG</li>
                        <li>PNG</li>
                        <li>Dokumen scan</li>
                    </ul>

                    <h6 class="mt-4 mb-2">Ukuran Maksimal</h6>
                    <p class="small">5 MB per file</p>
                </div>
            </div>

            @if(auth()->id() === $leaveRequest->user_id)
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-upload"></i> Upload Dokumen</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            Anda dapat menambahkan dokumen pendukung untuk pengajuan cuti ini.
                        </p>
                        @if($leaveRequest->status === 'diajukan' || $leaveRequest->status === 'pertimbangan_atasan')
                            <button type="button" class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="bi bi-plus"></i> Upload Dokumen
                            </button>
                        @else
                            <p class="small text-muted mb-0">
                                Hanya dapat upload dokumen saat status pengajuan masih aktif.
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Dokumen Pendukung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('document.upload', $leaveRequest) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dokumen" class="form-label">Pilih File</label>
                        <input type="file" class="form-control @error('dokumen') is-invalid @enderror"
                               id="dokumen" name="dokumen" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">
                            PDF, JPG, JPEG, atau PNG (Max. 5 MB)
                        </small>
                        @error('dokumen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Jelaskan jenis dokumen..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.document-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f0f0f0;
    border-radius: 4px;
    color: #007bff;
}

.list-group-item {
    border: 1px solid #dee2e6;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 4px;
}

.list-group-item:hover {
    background-color: #f9f9f9;
    transition: all 0.3s ease;
}
</style>
@endsection
