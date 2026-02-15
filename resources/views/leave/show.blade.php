@extends('layouts.app')

@section('title', 'Detail Cuti - SiHEALING')

@section('content')
{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;">
            <i class="ti ti-arrow-left" style="font-size: 1.2rem;"></i>
        </a>
        <div>
            <h2 class="sh-page-title mb-0">Detail Pengajuan Cuti</h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                {{ $leaveRequest->type_label }} &mdash; {{ $leaveRequest->user->name }}
            </div>
        </div>
    </div>
</div>

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
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Approval Timeline --}}
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-git-merge me-2" style="color: #7c3aed;"></i>
                    Alur Persetujuan
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="sh-timeline">
                    {{-- Step 1: Pengajuan --}}
                    <div class="sh-timeline-item">
                        <div class="sh-timeline-dot" style="background: var(--sh-success);"></div>
                        <div class="sh-timeline-content">
                            <div class="fw-bold" style="font-size: 0.9rem;">Pengajuan Diajukan</div>
                            <div class="text-muted" style="font-size: 0.8rem;">
                                {{ $leaveRequest->created_at->format('d M Y, H:i') }} &mdash; {{ $leaveRequest->user->name }}
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Pertimbangan Atasan --}}
                    @if($leaveRequest->atasanReviewer)
                    <div class="sh-timeline-item">
                        @php
                            $atasanColor = match($leaveRequest->pertimbangan_atasan) {
                                'setuju' => 'var(--sh-success)',
                                'tolak' => 'var(--sh-danger)',
                                'tangguhkan' => 'var(--sh-warning)',
                                'ubah' => 'var(--sh-primary)',
                                default => '#94a3b8',
                            };
                            $atasanLabel = match($leaveRequest->pertimbangan_atasan) {
                                'setuju' => 'Disetujui',
                                'tolak' => 'Tidak Disetujui',
                                'tangguhkan' => 'Ditangguhkan',
                                'ubah' => 'Perubahan',
                                default => $leaveRequest->pertimbangan_atasan,
                            };
                        @endphp
                        <div class="sh-timeline-dot" style="background: {{ $atasanColor }};"></div>
                        <div class="sh-timeline-content">
                            <div class="fw-bold" style="font-size: 0.9rem;">
                                Pertimbangan Atasan: <span style="color: {{ $atasanColor }};">{{ $atasanLabel }}</span>
                            </div>
                            <div class="text-muted" style="font-size: 0.8rem;">
                                {{ $leaveRequest->reviewed_at?->format('d M Y, H:i') }} &mdash; {{ $leaveRequest->atasanReviewer->name }}
                            </div>
                            @if($leaveRequest->catatan_atasan)
                            <div style="margin-top: 0.4rem; padding: 0.5rem 0.75rem; background: var(--sh-gray-50); border-radius: 8px; font-size: 0.82rem; color: #475569;">
                                <i class="ti ti-message me-1"></i> {{ $leaveRequest->catatan_atasan }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="sh-timeline-item">
                        <div class="sh-timeline-dot" style="background: {{ $leaveRequest->needsAtasanReview() ? '#94a3b8' : '#e2e8f0' }};"></div>
                        <div class="sh-timeline-content">
                            <div class="fw-semibold text-muted" style="font-size: 0.9rem;">
                                Pertimbangan Atasan Langsung
                            </div>
                            <div class="text-muted" style="font-size: 0.8rem;">
                                {{ $leaveRequest->needsAtasanReview() ? 'Menunggu pertimbangan...' : 'Belum diproses' }}
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Step 3: Keputusan Pejabat --}}
                    @if($leaveRequest->pejabat)
                    <div class="sh-timeline-item">
                        @php
                            $pejabatColor = match($leaveRequest->keputusan_pejabat) {
                                'setuju' => 'var(--sh-success)',
                                'tolak' => 'var(--sh-danger)',
                                'tangguhkan' => 'var(--sh-warning)',
                                'ubah' => 'var(--sh-primary)',
                                default => '#94a3b8',
                            };
                            $pejabatLabel = match($leaveRequest->keputusan_pejabat) {
                                'setuju' => 'Disetujui',
                                'tolak' => 'Tidak Disetujui',
                                'tangguhkan' => 'Ditangguhkan',
                                'ubah' => 'Perubahan',
                                default => $leaveRequest->keputusan_pejabat,
                            };
                        @endphp
                        <div class="sh-timeline-dot" style="background: {{ $pejabatColor }};"></div>
                        <div class="sh-timeline-content">
                            <div class="fw-bold" style="font-size: 0.9rem;">
                                Keputusan Pejabat: <span style="color: {{ $pejabatColor }};">{{ $pejabatLabel }}</span>
                            </div>
                            <div class="text-muted" style="font-size: 0.8rem;">
                                {{ $leaveRequest->decided_at?->format('d M Y, H:i') }} &mdash; {{ $leaveRequest->pejabat->name }}
                            </div>
                            @if($leaveRequest->catatan_pejabat)
                            <div style="margin-top: 0.4rem; padding: 0.5rem 0.75rem; background: var(--sh-gray-50); border-radius: 8px; font-size: 0.82rem; color: #475569;">
                                <i class="ti ti-message me-1"></i> {{ $leaveRequest->catatan_pejabat }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="sh-timeline-item">
                        <div class="sh-timeline-dot" style="background: {{ $leaveRequest->needsPejabatDecision() ? '#94a3b8' : '#e2e8f0' }};"></div>
                        <div class="sh-timeline-content">
                            <div class="fw-semibold text-muted" style="font-size: 0.9rem;">
                                Keputusan Pejabat Berwenang
                            </div>
                            <div class="text-muted" style="font-size: 0.8rem;">
                                {{ $leaveRequest->needsPejabatDecision() ? 'Menunggu keputusan...' : 'Belum diproses' }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .sh-timeline { position: relative; padding-left: 28px; }
    .sh-timeline-item { position: relative; padding-bottom: 1.5rem; }
    .sh-timeline-item:last-child { padding-bottom: 0; }
    .sh-timeline-item::before {
        content: '';
        position: absolute;
        left: -22px;
        top: 18px;
        bottom: -8px;
        width: 2px;
        background: #e2e8f0;
    }
    .sh-timeline-item:last-child::before { display: none; }
    .sh-timeline-dot {
        position: absolute;
        left: -28px;
        top: 4px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #e2e8f0;
    }
</style>
@endpush
@endsection
