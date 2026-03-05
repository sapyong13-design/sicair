@extends('layouts.app')

@section('title', 'Dashboard - SiHEALING')

@section('content')
{{-- Page Header --}}
@php
    $hour = (int)\Carbon\Carbon::now()->format('H');
    $greeting = match(true) {
        $hour >= 5  && $hour < 11 => 'Selamat pagi',
        $hour >= 11 && $hour < 15 => 'Selamat siang',
        $hour >= 15 && $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };
    $greetingIcon = match(true) {
        $hour >= 5  && $hour < 11 => 'sun',
        $hour >= 11 && $hour < 15 => 'sun-high',
        $hour >= 15 && $hour < 18 => 'sunset',
        default => 'moon',
    };
@endphp
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sh-page-title mb-1">
                <i class="ti ti-layout-dashboard me-1" style="color: var(--sh-primary);"></i>
                Dashboard
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                <i class="ti ti-{{ $greetingIcon }} me-1" style="color:var(--sh-accent);"></i>
                {{ $greeting }}, <strong class="text-dark">{{ $user->name }}</strong>
@php
    $roleLabels = [
        'admin' => 'Admin', 'ketua' => 'Ketua', 'atasan' => 'Atasan',
        'panitera' => 'Panitera', 'sekretaris' => 'Sekretaris',
        'pegawai' => 'Pegawai', 'hakim' => 'Hakim', 'hakim_ad_hoc' => 'Hakim Ad Hoc',
    ];
    $roleBadge = $user->isAdmin() ? 'approved' : ($user->isKetua() ? 'pending' : ($user->isAtasan() ? 'pending' : 'approved'));
    $roleIcon  = $user->isAdmin() ? 'shield-check' : ($user->isKetua() ? 'gavel' : ($user->isAtasan() ? 'user-check' : 'user'));
