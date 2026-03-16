{{-- Approval Notes & Timeline Comments Component --}}
<div class="approval-timeline">
    {{-- Atasan Review --}}
    @if($leaveRequest->atasanReviewer)
    <div class="approval-item">
        <div class="approval-icon" style="background: var(--sc-warning-light); color: var(--sc-warning);">
            <i class="ti ti-user-check"></i>
        </div>
        <div class="approval-content">
            <div class="approval-header">
                <div>
                    <h4 class="approval-title">Pertimbangan Atasan Langsung</h4>
                    <div class="approval-meta">
                        {{ $leaveRequest->atasanReviewer->name }} • {{ optional($leaveRequest->reviewed_at ?? null)->format('d M Y, H:i') ?? 'Pending' }}
                    </div>
                </div>
                @if($leaveRequest->pertimbangan_atasan)
                <span class="approval-status-badge {{ $leaveRequest->pertimbangan_atasan }}">
                    <i class="ti ti-{{ $leaveRequest->pertimbangan_atasan === 'setuju' ? 'circle-check' : ($leaveRequest->pertimbangan_atasan === 'tolak' ? 'circle-x' : ($leaveRequest->pertimbangan_atasan === 'ubah' ? 'edit' : 'clock-pause')) }}"></i>
                    {{ ucfirst($leaveRequest->pertimbangan_atasan === 'setuju' ? 'Disetujui' : ($leaveRequest->pertimbangan_atasan === 'tolak' ? 'Tidak Disetujui' : ($leaveRequest->pertimbangan_atasan === 'ubah' ? 'Perubahan' : 'Ditangguhkan'))) }}
                </span>
                @else
                <span class="approval-status-badge pending">
                    <i class="ti ti-clock-hour-4"></i> Menunggu
                </span>
                @endif
            </div>

            @if($leaveRequest->catatan_atasan)
            <div class="approval-notes">
                <div class="notes-label">
                    <i class="ti ti-note me-1"></i> Catatan:
                </div>
                <p class="notes-content">{{ $leaveRequest->catatan_atasan }}</p>
            </div>
            @endif

            @if($leaveRequest->pertimbangan_atasan === 'ubah')
            <div class="approval-changes">
                <div class="changes-label">
                    <i class="ti ti-edit me-1"></i> Perubahan yang Diminta:
                </div>
                <div class="changes-list">
                    @if($leaveRequest->alasan_ubah)
                    <div class="change-item">
                        <span class="change-label">Alasan:</span>
                        <span class="change-value">{{ $leaveRequest->alasan_ubah }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Connector --}}
    @if($leaveRequest->atasanReviewer && $leaveRequest->pejabatReviewer)
    <div class="approval-connector"></div>
    @endif

    {{-- Pejabat Decision --}}
    @if($leaveRequest->pejabatReviewer)
    <div class="approval-item">
        <div class="approval-icon" style="background: var(--sc-primary-light); color: var(--sc-primary);">
            <i class="ti ti-gavel"></i>
        </div>
        <div class="approval-content">
            <div class="approval-header">
                <div>
                    <h4 class="approval-title">Keputusan Pejabat Berwenang</h4>
                    <div class="approval-meta">
                        {{ $leaveRequest->pejabatReviewer->name }} • {{ optional($leaveRequest->decided_at ?? null)->format('d M Y, H:i') ?? 'Pending' }}
                    </div>
                </div>
                @if($leaveRequest->keputusan)
                <span class="approval-status-badge {{ $leaveRequest->keputusan }}">
                    <i class="ti ti-{{ $leaveRequest->keputusan === 'setuju' ? 'circle-check' : ($leaveRequest->keputusan === 'tolak' ? 'circle-x' : ($leaveRequest->keputusan === 'ubah' ? 'edit' : 'clock-pause')) }}"></i>
                    {{ ucfirst($leaveRequest->keputusan === 'setuju' ? 'Disetujui' : ($leaveRequest->keputusan === 'tolak' ? 'Tidak Disetujui' : ($leaveRequest->keputusan === 'ubah' ? 'Perubahan' : 'Ditangguhkan'))) }}
                </span>
                @else
                <span class="approval-status-badge pending">
                    <i class="ti ti-clock-hour-4"></i> Menunggu
                </span>
                @endif
            </div>

            @if($leaveRequest->catatan_pejabat)
            <div class="approval-notes">
                <div class="notes-label">
                    <i class="ti ti-note me-1"></i> Catatan:
                </div>
                <p class="notes-content">{{ $leaveRequest->catatan_pejabat }}</p>
            </div>
            @endif

            @if($leaveRequest->keputusan === 'ubah')
            <div class="approval-changes">
                <div class="changes-label">
                    <i class="ti ti-edit me-1"></i> Perubahan yang Ditetapkan:
                </div>
                <div class="changes-list">
                    @if($leaveRequest->durasi_ubah)
                    <div class="change-item">
                        <span class="change-label">Durasi:</span>
                        <span class="change-value">{{ $leaveRequest->durasi_ubah ?? 0 }} hari (dari {{ $leaveRequest->total_days ?? $leaveRequest->total_hari_kerja ?? 0 }} hari)</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<style>
