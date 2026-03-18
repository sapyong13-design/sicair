@extends('layouts.app')

@section('title', 'Detail Cuti — SiCAIR')

@section('content')
{{-- Breadcrumb (#10) --}}
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Detail Cuti</span>
</nav>

{{-- Page Header --}}
<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;" aria-label="Kembali ke dashboard">
                <i class="ti ti-arrow-left" style="font-size: 1.2rem;" aria-hidden="true"></i>
            </a>
            <div>
                <h2 class="sc-page-title mb-0">Detail Pengajuan Cuti</h2>
                <div class="text-muted" style="font-size: 0.85rem;">
                    {{ $leaveRequest->type_label }} &mdash; {{ $leaveRequest->user->name }}
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            {{-- #28 Print Preview --}}
            <button type="button" class="btn btn-outline-secondary"
               style="border-radius: 10px; font-size: 0.85rem;"
               data-bs-toggle="modal" data-bs-target="#printPreviewModal"
               title="Preview sebelum cetak">
                <i class="ti ti-printer me-1"></i>
                <span class="d-none d-sm-inline">Preview</span>
            </button>
            <a href="{{ route('leave.surat-permohonan', $leaveRequest) }}"
               class="btn sc-btn-primary sc-export-trigger"
               style="border-radius: 10px; font-size: 0.85rem;"
               title="Download Surat Permohonan Cuti (PDF)"
               target="_blank">
                <i class="ti ti-file-text me-1"></i>
                <span class="d-none d-sm-inline">Surat Permohonan</span>
                <span class="d-sm-none">PDF</span>
            </a>
            <a href="{{ route('leave.surat-permohonan-docx', $leaveRequest) }}"
               class="btn sc-btn-primary sc-export-trigger"
               style="border-radius: 10px; font-size: 0.85rem;"
               title="Download Surat Permohonan Cuti (DOCX Folio)"
               target="_blank">
                <i class="ti ti-file-word me-1"></i>
                DOCX
            </a>
            <a href="{{ route('leave.form-cuti', $leaveRequest) }}"
               class="btn sc-btn-primary sc-export-trigger"
               style="border-radius: 10px; font-size: 0.85rem;"
               title="Download Form Permintaan dan Pemberian Cuti (PDF)"
               target="_blank">
                <i class="ti ti-clipboard-text me-1"></i>
                <span class="d-none d-sm-inline">Form Cuti</span>
                <span class="d-sm-none">Form</span>
            </a>
            {{-- #21 Re-apply button (for rejected or diubah) --}}
            @if(auth()->id() === $leaveRequest->user_id && ($leaveRequest->isRejected() || $leaveRequest->status === 'diubah'))
            <a href="{{ route('leave.reapply', $leaveRequest) }}"
               class="btn btn-outline-warning"
               style="border-radius: 10px; font-size: 0.85rem;"
               title="Ajukan ulang dengan data yang sama">
                <i class="ti ti-refresh me-1"></i>
                Ajukan Ulang
            </a>
            @endif
            {{-- Share button --}}
            <button id="sc-share-btn" class="btn btn-outline-secondary btn-sm" style="border-radius: 10px;" title="Bagikan status cuti">
                <i class="ti ti-share me-1"></i> Bagikan
            </button>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert mb-4" style="background: var(--sc-success-light); color: var(--sc-success); border-radius: 12px; border: none;">
    <i class="ti ti-circle-check me-2"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert mb-4" style="background: var(--sc-danger-light); color: var(--sc-danger); border-radius: 12px; border: none;">
    <i class="ti ti-alert-circle me-2"></i> {{ session('error') }}
</div>
@endif

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        {{-- Status Banner --}}
        @php
            $statusConfig = match(true) {
                $leaveRequest->isApproved() => ['bg' => 'var(--sc-success-light)', 'color' => 'var(--sc-success)', 'icon' => 'ti-circle-check', 'text' => 'Disetujui'],
                $leaveRequest->isRejected() => ['bg' => 'var(--sc-danger-light)', 'color' => 'var(--sc-danger)', 'icon' => 'ti-circle-x', 'text' => 'Ditolak'],
                $leaveRequest->status === 'ditangguhkan' => ['bg' => 'var(--sc-warning-light)', 'color' => 'var(--sc-warning)', 'icon' => 'ti-clock-pause', 'text' => 'Ditangguhkan'],
                $leaveRequest->status === 'diubah' => ['bg' => 'var(--sc-primary-light)', 'color' => 'var(--sc-primary)', 'icon' => 'ti-edit', 'text' => 'Diubah'],
                $leaveRequest->status === 'pertimbangan_atasan' => ['bg' => '#fef3c7', 'color' => '#d97706', 'icon' => 'ti-user-check', 'text' => 'Menunggu Pertimbangan Atasan'],
                default => ['bg' => '#e0f2fe', 'color' => '#0284c7', 'icon' => 'ti-clock-hour-4', 'text' => 'Diajukan — Menunggu Pertimbangan Atasan'],
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

        {{-- Rejection Reason Banner --}}
        @if($leaveRequest->isRejected() && $leaveRequest->admin_note)
        @php
            $alasanLabels = [
                'tanggal_konflik'   => 'Konflik tanggal dengan cuti lain',
                'kuota_habis'       => 'Kuota cuti habis',
                'alasan_tidak_jelas' => 'Alasan tidak jelas',
                'dokumen_kurang'    => 'Dokumen pendukung kurang',
                'lainnya'           => 'Alasan lain',
            ];
            preg_match('/^\[ALASAN: ([^\]]+)\]\s*(.*)$/s', $leaveRequest->admin_note, $matches);
            $alasanCode  = $matches[1] ?? null;
            $alasanLabel = $alasanCode ? ($alasanLabels[$alasanCode] ?? $alasanCode) : null;
            $catatanBebas = $matches[2] ?? $leaveRequest->admin_note;
        @endphp
        <div class="mb-4" style="background: var(--sc-danger-light); border-radius: 14px; padding: 1rem 1.25rem; border: 1px solid var(--sc-danger);">
            <div class="d-flex align-items-start gap-2 mb-1">
                <i class="ti ti-alert-triangle" style="color: var(--sc-danger); font-size: 1.1rem; margin-top: 2px;"></i>
                <div>
                    <div class="fw-bold" style="color: var(--sc-danger); font-size: 0.9rem;">Alasan Penolakan</div>
                    @if($alasanLabel)
                    <div style="font-size: 0.85rem; color: var(--sc-danger); margin-top: 0.2rem;">
                        <span class="fw-semibold">Kategori:</span> {{ $alasanLabel }}
                    </div>
                    @endif
                    @if($catatanBebas)
                    <div style="font-size: 0.85rem; color: #475569; margin-top: 0.25rem;">{{ $catatanBebas }}</div>
                    @endif
                </div>
            </div>
            @if(auth()->id() === $leaveRequest->user_id)
            <div style="font-size: 0.8rem; color: var(--sc-danger); margin-top: 0.5rem;">
                <i class="ti ti-info-circle me-1"></i>
                Klik <strong>Ajukan Ulang</strong> di atas untuk mengajukan kembali dengan perbaikan.
            </div>
            @endif
        </div>
        @endif

        {{-- Main Info Card --}}
        <div class="card sc-card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-file-text me-2" style="color: var(--sc-primary);"></i>
                    Informasi Pengajuan
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
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
                                <span style="color: var(--sc-primary);">{{ $leaveRequest->type_label }}</span>
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
                                <a href="{{ route('document.list', $leaveRequest) }}" class="btn btn-sm sc-btn-primary">
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
        </div>

        {{-- #24 Enhanced Approval Timeline --}}
        <div class="card sc-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-git-merge me-2" style="color: #7c3aed;"></i>
                    Alur Persetujuan
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="sc-timeline-v2">
                    {{-- Step 1: Pengajuan --}}
                    <div class="sc-tl-item sc-tl-done">
                        <div class="sc-tl-icon" style="background: var(--sc-success-light); color: var(--sc-success);">
                            <i class="ti ti-send"></i>
                        </div>
                        <div class="sc-tl-card">
                            <div class="sc-tl-header">
                                <div>
                                    <div class="sc-tl-title">Pengajuan Dikirim</div>
                                    <div class="sc-tl-meta">
                                        <i class="ti ti-user me-1"></i>{{ $leaveRequest->user->name }}
                                        &bull; {{ $leaveRequest->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                                <span class="sc-badge sc-badge-approved">Selesai</span>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Pertimbangan Atasan --}}
                    @php $isDirectToKetua = $leaveRequest->user->skipAtasanReview(); @endphp
                    @if($leaveRequest->atasanReviewer)
                    @php
                        $atasanColor = match($leaveRequest->pertimbangan_atasan) {
                            'setuju' => 'var(--sc-success)', 'tolak' => 'var(--sc-danger)',
                            'tangguhkan' => 'var(--sc-warning)', 'ubah' => 'var(--sc-primary)',
                            default => '#94a3b8',
                        };
                        $atasanBgColor = match($leaveRequest->pertimbangan_atasan) {
                            'setuju' => 'var(--sc-success-light)', 'tolak' => 'var(--sc-danger-light)',
                            'tangguhkan' => 'var(--sc-warning-light)', 'ubah' => 'var(--sc-primary-light)',
                            default => 'var(--sc-gray-100)',
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
                    <div class="sc-tl-item sc-tl-done">
                        <div class="sc-tl-icon" style="background: {{ $atasanBgColor }}; color: {{ $atasanColor }};">
                            <i class="ti {{ $atasanIcon }}"></i>
                        </div>
                        <div class="sc-tl-card">
                            <div class="sc-tl-header">
                                <div>
                                    <div class="sc-tl-title">Pertimbangan Atasan Langsung</div>
                                    <div class="sc-tl-meta">
                                        <i class="ti ti-user-check me-1"></i>{{ $leaveRequest->atasanReviewer->name }}
                                        @if($leaveRequest->reviewed_at)
                                        &bull; {{ $leaveRequest->reviewed_at->format('d M Y, H:i') }}
                                        @endif
                                    </div>
                                </div>
                                <span class="sc-badge" style="background:{{ $atasanBgColor }};color:{{ $atasanColor }};">{{ $atasanLabel }}</span>
                            </div>
                            {{-- #25 Speech bubble notes --}}
                            @if($leaveRequest->catatan_atasan)
                            <div class="sc-speech-bubble mt-2">
                                <i class="ti ti-message-circle me-1" style="color: var(--sc-primary);"></i>
                                {{ $leaveRequest->catatan_atasan }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @elseif($isDirectToKetua)
                    <div class="sc-tl-item sc-tl-done">
                        <div class="sc-tl-icon" style="background: var(--sc-primary-light); color: var(--sc-primary);">
                            <i class="ti ti-arrow-right"></i>
                        </div>
                        <div class="sc-tl-card">
                            <div class="sc-tl-header">
                                <div>
                                    <div class="sc-tl-title">Langsung ke Pejabat Berwenang</div>
                                    <div class="sc-tl-meta">Tanpa pertimbangan atasan &mdash; {{ $leaveRequest->user->jabatan }}</div>
                                </div>
                                <span class="sc-badge sc-badge-approved">Otomatis</span>
                            </div>
                        </div>
                    </div>
                    @else
                    @php $isWaitingAtasan = $leaveRequest->needsAtasanReview(); @endphp
                    <div class="sc-tl-item {{ $isWaitingAtasan ? 'sc-tl-active' : 'sc-tl-pending' }}">
                        <div class="sc-tl-icon" style="background: {{ $isWaitingAtasan ? 'var(--sc-warning-light)' : 'var(--sc-gray-100)' }}; color: {{ $isWaitingAtasan ? 'var(--sc-warning)' : '#94a3b8' }};">
                            <i class="ti {{ $isWaitingAtasan ? 'ti-clock-hour-4' : 'ti-user-check' }}"></i>
                        </div>
                        <div class="sc-tl-card">
                            <div class="sc-tl-header">
                                <div>
                                    <div class="sc-tl-title">Pertimbangan Atasan Langsung</div>
                                    <div class="sc-tl-meta">{{ $isWaitingAtasan ? 'Menunggu pertimbangan atasan...' : 'Belum diproses' }}</div>
                                </div>
                                @if($isWaitingAtasan)
                                <span class="sc-badge sc-badge-pending"><i class="ti ti-clock"></i> Menunggu</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Step 3: Keputusan Pejabat --}}
                    @if($leaveRequest->pejabat)
                    @php
                        $pejabatColor = match($leaveRequest->keputusan_pejabat) {
                            'setuju' => 'var(--sc-success)', 'tolak' => 'var(--sc-danger)',
                            'tangguhkan' => 'var(--sc-warning)', 'ubah' => 'var(--sc-primary)',
                            default => '#94a3b8',
                        };
                        $pejabatBgColor = match($leaveRequest->keputusan_pejabat) {
                            'setuju' => 'var(--sc-success-light)', 'tolak' => 'var(--sc-danger-light)',
                            'tangguhkan' => 'var(--sc-warning-light)', default => 'var(--sc-primary-light)',
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
                    <div class="sc-tl-item sc-tl-done">
                        <div class="sc-tl-icon" style="background: {{ $pejabatBgColor }}; color: {{ $pejabatColor }};">
                            <i class="ti {{ $pejabatIcon }}"></i>
                        </div>
                        <div class="sc-tl-card">
                            <div class="sc-tl-header">
                                <div>
                                    <div class="sc-tl-title">Keputusan Pejabat Berwenang</div>
                                    <div class="sc-tl-meta">
                                        <i class="ti ti-gavel me-1"></i>{{ $leaveRequest->pejabat->name }}
                                        @if($leaveRequest->decided_at)
                                        &bull; {{ $leaveRequest->decided_at->format('d M Y, H:i') }}
                                        @endif
                                    </div>
                                </div>
                                <span class="sc-badge" style="background:{{ $pejabatBgColor }};color:{{ $pejabatColor }};">{{ $pejabatLabel }}</span>
                            </div>
                            {{-- #25 Speech bubble notes --}}
                            @if($leaveRequest->catatan_pejabat)
                            <div class="sc-speech-bubble mt-2">
                                <i class="ti ti-message-circle me-1" style="color: #7c3aed;"></i>
                                {{ $leaveRequest->catatan_pejabat }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    @php $isWaitingPejabat = $leaveRequest->needsPejabatDecision(); @endphp
                    <div class="sc-tl-item {{ $isWaitingPejabat ? 'sc-tl-active' : 'sc-tl-pending' }}">
                        <div class="sc-tl-icon" style="background: {{ $isWaitingPejabat ? 'var(--sc-warning-light)' : 'var(--sc-gray-100)' }}; color: {{ $isWaitingPejabat ? 'var(--sc-warning)' : '#94a3b8' }};">
                            <i class="ti {{ $isWaitingPejabat ? 'ti-clock-hour-4' : 'ti-gavel' }}"></i>
                        </div>
                        <div class="sc-tl-card">
                            <div class="sc-tl-header">
                                <div>
                                    <div class="sc-tl-title">Keputusan Pejabat Berwenang</div>
                                    <div class="sc-tl-meta">{{ $isWaitingPejabat ? 'Menunggu keputusan pejabat berwenang...' : 'Belum diproses' }}</div>
                                </div>
                                @if($isWaitingPejabat)
                                <span class="sc-badge sc-badge-pending"><i class="ti ti-clock"></i> Menunggu</span>
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
            <div class="modal-header" style="background: var(--sc-primary); color: #fff; border: none;">
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
                <div id="previewLoading" class="d-flex align-items-center justify-content-center" style="height:65vh; display:none!important;">
                    <div class="text-center text-muted">
                        <div class="spinner-border mb-2" role="status" style="color: var(--sc-primary);"></div>
                        <div>Memuat pratinjau...</div>
                    </div>
                </div>
                <div id="previewDocxMsg" class="d-flex align-items-center justify-content-center d-none" style="height:65vh;">
                    <div class="text-center text-muted">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">📄</div>
                        <div class="fw-semibold mb-1">Format DOCX tidak dapat dipratinjau</div>
                        <div style="font-size: 0.85rem;">Klik <strong>Download</strong> untuk mengunduh file.</div>
                    </div>
                </div>
                <iframe id="previewFrame"
                    src="about:blank"
                    style="width:100%; height:65vh; border:none;"
                    title="Preview dokumen cuti">
                </iframe>
            </div>
            <div class="modal-footer border-0 pt-0">
                <a id="previewDownloadBtn" href="{{ route('leave.surat-permohonan', $leaveRequest) }}" target="_blank" class="btn sc-btn-primary">
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
    .sc-timeline-v2 { display: flex; flex-direction: column; gap: 0; }
    .sc-tl-item {
        display: flex;
        gap: 1rem;
        position: relative;
        padding-bottom: 1.5rem;
    }
    .sc-tl-item:last-child { padding-bottom: 0; }
    .sc-tl-item::before {
        content: '';
        position: absolute;
        left: 19px;
        top: 42px;
        bottom: 0;
        width: 2px;
        background: var(--sc-border);
    }
    .sc-tl-item:last-child::before { display: none; }
    .sc-tl-icon {
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
        border: 2px solid var(--sc-card-bg);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .sc-tl-card {
        flex: 1;
        background: var(--sc-gray-50);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        border: 1px solid var(--sc-border);
        min-width: 0;
    }
    .sc-tl-item.sc-tl-active .sc-tl-card {
        border-color: var(--sc-warning);
        background: var(--sc-warning-light);
    }
    .sc-tl-item.sc-tl-pending .sc-tl-card {
        opacity: 0.65;
    }
    .sc-tl-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .sc-tl-title {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--sc-text);
    }
    .sc-tl-meta {
        font-size: 0.78rem;
        color: var(--sc-text-muted);
        margin-top: 0.2rem;
    }

    /* #25 Speech Bubble Notes */
    .sc-speech-bubble {
        position: relative;
        background: var(--sc-card-bg);
        border: 1px solid var(--sc-border);
        border-radius: 0 12px 12px 12px;
        padding: 0.5rem 0.75rem;
        font-size: 0.82rem;
        color: var(--sc-text);
        margin-top: 0.5rem;
        font-style: italic;
    }
    .sc-speech-bubble::before {
        content: '';
        position: absolute;
        top: -8px;
        left: 12px;
        border: 4px solid transparent;
        border-bottom-color: var(--sc-border);
    }
    .sc-speech-bubble::after {
        content: '';
        position: absolute;
        top: -6px;
        left: 13px;
        border: 3px solid transparent;
        border-bottom-color: var(--sc-card-bg);
    }
    [data-bs-theme="dark"] .sc-tl-card {
        background: var(--sc-gray-100);
    }
    [data-bs-theme="dark"] .sc-tl-item.sc-tl-active .sc-tl-card {
        background: rgba(217, 119, 6, 0.15);
    }
</style>
<script>
// #28 Print Preview
@php
    $suratUrl = route('leave.surat-permohonan', $leaveRequest);
    $formUrl  = route('leave.form-cuti', $leaveRequest);
@endphp
const previewUrls = {
    surat: @json($suratUrl),
    form:  @json($formUrl),
};
let currentPreviewType = 'surat';

// Lazy load: set iframe src only when modal opens
document.getElementById('printPreviewModal').addEventListener('show.bs.modal', function () {
    loadPreview('surat');
});

// Clear iframe when modal closes to stop loading
document.getElementById('printPreviewModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('previewFrame').src = 'about:blank';
    currentPreviewType = 'surat';
});

function loadPreview(type) {
    currentPreviewType = type;
    const frame  = document.getElementById('previewFrame');
    const dlBtn  = document.getElementById('previewDownloadBtn');
    const docxMsg = document.getElementById('previewDocxMsg');

    if (type === 'surat') {
        // PDF — can be shown inline in iframe
        frame.classList.remove('d-none');
        docxMsg.classList.add('d-none');
        frame.src = previewUrls.surat;
        dlBtn.href = previewUrls.surat;
        dlBtn.style.display = '';
    } else {
        // DOCX — cannot iframe; show message, update download link
        frame.classList.add('d-none');
        frame.src = 'about:blank';
        docxMsg.classList.remove('d-none');
        dlBtn.href = previewUrls.form;
        dlBtn.style.display = '';
    }
}

// Share button
var shareBtn = document.getElementById('sc-share-btn');
if (shareBtn) {
    var shareData = {
        title: 'Status Cuti — SiCAIR',
        text: '{{ "Pengajuan cuti: " . ($leaveRequest->type_label ?? "") . " | Status: " . ucfirst($leaveRequest->status ?? "") . " | " . ($leaveRequest->start_date ? $leaveRequest->start_date->format("d M Y") : "") . " s/d " . ($leaveRequest->end_date ? $leaveRequest->end_date->format("d M Y") : "") }}',
        url: window.location.href
    };
    if (navigator.share) {
        shareBtn.addEventListener('click', function() {
            navigator.share(shareData).catch(function() {});
        });
    } else {
        shareBtn.addEventListener('click', function() {
            var text = shareData.text + ' — ' + shareData.url;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    if (typeof showToast === 'function') showToast('Link berhasil disalin!', 'success');
                }).catch(function() {});
            }
        });
    }
}
</script>
@endpush
@endsection