@endphp
                <span class="sh-badge sh-badge-{{ $roleBadge }} ms-1" style="font-size: 0.7rem;">
                    <i class="ti ti-{{ $roleIcon }}"></i>
                    {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                </span>
            </div>
        </div>
        @if(!$user->isAdmin() && $user->bolehCuti())
        <a href="{{ route('leave.select-type') }}" class="btn btn-primary sh-btn-primary">
            <i class="ti ti-file-plus me-1"></i> Ajukan Cuti
        </a>
        @endif
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

@if($user->isAdmin())
    {{-- ============================= --}}
    {{--       ADMIN DASHBOARD         --}}
    {{-- ============================= --}}

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sh-stat-card stat-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Total Pegawai</div>
                            <div class="sh-stat-number" style="color: var(--sh-primary);">{{ $totalPegawai }}</div>
                        </div>
                        <div class="sh-stat-icon icon-primary">
                            <i class="ti ti-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sh-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Menunggu Proses</div>
                            <div class="sh-stat-number" style="color: var(--sh-warning);">{{ $pendingRequests->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-warning">
                            <i class="ti ti-clock-hour-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sh-stat-card stat-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Disetujui</div>
                            <div class="sh-stat-number" style="color: var(--sh-success);">{{ $recentDecisions->filter(fn($r) => $r->isApproved())->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sh-stat-card stat-danger">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Ditolak</div>
                            <div class="sh-stat-number" style="color: var(--sh-danger);">{{ $recentDecisions->filter(fn($r) => $r->isRejected())->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-danger">
                            <i class="ti ti-circle-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- #10 & #11: Widgets Cuti Hari Ini + Mendatang --}}
    <div class="row g-3 mb-4">
        {{-- #10: Siapa Cuti Hari Ini --}}
        <div class="col-md-6 animate-in">
            <div class="card sh-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-beach me-2" style="color: var(--sh-success);"></i>
                        Cuti Hari Ini
                    </h3>
                    <span class="sh-badge sh-badge-{{ $todayOnLeave->isNotEmpty() ? 'pending' : 'approved' }}">
                        {{ $todayOnLeave->count() }} pegawai
                    </span>
                </div>
                <div class="card-body p-3" style="max-height: 220px; overflow-y: auto;">
                    @if($todayOnLeave->isEmpty())
                    <div class="text-center py-3">
                        <i class="ti ti-users" style="font-size: 2rem; color: var(--sh-text-muted); opacity: 0.3;"></i>
                        <p class="text-muted mb-0 mt-2" style="font-size: 0.85rem;">Tidak ada pegawai yang cuti hari ini</p>
                    </div>
                    @else
                    @foreach($todayOnLeave as $req)
                    <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sh-user-avatar" style="width:32px;height:32px;font-size:0.7rem;background:var(--sh-primary-light);color:var(--sh-primary);border:none;border-radius:8px;flex-shrink:0;">
                            {{ strtoupper(substr($req->user->name, 0, 2)) }}
                        </div>
                        <div class="flex-fill" style="min-width:0;">
                            <div class="fw-semibold" style="font-size:0.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req->user->name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $req->type_label }} &bull; s.d. {{ $req->end_date->format('d M') }}</div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- #11: Cuti Mendatang 7 Hari --}}
        <div class="col-md-6 animate-in">
            <div class="card sh-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-calendar-event me-2" style="color: var(--sh-accent);"></i>
                        Cuti Mendatang (7 Hari)
                    </h3>
                    <span class="sh-badge sh-badge-pending">{{ $upcomingLeaves7Days->count() }}</span>
                </div>
                <div class="card-body p-3" style="max-height: 220px; overflow-y: auto;">
                    @if($upcomingLeaves7Days->isEmpty())
                    <div class="text-center py-3">
                        <i class="ti ti-calendar" style="font-size: 2rem; color: var(--sh-text-muted); opacity: 0.3;"></i>
                        <p class="text-muted mb-0 mt-2" style="font-size: 0.85rem;">Tidak ada cuti disetujui dalam 7 hari ke depan</p>
                    </div>
                    @else
                    @foreach($upcomingLeaves7Days as $req)
                    <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sh-user-avatar" style="width:32px;height:32px;font-size:0.7rem;background:var(--sh-accent-light);color:var(--sh-accent);border:none;border-radius:8px;flex-shrink:0;">
                            {{ strtoupper(substr($req->user->name, 0, 2)) }}
                        </div>
                        <div class="flex-fill" style="min-width:0;">
                            <div class="fw-semibold" style="font-size:0.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req->user->name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $req->start_date->format('d M') }} &bull; {{ $req->type_label }}</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge" style="background:var(--sh-accent-light);color:var(--sh-accent);border-radius:50px;font-size:0.7rem;">{{ $req->start_date->diffInDays(\Carbon\Carbon::today()) }}h lagi</span>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Sprint 3 Widgets Row --}}
    <div class="row g-3 mb-4">
        {{-- #16: Widget Saldo Rendah --}}
        @if($saldoRendah->isNotEmpty())
        <div class="col-md-4 animate-in">
            <div class="card sh-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0" style="font-size:0.9rem;">
                        <i class="ti ti-alert-triangle me-2" style="color:var(--sh-warning);"></i>
                        Saldo Cuti Rendah
                    </h3>
                    <span class="sh-badge" style="background:var(--sh-warning-light);color:var(--sh-warning);">≤ 3 hari</span>
                </div>
                <div class="card-body p-3">
                    @foreach($saldoRendah as $p)
                    <div class="d-flex align-items-center gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sh-user-avatar" style="width:28px;height:28px;font-size:0.65rem;background:var(--sh-warning-light);color:var(--sh-warning);border:none;border-radius:6px;flex-shrink:0;">{{ strtoupper(substr($p->name,0,2)) }}</div>
                        <div class="flex-fill" style="min-width:0;">
                            <div style="font-size:0.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p->name }}</div>
                        </div>
                        <span class="fw-bold" style="color:var(--sh-warning);font-size:0.82rem;">{{ $p->leave_balance }}h</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- #19: Top 5 Paling Banyak Cuti --}}
        @if($top5Cuti->isNotEmpty())
        <div class="col-md-4 animate-in">
            <div class="card sh-card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0" style="font-size:0.9rem;">
                        <i class="ti ti-trophy me-2" style="color:var(--sh-accent);"></i>
                        Top 5 Penggunaan Cuti {{ $year }}
                    </h3>
                </div>
                <div class="card-body p-3">
                    @foreach($top5Cuti as $i => $p)
                    <div class="d-flex align-items-center gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <span style="font-size:0.75rem;font-weight:700;color:var(--sh-text-muted);width:16px;flex-shrink:0;">{{ $i+1 }}</span>
                        <div class="flex-fill" style="min-width:0;">
                            <div style="font-size:0.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p->name }}</div>
                        </div>
                        <span class="fw-bold" style="color:var(--sh-primary);font-size:0.82rem;">{{ $p->total_hari }}h</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- #21: Recent Activity Feed --}}
        <div class="col-md-4 animate-in">
            <div class="card sh-card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0" style="font-size:0.9rem;">
                        <i class="ti ti-activity me-2" style="color:var(--sh-primary);"></i>
                        Aktivitas Terbaru
                    </h3>
                </div>
                <div class="card-body p-3">
                    @forelse($recentActivity as $act)
                    <div class="d-flex align-items-start gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sh-user-avatar" style="width:26px;height:26px;font-size:0.6rem;background:var(--sh-primary-light);color:var(--sh-primary);border:none;border-radius:6px;flex-shrink:0;margin-top:2px;">{{ strtoupper(substr($act->user->name,0,2)) }}</div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.8rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <strong>{{ $act->user->name }}</strong>
                                {{ $act->status === 'diajukan' ? 'mengajukan' : ($act->status === 'disetujui' ? 'cuti disetujui' : ($act->status === 'ditolak' ? 'cuti ditolak' : 'update')) }}
                                <span style="color:var(--sh-primary);">{{ $act->type_label }}</span>
                            </div>
                            <div class="text-muted" style="font-size:0.72rem;">{{ $act->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="ti ti-calendar-off" style="font-size:1.8rem;opacity:0.3;color:var(--sh-primary);"></i>
                        <div class="text-muted mt-1" style="font-size:0.8rem;">Belum ada aktivitas terbaru.</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- #18: Trend Indicator on Stats (#18) - inline with stat cards --}}
    @if(isset($monthTrend))
    <div class="mb-4 p-3 sh-card" style="border-radius:12px;">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="ti ti-trending-{{ $monthTrend['trending'] }}" style="font-size:1.5rem;color:{{ $monthTrend['trending'] === 'up' ? 'var(--sh-danger)' : 'var(--sh-success)' }};"></i>
            <div>
                <div class="fw-bold" style="font-size:0.9rem;">Trend Pengajuan Cuti</div>
                <div class="text-muted" style="font-size:0.82rem;">
                    Bulan ini: <strong>{{ $monthTrend['this_month'] }}</strong> cuti disetujui &bull;
                    Bulan lalu: <strong>{{ $monthTrend['last_month'] }}</strong> &bull;
                    <strong style="color:{{ $monthTrend['trending'] === 'up' ? 'var(--sh-danger)' : 'var(--sh-success)' }}">
                        {{ $monthTrend['diff_pct'] >= 0 ? '+' : '' }}{{ $monthTrend['diff_pct'] }}%
                    </strong>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- #17: Widget Carry-Over Akan Hangus (shown Oct-Dec) --}}
    @if(!empty($carryOverHangus) && $carryOverHangus->isNotEmpty())
    <div class="alert mb-4" style="background:var(--sh-warning-light);border:2px solid var(--sh-warning);border-radius:12px;">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="ti ti-clock-exclamation" style="color:var(--sh-warning);font-size:1.2rem;"></i>
            <strong style="color:var(--sh-warning);">Carry-Over Cuti Akan Hangus Akhir Tahun!</strong>
        </div>
        <div style="font-size:0.85rem;color:#78350f;">
            {{ $carryOverHangus->count() }} pegawai memiliki sisa carry-over dari tahun lalu yang belum digunakan:
            @foreach($carryOverHangus->take(5) as $co)
            <span class="sh-badge ms-1" style="background:#fef3c7;color:#92400e;">{{ $co->user->name ?? '-' }} ({{ $co->carry_over }}h)</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- #20: Mini Calendar Widget --}}
    @php
        $calNow      = \Carbon\Carbon::now();
        $calYear     = $calNow->year;
        $calMonth    = $calNow->month;
        $calFirst    = \Carbon\Carbon::create($calYear, $calMonth, 1);
        $calDays     = $calFirst->daysInMonth;
        $calStartDow = $calFirst->dayOfWeekIso; // 1=Mon, 7=Sun
        // Build leave day set for current month
        $calLeaveDays = \App\Models\LeaveRequest::whereIn('status',['disetujui','approved'])
            ->where(function($q) use ($calYear, $calMonth, $calDays) {
                $monthStart = sprintf('%04d-%02d-01', $calYear, $calMonth);
                $monthEnd   = sprintf('%04d-%02d-%02d', $calYear, $calMonth, $calDays);
                $q->where('start_date', '<=', $monthEnd)->where('end_date', '>=', $monthStart);
            })
            ->get()
            ->flatMap(function($lr) use ($calYear, $calMonth) {
                $days = [];
                $s = max(strtotime($lr->start_date), mktime(0,0,0,$calMonth,1,$calYear));
                $e = min(strtotime($lr->end_date), mktime(0,0,0,$calMonth+1,0,$calYear));
                for ($d=$s; $d<=$e; $d+=86400) { $days[] = (int)date('j',$d); }
                return $days;
            })->unique()->values()->toArray();
        $calMonthNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        // Hari libur nasional & cuti bersama Indonesia 2026
        // Format: 'Y-m-d' => ['label' => '...', 'type' => 'libur|bersama']
        $hariLiburNasional = [
            '2026-01-01' => ['label' => 'Tahun Baru Masehi', 'type' => 'libur'],
            '2026-01-27' => ['label' => 'Isra Mikraj Nabi Muhammad SAW', 'type' => 'libur'],
            '2026-01-28' => ['label' => 'Cuti Bersama Isra Mikraj', 'type' => 'bersama'],
            '2026-02-17' => ['label' => 'Tahun Baru Imlek 2577', 'type' => 'libur'],
            '2026-03-03' => ['label' => 'Cuti Bersama Tahun Baru Imlek', 'type' => 'bersama'],
            '2026-03-22' => ['label' => 'Hari Raya Nyepi Tahun Saka 1948', 'type' => 'libur'],
            '2026-03-23' => ['label' => 'Cuti Bersama Nyepi', 'type' => 'bersama'],
            '2026-04-02' => ['label' => 'Wafat Isa Al-Masih', 'type' => 'libur'],
            '2026-04-03' => ['label' => 'Cuti Bersama Paskah', 'type' => 'bersama'],
            '2026-04-06' => ['label' => 'Cuti Bersama Paskah', 'type' => 'bersama'],
            '2026-04-20' => ['label' => 'Idul Fitri 1447 H', 'type' => 'libur'],
            '2026-04-21' => ['label' => 'Idul Fitri 1447 H', 'type' => 'libur'],
            '2026-04-17' => ['label' => 'Cuti Bersama Idul Fitri', 'type' => 'bersama'],
            '2026-04-22' => ['label' => 'Cuti Bersama Idul Fitri', 'type' => 'bersama'],
            '2026-04-23' => ['label' => 'Cuti Bersama Idul Fitri', 'type' => 'bersama'],
            '2026-04-24' => ['label' => 'Cuti Bersama Idul Fitri', 'type' => 'bersama'],
            '2026-05-01' => ['label' => 'Hari Buruh Internasional', 'type' => 'libur'],
            '2026-05-14' => ['label' => 'Kenaikan Isa Al-Masih', 'type' => 'libur'],
            '2026-05-15' => ['label' => 'Cuti Bersama Kenaikan Isa Al-Masih', 'type' => 'bersama'],
            '2026-05-25' => ['label' => 'Hari Raya Waisak', 'type' => 'libur'],
            '2026-06-01' => ['label' => 'Hari Lahir Pancasila', 'type' => 'libur'],
            '2026-06-27' => ['label' => 'Idul Adha 1447 H', 'type' => 'libur'],
            '2026-06-26' => ['label' => 'Cuti Bersama Idul Adha', 'type' => 'bersama'],
            '2026-07-17' => ['label' => 'Tahun Baru Islam 1448 H', 'type' => 'libur'],
            '2026-08-17' => ['label' => 'Hari Kemerdekaan RI', 'type' => 'libur'],
            '2026-09-25' => ['label' => 'Maulid Nabi Muhammad SAW', 'type' => 'libur'],
            '2026-12-25' => ['label' => 'Hari Raya Natal', 'type' => 'libur'],
            '2026-12-24' => ['label' => 'Cuti Bersama Natal', 'type' => 'bersama'],
        ];

        // Filter to current month
        $calHolidays = []; // day => ['label', 'type']
        foreach ($hariLiburNasional as $dateStr => $info) {
            [$hy, $hm, $hd] = explode('-', $dateStr);
            if ((int)$hy === $calYear && (int)$hm === $calMonth) {
                $calHolidays[(int)$hd] = $info;
            }
        }
    @endphp
    <div class="card sh-card mb-4 animate-in">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0" style="font-size:0.9rem;">
                <i class="ti ti-calendar me-2" style="color:var(--sh-primary);"></i>
                {{ $calMonthNames[$calMonth] }} {{ $calYear }}
            </h3>
            <a href="{{ route('kalender', ['year' => $calYear, 'month' => $calMonth]) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:0.78rem;">
                <i class="ti ti-external-link me-1"></i> Buka Kalender
            </a>
        </div>
        <div class="card-body p-3">
            {{-- Legend --}}
            <div class="d-flex align-items-center gap-3 mb-2 flex-wrap" style="font-size:0.7rem;color:var(--sh-text-muted);">
                <span class="d-flex align-items-center gap-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:var(--sh-success);display:inline-block;"></span> Cuti pegawai
                </span>
                <span class="d-flex align-items-center gap-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:#dc2626;display:inline-block;"></span> Hari libur
                </span>
                <span class="d-flex align-items-center gap-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:#b8860b;display:inline-block;"></span> Cuti bersama
                </span>
            </div>
            <div class="row g-0 text-center mb-2">
                @foreach(['S','S','R','K','J','S','M'] as $dn)
                <div class="col" style="font-size:0.7rem;font-weight:700;color:var(--sh-text-muted);text-transform:uppercase;">{{ $dn }}</div>
                @endforeach
            </div>
            @php $dc = 1; @endphp
            @for($row = 0; $row < 6; $row++)
                @if($dc > $calDays) @break @endif
                <div class="row g-0 text-center mb-1">
                    @for($col = 1; $col <= 7; $col++)
                        @if(($row === 0 && $col < $calStartDow) || $dc > $calDays)
                            <div class="col"></div>
                        @else
                            @php
                                $isToday    = ($dc === $calNow->day);
                                $hasLeave   = in_array($dc, $calLeaveDays);
                                $isWeekend  = ($col >= 6);
                                $holiday    = $calHolidays[$dc] ?? null;
                                $isLibur    = $holiday && $holiday['type'] === 'libur';
                                $isBersama  = $holiday && $holiday['type'] === 'bersama';
                                $isRed      = $isWeekend || $isLibur || $isBersama;
                                $tooltipParts = [];
                                if ($hasLeave)   $tooltipParts[] = 'Ada cuti pegawai';
                                if ($holiday)    $tooltipParts[] = $holiday['label'];
                                $tooltip = implode(' • ', $tooltipParts);
                                $isClickable = $hasLeave || $holiday;
                            @endphp
                            <div class="col d-flex flex-column align-items-center sh-cal-day-cell"
                                 style="cursor:{{ $isClickable ? 'pointer' : 'default' }};border-radius:6px;transition:background 0.15s;"
                                 @if($isClickable) onclick="window.location='{{ route('kalender', ['year'=>$calYear,'month'=>$calMonth]) }}'"
                                 title="{{ $tooltip }}" @endif>
                                <span style="width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:{{ $isToday ? '800' : '500' }};
                                    {{ $isToday ? 'background:var(--sh-primary);color:#fff;' : ($isRed ? 'color:#dc2626;' : 'color:var(--sh-text);') }}">
                                    {{ $dc }}
                                </span>
                                {{-- Dots row: leave (green), libur (red), bersama (gold) --}}
                                <span class="d-flex gap-1" style="height:6px;margin-top:1px;align-items:center;">
                                    @if($hasLeave)<span style="width:5px;height:5px;border-radius:50%;background:var(--sh-success);"></span>@endif
                                    @if($isLibur)<span style="width:5px;height:5px;border-radius:50%;background:#dc2626;"></span>@endif
                                    @if($isBersama)<span style="width:5px;height:5px;border-radius:50%;background:#b8860b;"></span>@endif
                                    @if(!$hasLeave && !$holiday)<span style="width:5px;height:5px;"></span>@endif
                                </span>
                            </div>
                            @php $dc++; @endphp
                        @endif
                    @endfor
                </div>
            @endfor
        </div>
    </div>

    {{-- Pending Requests --}}
    <div class="card sh-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-clock-hour-4 me-2" style="color: var(--sh-warning);"></i>
                Pengajuan Menunggu Proses
            </h3>
            @if($pendingRequests->isNotEmpty())
            <span class="sh-badge sh-badge-pending">{{ $pendingRequests->count() }} antrian</span>
            @endif
        </div>

        @if($pendingRequests->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                {{-- #46 Empty state SVG: no-data --}}
                <svg width="88" height="88" viewBox="0 0 88 88" fill="none" xmlns="http://www.w3.org/2000/svg" class="mb-3" aria-hidden="true">
                    <circle cx="44" cy="44" r="40" fill="var(--sh-success-light)"/>
                    <path d="M28 44L38 54L60 32" stroke="var(--sh-success)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="44" cy="44" r="18" stroke="var(--sh-success)" stroke-width="2.5" fill="none" opacity="0.3"/>
                </svg>
                <h4 class="fw-bold text-dark mb-1">Semua Beres!</h4>
                <p class="text-muted mb-0">Tidak ada pengajuan cuti yang menunggu proses saat ini.</p>
            </div>
        </div>
        @else
        {{-- Desktop Table View --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table sh-table mb-0">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Jenis Cuti</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pendingRequests as $req)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="sh-user-avatar" style="width: 36px; height: 36px; font-size: 0.75rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 10px;">
                                    {{ strtoupper(substr($req->user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size: 0.9rem;">{{ $req->user->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span style="font-size: 0.85rem;">{{ $req->type_label }}</span></td>
                        <td>
                            <div style="font-size: 0.85rem;">{{ $req->start_date->format('d M Y') }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">s.d. {{ $req->end_date->format('d M Y') }} &middot; {{ $req->total_days }} hari</div>
                        </td>
                        <td>
                            <span class="sh-badge sh-badge-pending">{{ $req->status_label }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="card-body d-md-none">
            @foreach($pendingRequests as $req)
            <div class="card sh-history-card mb-3" style="border-left-color: var(--sh-warning);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sh-user-avatar" style="width: 36px; height: 36px; font-size: 0.75rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($req->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.9rem;">{{ $req->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                            </div>
                        </div>
                        <span class="sh-badge sh-badge-pending">{{ $req->status_label }}</span>
                    </div>
                    <div class="row g-2 mb-2" style="font-size: 0.82rem;">
                        <div class="col-6">
                            <div class="text-muted">Jenis</div>
                            <div class="fw-semibold">{{ $req->type_label }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">Durasi</div>
                            <div class="fw-semibold">{{ $req->total_days }} hari</div>
                        </div>
                    </div>
                    <div class="mb-2" style="font-size: 0.82rem;">
                        <div class="text-muted">Periode</div>
                        <div class="fw-semibold">{{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }}</div>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                            <i class="ti ti-eye me-1"></i> Lihat
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Recent Decisions --}}
    @if($recentDecisions->isNotEmpty())
    <div class="card sh-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-history me-2" style="color: #64748b;"></i>
                Keputusan Terbaru
            </h3>
        </div>

        {{-- Desktop Table View --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table sh-table mb-0">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Jenis</th>
                        <th>Periode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentDecisions as $req)
                    <tr>
                        <td class="fw-semibold" style="font-size: 0.9rem;">{{ $req->user->name }}</td>
                        <td style="font-size: 0.85rem;">{{ $req->type_label }}</td>
                        <td style="font-size: 0.85rem;">{{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }}</td>
                        <td>
                            @if($req->isApproved())
                                <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @elseif($req->isRejected())
                                <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                            @else
                                <span class="sh-badge sh-badge-pending">{{ $req->status_label }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="card-body d-md-none">
            @foreach($recentDecisions as $req)
            <div class="card sh-history-card mb-3" style="border-left-color: #64748b;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;">{{ $req->user->name }}</div>
                        </div>
                        @if($req->isApproved())
                            <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i></span>
                        @elseif($req->isRejected())
                            <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i></span>
                        @else
                            <span class="sh-badge sh-badge-pending">{{ $req->status_label }}</span>
                        @endif
                    </div>
                    <div class="row g-2 mb-2" style="font-size: 0.82rem;">
                        <div class="col-6">
                            <div class="text-muted">Jenis</div>
                            <div class="fw-semibold">{{ $req->type_label }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">Status</div>
                            <div class="fw-semibold">
                                @if($req->isApproved())
                                    Disetujui
                                @elseif($req->isRejected())
                                    Ditolak
                                @else
                                    {{ $req->status_label }}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div style="font-size: 0.82rem;">
                        <div class="text-muted">Periode</div>
                        <div class="fw-semibold">{{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif


@elseif($user->isKetua())
    {{-- ============================= --}}
    {{--       KETUA DASHBOARD         --}}
    {{-- ============================= --}}

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 animate-in">
            <div class="card sh-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Perlu Keputusan</div>
                            <div class="sh-stat-number" style="color: var(--sh-warning);">{{ $needsDecision->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">menunggu keputusan Anda</div>
                        </div>
                        <div class="sh-stat-icon icon-warning">
                            <i class="ti ti-gavel"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 animate-in">
            <div class="card sh-stat-card stat-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Keputusan Saya</div>
                            <div class="sh-stat-number" style="color: var(--sh-success);">{{ $recentDecisions->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">total keputusan</div>
                        </div>
                        <div class="sh-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Needs Decision (already reviewed by atasan) --}}
    <div class="card sh-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-gavel me-2" style="color: var(--sh-warning);"></i>
                Menunggu Keputusan Anda
            </h3>
            @if($needsDecision->isNotEmpty())
            <span class="sh-badge sh-badge-pending">{{ $needsDecision->count() }} antrian</span>
            @endif
        </div>
        @if($needsDecision->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sh-empty-icon"><i class="ti ti-mood-happy"></i></div>
                <h4 class="fw-bold text-dark mb-1">Tidak Ada Antrian</h4>
                <p class="text-muted mb-0">Tidak ada pengajuan yang menunggu keputusan Anda saat ini.</p>
            </div>
        </div>
        @else
        <div class="card-body p-3">
            @foreach($needsDecision as $req)
            <div class="card sh-history-card status-{{ $req->status }} mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sh-user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($req->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.95rem;">{{ $req->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                            </div>
                        </div>
                        <span class="sh-badge sh-badge-pending">{{ $req->type_label }}</span>
                    </div>
                    <div class="row g-2 mb-2" style="font-size: 0.82rem;">
                        <div class="col-sm-6">
                            <i class="ti ti-calendar me-1 text-muted"></i>
                            {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                        </div>
                        <div class="col-sm-6">
                            <i class="ti ti-clock me-1 text-muted"></i>
                            {{ $req->total_days }} hari
                            @if($req->total_hari_kerja)
                                ({{ $req->total_hari_kerja }} hari kerja)
                            @endif
                        </div>
                    </div>
                    @if($req->atasanReviewer)
                    <div class="mb-2" style="background: var(--sh-primary-light); border-radius: 8px; padding: 0.5rem 0.75rem; font-size: 0.82rem;">
                        <i class="ti ti-user-check me-1" style="color: var(--sh-primary);"></i>
                        <strong>Pertimbangan {{ $req->atasanReviewer->name }}:</strong>
                        @if($req->pertimbangan_atasan === 'setuju')
                            <span style="color: var(--sh-success);">Disetujui</span>
                        @elseif($req->pertimbangan_atasan === 'ubah')
                            <span style="color: var(--sh-primary);">Perubahan</span>
                        @elseif($req->pertimbangan_atasan === 'tangguhkan')
                            <span style="color: var(--sh-warning);">Ditangguhkan</span>
                        @endif
                        @if($req->catatan_atasan)
                            &mdash; {{ Str::limit($req->catatan_atasan, 80) }}
                        @endif
                    </div>
                    @else
                    <div class="mb-2" style="background: #ecfdf5; border-radius: 8px; padding: 0.5rem 0.75rem; font-size: 0.82rem;">
                        <i class="ti ti-arrow-forward me-1" style="color: var(--sh-success);"></i>
                        <strong>Pengajuan Langsung</strong> &mdash; tanpa pertimbangan atasan
                    </div>
                    @endif
                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-sm sh-btn-primary flex-fill" data-bs-toggle="modal" data-bs-target="#decisionModal{{ $req->id }}">
                            <i class="ti ti-gavel me-1"></i> Beri Keputusan
                        </button>
                        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @include('partials.pejabat-decision-modal', ['req' => $req])
            @endforeach
        </div>
        @endif
    </div>

    {{-- Ketua's Own Leave --}}
    @if(isset($leaveRequests) && $leaveRequests->isNotEmpty())
    <div class="card sh-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-list-details me-2" style="color: #64748b;"></i>
                Pengajuan Cuti Saya
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table sh-table mb-0">
                <thead>
                    <tr><th>Jenis</th><th>Periode</th><th>Durasi</th><th>Status</th></tr>
                </thead>
                <tbody>
                @foreach($leaveRequests as $req)
                    <tr>
                        <td style="font-size: 0.85rem;">{{ $req->type_label }}</td>
                        <td style="font-size: 0.85rem;">{{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }}</td>
                        <td><span class="badge bg-blue-lt" style="border-radius: 50px;">{{ $req->total_days }} hari</span></td>
                        <td>
                            @if($req->isApproved())
                                <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @elseif($req->isRejected())
                                <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                            @else
                                <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

@elseif($user->isAtasan())
    {{-- ============================= --}}
    {{--      ATASAN DASHBOARD         --}}
    {{-- ============================= --}}

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Perlu Pertimbangan</div>
                            <div class="sh-stat-number" style="color: var(--sh-warning);">{{ $pendingReview->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">dari bawahan Anda</div>
                        </div>
                        <div class="sh-stat-icon icon-warning">
                            <i class="ti ti-checklist"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Sudah Dipertimbangkan</div>
                            <div class="sh-stat-number" style="color: var(--sh-success);">{{ $reviewedByMe->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">riwayat pertimbangan</div>
                        </div>
                        <div class="sh-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-lg-4 animate-in">
            <div class="card sh-stat-card stat-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-2">Cuti Saya</div>
                            <div class="sh-stat-number" style="color: var(--sh-primary);">{{ $user->leave_balance }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">hari sisa cuti</div>
                        </div>
                        <div class="sh-stat-icon icon-primary">
                            <i class="ti ti-calendar-stats"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Review --}}
    <div class="card sh-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-checklist me-2" style="color: var(--sh-warning);"></i>
                Menunggu Pertimbangan Anda
            </h3>
            @if($pendingReview->isNotEmpty())
            <span class="sh-badge sh-badge-pending">{{ $pendingReview->count() }} antrian</span>
            @endif
        </div>
        @if($pendingReview->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sh-empty-icon"><i class="ti ti-mood-happy"></i></div>
                <h4 class="fw-bold text-dark mb-1">Tidak Ada Antrian</h4>
                <p class="text-muted mb-0">Tidak ada pengajuan bawahan yang menunggu pertimbangan Anda.</p>
            </div>
        </div>
        @else
        <div class="card-body p-3">
            @foreach($pendingReview as $req)
            <div class="card sh-history-card status-{{ $req->status }} mb-3" data-request-id="{{ $req->id }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sh-user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--sh-primary-light); color: var(--sh-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($req->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.95rem;">{{ $req->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                            </div>
                        </div>
                        <span class="sh-badge sh-badge-pending">{{ $req->type_label }}</span>
                    </div>
                    <div class="row g-2 mb-2" style="font-size: 0.82rem;">
                        <div class="col-sm-6">
                            <i class="ti ti-calendar me-1 text-muted"></i>
                            {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                        </div>
                        <div class="col-sm-6">
                            <i class="ti ti-clock me-1 text-muted"></i>
                            {{ $req->total_days }} hari
                        </div>
                    </div>
                    @if($req->reason)
                    <div class="mb-2" style="font-size: 0.82rem; color: #475569;">
                        {{ Str::limit($req->reason, 100) }}
                    </div>
                    @endif
                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-sm sh-btn-primary flex-fill" onclick="openReviewModal{{ $req->id }}()">
                            <i class="ti ti-checklist me-1"></i> Beri Pertimbangan
                        </button>
                        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @include('partials.atasan-review-modal', ['req' => $req])
            @endforeach
        </div>
        @endif
    </div>

    {{-- Reviewed by Me --}}
    @if($reviewedByMe->isNotEmpty())
    <div class="card sh-card mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-history me-2" style="color: #64748b;"></i>
                Riwayat Pertimbangan Saya
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table sh-table mb-0">
                <thead>
                    <tr><th>Pemohon</th><th>Jenis</th><th>Periode</th><th>Pertimbangan</th><th>Status Akhir</th></tr>
                </thead>
                <tbody>
                @foreach($reviewedByMe as $req)
                    <tr>
                        <td class="fw-semibold" style="font-size: 0.88rem;">{{ $req->user->name }}</td>
                        <td style="font-size: 0.85rem;">{{ $req->type_label }}</td>
                        <td style="font-size: 0.82rem;">{{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }}</td>
                        <td>
                            @if($req->pertimbangan_atasan === 'setuju')
                                <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Setuju</span>
                            @elseif($req->pertimbangan_atasan === 'ubah')
                                <span class="sh-badge" style="background: var(--sh-primary-light); color: var(--sh-primary);"><i class="ti ti-edit"></i> Ubah</span>
                            @elseif($req->pertimbangan_atasan === 'tangguhkan')
                                <span class="sh-badge sh-badge-pending"><i class="ti ti-clock-pause"></i> Tangguhkan</span>
                            @elseif($req->pertimbangan_atasan === 'tolak')
                                <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Tolak</span>
                            @endif
                        </td>
                        <td>
                            @if($req->isApproved())
                                <span class="sh-badge sh-badge-approved">Disetujui</span>
                            @elseif($req->isRejected())
                                <span class="sh-badge sh-badge-rejected">Ditolak</span>
                            @else
                                <span class="sh-badge sh-badge-pending">{{ $req->status_label }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Atasan's Own Leave --}}
    @if(isset($leaveRequests) && $leaveRequests->isNotEmpty())
    <div class="card sh-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-list-details me-2" style="color: var(--sh-primary);"></i>
                Riwayat Cuti Saya
            </h3>
        </div>
        <div class="card-body p-3">
            @foreach($leaveRequests as $req)
            <div class="card sh-history-card status-{{ $req->status }} mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="fw-bold" style="font-size: 0.9rem;">{{ $req->type_label }}</span>
                            <div class="text-muted" style="font-size: 0.78rem;">
                                {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }} &middot; {{ $req->total_days }} hari
                            </div>
                        </div>
                        @if($req->isApproved())
                            <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                        @elseif($req->isRejected())
                            <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                        @else
                            <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

@else
    {{-- ============================= --}}
    {{--      PEGAWAI DASHBOARD        --}}
    {{-- ============================= --}}

    {{-- Hero Balance Card --}}
    <div class="card sh-hero-balance mb-4 animate-in">
        <div class="card-body p-3 p-md-4">
            <div class="hero-content">
                <div class="row align-items-center">
                    <div class="col">
                        @if($cutiInfo)
                        {{-- User sudah bekerja ≥ 1 tahun, cutiInfo tersedia --}}
                        <div style="font-size: 0.82rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 0.5rem;">
                            <i class="ti ti-calendar-stats me-1"></i> Sisa Cuti Tahunan
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <span class="sh-hero-number">{{ $cutiInfo['sisa_cuti'] ?? $cutiInfo['sisa'] ?? $user->leave_balance }}</span>
                            <span style="font-size: 1.1rem; opacity: 0.8;">/ {{ $cutiInfo['total_hak'] ?? 12 }} hari</span>
                        </div>
                        <div class="sh-hero-progress mb-2" style="max-width: 280px;">
                            @php $hakTotal = $cutiInfo['total_hak'] ?? 12; $hakTotal = $hakTotal > 0 ? $hakTotal : 1; @endphp
                            <div class="sh-hero-progress-bar" style="width: {{ min(100, (($cutiInfo['sisa_cuti'] ?? $cutiInfo['sisa'] ?? $user->leave_balance) / $hakTotal) * 100) }}%;"></div>
                        </div>
                        <div style="font-size: 0.8rem; opacity: 0.75;" class="mb-3">
                            Hak: {{ $cutiInfo['hak_cuti'] ?? $cutiInfo['hak_dasar'] ?? 12 }} hari
                            @if(($cutiInfo['carry_over'] ?? 0) > 0) + Carry Over: {{ $cutiInfo['carry_over'] }} hari @endif
                            @if(($cutiInfo['tambahan_terpencil'] ?? 0) > 0) + Terpencil: {{ $cutiInfo['tambahan_terpencil'] }} hari @endif
                            &mdash; Terpakai: {{ $cutiInfo['cuti_diambil'] ?? 0 }} hari
                        </div>
                        @elseif(!$user->sudahBekerjaSatuTahun())
                        {{-- Belum bekerja 1 tahun — tampilkan status jelas tanpa angka menyesatkan --}}
                        <div style="font-size: 0.82rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 0.75rem;">
                            <i class="ti ti-calendar-stats me-1"></i> Status Cuti Tahunan
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width:52px;height:52px;border-radius:12px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="ti ti-hourglass-high" style="font-size:1.6rem;"></i>
                            </div>
                            <div>
                                <div style="font-size:1.05rem;font-weight:700;margin-bottom:0.2rem;">Belum Berhak Cuti Tahunan</div>
                                <div style="font-size:0.8rem;opacity:0.75;">
                                    Masa kerja saat ini: {{ $user->masaKerjaFormat ?? '-' }}.
                                    Diperlukan minimal 1 tahun.
                                </div>
                            </div>
                        </div>
                        @else
                        {{-- Sudah 1 tahun tapi cutiInfo null (fallback) --}}
                        <div style="font-size: 0.82rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 0.5rem;">
                            <i class="ti ti-calendar-stats me-1"></i> Sisa Cuti Tahunan
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <span class="sh-hero-number">{{ $user->leave_balance }}</span>
                            <span style="font-size: 1.1rem; opacity: 0.8;">/ 12 hari</span>
                        </div>
                        <div class="sh-hero-progress mb-2" style="max-width: 280px;">
                            <div class="sh-hero-progress-bar" style="width: {{ ($user->leave_balance / 12) * 100 }}%;"></div>
                        </div>
                        <div style="font-size: 0.8rem; opacity: 0.75;" class="mb-3">
                            Terpakai {{ 12 - $user->leave_balance }} hari dari 12 hari
                        </div>
                        @endif

                        {{-- CTA: Ajukan Cuti --}}
                        @if($user->bolehCuti())
                        <a href="{{ route('leave.create') }}"
                           class="d-inline-flex align-items-center gap-2 d-block d-sm-inline-flex"
                           style="background:rgba(255,255,255,0.2);color:#fff;border:2px solid rgba(255,255,255,0.45);border-radius:10px;font-weight:700;font-size:0.88rem;padding:0.5rem 1.25rem;text-decoration:none;transition:all 0.2s;max-width:100%;"
                           onmouseover="this.style.background='rgba(255,255,255,0.32)'"
                           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                            <i class="ti ti-file-plus"></i>
                            <span>Ajukan Cuti</span>
                        </a>
                        @endif
                    </div>
                    <div class="col-auto d-none d-sm-block">
                        <i class="ti ti-beach" style="font-size: 5rem; opacity: 0.15;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mini Stats --}}
    <div class="row g-2 g-md-3 mb-4 sh-pegawai-stats">
        <div class="col-4 animate-in">
            <div class="card sh-stat-card stat-warning h-100">
                <div class="card-body p-2 p-md-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-1" style="font-size:clamp(0.62rem,2vw,0.78rem);">Diproses</div>
                            <div class="sh-stat-number" style="color:var(--sh-warning);font-size:clamp(1.3rem,5vw,2rem);">{{ $leaveRequests->filter(fn($r) => $r->isPending())->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-warning d-none d-sm-flex">
                            <i class="ti ti-clock-hour-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4 animate-in">
            <div class="card sh-stat-card stat-success h-100">
                <div class="card-body p-2 p-md-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-1" style="font-size:clamp(0.62rem,2vw,0.78rem);">Disetujui</div>
                            <div class="sh-stat-number" style="color:var(--sh-success);font-size:clamp(1.3rem,5vw,2rem);">{{ $leaveRequests->filter(fn($r) => $r->isApproved())->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-success d-none d-sm-flex">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4 animate-in">
            <div class="card sh-stat-card stat-danger h-100">
                <div class="card-body p-2 p-md-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sh-stat-label mb-1" style="font-size:clamp(0.62rem,2vw,0.78rem);">Ditolak</div>
                            <div class="sh-stat-number" style="color:var(--sh-danger);font-size:clamp(1.3rem,5vw,2rem);">{{ $leaveRequests->filter(fn($r) => $r->isRejected())->count() }}</div>
                        </div>
                        <div class="sh-stat-icon icon-danger d-none d-sm-flex">
                            <i class="ti ti-circle-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Widget: Siapa yang Cuti & Dinas Luar Hari Ini --}}
    <div class="row g-3 mb-4">
        {{-- Cuti Hari Ini --}}
        <div class="col-md-6 animate-in">
            <div class="card sh-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0" style="font-size:0.9rem;">
                        <i class="ti ti-beach me-2" style="color:var(--sh-success);"></i>
                        Cuti Hari Ini
                    </h3>
                    <span class="sh-badge sh-badge-{{ $todayOnLeave->isNotEmpty() ? 'pending' : 'approved' }}">
                        {{ $todayOnLeave->count() }} pegawai
                    </span>
                </div>
                <div class="card-body p-3" style="max-height:200px;overflow-y:auto;">
                    @if($todayOnLeave->isEmpty())
                    <div class="text-center py-3">
                        <i class="ti ti-users" style="font-size:1.8rem;color:var(--sh-text-muted);opacity:0.3;"></i>
                        <p class="text-muted mb-0 mt-2" style="font-size:0.82rem;">Tidak ada pegawai yang cuti hari ini</p>
                    </div>
                    @else
                    @foreach($todayOnLeave as $req)
                    <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sh-user-avatar" style="width:30px;height:30px;font-size:0.65rem;background:var(--sh-primary-light);color:var(--sh-primary);border:none;border-radius:7px;flex-shrink:0;">
                            {{ strtoupper(substr($req->user->name, 0, 2)) }}
                        </div>
                        <div class="flex-fill" style="min-width:0;">
                            <div class="fw-semibold" style="font-size:0.82rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req->user->name }}</div>
                            <div class="text-muted" style="font-size:0.72rem;">{{ $req->type_label }} &bull; s.d. {{ $req->end_date->format('d M') }}</div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- Dinas Luar Hari Ini --}}
        <div class="col-md-6 animate-in">
            <div class="card sh-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0" style="font-size:0.9rem;">
                        <i class="ti ti-map-pin me-2" style="color:var(--sh-accent);"></i>
                        Dinas Luar Hari Ini
                    </h3>
                    <span class="sh-badge" style="background:var(--sh-accent-light);color:var(--sh-accent);">
                        {{ $todayDinasLuar->count() }} pegawai
                    </span>
                </div>
                <div class="card-body p-3" style="max-height:200px;overflow-y:auto;">
                    @if($todayDinasLuar->isEmpty())
                    <div class="text-center py-3">
                        <i class="ti ti-briefcase" style="font-size:1.8rem;color:var(--sh-text-muted);opacity:0.3;"></i>
                        <p class="text-muted mb-0 mt-2" style="font-size:0.82rem;">Tidak ada pegawai dinas luar hari ini</p>
                    </div>
                    @else
                    @foreach($todayDinasLuar as $dl)
                    <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sh-user-avatar" style="width:30px;height:30px;font-size:0.65rem;background:var(--sh-accent-light);color:var(--sh-accent);border:none;border-radius:7px;flex-shrink:0;">
                            {{ strtoupper(substr($dl->user->name, 0, 2)) }}
                        </div>
                        <div class="flex-fill" style="min-width:0;">
                            <div class="fw-semibold" style="font-size:0.82rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $dl->user->name }}</div>
                            <div class="text-muted" style="font-size:0.72rem;">{{ Str::limit($dl->tujuan, 30) }} &bull; s.d. {{ $dl->end_date->format('d M') }}</div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Fitur 6: Search & Filter --}}
    <div class="card sh-card mb-4 animate-in">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('dashboard') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                            <i class="ti ti-search me-1" style="color: var(--sh-primary);"></i> Cari Alasan
                        </label>
                        <input type="text" name="search" class="form-control" placeholder="Cari alasan cuti..."
                               value="{{ request('search') }}"
                               style="border-radius: 10px; border: 2px solid #e2e8f0; height: 42px;">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                            <i class="ti ti-category me-1" style="color: var(--sh-primary);"></i> Jenis
                        </label>
                        <select name="type" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0; height: 42px;">
                            <option value="">Semua Jenis</option>
                            @foreach(\App\Models\LeaveRequest::typeLabels() as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                            <i class="ti ti-filter me-1" style="color: var(--sh-primary);"></i> Status
                        </label>
                        <select name="status" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0; height: 42px;">
                            <option value="">Semua</option>
                            <option value="diajukan" {{ request('status') === 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                            <option value="pertimbangan_atasan" {{ request('status') === 'pertimbangan_atasan' ? 'selected' : '' }}>Pertimbangan</option>
                            <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary sh-btn-primary flex-fill" style="height: 42px;">
                                <i class="ti ti-search me-1"></i> Filter
                            </button>
                            @if(request()->hasAny(['search', 'type', 'status']))
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" style="border-radius: 10px; height: 42px; display: flex; align-items: center; justify-content: center;" title="Reset">
                                <i class="ti ti-x"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Leave History --}}
    <div class="card sh-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-list-details me-2" style="color: var(--sh-primary);"></i>
                Riwayat Pengajuan Cuti
            </h3>
            <span class="text-muted" style="font-size: 0.8rem;">{{ $leaveRequests->count() }} total</span>
        </div>

        @if($leaveRequests->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sh-empty-icon"><i class="ti ti-calendar-off"></i></div>
                <h4 class="fw-bold text-dark mb-1">Belum Ada Pengajuan</h4>
                @if($user->bolehCuti())
                <p class="text-muted mb-3">Anda belum pernah mengajukan cuti. Mulai dengan klik tombol di bawah.</p>
                <a href="{{ route('leave.select-type') }}" class="btn btn-primary sh-btn-primary">
                    <i class="ti ti-file-plus me-1"></i> Ajukan Cuti Pertama Anda
                </a>
                @else
                <p class="text-muted mb-3">
                    @if($user->status_pegawai === 'cpns')
                        CPNS belum berhak mengajukan cuti.
                    @else
                        PPPK yang baru dilantik (masa kerja &lt; 1 tahun) belum berhak mengajukan cuti.
                    @endif
                </p>
                @endif
            </div>
        </div>
        @else

        {{-- Mobile: cards --}}
        <div class="card-body d-md-none">
            @foreach($leaveRequests as $req)
            <div class="card sh-history-card status-{{ $req->status }} mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;">
                                {{ $req->type_label }}
                            </div>
                            <div class="text-muted" style="font-size: 0.78rem;">
                                <i class="ti ti-calendar me-1"></i>
                                {{ $req->start_date->format('d M Y') }} s.d. {{ $req->end_date->format('d M Y') }} &middot; {{ $req->total_days }} hari
                            </div>
                        </div>
                        @if($req->isApproved())
                            <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                        @elseif($req->isRejected())
                            <span class="sh-badge sh-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                        @else
                            <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
                        @endif
                    </div>

                    {{-- Show rejection reason if rejected --}}
                    @if($req->isRejected() && ($req->catatan_atasan || $req->catatan_pejabat))
                    <div class="alert mb-2" style="background: var(--sh-danger-light); color: var(--sh-danger); border: 1px solid rgba(220, 38, 38, 0.2); border-radius: 8px; padding: 0.75rem; font-size: 0.85rem;">
                        <div class="d-flex gap-2">
                            <i class="ti ti-alert-circle" style="flex-shrink: 0; margin-top: 2px;"></i>
                            <div>
                                <div class="fw-semibold mb-1">Alasan Penolakan:</div>
                                <div>{{ $req->catatan_pejabat ?? $req->catatan_atasan }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($req->reason)
                    <div style="font-size: 0.85rem; color: #475569;">{{ Str::limit($req->reason, 80) }}</div>
                    @endif
                    <div class="mt-2">
                        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.78rem;">
                            <i class="ti ti-eye me-1"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Desktop: table --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table sh-table mb-0">
                <thead>
                    <tr>
                        <th>Jenis Cuti</th>
                        <th>Periode</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($leaveRequests as $req)
                    <tr>
                        <td>
                            <span class="fw-semibold" style="font-size: 0.88rem;">{{ $req->type_label }}</span>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">{{ $req->start_date->format('d M Y') }}</div>
                            <div class="text-muted" style="font-size: 0.78rem;">s.d. {{ $req->end_date->format('d M Y') }}</div>
                        </td>
                        <td>
                            <span class="badge bg-blue-lt" style="border-radius: 50px;">{{ $req->total_days }} hari</span>
                        </td>
                        <td>
                            @if($req->isApproved())
                                <span class="sh-badge sh-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @elseif($req->isRejected())
                                <div>
                                    <span class="sh-badge sh-badge-rejected" title="{{ $req->catatan_pejabat ?? $req->catatan_atasan ?? 'Tidak ada catatan' }}" style="cursor: help;"><i class="ti ti-circle-x"></i> Ditolak</span>
                                    @if($req->catatan_pejabat || $req->catatan_atasan)
                                    <div class="small text-muted mt-1" style="font-size: 0.75rem; max-width: 200px; white-space: normal;">
                                        <i class="ti ti-info-circle" style="font-size: 0.7rem;"></i> {{ Str::limit($req->catatan_pejabat ?? $req->catatan_atasan, 60) }}
                                    </div>
                                    @endif
                                </div>
                            @else
                                <span class="sh-badge sh-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endif

@if($user->isAdmin())
@push('scripts')
<style>
.sh-cal-day-cell[title]:hover { background: var(--sh-primary-light) !important; }
[data-bs-theme="dark"] .sh-cal-day-cell[title]:hover { background: rgba(34,197,94,0.12) !important; }
</style>
<script>
// #12 Count-up animation for stat numbers
(function() {
    function countUp(el, target, duration) {
        var start = 0;
        var startTime = null;
        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(start + (target - start) * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.sh-stat-number').forEach(function(el) {
            var val = parseInt(el.textContent.trim());
            if (!isNaN(val) && val > 0) {
                el.textContent = '0';
                countUp(el, val, 900);
            }
        });
    });
})();

</script>
@endpush
@endif
@endsection