.approval-timeline {
    position: relative;
    padding: 1rem 0;
}

.approval-timeline::before {
    content: '';
    position: absolute;
    left: 23px;
    top: 60px;
    bottom: 0;
    width: 2px;
    background: var(--sc-gray-200);
    z-index: 0;
}

.approval-item {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 2rem;
    position: relative;
    z-index: 1;
}

.approval-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.approval-content {
    flex: 1;
    background: white;
    border: 1px solid var(--sc-gray-200);
    border-radius: 12px;
    padding: 1.25rem;
}

.approval-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.approval-title {
    font-weight: 700;
    font-size: 1rem;
    color: var(--sc-gray-900);
    margin: 0;
}

.approval-meta {
    font-size: 0.85rem;
    color: var(--sc-gray-600);
    margin-top: 0.25rem;
}

.approval-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
}

.approval-status-badge.setuju {
    background: var(--sc-success-light);
    color: var(--sc-success);
}

.approval-status-badge.tolak {
    background: var(--sc-danger-light);
    color: var(--sc-danger);
}

.approval-status-badge.ubah {
    background: var(--sc-primary-light);
    color: var(--sc-primary);
}

.approval-status-badge.tangguhkan {
    background: var(--sc-warning-light);
    color: var(--sc-warning);
}

.approval-status-badge.pending {
    background: var(--sc-gray-100);
    color: var(--sc-gray-600);
}

.approval-notes {
    margin-top: 1rem;
    padding: 0.75rem 1rem;
    background: var(--sc-gray-50);
    border-radius: 8px;
    border-left: 3px solid var(--sc-primary);
}

.notes-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--sc-gray-700);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.notes-content {
    font-size: 0.9rem;
    color: var(--sc-gray-700);
    line-height: 1.5;
    margin: 0;
}

.approval-changes {
    margin-top: 1rem;
    padding: 0.75rem 1rem;
    background: var(--sc-warning-light);
    border-radius: 8px;
    border-left: 3px solid var(--sc-warning);
}

.changes-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--sc-warning);
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.changes-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.change-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.change-label {
    font-weight: 600;
    font-size: 0.8rem;
    color: var(--sc-gray-700);
    min-width: 80px;
}

.change-value {
    font-size: 0.9rem;
    color: var(--sc-gray-800);
    flex: 1;
}

.approval-connector {
    position: relative;
    height: 1rem;
    margin: -1rem 0;
    z-index: 1;
}

@media (max-width: 576px) {
    .approval-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .approval-status-badge {
        align-self: flex-start;
    }

    .approval-content {
        padding: 1rem;
    }

    .approval-item {
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .approval-icon {
        width: 40px;
        height: 40px;
    }
}
</style>
