{{-- Analytics & Comparison Cards Component --}}
<div class="row g-3">
    {{-- Card 1: Monthly Statistics --}}
    <div class="col-md-6 col-lg-3">
        <div class="card sh-card analytics-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="card-title mb-0">Rata-rata Penggunaan</h6>
                    <i class="ti ti-trending-up" style="color: var(--sh-primary);"></i>
                </div>
                <div style="font-size: 2rem; font-weight: 700; color: var(--sh-primary); margin-bottom: 0.5rem;">
                    {{ $averageUsage ?? 0 }} <span style="font-size: 0.9rem; color: var(--sh-gray-600);">hari/bulan</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--sh-gray-600);">
                    <i class="ti ti-info-circle me-1"></i> Dibanding industri: 3.2 hari
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2: Approval Rate --}}
    <div class="col-md-6 col-lg-3">
        <div class="card sh-card analytics-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="card-title mb-0">Tingkat Persetujuan</h6>
                    <i class="ti ti-percentage" style="color: var(--sh-success);"></i>
                </div>
                <div style="font-size: 2rem; font-weight: 700; color: var(--sh-success); margin-bottom: 0.5rem;">
                    {{ $approvalRate ?? 100 }}<span style="font-size: 0.9rem; color: var(--sh-gray-600);">%</span>
                </div>
                <div style="height: 4px; border-radius: 2px; background: var(--sh-gray-200); margin-top: 0.5rem;">
                    <div style="height: 100%; border-radius: 2px; background: var(--sh-success); width: {{ $approvalRate ?? 100 }}%;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 3: Most Common Month --}}
    <div class="col-md-6 col-lg-3">
        <div class="card sh-card analytics-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="card-title mb-0">Bulan Terbanyak</h6>
                    <i class="ti ti-calendar-stats" style="color: var(--sh-warning);"></i>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--sh-warning); margin-bottom: 0.5rem;">
                    {{ $mostUsedMonth ?? 'November' }}
                </div>
                <div style="font-size: 0.8rem; color: var(--sh-gray-600);">
                    <i class="ti ti-info-circle me-1"></i> {{ $mostUsedCount ?? 0 }} pengajuan
                </div>
            </div>
        </div>
    </div>

    {{-- Card 4: Comparison vs Team --}}
    <div class="col-md-6 col-lg-3">
        <div class="card sh-card analytics-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="card-title mb-0">vs Tim Anda</h6>
                    <i class="ti ti-users" style="color: var(--sh-primary);"></i>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: var(--sh-primary); margin-bottom: 0.5rem;">
                    {{ $positionInTeam ?? 'Rata-rata' }}
                </div>
                <div style="font-size: 0.8rem; color: var(--sh-gray-600);">
                    <i class="ti ti-info-circle me-1"></i> {{ $teamMemberCount ?? 0 }} orang di tim
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Trend Chart --}}
<div class="row g-3 mt-1">
    <div class="col-lg-12">
        <div class="card sh-card analytics-card">
            <div class="card-body">
                <h6 class="card-title mb-4">
                    <i class="ti ti-chart-line me-2" style="color: var(--sh-primary);"></i>
                    Tren Penggunaan Cuti (12 Bulan)
                </h6>
                <div id="trendChart" style="height: 250px;">
                    <svg width="100%" height="250" style="margin-top: 1rem;">
                        <defs>
                            <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:var(--sh-primary);stop-opacity:0.3" />
                                <stop offset="100%" style="stop-color:var(--sh-primary);stop-opacity:0" />
                            </linearGradient>
                        </defs>

                        <!-- Grid lines -->
                        <line x1="0" y1="50" x2="100%" y2="50" stroke="var(--sh-gray-200)" stroke-width="1" stroke-dasharray="5,5" />
                        <line x1="0" y1="100" x2="100%" y2="100" stroke="var(--sh-gray-200)" stroke-width="1" stroke-dasharray="5,5" />
                        <line x1="0" y1="150" x2="100%" y2="150" stroke="var(--sh-gray-200)" stroke-width="1" stroke-dasharray="5,5" />
                        <line x1="0" y1="200" x2="100%" y2="200" stroke="var(--sh-gray-200)" stroke-width="1" stroke-dasharray="5,5" />

                        <!-- Area -->
                        <polygon points="0,200 {{ $trendChartData ?? '10,180 20,150 30,160 40,140 50,130 60,120 70,110 80,100 90,90 100,80 110,85' }} 110,200" fill="url(#gradient)" />

                        <!-- Line -->
                        <polyline points="0,200 {{ $trendChartData ?? '10,180 20,150 30,160 40,140 50,130 60,120 70,110 80,100 90,90 100,80 110,85' }}" fill="none" stroke="var(--sh-primary)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                        <!-- X-axis labels -->
                        <text x="10" y="230" font-size="12" fill="var(--sh-gray-600)" text-anchor="middle">Jan</text>
                        <text x="30" y="230" font-size="12" fill="var(--sh-gray-600)" text-anchor="middle">Feb</text>
                        <text x="50" y="230" font-size="12" fill="var(--sh-gray-600)" text-anchor="middle">Mar</text>
                        <text x="70" y="230" font-size="12" fill="var(--sh-gray-600)" text-anchor="middle">Apr</text>
                        <text x="90" y="230" font-size="12" fill="var(--sh-gray-600)" text-anchor="middle">Mei</text>
                        <text x="110" y="230" font-size="12" fill="var(--sh-gray-600)" text-anchor="middle">Jun</text>
                    </svg>
                </div>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--sh-gray-200);">
                    <div style="font-size: 0.85rem; color: var(--sh-gray-600);">
                        📊 Trend cenderung menurun. Anda menggunakan lebih sedikit cuti dibanding bulan lalu.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.analytics-card {
    border: none;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    transition: all 0.3s;
}

.analytics-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.analytics-card .card-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--sh-gray-600);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
