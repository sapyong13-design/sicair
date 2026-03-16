{{-- Leave Status Timeline Component --}}
<div class="sc-timeline">
    <div class="timeline-container">
        {{-- Step 1: Diajukan --}}
        <div class="timeline-step {{ $leaveRequest->created_at ? 'completed' : '' }}">
            <div class="timeline-marker">
                <i class="ti ti-file-plus"></i>
            </div>
            <div class="timeline-content">
                <div class="timeline-title">Diajukan</div>
                <div class="timeline-date">{{ $leaveRequest->created_at?->format('d M Y, H:i') ?? '-' }}</div>
                <div class="timeline-description">Pengajuan cuti telah dibuat</div>
            </div>
        </div>

        {{-- Step 2: Pertimbangan Atasan --}}
        <div class="timeline-step {{ in_array($leaveRequest->status, ['pertimbangan_atasan', 'disetujui_atasan', 'ditolak_atasan', 'diubah', 'ditangguhkan', 'disetujui', 'ditolak']) ? 'completed' : 'pending' }}">
            <div class="timeline-marker">
                <i class="ti ti-user-check"></i>
            </div>
            <div class="timeline-content">
                <div class="timeline-title">Pertimbangan Atasan</div>
                @if($leaveRequest->atasanReviewer)
                    <div class="timeline-date">{{ $leaveRequest->pertimbangan_atasan ? $leaveRequest->updated_at->format('d M Y, H:i') : 'Menunggu...' }}</div>
                    <div class="timeline-description">
                        <strong>{{ $leaveRequest->atasanReviewer->name }}</strong>
                        @if($leaveRequest->pertimbangan_atasan)
                            <span class="timeline-status-badge {{ $leaveRequest->pertimbangan_atasan }}">
                                <i class="ti ti-{{ $leaveRequest->pertimbangan_atasan === 'setuju' ? 'circle-check' : ($leaveRequest->pertimbangan_atasan === 'tolak' ? 'circle-x' : ($leaveRequest->pertimbangan_atasan === 'ubah' ? 'edit' : 'clock-pause')) }}"></i>
                                {{ ucfirst($leaveRequest->pertimbangan_atasan === 'setuju' ? 'Disetujui' : ($leaveRequest->pertimbangan_atasan === 'tolak' ? 'Tidak Disetujui' : ($leaveRequest->pertimbangan_atasan === 'ubah' ? 'Perubahan' : 'Ditangguhkan'))) }}
                            </span>
                            @if($leaveRequest->catatan_atasan)
                                <div class="timeline-notes">
                                    <i class="ti ti-message me-1"></i>{{ $leaveRequest->catatan_atasan }}
                                </div>
                            @endif
                        @else
                            <span class="timeline-status-badge pending">
                                <i class="ti ti-clock-hour-4"></i> Menunggu
                            </span>
                        @endif
                    </div>
                @else
                    <div class="timeline-date">Belum ditugaskan</div>
                    <div class="timeline-description">Atasan langsung belum ditugaskan</div>
                @endif
            </div>
        </div>

        {{-- Step 3: Keputusan Pejabat --}}
        <div class="timeline-step {{ in_array($leaveRequest->status, ['disetujui', 'ditolak']) ? 'completed' : (in_array($leaveRequest->status, ['pertimbangan_pejabat', 'disetujui_atasan', 'diubah']) ? 'in-progress' : 'pending') }}">
            <div class="timeline-marker">
                <i class="ti ti-gavel"></i>
            </div>
            <div class="timeline-content">
                <div class="timeline-title">Keputusan Pejabat Berwenang</div>
                @if($leaveRequest->pejabatReviewer)
                    <div class="timeline-date">{{ $leaveRequest->keputusan ? $leaveRequest->decided_at?->format('d M Y, H:i') ?? '' : 'Menunggu...' }}</div>
                    <div class="timeline-description">
                        <strong>{{ $leaveRequest->pejabatReviewer->name }}</strong>
                        @if($leaveRequest->keputusan)
                            <span class="timeline-status-badge {{ $leaveRequest->keputusan }}">
                                <i class="ti ti-{{ $leaveRequest->keputusan === 'setuju' ? 'circle-check' : ($leaveRequest->keputusan === 'tolak' ? 'circle-x' : ($leaveRequest->keputusan === 'ubah' ? 'edit' : 'clock-pause')) }}"></i>
                                {{ ucfirst($leaveRequest->keputusan === 'setuju' ? 'Disetujui' : ($leaveRequest->keputusan === 'tolak' ? 'Tidak Disetujui' : ($leaveRequest->keputusan === 'ubah' ? 'Perubahan' : 'Ditangguhkan'))) }}
                            </span>
                            @if($leaveRequest->catatan_pejabat)
                                <div class="timeline-notes">
                                    <i class="ti ti-message me-1"></i>{{ $leaveRequest->catatan_pejabat }}
                                </div>
                            @endif
                        @else
                            <span class="timeline-status-badge pending">
                                <i class="ti ti-clock-hour-4"></i> Menunggu
                            </span>
                        @endif
                    </div>
                @else
                    <div class="timeline-date">Belum ditugaskan</div>
                    <div class="timeline-description">Pejabat berwenang belum ditugaskan</div>
                @endif
            </div>
        </div>

        {{-- Step 4: Selesai --}}
        <div class="timeline-step {{ in_array($leaveRequest->status, ['disetujui', 'ditolak']) ? 'completed' : 'pending' }}">
            <div class="timeline-marker">
                <i class="ti ti-check"></i>
            </div>
            <div class="timeline-content">
                <div class="timeline-title">Selesai</div>
                @if(in_array($leaveRequest->status, ['disetujui', 'ditolak']))
                    <div class="timeline-date">{{ $leaveRequest->updated_at->format('d M Y, H:i') }}</div>
                    <div class="timeline-description">
                        Pengajuan {{ $leaveRequest->isApproved() ? 'disetujui' : 'ditolak' }}
                    </div>
                    @php
                        $durasi = $leaveRequest->created_at->diffInDays($leaveRequest->updated_at);
                    @endphp
                    <div class="text-muted mt-1" style="font-size:0.8rem;">
                        <i class="ti ti-clock me-1"></i>Diproses dalam {{ $durasi > 0 ? $durasi . ' hari' : 'kurang dari 1 hari' }}
                    </div>
                @else
                    <div class="timeline-date">Menunggu penyelesaian</div>
                    <div class="timeline-description">Proses masih berjalan</div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.sc-timeline {
    padding: 1rem 0;
}

