@extends('layouts.app')

@section('title', 'Dokumen - ' . $leaveRequest->user->name)

@section('content')
<div style="padding-top: 1rem; padding-bottom: 2rem;">
    <div class="container-xl">
        {{-- Header --}}
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('leave.show', $leaveRequest) }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;">
                <i class="ti ti-arrow-left" style="font-size: 1.2rem;"></i>
            </a>
            <div>
                <h2 class="sh-page-title mb-0">Dokumen Pengajuan Cuti</h2>
                <div class="text-muted" style="font-size: 0.85rem;">
                    {{ $leaveRequest->user->name }}
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Main Content --}}
            <div class="col-lg-8">
                {{-- Leave Request Info Card --}}
                <div class="card sh-card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="ti ti-info-circle me-2" style="color: var(--sh-primary);"></i>
                            Informasi Pengajuan
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted" style="font-size: 0.85rem;">Pemohon</div>
                                <div class="fw-semibold">{{ $leaveRequest->user->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted" style="font-size: 0.85rem;">Jenis Cuti</div>
                                <div class="fw-semibold">{{ $leaveRequest->type_label }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted" style="font-size: 0.85rem;">Periode</div>
                                <div class="fw-semibold">{{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted" style="font-size: 0.85rem;">Status</div>
                                <span class="sh-badge sh-badge-{{ match($leaveRequest->status) {
                                    'diajukan', 'pending' => 'pending',
                                    'pertimbangan_atasan' => 'pending',
                                    'disetujui', 'approved' => 'approved',
                                    'ditolak', 'rejected' => 'rejected',
                                    default => 'pending'
                                } }}">{{ $leaveRequest->status_label }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Documents Card --}}
                <div class="card sh-card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="ti ti-file me-2" style="color: var(--sh-primary);"></i>
                            File Dokumen
                        </h3>
                    </div>

                    @if(count($documents) > 0)
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($documents as $document)
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between p-3" style="background: var(--sh-gray-50); border-radius: 10px; border: 1px solid var(--sh-border);">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="document-icon" style="width: 44px; height: 44px; border-radius: 10px; background: var(--sh-primary-light); color: var(--sh-primary); display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                                            @php
                                                $ext = strtolower($document['extension']);
                                                $icon = match($ext) {
                                                    'pdf' => 'ti-file-pdf',
                                                    'jpg', 'jpeg', 'png', 'gif' => 'ti-file-image',
                                                    'doc', 'docx' => 'ti-file-text',
                                                    'xls', 'xlsx' => 'ti-file-spreadsheet',
                                                    default => 'ti-file'
                                                };
                                            @endphp
                                            <i class="ti {{ $icon }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size: 0.95rem;">{{ $document['label'] }}</div>
                                            <div class="text-muted" style="font-size: 0.8rem;">
                                                {{ $document['size'] }} • {{ strtoupper($document['extension']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if($document['extension'] === 'pdf' || str_contains($document['mime'], 'image'))
                                            <a href="{{ route('document.view', [$leaveRequest, $document['type']]) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;"
                                               title="Lihat dokumen">
                                                <i class="ti ti-eye me-1"></i> Lihat
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled style="border-radius: 8px;" title="Tipe file tidak dapat ditampilkan">
                                                <i class="ti ti-eye me-1"></i> Lihat
                                            </button>
                                        @endif
                                        <a href="{{ route('document.download', [$leaveRequest, $document['type']]) }}"
                                           class="btn btn-sm btn-outline-primary" style="border-radius: 8px;"
                                           title="Download dokumen">
                                            <i class="ti ti-download me-1"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="card-body py-5">
                        <div class="text-center">
                            <div class="sh-empty-icon">
                                <i class="ti ti-file-off"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-1">Tidak Ada Dokumen</h4>
                            <p class="text-muted mb-0">Tidak ada dokumen pendukung yang dilampirkan untuk pengajuan cuti ini.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Information Card --}}
                <div class="card sh-card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="ti ti-help-circle me-2" style="color: var(--sh-warning);"></i>
                            Informasi
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            Dokumen yang dilampirkan akan tersedia untuk ditinjau oleh atasan dan pejabat yang bertanggung jawab dalam proses persetujuan cuti.
                        </p>

                        <div class="mb-3">
                            <h6 class="fw-semibold mb-2">Format yang Didukung</h6>
                            <ul class="small text-muted ps-3 mb-0">
                                <li>PDF</li>
                                <li>JPG/JPEG</li>
                                <li>PNG</li>
                                <li>Dokumen scan</li>
                            </ul>
                        </div>

                        <div>
                            <h6 class="fw-semibold mb-2">Ukuran Maksimal</h6>
                            <p class="small text-muted mb-0">5 MB per file</p>
                        </div>
                    </div>
                </div>

                {{-- Upload Card --}}
                @if(auth()->id() === $leaveRequest->user_id)
                <div class="card sh-card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            <i class="ti ti-upload me-2" style="color: var(--sh-success);"></i>
                            Upload Dokumen
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            Anda dapat menambahkan dokumen pendukung untuk pengajuan cuti ini.
                        </p>
                        @if($leaveRequest->status === 'diajukan' || $leaveRequest->status === 'pertimbangan_atasan')
                            <button type="button" class="btn btn-primary sh-btn-primary w-100" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="ti ti-plus me-1"></i> Upload Dokumen
                            </button>
                        @else
                            <p class="small text-muted mb-0">
                                <i class="ti ti-info-circle me-1"></i>
                                Hanya dapat upload dokumen saat status pengajuan masih aktif.
                            </p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal modal-blur fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <form action="{{ route('document.upload', $leaveRequest) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <h5 class="mb-3">Upload Dokumen Pendukung</h5>

                    <div class="mb-3">
                        <label for="dokumen" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            Pilih File <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control @error('dokumen') is-invalid @enderror"
                               id="dokumen" name="dokumen" accept=".pdf,.jpg,.jpeg,.png" required
                               style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                        <small class="form-text text-muted mt-2 d-block">
                            <i class="ti ti-info-circle"></i> PDF, JPG, JPEG, atau PNG (Max. 5 MB)
                        </small>
                        @error('dokumen')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="keterangan" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            Keterangan (Opsional)
                        </label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                  placeholder="Jelaskan jenis dokumen..."
                                  style="border-radius: 10px; border: 2px solid #e2e8f0;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4" style="justify-content: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;">Batal</button>
                    <button type="submit" class="btn btn-primary sh-btn-primary" style="min-width: 100px;">
                        <i class="ti ti-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Light mode */
.sh-empty-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--sh-gray-100);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--sh-text-muted);
    margin-bottom: 1rem;
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .document-icon {
    background: var(--sh-primary-light) !important;
}

[data-bs-theme="dark"] [style*="background: var(--sh-gray-50)"] {
    background: var(--sh-gray-100) !important;
}

[data-bs-theme="dark"] .sh-empty-icon {
    background: var(--sh-gray-100) !important;
    color: var(--sh-text-muted) !important;
}
</style>
@endsection
