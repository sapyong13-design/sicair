@extends('layouts.app')

@section('title', 'Detail Cuti - SiHEALING')

@section('content')
{{-- Breadcrumb (#10) --}}
<nav class="sh-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sh-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sh-breadcrumb-current">Detail Cuti</span>
</nav>

{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;" aria-label="Kembali ke dashboard">
                <i class="ti ti-arrow-left" style="font-size: 1.2rem;" aria-hidden="true"></i>
            </a>
            <div>
                <h2 class="sh-page-title mb-0">Detail Pengajuan Cuti</h2>
                <div class="text-muted" style="font-size: 0.85rem;">
                    {{ $leaveRequest->type_label }} &mdash; {{ $leaveRequest->user->name }}
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            {{-- #28 Print Preview --}}
            <button type="button" class="btn btn-outline-secondary"
               style="border-radius: 10px; font-size: 0.85rem;"
               onclick="document.getElementById('printPreviewModal').classList.add('show'); document.getElementById('printPreviewModal').style.display='block';"
               title="Preview sebelum cetak">
                <i class="ti ti-printer me-1"></i>
                Preview
            </button>
            <a href="{{ route('leave.surat-permohonan', $leaveRequest) }}"
               class="btn sh-btn-primary"
               style="border-radius: 10px; font-size: 0.85rem;"
               title="Download Surat Permohonan Cuti (PDF)"
               target="_blank">
                <i class="ti ti-file-text me-1"></i>
                Surat Permohonan
            </a>
            <a href="{{ route('leave.surat-permohonan-docx', $leaveRequest) }}"
               class="btn sh-btn-primary"
               style="border-radius: 10px; font-size: 0.85rem;"
               title="Download Surat Permohonan Cuti (DOCX Folio)"
               target="_blank">
                <i class="ti ti-file-word me-1"></i>
                DOCX
            </a>
            <a href="{{ route('leave.form-cuti', $leaveRequest) }}"
               class="btn sh-btn-primary"
               style="border-radius: 10px; font-size: 0.85rem;"
               title="Download Form Permintaan dan Pemberian Cuti (PDF)"
               target="_blank">
                <i class="ti ti-clipboard-text me-1"></i>
                Form Cuti
            </a>
            {{-- #21 Re-apply button (only for rejected/cancelled) --}}
            @if(auth()->id() === $leaveRequest->user_id && $leaveRequest->isRejected())
            <a href="{{ route('leave.create', ['reapply' => $leaveRequest->id]) }}"
               class="btn btn-outline-warning"
               style="border-radius: 10px; font-size: 0.85rem;"
               title="Ajukan ulang dengan data yang sama">
                <i class="ti ti-refresh me-1"></i>
                Ajukan Ulang
            </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert mb-4" style="background: var(--sh-success-light); color: var(--sh-success); border-radius: 12px; border: none;">
    <i class="ti ti-circle-check me-2"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert mb-4" style="background: var(--sh-danger-light); color: var(--sh-danger); border-radius: 12px; border: none;">
    <i class="ti ti-alert-circle me-2"></i> {{ session('error') }}
</div>
@endif

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        {{-- Status Banner --}}
        @php
            $statusConfig = match(true) {
                $leaveRequest->isApproved() => ['bg' => 'var(--sh-success-light)', 'color' => 'var(--sh-success)', 'icon' => 'ti-circle-check', 'text' => 'Disetujui'],
                $leaveRequest->isRejected() => ['bg' => 'var(--sh-danger-light)', 'color' => 'var(--sh-danger)', 'icon' => 'ti-circle-x', 'text' => 'Ditolak'],
                $leaveRequest->status === 'ditangguhkan' => ['bg' => 'var(--sh-warning-light)', 'color' => 'var(--sh-warning)', 'icon' => 'ti-clock-pause', 'text' => 'Ditangguhkan'],
                $leaveRequest->status === 'diubah' => ['bg' => 'var(--sh-primary-light)', 'color' => 'var(--sh-primary)', 'icon' => 'ti-edit', 'text' => 'Diubah'],
                $leaveRequest->status === 'pertimbangan_atasan' => ['bg' => '#fef3c7', 'color' => '#d97706', 'icon' => 'ti-user-check', 'text' => 'Menunggu Keputusan Pejabat'],
                default => ['bg' => '#e0f2fe', 'color' => '#0284c7', 'icon' => 'ti-clock-hour-4', 'text' => 'Diajukan / Menunggu Pertimbangan'],
            };
        @endphp
        <div class="mb-4" style="background: {{ $statusConfig['bg'] }}; border-radius: 14px; padding: 1rem 1.25rem;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: {{ $statusConfig['color'] }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="ti {{ $statusConfig['icon'] }}" style="color: #fff; font-size: 1.3rem;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 1.05rem; color: {{ $statusConfig['color'] }};">{{ $statusConfig['text'] }}</div>
                    <div class="text-muted" style="font-size: 0.82rem;">
                        Diajukan {{ $leaveRequest->created_at->format('d M Y, H:i') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Info Card --}}
        <div class="card sh-card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-file-text me-2" style="color: var(--sh-primary);"></i>
                    Informasi Pengajuan
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0" style="font-size: 0.88rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 35%; padding: 0.75rem 1.25rem;">Pemohon</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1.25rem;">
                                {{ $leaveRequest->user->name }}
                                <span class="text-muted fw-normal" style="font-size: 0.8rem;">({{ $leaveRequest->user->nip }})</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Jabatan</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1.25rem;">{{ $leaveRequest->user->jabatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Jenis Cuti</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1.25rem;">
                                <span style="color: var(--sh-primary);">{{ $leaveRequest->type_label }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Periode</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1.25rem;">
                                {{ $leaveRequest->start_date->format('d M Y') }} &mdash; {{ $leaveRequest->end_date->format('d M Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Durasi</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1.25rem;">
                                {{ $leaveRequest->total_days }} hari kalender
                                @if($leaveRequest->total_hari_kerja)
                                    ({{ $leaveRequest->total_hari_kerja }} hari kerja)
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Alasan</td>
                            <td style="padding: 0.75rem 1.25rem; color: #475569;">{{ $leaveRequest->reason }}</td>
                        </tr>
                        @if($leaveRequest->alasan_cap)
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Kategori Alasan</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1.25rem;">
                                {{ \App\Models\LeaveRequest::capLabels()[$leaveRequest->alasan_cap] ?? $leaveRequest->alasan_cap }}
                            </td>
                        </tr>
                        @endif
                        @if($leaveRequest->kelahiran_ke)
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Kelahiran Anak Ke-</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1.25rem;">{{ $leaveRequest->kelahiran_ke }}</td>
                        </tr>
                        @endif
                        @if($leaveRequest->alamat_cuti)
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Alamat Selama Cuti</td>
                            <td style="padding: 0.75rem 1.25rem;">{{ $leaveRequest->alamat_cuti }}</td>
                        </tr>
                        @endif
                        @if($leaveRequest->telepon_cuti)
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Telepon Selama Cuti</td>
                            <td style="padding: 0.75rem 1.25rem;">{{ $leaveRequest->telepon_cuti }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Dokumen</td>
                            <td style="padding: 0.75rem 1.25rem;">
                                <a href="{{ route('document.list', $leaveRequest) }}" class="btn btn-sm sh-btn-primary">
                                    <i class="ti ti-file-download me-1"></i>
                                    @if($leaveRequest->dokumen_pendukung)
                                        Lihat Dokumen (1)
                                    @else
                                        Kelola Dokumen
                                    @endif
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Perubahan</td>
                            <td style="padding: 0.75rem 1.25rem;">
                                @if(auth()->id() === $leaveRequest->user_id && in_array($leaveRequest->status, ['diajukan', 'pertimbangan_atasan']))
                                    <a href="{{ route('amendment.create', $leaveRequest) }}" class="btn btn-sm btn-warning">
                                        <i class="ti ti-edit me-1"></i> Ajukan Perubahan
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif

                                @if($leaveRequest->amendments()->where('status', 'pending')->exists())
                                    <div class="mt-2">
                                        @foreach($leaveRequest->amendments()->where('status', 'pending')->get() as $amendment)
                                            <a href="{{ route('amendment.show', $amendment) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="ti ti-clock me-1"></i> Perubahan Pending
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1.25rem;">Banding</td>
                            <td style="padding: 0.75rem 1.25rem;">
                                @if(auth()->id() === $leaveRequest->user_id && $leaveRequest->isRejected())
                                    <a href="{{ route('appeal.create', $leaveRequest) }}" class="btn btn-sm btn-danger">
                                        <i class="ti ti-alert-triangle me-1"></i> Ajukan Banding
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif

                                @if($leaveRequest->appeals()->where('status', 'pending')->exists())
                                    <div class="mt-2">
                                        @foreach($leaveRequest->appeals()->where('status', 'pending')->get() as $appeal)
                                            <a href="{{ route('appeal.show', $appeal) }}" class="btn btn-sm btn-outline-danger">
                                                <i class="ti ti-clock me-1"></i> Banding Pending
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- #24 Enhanced Approval Timeline --}}
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-git-merge me-2" style="color: #7c3aed;"></i>
                    Alur Persetujuan
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="sh-timeline-v2">
                    {{-- Step 1: Pengajuan --}}
                    <div class="sh-tl-item sh-tl-done">
                        <div class="sh-tl-icon" style="background: var(--sh-success-light); color: var(--sh-success);">
                            <i class="ti ti-send"></i>
                        </div>
                        <div class="sh-tl-card">
                            <div class="sh-tl-header">
                                <div>
                                    <div class="sh-tl-title">Pengajuan Dikirim</div>
                                    <div class="sh-tl-meta">
                                        <i class="ti ti-user me-1"></i>{{ $leaveRequest->user->name }}
                                        &bull; {{ $leaveRequest->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                                <span class="sh-badge sh-badge-approved">Selesai</span>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Pertimbangan Atasan --}}
                    @php $isDirectToKetua = $leaveRequest->user->skipAtasanReview(); @endphp
                    @if($leaveRequest->atasanReviewer)
                    @php
                        $atasanColor = match($leaveRequest->pertimbangan_atasan) {
                            'setuju' => 'var(--sh-success)', 'tolak' => 'var(--sh-danger)',
                            'tangguhkan' => 'var(--sh-warning)', 'ubah' => 'var(--sh-primary)',
                            default => '#94a3b8',
                        };
                        $atasanBgColor = match($leaveRequest->pertimbangan_atasan) {
                            'setuju' => 'var(--sh-success-light)', 'tolak' => 'var(--sh-danger-light)',
                            'tangguhkan' => 'var(--sh-warning-light)', 'ubah' => 'var(--sh-primary-light)',
                            default => 'var(--sh-gray-100)',
                        };
                        $atasanLabel = match($leaveRequest->pertimbangan_atasan) {
                            'setuju' => 'Disetujui', 'tolak' => 'Tidak Disetujui',
                            'tangguhkan' => 'Ditangguhkan', 'ubah' => 'Diubah',
                            default => $leaveRequest->pertimbangan_atasan ?? 'Diproses',
                        };
                        $atasanIcon = match($leaveRequest->pertimbangan_atasan) {
                            'setuju' => 'ti-circle-check', 'tolak' => 'ti-circle-x',
                            'tangguhkan' => 'ti-clock-pause', default => 'ti-user-check',
                        };
                    @endphp
                    <div class="sh-tl-item sh-tl-done">
                        <div class="sh-tl-icon" style="background: {{ $atasanBgColor }}; color: {{ $atasanColor }};">
                            <i class="ti {{ $atasanIcon }}"></i>
                        </div>
                        <div class="sh-tl-card">
                            <div class="sh-tl-header">
                                <div>
                                    <div class="sh-tl-title">Pertimbangan Atasan Langsung</div>
                                    <div class="sh-tl-meta">
                                        <i class="ti ti-user-check me-1"></i>{{ $leaveRequest->atasanReviewer->name }}
                                        @if($leaveRequest->reviewed_at)
                                        &bull; {{ $leaveRequest->reviewed_at->format('d M Y, H:i') }}
                                        @endif
                                    </div>
                                </div>
                                <span class="sh-badge" style="background:{{ $atasanBgColor }};color:{{ $atasanColor }};">{{ $atasanLabel }}</span>
                            </div>
                            {{-- #25 Speech bubble notes --}}
                            @if($leaveRequest->catatan_atasan)
                            <div class="sh-speech-bubble mt-2">
                                <i class="ti ti-message-circle me-1" style="color: var(--sh-primary);"></i>
                                {{ $leaveRequest->catatan_atasan }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @elseif($isDirectToKetua)
                    <div class="sh-tl-item sh-tl-done">
                        <div class="sh-tl-icon" style="background: var(--sh-primary-light); color: var(--sh-primary);">
                            <i class="ti ti-arrow-right"></i>
                        </div>
                        <div class="sh-tl-card">
                            <div class="sh-tl-header">
                                <div>
                                    <div class="sh-tl-title">Langsung ke Pejabat Berwenang</div>
                                    <div class="sh-tl-meta">Tanpa pertimbangan atasan &mdash; {{ $leaveRequest->user->jabatan }}</div>
                                </div>
                                <span class="sh-badge sh-badge-approved">Otomatis</span>
                            </div>
                        </div>
                    </div>
                    @else
                    @php $isWaitingAtasan = $leaveRequest->needsAtasanReview(); @endphp
                    <div class="sh-tl-item {{ $isWaitingAtasan ? 'sh-tl-active' : 'sh-tl-pending' }}">
                        <div class="sh-tl-icon" style="background: {{ $isWaitingAtasan ? 'var(--sh-warning-light)' : 'var(--sh-gray-100)' }}; color: {{ $isWaitingAtasan ? 'var(--sh-warning)' : '#94a3b8' }};">
                            <i class="ti {{ $isWaitingAtasan ? 'ti-clock-hour-4' : 'ti-user-check' }}"></i>
                        </div>
                        <div class="sh-tl-card">
                            <div class="sh-tl-header">
                                <div>
                                    <div class="sh-tl-title">Pertimbangan Atasan Langsung</div>
                                    <div class="sh-tl-meta">{{ $isWaitingAtasan ? 'Menunggu pertimbangan atasan...' : 'Belum diproses' }}</div>
                                </div>
                                @if($isWaitingAtasan)
                                <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> Menunggu</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Step 3: Keputusan Pejabat --}}
                    @if($leaveRequest->pejabat)
                    @php
                        $pejabatColor = match($leaveRequest->keputusan_pejabat) {
                            'setuju' => 'var(--sh-success)', 'tolak' => 'var(--sh-danger)',
                            'tangguhkan' => 'var(--sh-warning)', 'ubah' => 'var(--sh-primary)',
                            default => '#94a3b8',
                        };
                        $pejabatBgColor = match($leaveRequest->keputusan_pejabat) {
                            'setuju' => 'var(--sh-success-light)', 'tolak' => 'var(--sh-danger-light)',
                            'tangguhkan' => 'var(--sh-warning-light)', default => 'var(--sh-primary-light)',
                        };
                        $pejabatLabel = match($leaveRequest->keputusan_pejabat) {
                            'setuju' => 'Disetujui', 'tolak' => 'Tidak Disetujui',
                            'tangguhkan' => 'Ditangguhkan', 'ubah' => 'Diubah',
                            default => $leaveRequest->keputusan_pejabat ?? 'Diproses',
                        };
                        $pejabatIcon = match($leaveRequest->keputusan_pejabat) {
                            'setuju' => 'ti-circle-check', 'tolak' => 'ti-circle-x',
                            'tangguhkan' => 'ti-clock-pause', default => 'ti-gavel',
                        };
                    @endphp
                    <div class="sh-tl-item sh-tl-done">
                        <div class="sh-tl-icon" style="background: {{ $pejabatBgColor }}; color: {{ $pejabatColor }};">
                            <i class="ti {{ $pejabatIcon }}"></i>
                        </div>
                        <div class="sh-tl-card">
                            <div class="sh-tl-header">
                                <div>
                                    <div class="sh-tl-title">Keputusan Pejabat Berwenang</div>
                                    <div class="sh-tl-meta">
                                        <i class="ti ti-gavel me-1"></i>{{ $leaveRequest->pejabat->name }}
                                        @if($leaveRequest->decided_at)
                                        &bull; {{ $leaveRequest->decided_at->format('d M Y, H:i') }}
                                        @endif
                                    </div>
                                </div>
                                <span class="sh-badge" style="background:{{ $pejabatBgColor }};color:{{ $pejabatColor }};">{{ $pejabatLabel }}</span>
                            </div>
                            {{-- #25 Speech bubble notes --}}
                            @if($leaveRequest->catatan_pejabat)
                            <div class="sh-speech-bubble mt-2">
                                <i class="ti ti-message-circle me-1" style="color: #7c3aed;"></i>
                                {{ $leaveRequest->catatan_pejabat }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    @php $isWaitingPejabat = $leaveRequest->needsPejabatDecision(); @endphp
                    <div class="sh-tl-item {{ $isWaitingPejabat ? 'sh-tl-active' : 'sh-tl-pending' }}">
                        <div class="sh-tl-icon" style="background: {{ $isWaitingPejabat ? 'var(--sh-warning-light)' : 'var(--sh-gray-100)' }}; color: {{ $isWaitingPejabat ? 'var(--sh-warning)' : '#94a3b8' }};">
                            <i class="ti {{ $isWaitingPejabat ? 'ti-clock-hour-4' : 'ti-gavel' }}"></i>
                        </div>
                        <div class="sh-tl-card">
                            <div class="sh-tl-header">
                                <div>
                                    <div class="sh-tl-title">Keputusan Pejabat Berwenang</div>
                                    <div class="sh-tl-meta">{{ $isWaitingPejabat ? 'Menunggu keputusan pejabat berwenang...' : 'Belum diproses' }}</div>
                                </div>
                                @if($isWaitingPejabat)
                                <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> Menunggu</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- #28 Print Preview Modal --}}
<div class="modal fade" id="printPreviewModal" tabindex="-1" aria-labelledby="printPreviewModalLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header" style="background: var(--sh-primary); color: #fff; border: none;">
                <h5 class="modal-title" id="printPreviewModalLabel">
                    <i class="ti ti-printer me-2"></i> Preview Dokumen Cuti
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="min-height: 60vh;">
                <div class="p-3 border-bottom d-flex gap-2 flex-wrap">
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadPreview('surat')" id="prevBtnSurat">
                        <i class="ti ti-file-text me-1"></i> Surat Permohonan
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadPreview('form')">
                        <i class="ti ti-clipboard-text me-1"></i> Form Cuti
                    </button>
                </div>
                <iframe id="previewFrame"
                    src="{{ route('leave.surat-permohonan', $leaveRequest) }}"
                    style="width:100%; height:65vh; border:none;"
                    title="Preview dokumen cuti">
                </iframe>
            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="{{ route('leave.surat-permohonan', $leaveRequest) }}" target="_blank" class="btn sh-btn-primary">
                    <i class="ti ti-download me-1"></i> Download
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    /* #24 Enhanced Timeline V2 */
    .sh-timeline-v2 { display: flex; flex-direction: column; gap: 0; }
    .sh-tl-item {
        display: flex;
        gap: 1rem;
        position: relative;
        padding-bottom: 1.5rem;
    }
    .sh-tl-item:last-child { padding-bottom: 0; }
    .sh-tl-item::before {
        content: '';
        position: absolute;
        left: 19px;
        top: 42px;
        bottom: 0;
        width: 2px;
        background: var(--sh-border);
    }
    .sh-tl-item:last-child::before { display: none; }
    .sh-tl-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
        border: 2px solid var(--sh-card-bg);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .sh-tl-card {
        flex: 1;
        background: var(--sh-gray-50);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        border: 1px solid var(--sh-border);
        min-width: 0;
    }
    .sh-tl-item.sh-tl-active .sh-tl-card {
        border-color: var(--sh-warning);
        background: var(--sh-warning-light);
    }
    .sh-tl-item.sh-tl-pending .sh-tl-card {
        opacity: 0.65;
    }
    .sh-tl-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .sh-tl-title {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--sh-text);
    }
    .sh-tl-meta {
        font-size: 0.78rem;
        color: var(--sh-text-muted);
        margin-top: 0.2rem;
    }

    /* #25 Speech Bubble Notes */
    .sh-speech-bubble {
        position: relative;
        background: var(--sh-card-bg);
        border: 1px solid var(--sh-border);
        border-radius: 0 12px 12px 12px;
        padding: 0.5rem 0.75rem;
        font-size: 0.82rem;
        color: var(--sh-text);
        margin-top: 0.5rem;
        font-style: italic;
    }
    .sh-speech-bubble::before {
        content: '';
        position: absolute;
        top: -8px;
        left: 12px;
        border: 4px solid transparent;
        border-bottom-color: var(--sh-border);
    }
    .sh-speech-bubble::after {
        content: '';
        position: absolute;
        top: -6px;
        left: 13px;
        border: 3px solid transparent;
        border-bottom-color: var(--sh-card-bg);
    }
    [data-bs-theme="dark"] .sh-tl-card {
        background: var(--sh-gray-100);
    }
    [data-bs-theme="dark"] .sh-tl-item.sh-tl-active .sh-tl-card {
        background: rgba(217, 119, 6, 0.15);
    }
</style>
<script>
// #28 Print Preview
function loadPreview(type) {
    var frame = document.getElementById('previewFrame');
    @php
        $suratUrl = route('leave.surat-permohonan', $leaveRequest);
        $formUrl = route('leave.form-cuti', $leaveRequest);
    @endphp
    if (type === 'surat') {
        frame.src = @json($suratUrl);
    } else {
        frame.src = @json($formUrl);
    }
}
</script>
@endpush
@endsection
