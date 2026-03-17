@extends('layouts.app')

@section('title', 'Dashboard - SiCAIR')

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
<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sc-page-title mb-1">
                <i class="ti ti-layout-dashboard me-1" style="color: var(--sc-primary);"></i>
                Dashboard
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                <i class="ti ti-{{ $greetingIcon }} me-1" style="color:var(--sc-accent);"></i>
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
                <span class="sc-badge sc-badge-{{ $roleBadge }} ms-1" style="font-size: 0.7rem;">
                    <i class="ti ti-{{ $roleIcon }}"></i>
                    {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                </span>
            </div>
        </div>
        @if(!$user->isAdmin() && $user->bolehCuti())
        <a href="{{ route('leave.select-type') }}" class="btn btn-primary sc-btn-primary">
            <i class="ti ti-file-plus me-1"></i> Ajukan Cuti
        </a>
        @endif
        @if(config('whatsapp.enabled') && config('whatsapp.bot_number'))
        <a href="https://wa.me/{{ config('whatsapp.bot_number') }}?text=BANTUAN"
           target="_blank" rel="noopener"
           class="btn btn-success"
           style="background:#25d366; border-color:#25d366; display:inline-flex; align-items:center; gap:6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
            </svg>
            WA Bot
        </a>
        @endif
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

@if($user->isAdmin())
    {{-- ============================= --}}
    {{--       ADMIN DASHBOARD         --}}
    {{-- ============================= --}}

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sc-stat-card stat-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-2">Total Pegawai</div>
                            <div class="sc-stat-number" style="color: var(--sc-primary);">{{ $totalPegawai }}</div>
                        </div>
                        <div class="sc-stat-icon icon-primary">
                            <i class="ti ti-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sc-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-2">Menunggu Proses</div>
                            <div class="sc-stat-number" id="stat-pending" style="color: var(--sc-warning);">{{ $pendingRequests->count() }}</div>
                        </div>
                        <div class="sc-stat-icon icon-warning">
                            <i class="ti ti-clock-hour-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sc-stat-card stat-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-2">Disetujui</div>
                            <div class="sc-stat-number" id="stat-approved" style="color: var(--sc-success);">{{ $recentDecisions->filter(fn($r) => $r->isApproved())->count() }}</div>
                        </div>
                        <div class="sc-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 animate-in">
            <div class="card sc-stat-card stat-danger">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-2">Ditolak</div>
                            <div class="sc-stat-number" style="color: var(--sc-danger);">{{ $recentDecisions->filter(fn($r) => $r->isRejected())->count() }}</div>
                        </div>
                        <div class="sc-stat-icon icon-danger">
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
            <div class="card sc-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-beach me-2" style="color: var(--sc-success);"></i>
                        Cuti Hari Ini
                    </h3>
                    <span class="sc-badge sc-badge-{{ $todayOnLeave->isNotEmpty() ? 'pending' : 'approved' }}">
                        <span id="stat-on-leave">{{ $todayOnLeave->count() }}</span> pegawai
                    </span>
                </div>
                <div class="card-body p-3" style="max-height: 220px; overflow-y: auto;">
                    @if($todayOnLeave->isEmpty())
                    <div class="text-center py-3">
                        <i class="ti ti-users" style="font-size: 2rem; color: var(--sc-text-muted); opacity: 0.3;"></i>
                        <p class="text-muted mb-0 mt-2" style="font-size: 0.85rem;">Tidak ada pegawai yang cuti hari ini</p>
                    </div>
                    @else
                    @foreach($todayOnLeave as $req)
                    <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sc-user-avatar" style="width:32px;height:32px;font-size:0.7rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:8px;flex-shrink:0;">
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
            <div class="card sc-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-calendar-event me-2" style="color: var(--sc-accent);"></i>
                        Cuti Mendatang (7 Hari)
                    </h3>
                    <span class="sc-badge sc-badge-pending">{{ $upcomingLeaves7Days->count() }}</span>
                </div>
                <div class="card-body p-3" style="max-height: 220px; overflow-y: auto;">
                    @if($upcomingLeaves7Days->isEmpty())
                    <div class="text-center py-3">
                        <i class="ti ti-calendar" style="font-size: 2rem; color: var(--sc-text-muted); opacity: 0.3;"></i>
                        <p class="text-muted mb-0 mt-2" style="font-size: 0.85rem;">Tidak ada cuti disetujui dalam 7 hari ke depan</p>
                    </div>
                    @else
                    @foreach($upcomingLeaves7Days as $req)
                    <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sc-user-avatar" style="width:32px;height:32px;font-size:0.7rem;background:var(--sc-accent-light);color:var(--sc-accent);border:none;border-radius:8px;flex-shrink:0;">
                            {{ strtoupper(substr($req->user->name, 0, 2)) }}
                        </div>
                        <div class="flex-fill" style="min-width:0;">
                            <div class="fw-semibold" style="font-size:0.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req->user->name }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $req->start_date->format('d M') }} &bull; {{ $req->type_label }}</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge" style="background:var(--sc-accent-light);color:var(--sc-accent);border-radius:50px;font-size:0.7rem;">{{ $req->start_date->diffInDays(\Carbon\Carbon::today()) }}h lagi</span>
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
            <div class="card sc-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0" style="font-size:0.9rem;">
                        <i class="ti ti-alert-triangle me-2" style="color:var(--sc-warning);"></i>
                        Saldo Cuti Rendah
                    </h3>
                    <span class="sc-badge" style="background:var(--sc-warning-light);color:var(--sc-warning);">≤ 3 hari</span>
                </div>
                <div class="card-body p-3">
                    @foreach($saldoRendah as $p)
                    <div class="d-flex align-items-center gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sc-user-avatar" style="width:28px;height:28px;font-size:0.65rem;background:var(--sc-warning-light);color:var(--sc-warning);border:none;border-radius:6px;flex-shrink:0;">{{ strtoupper(substr($p->name,0,2)) }}</div>
                        <div class="flex-fill" style="min-width:0;">
                            <div style="font-size:0.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p->name }}</div>
                        </div>
                        <span class="fw-bold" style="color:var(--sc-warning);font-size:0.82rem;">{{ $p->leave_balance }}h</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- #19: Top 5 Paling Banyak Cuti --}}
        @if($top5Cuti->isNotEmpty())
        <div class="col-md-4 animate-in">
            <div class="card sc-card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0" style="font-size:0.9rem;">
                        <i class="ti ti-trophy me-2" style="color:var(--sc-accent);"></i>
                        Top 5 Penggunaan Cuti {{ $year }}
                    </h3>
                </div>
                <div class="card-body p-3">
                    @foreach($top5Cuti as $i => $p)
                    <div class="d-flex align-items-center gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <span style="font-size:0.75rem;font-weight:700;color:var(--sc-text-muted);width:16px;flex-shrink:0;">{{ $i+1 }}</span>
                        <div class="flex-fill" style="min-width:0;">
                            <div style="font-size:0.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p->name }}</div>
                        </div>
                        <span class="fw-bold" style="color:var(--sc-primary);font-size:0.82rem;">{{ $p->total_hari }}h</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- #21: Recent Activity Feed --}}
        <div class="col-md-4 animate-in">
            <div class="card sc-card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0" style="font-size:0.9rem;">
                        <i class="ti ti-activity me-2" style="color:var(--sc-primary);"></i>
                        Aktivitas Terbaru
                    </h3>
                </div>
                <div class="card-body p-3">
                    @forelse($recentActivity as $act)
                    <div class="d-flex align-items-start gap-2 py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sc-user-avatar" style="width:26px;height:26px;font-size:0.6rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:6px;flex-shrink:0;margin-top:2px;">{{ strtoupper(substr($act->user->name,0,2)) }}</div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.8rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <strong>{{ $act->user->name }}</strong>
                                {{ $act->status === 'diajukan' ? 'mengajukan' : ($act->status === 'disetujui' ? 'cuti disetujui' : ($act->status === 'ditolak' ? 'cuti ditolak' : 'update')) }}
                                <span style="color:var(--sc-primary);">{{ $act->type_label }}</span>
                            </div>
                            <div class="text-muted" style="font-size:0.72rem;">{{ $act->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="ti ti-calendar-off" style="font-size:1.8rem;opacity:0.3;color:var(--sc-primary);"></i>
                        <div class="text-muted mt-1" style="font-size:0.8rem;">Belum ada aktivitas terbaru.</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- #18: Trend Indicator on Stats (#18) - inline with stat cards --}}
    @if(isset($monthTrend))
    <div class="mb-4 p-3 sc-card" style="border-radius:12px;">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <i class="ti ti-trending-{{ $monthTrend['trending'] }}" style="font-size:1.5rem;color:{{ $monthTrend['trending'] === 'up' ? 'var(--sc-danger)' : 'var(--sc-success)' }};"></i>
            <div>
                <div class="fw-bold" style="font-size:0.9rem;">Trend Pengajuan Cuti</div>
                <div class="text-muted" style="font-size:0.82rem;">
                    Bulan ini: <strong>{{ $monthTrend['this_month'] }}</strong> cuti disetujui &bull;
                    Bulan lalu: <strong>{{ $monthTrend['last_month'] }}</strong> &bull;
                    <strong style="color:{{ $monthTrend['trending'] === 'up' ? 'var(--sc-danger)' : 'var(--sc-success)' }}">
                        {{ $monthTrend['diff_pct'] >= 0 ? '+' : '' }}{{ $monthTrend['diff_pct'] }}%
                    </strong>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- #17: Widget Carry-Over Akan Hangus (shown Oct-Dec) --}}
    @if(!empty($carryOverHangus) && $carryOverHangus->isNotEmpty())
    <div class="alert mb-4" style="background:var(--sc-warning-light);border:2px solid var(--sc-warning);border-radius:12px;">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="ti ti-clock-exclamation" style="color:var(--sc-warning);font-size:1.2rem;"></i>
            <strong style="color:var(--sc-warning);">Carry-Over Cuti Akan Hangus Akhir Tahun!</strong>
        </div>
        <div style="font-size:0.85rem;color:#78350f;">
            {{ $carryOverHangus->count() }} pegawai memiliki sisa carry-over dari tahun lalu yang belum digunakan:
            @foreach($carryOverHangus->take(5) as $co)
            <span class="sc-badge ms-1" style="background:#fef3c7;color:#92400e;">{{ $co->user->name ?? '-' }} ({{ $co->carry_over }}h)</span>
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
    <div class="card sc-card mb-4 animate-in">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0" style="font-size:0.9rem;">
                <i class="ti ti-calendar me-2" style="color:var(--sc-primary);"></i>
                {{ $calMonthNames[$calMonth] }} {{ $calYear }}
            </h3>
            <a href="{{ route('kalender', ['year' => $calYear, 'month' => $calMonth]) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:0.78rem;">
                <i class="ti ti-external-link me-1"></i> Buka Kalender
            </a>
        </div>
        <div class="card-body p-3">
            {{-- Legend --}}
            <div class="d-flex align-items-center gap-3 mb-2 flex-wrap" style="font-size:0.7rem;color:var(--sc-text-muted);">
                <span class="d-flex align-items-center gap-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:var(--sc-success);display:inline-block;"></span> Cuti pegawai
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
                <div class="col" style="font-size:0.7rem;font-weight:700;color:var(--sc-text-muted);text-transform:uppercase;">{{ $dn }}</div>
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
                            <div class="col d-flex flex-column align-items-center sc-cal-day-cell"
                                 style="cursor:{{ $isClickable ? 'pointer' : 'default' }};border-radius:6px;transition:background 0.15s;"
                                 @if($isClickable) onclick="window.location='{{ route('kalender', ['year'=>$calYear,'month'=>$calMonth]) }}'"
                                 title="{{ $tooltip }}" @endif>
                                <span style="width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:{{ $isToday ? '800' : '500' }};
                                    {{ $isToday ? 'background:var(--sc-primary);color:#fff;' : ($isRed ? 'color:#dc2626;' : 'color:var(--sc-text);') }}">
                                    {{ $dc }}
                                </span>
                                {{-- Dots row: leave (green), libur (red), bersama (gold) --}}
                                <span class="d-flex gap-1" style="height:6px;margin-top:1px;align-items:center;">
                                    @if($hasLeave)<span style="width:5px;height:5px;border-radius:50%;background:var(--sc-success);"></span>@endif
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
    <div class="card sc-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-clock-hour-4 me-2" style="color: var(--sc-warning);"></i>
                Pengajuan Menunggu Proses
            </h3>
            @if($pendingRequests->isNotEmpty())
            <span class="sc-badge sc-badge-pending">{{ $pendingRequests->count() }} antrian</span>
            @endif
        </div>

        @if($pendingRequests->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                {{-- #46 Empty state SVG: no-data --}}
                <svg width="88" height="88" viewBox="0 0 88 88" fill="none" xmlns="http://www.w3.org/2000/svg" class="mb-3" aria-hidden="true">
                    <circle cx="44" cy="44" r="40" fill="var(--sc-success-light)"/>
                    <path d="M28 44L38 54L60 32" stroke="var(--sc-success)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="44" cy="44" r="18" stroke="var(--sc-success)" stroke-width="2.5" fill="none" opacity="0.3"/>
                </svg>
                <h4 class="fw-bold text-dark mb-1">Semua Beres!</h4>
                <p class="text-muted mb-0">Tidak ada pengajuan cuti yang menunggu proses saat ini.</p>
            </div>
        </div>
        @else
        {{-- Desktop Table View --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table sc-table mb-0">
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
                                <div class="sc-user-avatar" style="width: 36px; height: 36px; font-size: 0.75rem; background: var(--sc-primary-light); color: var(--sc-primary); border: none; border-radius: 10px;">
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
                            @php
                                $bc = match(true) {
                                    $req->isApproved() => 'sc-badge-approved',
                                    $req->isRejected() => 'sc-badge-rejected',
                                    default            => 'sc-badge-pending',
                                };
                            @endphp
                            <span class="sc-badge {{ $bc }}">{{ $req->status_label }}</span>
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
            <div class="card sc-history-card mb-3" style="border-left-color: var(--sc-warning);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sc-user-avatar" style="width: 36px; height: 36px; font-size: 0.75rem; background: var(--sc-primary-light); color: var(--sc-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($req->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.9rem;">{{ $req->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                            </div>
                        </div>
                        @php
                            $bc2 = match(true) {
                                $req->isApproved() => 'sc-badge-approved',
                                $req->isRejected() => 'sc-badge-rejected',
                                default            => 'sc-badge-pending',
                            };
                        @endphp
                        <span class="sc-badge {{ $bc2 }}">{{ $req->status_label }}</span>
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
    <div class="card sc-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-history me-2" style="color: #64748b;"></i>
                Keputusan Terbaru
            </h3>
        </div>

        {{-- Desktop Table View --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table sc-table mb-0">
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
                                <span class="sc-badge sc-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @elseif($req->isRejected())
                                <span class="sc-badge sc-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                            @else
                                <span class="sc-badge sc-badge-pending">{{ $req->status_label }}</span>
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
            <div class="card sc-history-card mb-3" style="border-left-color: #64748b;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;">{{ $req->user->name }}</div>
                        </div>
                        @if($req->isApproved())
                            <span class="sc-badge sc-badge-approved"><i class="ti ti-circle-check"></i></span>
                        @elseif($req->isRejected())
                            <span class="sc-badge sc-badge-rejected"><i class="ti ti-circle-x"></i></span>
                        @else
                            <span class="sc-badge sc-badge-pending">{{ $req->status_label }}</span>
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
            <div class="card sc-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-2">Perlu Keputusan</div>
                            <div class="sc-stat-number" style="color: var(--sc-warning);">{{ $needsDecisionCount }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">menunggu keputusan Anda</div>
                        </div>
                        <div class="sc-stat-icon icon-warning">
                            <i class="ti ti-gavel"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 animate-in">
            <div class="card sc-stat-card stat-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-2">Keputusan Saya</div>
                            <div class="sc-stat-number" style="color: var(--sc-success);">{{ $recentDecisions->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">total keputusan</div>
                        </div>
                        <div class="sc-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Widget: Siapa yang cuti hari ini --}}
    @if(isset($todayOnLeave) && $todayOnLeave->count() > 0)
    <div class="card sc-card mb-4 animate-in">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3" style="font-size:0.9rem;">
                <i class="ti ti-user-off me-2" style="color:var(--sc-accent,#b8860b)"></i>
                Sedang Cuti Hari Ini
                <span class="badge ms-1" style="background:var(--sc-primary-light,#dcfce7);color:var(--sc-primary,#166534);font-size:0.75rem;border-radius:99px;">{{ $todayOnLeave->count() }}</span>
            </h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach($todayOnLeave as $c)
                @php
                    $initials = collect(explode(' ', $c->user->name ?? ''))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
                    $colors = ['#166534','#1d4ed8','#7c3aed','#b45309','#0f766e','#9f1239'];
                    $bg = $colors[abs(crc32($c->user->name ?? '')) % count($colors)];
                @endphp
                <div class="d-flex align-items-center gap-2 px-3 py-2" style="background:var(--sc-primary-light,#dcfce7);border-radius:99px;">
                    <span class="sc-avatar-initials" style="width:26px;height:26px;font-size:0.68rem;background:{{ $bg }};color:#fff;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-weight:700;flex-shrink:0;">{{ $initials }}</span>
                    <span style="font-size:0.83rem;font-weight:600;color:var(--sc-primary,#166534);">{{ $c->user->name }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Needs Decision (already reviewed by atasan) --}}
    <div class="card sc-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-gavel me-2" style="color: var(--sc-warning);"></i>
                Menunggu Keputusan Anda
            </h3>
            @if($needsDecisionCount > 0)
            <span class="sc-badge sc-badge-pending">{{ $needsDecisionCount }} antrian</span>
            @endif
            <a href="{{ route('keputusan.index') }}" class="btn btn-sm btn-outline-secondary ms-auto" style="border-radius:8px;font-size:0.8rem;">
                Lihat semua <i class="ti ti-arrow-right ms-1"></i>
            </a>
        </div>
        @if($needsDecision->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sc-empty-icon"><i class="ti ti-mood-happy"></i></div>
                <h4 class="fw-bold text-dark mb-1">Tidak Ada Antrian</h4>
                <p class="text-muted mb-0">Tidak ada pengajuan yang menunggu keputusan Anda saat ini.</p>
            </div>
        </div>
        @else
        <div class="card-body p-3">
            <form id="sc-bulk-form" method="POST" action="{{ route('leave.bulk-decide') }}">
                @csrf
                <div class="mb-3" id="sc-bulk-actions" style="display:none;">
                    <div class="p-3" style="background:var(--sc-primary-light,#dcfce7);border-radius:10px;">
                        <div class="d-flex gap-2 align-items-center flex-wrap mb-2">
                            <span class="text-muted small" id="sc-bulk-count">0 dipilih</span>
                            <select name="decision" class="form-select form-select-sm" style="width:auto;" required>
                                <option value="">-- Pilih Keputusan --</option>
                                <option value="setuju">Setujui Semua</option>
                                <option value="tolak">Tolak Semua</option>
                                <option value="tangguhkan">Tangguhkan Semua</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="ti ti-check me-1"></i> Terapkan
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="sc-bulk-clear">Batal</button>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold" style="font-size:0.83rem;">Catatan (opsional)</label>
                            <textarea name="catatan" class="form-control form-control-sm" rows="3"
                                placeholder="Catatan untuk semua pengajuan yang dipilih..."></textarea>
                            <div class="form-text" style="font-size:0.78rem;">Catatan ini akan berlaku untuk semua pengajuan yang dipilih.</div>
                        </div>
                    </div>
                </div>

            @foreach($needsDecision as $req)
            <div class="card sc-history-card status-{{ $req->status }} mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" name="ids[]" value="{{ $req->id }}" class="sc-bulk-cb form-check-input" style="width:18px;height:18px;flex-shrink:0;margin-top:0;">
                            <div class="sc-user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--sc-primary-light); color: var(--sc-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($req->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.95rem;">{{ $req->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                            </div>
                        </div>
                        <span class="sc-badge sc-badge-pending">{{ $req->type_label }}</span>
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
                    <div class="mb-2" style="background: var(--sc-primary-light); border-radius: 8px; padding: 0.5rem 0.75rem; font-size: 0.82rem;">
                        <i class="ti ti-user-check me-1" style="color: var(--sc-primary);"></i>
                        <strong>Pertimbangan {{ $req->atasanReviewer->name }}:</strong>
                        @if($req->pertimbangan_atasan === 'setuju')
                            <span style="color: var(--sc-success);">Disetujui</span>
                        @elseif($req->pertimbangan_atasan === 'ubah')
                            <span style="color: var(--sc-primary);">Perubahan</span>
                        @elseif($req->pertimbangan_atasan === 'tangguhkan')
                            <span style="color: var(--sc-warning);">Ditangguhkan</span>
                        @endif
                        @if($req->catatan_atasan)
                            &mdash; {{ Str::limit($req->catatan_atasan, 80) }}
                        @endif
                    </div>
                    @else
                    <div class="mb-2" style="background: #ecfdf5; border-radius: 8px; padding: 0.5rem 0.75rem; font-size: 0.82rem;">
                        <i class="ti ti-arrow-forward me-1" style="color: var(--sc-success);"></i>
                        <strong>Pengajuan Langsung</strong> &mdash; tanpa pertimbangan atasan
                    </div>
                    @endif
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm sc-btn-primary flex-fill" data-bs-toggle="modal" data-bs-target="#decisionModal{{ $req->id }}">
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
            </form>
        </div>
        @endif
    </div>

    <script>
    document.querySelectorAll('.sc-bulk-cb').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var checked = document.querySelectorAll('.sc-bulk-cb:checked').length;
            document.getElementById('sc-bulk-count').textContent = checked + ' dipilih';
            document.getElementById('sc-bulk-actions').style.display = checked > 0 ? 'flex' : 'none';
        });
    });
    document.getElementById('sc-bulk-clear')?.addEventListener('click', function() {
        document.querySelectorAll('.sc-bulk-cb').forEach(function(cb) { cb.checked = false; });
        document.getElementById('sc-bulk-actions').style.display = 'none';
        document.getElementById('sc-bulk-count').textContent = '0 dipilih';
    });
    </script>

    {{-- Ketua's Own Leave --}}
    @if(isset($leaveRequests) && $leaveRequests->isNotEmpty())
    <div class="card sc-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-list-details me-2" style="color: #64748b;"></i>
                Pengajuan Cuti Saya
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table sc-table mb-0">
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
                                <span class="sc-badge sc-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @elseif($req->isRejected())
                                <span class="sc-badge sc-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                            @else
                                <span class="sc-badge sc-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
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
            <div class="card sc-stat-card stat-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-2">Perlu Pertimbangan</div>
                            <div class="sc-stat-number" style="color: var(--sc-warning);">{{ $pendingReview->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">dari bawahan Anda</div>
                        </div>
                        <div class="sc-stat-icon icon-warning">
                            <i class="ti ti-checklist"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4 animate-in">
            <div class="card sc-stat-card stat-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-2">Sudah Dipertimbangkan</div>
                            <div class="sc-stat-number" style="color: var(--sc-success);">{{ $reviewedByMe->count() }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">riwayat pertimbangan</div>
                        </div>
                        <div class="sc-stat-icon icon-success">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-lg-4 animate-in">
            <div class="card sc-stat-card stat-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-2">Cuti Saya</div>
                            <div class="sc-stat-number" style="color: var(--sc-primary);">{{ $user->leave_balance }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.8rem;">hari sisa cuti</div>
                        </div>
                        <div class="sc-stat-icon icon-primary">
                            <i class="ti ti-calendar-stats"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Widget: Siapa yang cuti hari ini --}}
    @if(isset($todayOnLeave) && $todayOnLeave->count() > 0)
    <div class="card sc-card mb-4 animate-in">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3" style="font-size:0.9rem;">
                <i class="ti ti-user-off me-2" style="color:var(--sc-accent,#b8860b)"></i>
                Sedang Cuti Hari Ini
                <span class="badge ms-1" style="background:var(--sc-primary-light,#dcfce7);color:var(--sc-primary,#166534);font-size:0.75rem;border-radius:99px;">{{ $todayOnLeave->count() }}</span>
            </h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach($todayOnLeave as $c)
                @php
                    $initials = collect(explode(' ', $c->user->name ?? ''))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
                    $colors = ['#166534','#1d4ed8','#7c3aed','#b45309','#0f766e','#9f1239'];
                    $bg = $colors[abs(crc32($c->user->name ?? '')) % count($colors)];
                @endphp
                <div class="d-flex align-items-center gap-2 px-3 py-2" style="background:var(--sc-primary-light,#dcfce7);border-radius:99px;">
                    <span class="sc-avatar-initials" style="width:26px;height:26px;font-size:0.68rem;background:{{ $bg }};color:#fff;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-weight:700;flex-shrink:0;">{{ $initials }}</span>
                    <span style="font-size:0.83rem;font-weight:600;color:var(--sc-primary,#166534);">{{ $c->user->name }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Pending Review --}}
    <div class="card sc-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-checklist me-2" style="color: var(--sc-warning);"></i>
                Menunggu Pertimbangan Anda
            </h3>
            @if($pendingReview->isNotEmpty())
            <span class="sc-badge sc-badge-pending">{{ $pendingReview->count() }} antrian</span>
            @endif
        </div>
        @if($pendingReview->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sc-empty-icon"><i class="ti ti-mood-happy"></i></div>
                <h4 class="fw-bold text-dark mb-1">Tidak Ada Antrian</h4>
                <p class="text-muted mb-0">Tidak ada pengajuan bawahan yang menunggu pertimbangan Anda.</p>
            </div>
        </div>
        @else
        <div class="card-body p-3">
            @foreach($pendingReview as $req)
            <div class="card sc-history-card status-{{ $req->status }} mb-3" data-request-id="{{ $req->id }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="sc-user-avatar" style="width: 40px; height: 40px; font-size: 0.8rem; background: var(--sc-primary-light); color: var(--sc-primary); border: none; border-radius: 10px;">
                                {{ strtoupper(substr($req->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.95rem;">{{ $req->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                            </div>
                        </div>
                        <span class="sc-badge sc-badge-pending">{{ $req->type_label }}</span>
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
                        <button class="btn btn-sm sc-btn-primary flex-fill" onclick="openReviewModal{{ $req->id }}()">
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
    <div class="card sc-card mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-history me-2" style="color: #64748b;"></i>
                Riwayat Pertimbangan Saya
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table sc-table mb-0">
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
                                <span class="sc-badge sc-badge-approved"><i class="ti ti-circle-check"></i> Setuju</span>
                            @elseif($req->pertimbangan_atasan === 'ubah')
                                <span class="sc-badge" style="background: var(--sc-primary-light); color: var(--sc-primary);"><i class="ti ti-edit"></i> Ubah</span>
                            @elseif($req->pertimbangan_atasan === 'tangguhkan')
                                <span class="sc-badge sc-badge-pending"><i class="ti ti-clock-pause"></i> Tangguhkan</span>
                            @elseif($req->pertimbangan_atasan === 'tolak')
                                <span class="sc-badge sc-badge-rejected"><i class="ti ti-circle-x"></i> Tolak</span>
                            @endif
                        </td>
                        <td>
                            @if($req->isApproved())
                                <span class="sc-badge sc-badge-approved">Disetujui</span>
                            @elseif($req->isRejected())
                                <span class="sc-badge sc-badge-rejected">Ditolak</span>
                            @else
                                <span class="sc-badge sc-badge-pending">{{ $req->status_label }}</span>
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
    <div class="card sc-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="ti ti-list-details me-2" style="color: var(--sc-primary);"></i>
                Riwayat Cuti Saya
            </h3>
        </div>
        <div class="card-body p-3">
            @foreach($leaveRequests as $req)
            <div class="card sc-history-card status-{{ $req->status }} mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="fw-bold" style="font-size: 0.9rem;">{{ $req->type_label }}</span>
                            <div class="text-muted" style="font-size: 0.78rem;">
                                {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }} &middot; {{ $req->total_days }} hari
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($req->isApproved())
                                <span class="sc-badge sc-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @elseif($req->isRejected())
                                <span class="sc-badge sc-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                            @else
                                <span class="sc-badge sc-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
                            @endif
                            <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-size: 0.78rem;">
                                <i class="ti ti-eye me-1"></i> Detail
                            </a>
                        </div>
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
    <div class="card sc-hero-balance mb-4 animate-in">
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
                            <span class="sc-hero-number">{{ $cutiInfo['sisa_cuti'] ?? $cutiInfo['sisa'] ?? $user->leave_balance }}</span>
                            <span style="font-size: 1.1rem; opacity: 0.8;">/ {{ $cutiInfo['total_hak'] ?? 12 }} hari</span>
                        </div>
                        <div class="sc-hero-progress mb-2" style="max-width: 280px;">
                            @php $hakTotal = $cutiInfo['total_hak'] ?? 12; $hakTotal = $hakTotal > 0 ? $hakTotal : 1; @endphp
                            <div class="sc-hero-progress-bar" style="width: {{ min(100, (($cutiInfo['sisa_cuti'] ?? $cutiInfo['sisa'] ?? $user->leave_balance) / $hakTotal) * 100) }}%;"></div>
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
                            <span class="sc-hero-number">{{ $user->leave_balance }}</span>
                            <span style="font-size: 1.1rem; opacity: 0.8;">/ 12 hari</span>
                        </div>
                        <div class="sc-hero-progress mb-2" style="max-width: 280px;">
                            <div class="sc-hero-progress-bar" style="width: {{ ($user->leave_balance / 12) * 100 }}%;"></div>
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
    <div class="row g-2 g-md-3 mb-4 sc-pegawai-stats">
        <div class="col-4 animate-in">
            <div class="card sc-stat-card stat-warning h-100">
                <div class="card-body p-2 p-md-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-1" style="font-size:clamp(0.62rem,2vw,0.78rem);">Diproses</div>
                            <div class="sc-stat-number" style="color:var(--sc-warning);font-size:clamp(1.3rem,5vw,2rem);">{{ $leaveRequests->filter(fn($r) => $r->isPending())->count() }}</div>
                        </div>
                        <div class="sc-stat-icon icon-warning d-none d-sm-flex">
                            <i class="ti ti-clock-hour-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4 animate-in">
            <div class="card sc-stat-card stat-success h-100">
                <div class="card-body p-2 p-md-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-1" style="font-size:clamp(0.62rem,2vw,0.78rem);">Disetujui</div>
                            <div class="sc-stat-number" style="color:var(--sc-success);font-size:clamp(1.3rem,5vw,2rem);">{{ $leaveRequests->filter(fn($r) => $r->isApproved())->count() }}</div>
                        </div>
                        <div class="sc-stat-icon icon-success d-none d-sm-flex">
                            <i class="ti ti-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4 animate-in">
            <div class="card sc-stat-card stat-danger h-100">
                <div class="card-body p-2 p-md-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="sc-stat-label mb-1" style="font-size:clamp(0.62rem,2vw,0.78rem);">Ditolak</div>
                            <div class="sc-stat-number" style="color:var(--sc-danger);font-size:clamp(1.3rem,5vw,2rem);">{{ $leaveRequests->filter(fn($r) => $r->isRejected())->count() }}</div>
                        </div>
                        <div class="sc-stat-icon icon-danger d-none d-sm-flex">
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
            <div class="card sc-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0" style="font-size:0.9rem;">
                        <i class="ti ti-beach me-2" style="color:var(--sc-success);"></i>
                        Cuti Hari Ini
                    </h3>
                    <span class="sc-badge sc-badge-{{ $todayOnLeave->isNotEmpty() ? 'pending' : 'approved' }}">
                        {{ $todayOnLeave->count() }} pegawai
                    </span>
                </div>
                <div class="card-body p-3" style="max-height:200px;overflow-y:auto;">
                    @if($todayOnLeave->isEmpty())
                    <div class="text-center py-3">
                        <i class="ti ti-users" style="font-size:1.8rem;color:var(--sc-text-muted);opacity:0.3;"></i>
                        <p class="text-muted mb-0 mt-2" style="font-size:0.82rem;">Tidak ada pegawai yang cuti hari ini</p>
                    </div>
                    @else
                    @foreach($todayOnLeave as $req)
                    <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sc-user-avatar" style="width:30px;height:30px;font-size:0.65rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:7px;flex-shrink:0;">
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
            <div class="card sc-card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0" style="font-size:0.9rem;">
                        <i class="ti ti-map-pin me-2" style="color:var(--sc-accent);"></i>
                        Dinas Luar Hari Ini
                    </h3>
                    <span class="sc-badge" style="background:var(--sc-accent-light);color:var(--sc-accent);">
                        {{ $todayDinasLuar->count() }} pegawai
                    </span>
                </div>
                <div class="card-body p-3" style="max-height:200px;overflow-y:auto;">
                    @if($todayDinasLuar->isEmpty())
                    <div class="text-center py-3">
                        <i class="ti ti-briefcase" style="font-size:1.8rem;color:var(--sc-text-muted);opacity:0.3;"></i>
                        <p class="text-muted mb-0 mt-2" style="font-size:0.82rem;">Tidak ada pegawai dinas luar hari ini</p>
                    </div>
                    @else
                    @foreach($todayDinasLuar as $dl)
                    <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="sc-user-avatar" style="width:30px;height:30px;font-size:0.65rem;background:var(--sc-accent-light);color:var(--sc-accent);border:none;border-radius:7px;flex-shrink:0;">
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

    {{-- Statistik Penggunaan Cuti --}}
    <div class="card sc-card mt-4 mb-4 animate-in">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ti ti-chart-bar me-2"></i>Penggunaan Cuti 12 Bulan Terakhir</h5>
        </div>
        <div class="card-body">
            <canvas id="sc-cuti-chart" height="80"></canvas>
        </div>
    </div>

    {{-- Fitur 6: Search & Filter --}}
    <div class="card sc-card mb-4 animate-in">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('dashboard') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                            <i class="ti ti-search me-1" style="color: var(--sc-primary);"></i> Cari Alasan
                        </label>
                        <input type="text" name="search" class="form-control" placeholder="Cari alasan cuti..."
                               value="{{ request('search') }}"
                               style="border-radius: 10px; border: 2px solid #e2e8f0; height: 42px;">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8rem;">
                            <i class="ti ti-category me-1" style="color: var(--sc-primary);"></i> Jenis
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
                            <i class="ti ti-filter me-1" style="color: var(--sc-primary);"></i> Status
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
                            <button type="submit" class="btn btn-primary sc-btn-primary flex-fill" style="height: 42px;">
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
    <div class="card sc-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="ti ti-list-details me-2" style="color: var(--sc-primary);"></i>
                Riwayat Pengajuan Cuti
            </h3>
            <span class="text-muted" style="font-size: 0.8rem;">{{ $leaveRequests->count() }} total</span>
        </div>

        @if($leaveRequests->isEmpty())
        <div class="card-body py-5">
            <div class="text-center">
                <div class="sc-empty-icon"><i class="ti ti-calendar-off"></i></div>
                <h4 class="fw-bold text-dark mb-1">Belum Ada Pengajuan</h4>
                @if($user->bolehCuti())
                <p class="text-muted mb-3">Anda belum pernah mengajukan cuti. Mulai dengan klik tombol di bawah.</p>
                <a href="{{ route('leave.select-type') }}" class="btn btn-primary sc-btn-primary">
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

        {{-- Filter chips + view toggle --}}
        <div class="card-body pb-0 pt-3">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                <div class="sc-filter-chips">
                    <button class="sc-filter-chip active" data-filter="all">Semua</button>
                    <button class="sc-filter-chip" data-filter="pending">Menunggu</button>
                    <button class="sc-filter-chip" data-filter="approved">Disetujui</button>
                    <button class="sc-filter-chip" data-filter="rejected">Ditolak</button>
                    <button class="sc-filter-chip" data-filter="ditangguhkan">Ditangguhkan</button>
                </div>
                <div class="sc-view-toggle d-none d-md-flex">
                    <button class="sc-view-btn active" id="sc-view-expanded" title="Tampilan normal">
                        <i class="ti ti-layout-list"></i>
                    </button>
                    <button class="sc-view-btn" id="sc-view-compact" title="Tampilan kompak">
                        <i class="ti ti-layout-rows"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile: cards --}}
        <div class="card-body d-md-none" id="sc-leave-cards">
            @foreach($leaveRequests as $req)
            <div class="card sc-history-card status-{{ $req->status }} mb-3" data-status="{{ $req->status }}">
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
                            <span class="sc-badge sc-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                        @elseif($req->isRejected())
                            <span class="sc-badge sc-badge-rejected"><i class="ti ti-circle-x"></i> Ditolak</span>
                        @else
                            <span class="sc-badge sc-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
                        @endif
                    </div>

                    {{-- Show rejection reason if rejected --}}
                    @if($req->isRejected() && ($req->catatan_atasan || $req->catatan_pejabat))
                    <div class="alert mb-2" style="background: var(--sc-danger-light); color: var(--sc-danger); border: 1px solid rgba(220, 38, 38, 0.2); border-radius: 8px; padding: 0.75rem; font-size: 0.85rem;">
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
            <table class="table sc-table mb-0">
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
                    <tr data-status="{{ $req->status }}">
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
                                <span class="sc-badge sc-badge-approved"><i class="ti ti-circle-check"></i> Disetujui</span>
                            @elseif($req->isRejected())
                                <div>
                                    <span class="sc-badge sc-badge-rejected" title="{{ $req->catatan_pejabat ?? $req->catatan_atasan ?? 'Tidak ada catatan' }}" style="cursor: help;"><i class="ti ti-circle-x"></i> Ditolak</span>
                                    @if($req->catatan_pejabat || $req->catatan_atasan)
                                    <div class="small text-muted mt-1" style="font-size: 0.75rem; max-width: 200px; white-space: normal;">
                                        <i class="ti ti-info-circle" style="font-size: 0.7rem;"></i> {{ Str::limit($req->catatan_pejabat ?? $req->catatan_atasan, 60) }}
                                    </div>
                                    @endif
                                </div>
                            @else
                                <span class="sc-badge sc-badge-pending"><i class="ti ti-clock"></i> {{ $req->status_label }}</span>
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

@if(!$user->isAdmin() && !$user->isKetua() && !$user->isAtasan())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function() {
    var ctx = document.getElementById('sc-cuti-chart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [{
                label: 'Hari Cuti',
                data: {!! json_encode($chartData ?? []) !!},
                backgroundColor: 'rgba(22, 101, 52, 0.7)',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
})();
</script>
@endpush
@endif

@if(!$user->isAdmin())
@push('scripts')
<script>
// Filter chips for Riwayat Pengajuan Cuti
(function() {
    var pendingStatuses   = ['diajukan', 'pertimbangan_atasan', 'pending'];
    var approvedStatuses  = ['disetujui', 'approved'];
    var rejectedStatuses  = ['ditolak', 'rejected'];
    var suspendStatuses   = ['ditangguhkan'];

    function statusMatchesFilter(status, filter) {
        if (filter === 'all')          return true;
        if (filter === 'pending')      return pendingStatuses.indexOf(status) !== -1;
        if (filter === 'approved')     return approvedStatuses.indexOf(status) !== -1;
        if (filter === 'rejected')     return rejectedStatuses.indexOf(status) !== -1;
        if (filter === 'ditangguhkan') return suspendStatuses.indexOf(status) !== -1;
        return status === filter;
    }

    document.querySelectorAll('.sc-filter-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            document.querySelectorAll('.sc-filter-chip').forEach(function(c) { c.classList.remove('active'); });
            this.classList.add('active');
            var filter = this.dataset.filter;
            document.querySelectorAll('[data-status]').forEach(function(row) {
                var show = statusMatchesFilter(row.dataset.status, filter);
                row.style.display = show ? '' : 'none';
            });
        });
    });

    // View toggle (desktop table only)
    var viewExp = document.getElementById('sc-view-expanded');
    var viewCmp = document.getElementById('sc-view-compact');
    if (viewExp && viewCmp) {
        viewCmp.addEventListener('click', function() {
            viewExp.classList.remove('active'); viewCmp.classList.add('active');
            document.querySelectorAll('tbody tr').forEach(function(r) { r.classList.add('sc-compact-row'); });
        });
        viewExp.addEventListener('click', function() {
            viewCmp.classList.remove('active'); viewExp.classList.add('active');
            document.querySelectorAll('tbody tr').forEach(function(r) { r.classList.remove('sc-compact-row'); });
        });
    }
})();
</script>
@endpush
@endif

@if($user->isAdmin())
@push('scripts')
<style>
.sc-cal-day-cell[title]:hover { background: var(--sc-primary-light) !important; }
[data-bs-theme="dark"] .sc-cal-day-cell[title]:hover { background: rgba(34,197,94,0.12) !important; }
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
        document.querySelectorAll('.sc-stat-number').forEach(function(el) {
            var val = parseInt(el.textContent.trim());
            if (!isNaN(val) && val > 0) {
                el.textContent = '0';
                countUp(el, val, 900);
            }
        });
    });
})();

</script>
<script>
// #10 Auto-refresh dashboard stats every 60 seconds
function refreshDashboardStats() {
    fetch('{{ route("dashboard.live-stats") }}')
        .then(r => r.json())
        .then(data => {
            const pe = document.getElementById('stat-pending');
            const ol = document.getElementById('stat-on-leave');
            const ap = document.getElementById('stat-approved');
            if (pe) pe.textContent = data.pending_count;
            if (ol) ol.textContent = data.on_leave_today;
            if (ap) ap.textContent = data.approved_this_month;
        })
        .catch(() => {}); // silent fail
}
setInterval(refreshDashboardStats, 60000);
</script>
@endpush
@endif
@endsection
