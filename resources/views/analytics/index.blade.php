@extends('layouts.app')

@section('title', 'Analytics - SiHEALING')

@section('content')
{{-- Page Header --}}
<div class="sh-page-header mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sh-page-title mb-1">
                <i class="ti ti-chart-bar me-1" style="color: var(--sh-primary);"></i>
                Analytics Cuti
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:0.8rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Analytics</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            {{-- Year Filter --}}
            <form method="GET" class="d-flex align-items-center gap-2">
                <label class="text-muted fw-semibold" style="font-size:0.875rem;">Tahun:</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()" style="width:100px;">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('analytics.export-annual', ['year' => $year]) }}" class="btn btn-sm sh-btn-primary">
                <i class="ti ti-download me-1"></i> Export CSV {{ $year }}
            </a>
        </div>
    </div>
</div>

{{-- Summary Stats --}}
@php $s = $analytics['summary'] ?? []; @endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card sh-card h-100 text-center py-3">
            <div class="sh-stat-number" style="font-size:2rem;font-weight:700;color:var(--sh-primary);">
                {{ $s['total_requests'] ?? 0 }}
            </div>
            <div class="text-muted" style="font-size:0.8rem;">Total Pengajuan</div>
            @php $prevTotal = ($prevSummary['total_requests'] ?? 0); $diff = ($s['total_requests'] ?? 0) - $prevTotal; @endphp
            @if($prevTotal > 0)
            <div class="mt-1" style="font-size:0.75rem; color:{{ $diff >= 0 ? '#16a34a' : '#dc2626' }};">
                <i class="ti ti-{{ $diff >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                {{ $diff >= 0 ? '+' : '' }}{{ $diff }} vs {{ $prevYear }}
            </div>
            @endif
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sh-card h-100 text-center py-3">
            <div class="sh-stat-number" style="font-size:2rem;font-weight:700;color:#16a34a;">
                {{ $s['approved'] ?? 0 }}
            </div>
            <div class="text-muted" style="font-size:0.8rem;">Disetujui</div>
            @if(($s['total_requests'] ?? 0) > 0)
            <div class="mt-1" style="font-size:0.75rem;color:#16a34a;">
                {{ round(($s['approved'] / $s['total_requests']) * 100) }}% approval rate
            </div>
            @endif
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sh-card h-100 text-center py-3">
            <div class="sh-stat-number" style="font-size:2rem;font-weight:700;color:#d97706;">
                {{ $s['pending'] ?? 0 }}
            </div>
            <div class="text-muted" style="font-size:0.8rem;">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card sh-card h-100 text-center py-3">
            <div class="sh-stat-number" style="font-size:2rem;font-weight:700;color:#dc2626;">
                {{ $s['rejected'] ?? 0 }}
            </div>
            <div class="text-muted" style="font-size:0.8rem;">Ditolak</div>
            <div class="mt-1" style="font-size:0.75rem;color:#dc2626;">
                {{ $s['total_days_approved'] ?? 0 }} hari kerja disetujui
            </div>
        </div>
    </div>
</div>

{{-- Year Comparison Banner --}}
@if(!empty($prevSummary))
<div class="card sh-card mb-4" style="background: linear-gradient(135deg, var(--sh-primary-light) 0%, var(--sh-accent-light) 100%); border: 2px solid var(--sh-primary);">
    <div class="card-body py-3">
        <h6 class="fw-bold mb-2" style="color:var(--sh-primary);">
            <i class="ti ti-arrows-right-left me-1"></i> Perbandingan {{ $year }} vs {{ $prevYear }}
        </h6>
        <div class="row g-3">
            @php
                $comparisons = [
                    ['label' => 'Total Pengajuan', 'curr' => $s['total_requests'] ?? 0, 'prev' => $prevSummary['total_requests'] ?? 0],
                    ['label' => 'Disetujui', 'curr' => $s['approved'] ?? 0, 'prev' => $prevSummary['approved'] ?? 0],
                    ['label' => 'Ditolak', 'curr' => $s['rejected'] ?? 0, 'prev' => $prevSummary['rejected'] ?? 0],
                    ['label' => 'Total Hari Kerja', 'curr' => $s['total_days_approved'] ?? 0, 'prev' => $prevSummary['total_days_approved'] ?? 0],
                ];
            @endphp
            @foreach($comparisons as $cmp)
            @php
                $d = $cmp['curr'] - $cmp['prev'];
                $pct = $cmp['prev'] > 0 ? round(($d / $cmp['prev']) * 100, 1) : 0;
            @endphp
            <div class="col-6 col-md-3">
                <div style="font-size:0.75rem;color:var(--sh-text-muted);">{{ $cmp['label'] }}</div>
                <div class="fw-bold" style="color:var(--sh-text);">
                    {{ $cmp['curr'] }}
                    <span style="font-size:0.75rem;color:{{ $d >= 0 ? '#16a34a' : '#dc2626' }};">
                        ({{ $d >= 0 ? '+' : '' }}{{ $d }}, {{ $pct >= 0 ? '+' : '' }}{{ $pct }}%)
                    </span>
                </div>
                <div style="font-size:0.72rem;color:var(--sh-text-muted);">{{ $prevYear }}: {{ $cmp['prev'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Charts Row 1: Monthly Trend + Donut by Type --}}
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card sh-card h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--sh-text);">
                    <i class="ti ti-trending-up me-1" style="color:var(--sh-primary);"></i>
                    Tren Cuti Bulanan {{ $year }}
                </h6>
                <canvas id="chartMonthly" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card sh-card h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--sh-text);">
                    <i class="ti ti-chart-donut me-1" style="color:var(--sh-primary);"></i>
                    Berdasarkan Jenis Cuti
                </h6>
                <canvas id="chartByType" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row 2: By Status + By Department --}}
