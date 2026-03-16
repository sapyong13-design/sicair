{{-- Dashboard Quick Actions Component --}}
<div class="quick-actions-grid">
    {{-- Action 1: Ajukan Cuti (hanya jika boleh cuti) --}}
    @if(Auth::user()?->bolehCuti() ?? true)
    <a href="{{ route('leave.select-type') }}" class="quick-action-card">
        <div class="action-icon" style="background: var(--sc-primary-light); color: var(--sc-primary);">
            <i class="ti ti-file-plus"></i>
        </div>
        <div class="action-content">
            <div class="action-title">Ajukan Cuti</div>
            <div class="action-desc">Buat pengajuan cuti baru</div>
        </div>
        <i class="ti ti-chevron-right action-arrow"></i>
    </a>
    @endif

    {{-- Action 2: Sisa Cuti --}}
    <a href="#balanceModal" class="quick-action-card" data-bs-toggle="modal">
        <div class="action-icon" style="background: var(--sc-success-light); color: var(--sc-success);">
            <i class="ti ti-calendar-stats"></i>
        </div>
        <div class="action-content">
            <div class="action-title">Sisa Cuti</div>
            <div class="action-desc">{{ $sisaCuti ?? 0 }} hari tersedia</div>
        </div>
        <i class="ti ti-chevron-right action-arrow"></i>
    </a>

    {{-- Action 3: Lihat Pengajuan --}}
    <a href="{{ route('dashboard') . '?tab=pengajuan' }}" class="quick-action-card">
        <div class="action-icon" style="background: var(--sc-warning-light); color: var(--sc-warning);">
            <i class="ti ti-list-check"></i>
        </div>
        <div class="action-content">
            <div class="action-title">Pengajuan Anda</div>
            <div class="action-desc">{{ $totalPengajuan ?? 0 }} pengajuan</div>
        </div>
        <i class="ti ti-chevron-right action-arrow"></i>
    </a>

    {{-- Action 4: Download Laporan --}}
    <a href="{{ route('leave.history') }}" class="quick-action-card">
        <div class="action-icon" style="background: var(--sc-danger-light); color: var(--sc-danger);">
            <i class="ti ti-download"></i>
        </div>
        <div class="action-content">
            <div class="action-title">Laporan Cuti</div>
            <div class="action-desc">Download summary</div>
        </div>
        <i class="ti ti-chevron-right action-arrow"></i>
    </a>

    {{-- Action 5: Notifikasi --}}
    <a href="{{ route('notifications') }}" class="quick-action-card">
        <div class="action-icon" style="background: var(--sc-primary-light); color: var(--sc-primary);">
            <i class="ti ti-bell"></i>
        </div>
        <div class="action-content">
            <div class="action-title">Notifikasi</div>
            <div class="action-desc">{{ $unreadNotifications ?? 0 }} belum dibaca</div>
        </div>
        <i class="ti ti-chevron-right action-arrow"></i>
    </a>

    {{-- Action 6: Kalender Cuti --}}
    <a href="{{ route('kalender') }}" class="quick-action-card">
        <div class="action-icon" style="background: var(--sc-success-light); color: var(--sc-success);">
            <i class="ti ti-calendar-month"></i>
        </div>
        <div class="action-content">
            <div class="action-title">Kalender Cuti</div>
            <div class="action-desc">Lihat cuti tim</div>
        </div>
        <i class="ti ti-chevron-right action-arrow"></i>
    </a>
</div>

<style>
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.quick-action-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: white;
    border: 1px solid var(--sc-gray-200);
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.quick-action-card:hover {
    border-color: var(--sc-primary);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.action-content {
    flex: 1;
}

.action-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--sc-gray-900);
    margin-bottom: 0.25rem;
}

.action-desc {
    font-size: 0.8rem;
    color: var(--sc-gray-600);
}

.action-arrow {
    color: var(--sc-gray-400);
    font-size: 1.2rem;
    flex-shrink: 0;
    transition: all 0.3s;
}

.quick-action-card:hover .action-arrow {
    transform: translateX(4px);
    color: var(--sc-primary);
}

@media (max-width: 768px) {
    .quick-actions-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .quick-action-card {
        padding: 0.85rem 1rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .action-icon {
        width: 44px;
        height: 44px;
    }

    .action-arrow {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
    }
}

@media (max-width: 576px) {
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }

    .quick-action-card {
        flex-direction: row;
        align-items: center;
        position: relative;
    }
}
</style>