.timeline-container {
    position: relative;
    padding: 0;
}

.timeline-container::before {
    content: '';
    position: absolute;
    left: 24px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--sc-gray-200);
    z-index: 0;
}

.timeline-step {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.75rem;
    position: relative;
    z-index: 1;
}

.timeline-step.completed::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: -1.75rem;
    width: 2px;
    background: var(--sc-success);
    z-index: -1;
}

.timeline-marker {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
    background: white;
    border: 3px solid var(--sc-gray-200);
    color: var(--sc-gray-500);
    font-weight: bold;
}

.timeline-step.completed .timeline-marker {
    background: var(--sc-success);
    border-color: var(--sc-success);
    color: white;
}

.timeline-step.in-progress .timeline-marker {
    background: var(--sc-primary);
    border-color: var(--sc-primary);
    color: white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(var(--sc-primary-rgb), 0.7); }
    50% { box-shadow: 0 0 0 8px rgba(var(--sc-primary-rgb), 0); }
}

.timeline-content {
    flex: 1;
    padding-top: 0.5rem;
}

.timeline-title {
    font-weight: 700;
    font-size: 1rem;
    color: var(--sc-gray-900);
    margin-bottom: 0.25rem;
}

.timeline-date {
    font-size: 0.8rem;
    color: var(--sc-gray-500);
    margin-bottom: 0.5rem;
}

.timeline-description {
    font-size: 0.9rem;
    color: var(--sc-gray-700);
    line-height: 1.4;
}

.timeline-status-badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 0.5rem;
}

.timeline-status-badge.setuju {
    background: var(--sc-success-light);
    color: var(--sc-success);
}

.timeline-status-badge.tolak {
    background: var(--sc-danger-light);
    color: var(--sc-danger);
}

.timeline-status-badge.ubah {
    background: var(--sc-primary-light);
    color: var(--sc-primary);
}

.timeline-status-badge.tangguhkan {
    background: var(--sc-warning-light);
    color: var(--sc-warning);
}

.timeline-status-badge.pending {
    background: var(--sc-gray-100);
    color: var(--sc-gray-600);
}

.timeline-notes {
    background: var(--sc-gray-50, #f9fafb);
    border-left: 3px solid var(--sc-gray-300, #d1d5db);
    padding: 0.5rem 0.75rem;
    border-radius: 0 6px 6px 0;
    font-size: 0.85rem;
    color: var(--sc-gray-600, #4b5563);
    margin-top: 0.5rem;
    font-style: italic;
}

@media (max-width: 576px) {
    .timeline-marker {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }

    .timeline-container::before {
        left: 20px;
    }

    .timeline-step {
        gap: 1rem;
    }

    .timeline-title {
        font-size: 0.95rem;
    }
}
</style>