<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="card sh-card h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--sh-text);">
                    <i class="ti ti-chart-pie me-1" style="color:var(--sh-primary);"></i>
                    Berdasarkan Status
                </h6>
                <canvas id="chartByStatus" height="180"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card sh-card h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--sh-text);">
                    <i class="ti ti-building me-1" style="color:var(--sh-primary);"></i>
                    Berdasarkan Unit Kerja
                </h6>
                <canvas id="chartByDept" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Heatmap #42 --}}
@if(!empty($heatmapData))
<div class="card sh-card mb-4">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3" style="color:var(--sh-text);">
            <i class="ti ti-grid-dots me-1" style="color:var(--sh-primary);"></i>
            Heatmap Utilisasi Cuti per Pegawai {{ $year }}
            <small class="text-muted fw-normal">(hari kerja per bulan)</small>
        </h6>
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:0.78rem; min-width:700px;">
                <thead>
                    <tr style="background: var(--sh-gray-100);">
                        <th style="padding:0.4rem 0.6rem; min-width:150px;">Pegawai</th>
                        @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'] as $mn)
                        <th class="text-center" style="padding:0.4rem 0.3rem;">{{ $mn }}</th>
                        @endforeach
                        <th class="text-center" style="padding:0.4rem 0.6rem;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($heatmapData as $row)
                    @php
                        $maxVal = max(max($row['months']), 1);
                    @endphp
                    <tr>
                        <td style="padding:0.3rem 0.6rem; font-weight:500; white-space:nowrap;">
                            {{ $row['name'] }}
                        </td>
                        @foreach($row['months'] as $days)
                        @php
                            $intensity = $days > 0 ? max(0.15, min(1, $days / $maxVal)) : 0;
                            $bg = $days > 0
                                ? 'rgba(22, 101, 52, ' . $intensity . ')'
                                : 'transparent';
                            $color = $intensity > 0.5 ? '#ffffff' : ($days > 0 ? '#14532d' : 'var(--sh-text-muted)');
                        @endphp
                        <td class="text-center" style="padding:0.3rem; background:{{ $bg }}; color:{{ $color }}; border-radius:4px;">
                            {{ $days > 0 ? $days : '—' }}
                        </td>
                        @endforeach
                        <td class="text-center fw-bold" style="padding:0.3rem 0.6rem; color:var(--sh-primary);">
                            {{ $row['total'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(count($heatmapData) > 15)
        <div class="text-muted mt-2" style="font-size:0.75rem;">
            <i class="ti ti-info-circle me-1"></i>Menampilkan {{ count($heatmapData) }} pegawai. Gunakan Export CSV untuk data lengkap.
        </div>
        @endif
    </div>
</div>
@endif

{{-- Top Users + Upcoming Leaves --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card sh-card h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--sh-text);">
                    <i class="ti ti-award me-1" style="color:var(--sh-accent);"></i>
                    Top 5 Pengaju Cuti {{ $year }}
                </h6>
                @forelse($topUsers as $i => $u)
                <div class="d-flex align-items-center justify-content-between py-2 {{ $i < count($topUsers) - 1 ? 'border-bottom' : '' }}">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--sh-primary-light);color:var(--sh-primary);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;">
                            {{ $i + 1 }}
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size:0.875rem;color:var(--sh-text);">{{ $u['name'] }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $u['nip'] ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge" style="background:var(--sh-primary);color:#fff;font-size:0.78rem;">
                            {{ $u['leave_count'] ?? 0 }} pengajuan
                        </span>
                        <div class="text-muted" style="font-size:0.72rem;">{{ $u['total_days'] ?? 0 }} hari</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="ti ti-users-minus" style="font-size:2rem;opacity:0.4;"></i>
                    <div class="mt-2 small">Belum ada data</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card sh-card h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:var(--sh-text);">
                    <i class="ti ti-calendar-event me-1" style="color:var(--sh-accent);"></i>
                    Cuti Akan Datang (Disetujui)
                </h6>
                @forelse($upcomingLeaves as $leave)
                <div class="d-flex align-items-start justify-content-between py-2 border-bottom">
                    <div>
                        <div class="fw-semibold" style="font-size:0.875rem;color:var(--sh-text);">
                            {{ $leave['name'] ?? '—' }}
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            {{ $leave['type'] ?? '—' }} &bull; {{ $leave['start_date'] ?? '' }}
                        </div>
                    </div>
                    <span class="badge sh-badge-approved" style="font-size:0.72rem;">{{ $leave['days'] ?? 0 }} hari</span>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="ti ti-calendar-off" style="font-size:2rem;opacity:0.4;"></i>
                    <div class="mt-2 small">Tidak ada cuti mendatang</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
    const tickColor = isDark ? '#94a3b8' : '#64748b';

    // ── Monthly Trend Chart ──────────────────────────────────────────
    @php $cm = $chartMonthly; @endphp
    new Chart(document.getElementById('chartMonthly').getContext('2d'), {
        type: 'bar',
        data: {
            labels: @json($cm['months'] ?? []),
            datasets: [
                {
                    label: 'Disetujui',
                    data: @json($cm['approved'] ?? []),
                    backgroundColor: 'rgba(22,101,52,0.75)',
                    borderRadius: 4,
                    order: 1,
                },
                {
                    label: 'Pending',
                    data: @json($cm['pending'] ?? []),
                    backgroundColor: 'rgba(217,119,6,0.7)',
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Ditolak',
                    data: @json($cm['rejected'] ?? []),
                    backgroundColor: 'rgba(220,38,38,0.7)',
                    borderRadius: 4,
                    order: 3,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { color: tickColor, boxWidth: 12 } },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                x: { stacked: true, grid: { color: gridColor }, ticks: { color: tickColor } },
                y: { stacked: true, beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1 } },
            },
        },
    });

    // ── By Type Donut ────────────────────────────────────────────────
    @php $ct = $chartByType; @endphp
    new Chart(document.getElementById('chartByType').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: @json($ct['labels'] ?? []),
            datasets: [{
                data: @json($ct['values'] ?? []),
                backgroundColor: @json($ct['colors'] ?? []),
                borderWidth: 2,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: tickColor, boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.raw} pengajuan`,
                    }
                },
            },
        },
    });

    // ── By Status Pie ────────────────────────────────────────────────
    @php $cs = $chartByStatus; @endphp
    new Chart(document.getElementById('chartByStatus').getContext('2d'), {
        type: 'pie',
        data: {
            labels: @json($cs['labels'] ?? []),
            datasets: [{
                data: @json($cs['values'] ?? []),
                backgroundColor: @json($cs['colors'] ?? []),
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: tickColor, boxWidth: 12, font: { size: 11 } } },
            },
        },
    });

    // ── By Department Bar ────────────────────────────────────────────
    @php $cd = $chartByDepartment; @endphp
    new Chart(document.getElementById('chartByDept').getContext('2d'), {
        type: 'bar',
        data: {
            labels: @json($cd['labels'] ?? []),
            datasets: [{
                label: 'Jumlah Cuti',
                data: @json($cd['values'] ?? []),
                backgroundColor: 'rgba(22,101,52,0.7)',
                borderRadius: 4,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                x: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1 } },
                y: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } } },
            },
        },
    });

    // ── Count-up animation for stat cards ───────────────────────────
    document.querySelectorAll('.sh-stat-number').forEach(el => {
        const target = parseInt(el.textContent.replace(/\D/g, ''), 10);
        if (!target) return;
        let start = 0;
        const duration = 900;
        const step = Math.ceil(target / (duration / 16));
        const timer = setInterval(() => {
            start = Math.min(start + step, target);
            el.textContent = start.toLocaleString('id-ID');
            if (start >= target) clearInterval(timer);
        }, 16);
    });
})();
</script>
@endpush
