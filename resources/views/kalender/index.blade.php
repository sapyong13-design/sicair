@extends('layouts.app')

@section('title', 'Kalender Cuti - SiHEALING')

@section('content')
{{-- Breadcrumb --}}
<nav class="sh-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sh-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sh-breadcrumb-current">Kalender Cuti</span>
</nav>

<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sh-page-title mb-1">
                <i class="ti ti-calendar me-1" style="color: var(--sh-primary);" aria-hidden="true"></i>
                Kalender Cuti
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Visualisasi jadwal cuti &amp; hari libur — klik tanggal untuk melihat detail
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{-- #32 Filter per Unit (Admin/Ketua) --}}
            @if((Auth::user()->isAdmin() || Auth::user()->isKetua()) && $unitList->isNotEmpty())
            <form method="GET" action="{{ route('kalender') }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <select name="unit" class="form-select form-select-sm" style="border-radius: 8px; width: auto; font-size: 0.82rem;" onchange="this.form.submit()">
                    <option value="">Semua Unit</option>
                    @foreach($unitList as $unit)
                    <option value="{{ $unit }}" {{ request('unit') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                    @endforeach
                </select>
            </form>
            @endif
            {{-- #26 Filter per Pegawai --}}
            @php $leaveUsers = $leaves->pluck('user')->unique('id')->filter(); @endphp
            @if($leaveUsers->isNotEmpty())
            <select id="calPegawaiFilter" class="form-select form-select-sm" style="border-radius: 8px; width: auto; font-size: 0.82rem;" onchange="filterCalPegawai(this.value)" title="Filter per pegawai">
                <option value="">Semua Pegawai</option>
                @foreach($leaveUsers as $pu)
                <option value="{{ $pu->id }}">{{ $pu->name }}</option>
                @endforeach
            </select>
            @endif
            {{-- #22 View toggle --}}
            <div class="btn-group btn-group-sm d-none d-md-flex" style="border-radius: 8px; overflow: hidden;">
                <button type="button" id="btnMonthView" class="btn btn-primary" onclick="setCalView('month')" title="Tampilan bulan">
                    <i class="ti ti-calendar-month"></i>
                </button>
                <button type="button" id="btnWeekView" class="btn btn-outline-secondary" onclick="setCalView('week')" title="Tampilan minggu">
                    <i class="ti ti-calendar-week"></i>
                </button>
            </div>
            {{-- #25 Print --}}
            <button type="button" class="btn btn-sm btn-outline-secondary d-print-none" onclick="window.print()" style="border-radius: 8px; font-size: 0.82rem;" title="Cetak kalender">
                <i class="ti ti-printer"></i>
            </button>
            {{-- #31 Export iCal --}}
            <a href="{{ route('kalender.export-ics', ['year' => $year]) }}"
               class="btn btn-sm btn-outline-secondary"
               style="border-radius: 8px; font-size: 0.82rem;"
               title="Export kalender ke Google Calendar / Outlook">
                <i class="ti ti-calendar-export me-1"></i> Export .ics
            </a>
            @if(auth()->user()->canApproveAsAtasan() || auth()->user()->isAdmin())
            <a href="{{ route('kalender.tim') }}" class="btn btn-outline-primary btn-sm ms-2" style="border-radius: 8px; font-size: 0.82rem;">
                <i class="ti ti-users me-1"></i>Kalender Tim
            </a>
            @endif
        </div>
    </div>
</div>

@php
    $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $prevMonth = $month == 1 ? 12 : $month - 1;
    $prevYear  = $month == 1 ? $year - 1 : $year;
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $nextYear  = $month == 12 ? $year + 1 : $year;

    // Summary stats bulan ini
    $hariLiburNasional = $holidays->where('tahun', $year)->filter(function($h) use ($month) {
        return \Carbon\Carbon::parse($h->tanggal)->month == $month && !$h->is_cuti_bersama;
    });
    $cutiBersamaBulanIni = $holidays->where('tahun', $year)->filter(function($h) use ($month) {
        return \Carbon\Carbon::parse($h->tanggal)->month == $month && $h->is_cuti_bersama;
    });
@endphp

{{-- Stats bar --}}
<div class="row g-2 mb-3">
    <div class="col-auto">
        <div class="d-flex align-items-center gap-2 px-3 py-2 sh-card" style="border-radius: 10px;">
            <span style="width:10px;height:10px;border-radius:50%;background:var(--sh-success);flex-shrink:0;"></span>
            <span style="font-size:0.82rem;"><strong>{{ $leaves->count() }}</strong> cuti disetujui bulan ini</span>
        </div>
    </div>
    <div class="col-auto">
        <div class="d-flex align-items-center gap-2 px-3 py-2 sh-card" style="border-radius: 10px;">
            <span style="width:10px;height:10px;border-radius:50%;background:#1d4ed8;flex-shrink:0;"></span>
            <span style="font-size:0.82rem;"><strong>{{ $cutiBersamaBulanIni->count() }}</strong> cuti bersama</span>
        </div>
    </div>
    <div class="col-auto">
        <div class="d-flex align-items-center gap-2 px-3 py-2 sh-card" style="border-radius: 10px;">
            <span style="width:10px;height:10px;border-radius:50%;background:var(--sh-danger);flex-shrink:0;"></span>
            <span style="font-size:0.82rem;"><strong>{{ $hariLiburNasional->count() }}</strong> hari libur nasional</span>
        </div>
    </div>
    <div class="col-auto">
        <div class="d-flex align-items-center gap-2 px-3 py-2 sh-card" style="border-radius: 10px;">
            <span style="width:10px;height:10px;border-radius:50%;background:#ea580c;flex-shrink:0;"></span>
            <span style="font-size:0.82rem;"><strong>{{ $dinasLuarList->count() }}</strong> dinas luar</span>
        </div>
    </div>
</div>

{{-- Month Navigator --}}
<div class="card sh-card mb-4">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('kalender', ['year' => $prevYear, 'month' => $prevMonth]) }}"
               class="btn btn-outline-secondary" style="border-radius: 10px;" aria-label="Bulan sebelumnya">
                <i class="ti ti-chevron-left" aria-hidden="true"></i>
            </a>
            <div class="text-center">
                <h3 class="fw-bold mb-0" style="color: var(--sh-primary);">{{ $months[$month - 1] }} {{ $year }}</h3>
            </div>
            <a href="{{ route('kalender', ['year' => $nextYear, 'month' => $nextMonth]) }}"
               class="btn btn-outline-secondary" style="border-radius: 10px;" aria-label="Bulan berikutnya">
                <i class="ti ti-chevron-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</div>

{{-- Calendar Grid --}}
<div class="card sh-card">
    <div class="card-body p-3">
        @php
            $firstDay    = mktime(0, 0, 0, $month, 1, $year);
            $daysInMonth = date('t', $firstDay);
            $startDay    = date('N', $firstDay); // 1=Mon, 7=Sun
            $today       = date('Y-m-d');

            // Build leave map: day => [leaves]
            $leaveMap = [];
            foreach ($leaves as $leave) {
                $s = max(strtotime($startOfMonth), strtotime($leave->start_date));
                $e = min(strtotime($endOfMonth), strtotime($leave->end_date));
                for ($d = $s; $d <= $e; $d += 86400) {
                    $leaveMap[date('j', $d)][] = $leave;
                }
            }

            // Build dinas luar map: day => [DinasLuar]
            $dinasLuarMap = [];
            foreach ($dinasLuarList as $dl) {
                $s = max(strtotime($startOfMonth), strtotime($dl->start_date));
                $e = min(strtotime($endOfMonth), strtotime($dl->end_date));
                for ($d = $s; $d <= $e; $d += 86400) {
                    $dinasLuarMap[date('j', $d)][] = $dl;
                }
            }

            // Build holiday map: day => HariLibur object
            $holidayMap = [];
            foreach ($holidays as $h) {
                $hDate = \Carbon\Carbon::parse($h->tanggal);
                if ($hDate->month == $month) {
                    $holidayMap[$hDate->day] = $h;
                }
            }
        @endphp

        @php
        // #23 Color map per leave type
        $leaveTypeColors = [
            'cuti_tahunan'         => ['bg' => 'rgba(22,101,52,0.85)',   'text' => '#fff'],
            'cuti_sakit'           => ['bg' => 'rgba(220,38,38,0.8)',    'text' => '#fff'],
            'cuti_melahirkan'      => ['bg' => 'rgba(219,39,119,0.8)',   'text' => '#fff'],
            'cuti_alasan_penting'  => ['bg' => 'rgba(202,138,4,0.85)',   'text' => '#fff'],
            'cuti_besar'           => ['bg' => 'rgba(124,58,237,0.8)',   'text' => '#fff'],
            'cuti_luar_tanggungan' => ['bg' => 'rgba(100,116,139,0.8)',  'text' => '#fff'],
        ];
        $defaultLeaveColor = ['bg' => 'rgba(22,101,52,0.75)', 'text' => '#fff'];
        @endphp
        {{-- Desktop Calendar --}}
        <div id="monthView" class="d-none d-md-block">
            <table class="table table-bordered mb-0" style="table-layout: fixed;">
                <thead>
                    <tr>
                        @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $day)
                        <th class="text-center py-2" style="font-size: 0.78rem; font-weight: 700; color: {{ in_array($day, ['Sab','Min']) ? 'var(--sh-danger)' : '#64748b' }}; text-transform: uppercase; letter-spacing: 0.5px; background: var(--sh-gray-50);">
                            {{ $day }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php $dayCount = 1; @endphp
                    @for($row = 0; $row < 6; $row++)
                        @if($dayCount > $daysInMonth) @break @endif
                        <tr>
                            @for($col = 1; $col <= 7; $col++)
                                @if(($row === 0 && $col < $startDay) || $dayCount > $daysInMonth)
                                    <td style="background: var(--sh-gray-50); min-height: 90px;"></td>
                                @else
                                    @php
                                        $dateStr   = sprintf('%04d-%02d-%02d', $year, $month, $dayCount);
                                        $isToday   = $dateStr === $today;
                                        $isWeekend = $col >= 6;
                                        $holiday   = $holidayMap[$dayCount] ?? null;
                                        $isHoliday = $holiday !== null;
                                        $isCutiBersama = $isHoliday && $holiday->is_cuti_bersama;
                                        $dayLeaves = $leaveMap[$dayCount] ?? [];
                                        $dayDinasLuar = $dinasLuarMap[$dayCount] ?? [];
                                        $hasEvents = $isHoliday || count($dayLeaves) > 0 || count($dayDinasLuar) > 0;

                                        // Cell background
                                        if ($isToday) $cellBg = 'background: var(--sh-primary-light);';
                                        elseif ($isCutiBersama) $cellBg = 'background: var(--sh-cal-cb-bg);';
                                        elseif ($isWeekend || $isHoliday) $cellBg = 'background: var(--sh-cal-holiday-bg);';
                                        else $cellBg = '';
                                    @endphp
                                    <td onclick="openDayModal('{{ $dateStr }}')"
                                        style="vertical-align: top; min-height: 90px; padding: 0.4rem; position: relative; cursor: pointer; {{ $cellBg }} transition: opacity 0.15s;"
                                        onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'"
                                        title="Klik untuk detail {{ $dayCount }} {{ $months[$month-1] }} {{ $year }}">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="fw-bold {{ $isToday ? 'sh-cal-today' : '' }}"
                                                  style="font-size: 0.85rem; {{ ($isWeekend || $isHoliday) && !$isCutiBersama ? 'color: var(--sh-danger);' : ($isCutiBersama ? 'color: var(--sh-cal-cb-text);' : 'color: var(--sh-text);') }}">
                                                {{ $dayCount }}
                                            </span>
                                            @if($hasEvents)
                                            <span style="width: 6px; height: 6px; border-radius: 50%; margin-top: 4px; flex-shrink: 0;
                                                  background: {{ count($dayLeaves) > 0 ? 'var(--sh-success)' : ($isCutiBersama ? 'var(--sh-cal-cb-border)' : 'var(--sh-danger)') }};"></span>
                                            @endif
                                        </div>
                                        {{-- Holiday badge --}}
                                        @if($isHoliday)
                                        <div class="sh-cal-event {{ $isCutiBersama ? 'sh-cal-cuti-bersama' : 'sh-cal-holiday' }}"
                                             title="{{ $holiday->keterangan }}">
                                            <i class="ti {{ $isCutiBersama ? 'ti-calendar-check' : 'ti-flag-filled' }}" style="font-size: 0.65rem;"></i>
                                            {{ Str::limit($holiday->keterangan, 14) }}
                                        </div>
                                        @endif
                                        {{-- Leave events (max 2 shown) --}}
                                        @php $shownSlots = 0; @endphp
                                        @foreach(array_slice($dayLeaves, 0, 2) as $lv)
                                        @php
                                            $lvColor = $leaveTypeColors[$lv->type] ?? $defaultLeaveColor;
                                        @endphp
                                        <div class="sh-cal-event sh-cal-leave"
                                             data-user-id="{{ $lv->user_id }}"
                                             data-leave-name="{{ $lv->user->name }}"
                                             data-leave-type="{{ $lv->type_label }}"
                                             data-leave-start="{{ $lv->start_date->format('d M Y') }}"
                                             data-leave-end="{{ $lv->end_date->format('d M Y') }}"
                                             data-leave-days="{{ $lv->total_hari_kerja ?? '—' }}"
                                             style="background: {{ $lvColor['bg'] }}; color: {{ $lvColor['text'] }};"
                                             title="{{ $lv->user->name }} — {{ $lv->type_label }}">
                                            {{ Str::limit($lv->user->name, 12) }}
                                        </div>
                                        @php $shownSlots++; @endphp
                                        @endforeach
                                        @if(count($dayLeaves) > 2)
                                        <div class="sh-cal-event" style="background: var(--sh-gray-100); color: #64748b; font-size: 0.65rem; text-align:center;">
                                            +{{ count($dayLeaves) - 2 }} lagi
                                        </div>
                                        @php $shownSlots++; @endphp
                                        @endif
                                        {{-- Dinas luar events --}}
                                        @if($shownSlots < 2)
                                            @foreach(array_slice($dayDinasLuar, 0, 2 - $shownSlots) as $dl)
                                            <div class="sh-cal-event sh-cal-dinas-luar"
                                                 title="{{ $dl->user->name }} — Dinas Luar: {{ $dl->tujuan }}">
                                                <i class="ti ti-briefcase" style="font-size:0.6rem;"></i>
                                                {{ Str::limit($dl->user->name, 10) }}
                                            </div>
                                            @endforeach
                                        @endif
                                        @if(count($dayDinasLuar) > (2 - $shownSlots) && $shownSlots < 2)
                                        <div class="sh-cal-event" style="background: var(--sh-gray-100); color: #64748b; font-size: 0.65rem; text-align:center;">
                                            +{{ count($dayDinasLuar) - max(0, 2 - $shownSlots) }} lagi
                                        </div>
                                        @endif
                                    </td>
                                    @php $dayCount++; @endphp
                                @endif
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        {{-- #22 Week View --}}
        @php
            // Determine current week (Mon-Sun containing today if this is current month, else first week)
            $todayDate = \Carbon\Carbon::today();
            $isCurrMonth = ($todayDate->year == $year && $todayDate->month == $month);
            $weekAnchor  = $isCurrMonth ? $todayDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY) : \Carbon\Carbon::create($year, $month, 1)->startOfWeek(\Carbon\Carbon::MONDAY);
            // Clamp to month
            $weekStart = $weekAnchor->copy()->max(\Carbon\Carbon::create($year, $month, 1));
            $weekEnd   = $weekAnchor->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->min(\Carbon\Carbon::create($year, $month, 1)->endOfMonth());
        @endphp
        <div id="weekView" style="display: none;">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="fw-semibold" style="font-size:0.88rem; color:var(--sh-text);">
                    <i class="ti ti-calendar-week me-1" style="color:var(--sh-primary);"></i>
                    Minggu {{ $weekStart->format('d') }}–{{ $weekEnd->format('d M Y') }}
                </span>
                <span class="text-muted" style="font-size:0.8rem;">(gunakan navigasi bulan untuk berpindah minggu)</span>
            </div>
            <div class="table-responsive">
            <table class="table table-bordered mb-0" style="table-layout: fixed; min-width: 600px;">
                <thead>
                    <tr>
                        @for($wi = 0; $wi < 7; $wi++)
                        @php
                            $wd = $weekAnchor->copy()->addDays($wi);
                            $isWkend = $wd->dayOfWeek === 0 || $wd->dayOfWeek === 6;
                        @endphp
                        <th class="text-center py-2" style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; background: var(--sh-gray-50); color: {{ $isWkend ? 'var(--sh-danger)' : '#64748b' }};">
                            {{ $wd->isoFormat('ddd') }}<br>
                            <span style="font-size: 1rem; font-weight: 800; {{ $wd->isToday() ? 'color: var(--sh-primary);' : '' }}">{{ $wd->day }}</span>
                        </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @for($wi = 0; $wi < 7; $wi++)
                        @php
                            $wd = $weekAnchor->copy()->addDays($wi);
                            $wdStr = $wd->format('Y-m-d');
                            $holiday = $wd->month == $month ? ($holidayMap[$wd->day] ?? null) : null;
                            $wdLeaves = $wd->month == $month ? ($leaveMap[$wd->day] ?? []) : [];
                            $wdDinas  = $wd->month == $month ? ($dinasLuarMap[$wd->day] ?? []) : [];
                            $isOut = $wd->month != $month;
                        @endphp
                        <td style="vertical-align: top; min-height: 120px; padding: 0.4rem; {{ $wd->isToday() ? 'background: var(--sh-primary-light);' : ($isOut ? 'background: var(--sh-gray-50);' : '') }}">
                            @if(!$isOut)
                            @if($holiday)
                            <div class="sh-cal-event {{ $holiday->is_cuti_bersama ? 'sh-cal-cuti-bersama' : 'sh-cal-holiday' }}">
                                {{ Str::limit($holiday->keterangan, 16) }}
                            </div>
                            @endif
                            @foreach($wdLeaves as $lv)
                            @php $lvColor = $leaveTypeColors[$lv->type] ?? $defaultLeaveColor; @endphp
                            <div class="sh-cal-event sh-cal-leave"
                                 data-user-id="{{ $lv->user_id }}"
                                 data-leave-name="{{ $lv->user->name }}"
                                 data-leave-type="{{ $lv->type_label }}"
                                 data-leave-start="{{ $lv->start_date->format('d M Y') }}"
                                 data-leave-end="{{ $lv->end_date->format('d M Y') }}"
                                 data-leave-days="{{ $lv->total_hari_kerja ?? '—' }}"
                                 style="background: {{ $lvColor['bg'] }}; color: {{ $lvColor['text'] }};">
                                {{ Str::limit($lv->user->name, 10) }}
                            </div>
                            @endforeach
                            @foreach($wdDinas as $dl)
                            <div class="sh-cal-event sh-cal-dinas-luar" title="{{ $dl->user->name }} — Dinas Luar">
                                <i class="ti ti-briefcase" style="font-size:0.6rem;"></i>
                                {{ Str::limit($dl->user->name, 10) }}
                            </div>
                            @endforeach
                            @endif
                        </td>
                        @endfor
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        {{-- Mobile Calendar --}}
        <div class="d-md-none">
            <div class="row g-1 mb-2">
                @foreach(['S','S','R','K','J','S','M'] as $day)
                <div class="col text-center" style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">{{ $day }}</div>
                @endforeach
            </div>
            @php $dayCount = 1; @endphp
            @for($row = 0; $row < 6; $row++)
                @if($dayCount > $daysInMonth) @break @endif
                <div class="row g-1 mb-1">
                    @for($col = 1; $col <= 7; $col++)
                        @if(($row === 0 && $col < $startDay) || $dayCount > $daysInMonth)
                            <div class="col" style="min-height: 36px;"></div>
                        @else
                            @php
                                $dateStr       = sprintf('%04d-%02d-%02d', $year, $month, $dayCount);
                                $isToday       = $dateStr === $today;
                                $isWeekend     = $col >= 6;
                                $holiday       = $holidayMap[$dayCount] ?? null;
                                $isHoliday     = $holiday !== null;
                                $isCutiBersama = $isHoliday && $holiday->is_cuti_bersama;
                                $hasLeave      = isset($leaveMap[$dayCount]);
                                $hasDinasLuar  = isset($dinasLuarMap[$dayCount]);
                            @endphp
                            <div class="col text-center" style="min-height: 36px;" onclick="openDayModal('{{ $dateStr }}')">
                                <div class="d-flex flex-column align-items-center" style="cursor: pointer;">
                                    <span style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: {{ $isToday ? '800' : '500' }};
                                        {{ $isToday ? 'background: var(--sh-primary); color: #fff;' : ($isCutiBersama ? 'color: var(--sh-cal-cb-text);' : (($isWeekend || $isHoliday) ? 'color: var(--sh-danger);' : 'color: var(--sh-text);')) }}">
                                        {{ $dayCount }}
                                    </span>
                                    @if($hasLeave)
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--sh-success); margin-top: 2px;"></span>
                                    @elseif($hasDinasLuar)
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #ea580c; margin-top: 2px;"></span>
                                    @elseif($isCutiBersama)
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--sh-cal-cb-border); margin-top: 2px;"></span>
                                    @elseif($isHoliday)
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--sh-danger); margin-top: 2px;"></span>
                                    @endif
                                </div>
                            </div>
                            @php $dayCount++; @endphp
                        @endif
                    @endfor
                </div>
            @endfor

            {{-- Mobile: list events below --}}
            @php
                $monthEvents = [];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    if (isset($leaveMap[$d]) || isset($holidayMap[$d]) || isset($dinasLuarMap[$d])) {
                        $monthEvents[$d] = ['leaves' => $leaveMap[$d] ?? [], 'holiday' => $holidayMap[$d] ?? null, 'dinasLuar' => $dinasLuarMap[$d] ?? []];
                    }
                }
            @endphp
            @if(!empty($monthEvents))
            <div class="mt-3 border-top pt-3">
                <div class="fw-bold mb-2" style="font-size: 0.85rem; color: var(--sh-primary);">
                    <i class="ti ti-list me-1"></i> Event Bulan Ini
                </div>
                @foreach($monthEvents as $d => $ev)
                <div class="mb-2 p-2" style="background: var(--sh-gray-50); border-radius: 8px; font-size: 0.82rem; cursor: pointer;"
                     onclick="openDayModal('{{ sprintf('%04d-%02d-%02d', $year, $month, $d) }}')">
                    <div class="fw-bold" style="color: var(--sh-text);">{{ $d }} {{ $months[$month - 1] }}</div>
                    @if($ev['holiday'])
                    @php $cb = $ev['holiday']->is_cuti_bersama; @endphp
                    <div style="color: {{ $cb ? 'var(--sh-cal-cb-text)' : 'var(--sh-danger)' }};">
                        <i class="ti {{ $cb ? 'ti-calendar-check' : 'ti-flag-filled' }} me-1" style="font-size: 0.7rem;"></i>
                        {{ $ev['holiday']->keterangan }}
                        @if($cb)<span class="badge ms-1" style="background: var(--sh-cal-cb-badge-bg); font-size:0.65rem;">Cuti Bersama</span>@endif
                    </div>
                    @endif
                    @foreach($ev['leaves'] as $lv)
                    <div style="color: var(--sh-success);">
                        <i class="ti ti-beach me-1" style="font-size: 0.7rem;"></i>
                        {{ $lv->user->name }} — {{ $lv->type_label }}
                    </div>
                    @endforeach
                    @foreach($ev['dinasLuar'] as $dl)
                    <div style="color: #ea580c;">
                        <i class="ti ti-briefcase me-1" style="font-size: 0.7rem;"></i>
                        {{ $dl->user->name }} — Dinas Luar: {{ $dl->tujuan }}
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Legend --}}
    <div class="card-footer" style="background: var(--sh-cal-legend-bg); border-top: 2px solid var(--sh-gray-100);">
        <div class="d-flex flex-wrap gap-3" style="font-size: 0.78rem; color: var(--sh-text);">
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: var(--sh-primary);"></span>
                Hari Ini
            </div>
            {{-- #23 Color coding per jenis cuti --}}
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: rgba(22,101,52,0.85);"></span>
                Cuti Tahunan
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: rgba(220,38,38,0.8);"></span>
                Cuti Sakit
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: rgba(219,39,119,0.8);"></span>
                Melahirkan
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: rgba(202,138,4,0.85);"></span>
                Alasan Penting
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: rgba(124,58,237,0.8);"></span>
                Cuti Besar
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: #1d4ed8;"></span>
                Cuti Bersama
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: var(--sh-danger);"></span>
                Hari Libur
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: #ea580c;"></span>
                Dinas Luar
            </div>
            <div class="d-flex align-items-center gap-1 ms-auto text-muted d-print-none">
                <i class="ti ti-hand-click" style="font-size: 0.8rem;"></i>
                Klik tanggal untuk detail
            </div>
        </div>
    </div>
</div>

{{-- Holiday list for this month --}}
@if($hariLiburNasional->isNotEmpty() || $cutiBersamaBulanIni->isNotEmpty())
<div class="card sh-card mt-4">
    <div class="card-header">
        <h3 class="card-title mb-0">
            <i class="ti ti-flag me-2" style="color: var(--sh-danger);"></i>
            Hari Libur &amp; Cuti Bersama — {{ $months[$month - 1] }} {{ $year }}
        </h3>
    </div>
    <div class="card-body p-3">
        @foreach($cutiBersamaBulanIni as $h)
        <div class="d-flex align-items-center gap-3 mb-2 p-2" style="background: var(--sh-cal-cb-bg); border-radius: 10px; border-left: 4px solid var(--sh-cal-cb-border);">
            <div style="width: 40px; text-align: center; font-weight: 800; color: var(--sh-cal-cb-text); font-size: 1.1rem;">
                {{ \Carbon\Carbon::parse($h->tanggal)->day }}
            </div>
            <div class="flex-fill">
                <div class="fw-bold" style="font-size: 0.85rem; color: var(--sh-cal-cb-text);">{{ $h->keterangan }}</div>
                <div style="font-size: 0.75rem; color: var(--sh-cal-cb-text);">
                    <span class="badge" style="background: var(--sh-cal-cb-badge-bg); font-size: 0.7rem;">Cuti Bersama</span>
                    &middot; {{ \Carbon\Carbon::parse($h->tanggal)->isoFormat('dddd, D MMMM Y') }}
                </div>
            </div>
        </div>
        @endforeach
        @foreach($hariLiburNasional as $h)
        <div class="d-flex align-items-center gap-3 mb-2 p-2" style="background: var(--sh-danger-light); border-radius: 10px; border-left: 4px solid var(--sh-danger);">
            <div style="width: 40px; text-align: center; font-weight: 800; color: var(--sh-danger); font-size: 1.1rem;">
                {{ \Carbon\Carbon::parse($h->tanggal)->day }}
            </div>
            <div class="flex-fill">
                <div class="fw-bold" style="font-size: 0.85rem;">{{ $h->keterangan }}</div>
                <div style="font-size: 0.75rem; color: var(--sh-text-muted);">
                    <span class="badge bg-danger" style="font-size: 0.7rem;">Hari Libur Nasional</span>
                    &middot; {{ \Carbon\Carbon::parse($h->tanggal)->isoFormat('dddd, D MMMM Y') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Leave list --}}
@if($leaves->isNotEmpty())
<div class="card sh-card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="ti ti-beach me-2" style="color: var(--sh-success);" aria-hidden="true"></i>
            Cuti Bulan {{ $months[$month - 1] }} {{ $year }}
        </h3>
        <span class="badge" style="background: var(--sh-success); font-size: 0.78rem;">{{ $leaves->count() }} cuti</span>
    </div>
    <div class="card-body p-3">
        @foreach($leaves->sortBy('start_date') as $lv)
        <div class="d-flex align-items-center gap-3 mb-2 p-2" style="background: var(--sh-success-light); border-radius: 10px;">
            <div class="sh-user-avatar" style="width: 36px; height: 36px; font-size: 0.7rem; background: var(--sh-success); color: #fff; border: none; border-radius: 8px; flex-shrink: 0;">
                {{ strtoupper(substr($lv->user->name, 0, 2)) }}
            </div>
            <div class="flex-fill min-width-0">
                <div class="fw-bold" style="font-size: 0.85rem;">{{ $lv->user->name }}</div>
                <div class="text-muted" style="font-size: 0.75rem;">
                    {{ $lv->type_label }}
                    &middot; {{ $lv->start_date->format('d M') }} – {{ $lv->end_date->format('d M Y') }}
                    @if($lv->total_hari_kerja)
                    &middot; {{ $lv->total_hari_kerja }} hari kerja
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Dinas Luar list --}}
@if($dinasLuarList->isNotEmpty())
<div class="card sh-card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="ti ti-briefcase me-2" style="color: #ea580c;" aria-hidden="true"></i>
            Dinas Luar Bulan {{ $months[$month - 1] }} {{ $year }}
        </h3>
        <span class="badge" style="background: #ea580c; font-size: 0.78rem;">{{ $dinasLuarList->count() }} pegawai</span>
    </div>
    <div class="card-body p-3">
        @foreach($dinasLuarList->sortBy('start_date') as $dl)
        <div class="d-flex align-items-center gap-3 mb-2 p-2" style="background: var(--sh-cal-dinas-bg); border-radius: 10px; border-left: 4px solid #ea580c;">
            <div class="sh-user-avatar" style="width: 36px; height: 36px; font-size: 0.7rem; background: #ea580c; color: #fff; border: none; border-radius: 8px; flex-shrink: 0;">
                {{ strtoupper(substr($dl->user->name, 0, 2)) }}
            </div>
            <div class="flex-fill min-width-0">
                <div class="fw-bold" style="font-size: 0.85rem;">{{ $dl->user->name }}</div>
                <div class="text-muted" style="font-size: 0.75rem;">
                    <i class="ti ti-map-pin me-1" style="font-size: 0.7rem;"></i>{{ $dl->tujuan }}
                    &middot; {{ $dl->start_date->format('d M') }}{{ $dl->start_date->ne($dl->end_date) ? ' – ' . $dl->end_date->format('d M Y') : ' ' . $dl->start_date->format('Y') }}
                    &middot; {{ $dl->durasi }} hari
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Empty state --}}
@if($leaves->isEmpty() && $holidays->isEmpty() && $dinasLuarList->isEmpty())
<div class="card sh-card mt-4">
    <div class="card-body py-4 text-center">
        {{-- #46 Empty state SVG: no-leaves --}}
        <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="mb-3" aria-hidden="true">
            <circle cx="40" cy="40" r="36" fill="var(--sh-primary-light)"/>
            <rect x="16" y="22" width="48" height="38" rx="5" fill="none" stroke="var(--sh-primary)" stroke-width="2.5" opacity="0.35"/>
            <rect x="16" y="22" width="48" height="38" rx="5" stroke="var(--sh-primary)" stroke-width="2.5" fill="none"/>
            <path d="M16 32H64" stroke="var(--sh-primary)" stroke-width="2"/>
            <rect x="27" y="14" width="4" height="10" rx="2" fill="var(--sh-primary)" opacity="0.6"/>
            <rect x="49" y="14" width="4" height="10" rx="2" fill="var(--sh-primary)" opacity="0.6"/>
            <path d="M28 48L52 36M28 36L52 48" stroke="var(--sh-danger)" stroke-width="2.5" stroke-linecap="round" opacity="0.7"/>
        </svg>
        <h5 class="fw-bold text-dark mb-1">Tidak Ada Event</h5>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Tidak ada jadwal cuti atau hari libur pada bulan {{ $months[$month - 1] }} {{ $year }}.</p>
    </div>
</div>
@endif

{{-- Modal Detail Hari --}}
<div class="modal fade" id="dayDetailModal" tabindex="-1" aria-labelledby="dayDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 2px solid var(--sh-gray-100);">
                <h5 class="modal-title fw-bold" id="dayDetailModalLabel">
                    <i class="ti ti-calendar-event me-2" style="color: var(--sh-primary);"></i>
                    <span id="modalDateTitle">Detail Hari</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4" id="modalDayBody">
                <div class="text-center p-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
/* #25 Print styles */
@media print {
    .sh-page-header .d-flex > :not(:first-child) { display: none !important; }
    .sh-breadcrumb, .sh-navbar, .sh-sidebar, .sh-mobile-nav,
    #dayDetailModal, .d-print-none { display: none !important; }
    #weekView { display: none !important; }
    #monthView { display: block !important; }
    body { font-size: 10pt; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    @page { size: landscape; margin: 1cm; }
}
/* #24 Hover tooltip */
.sh-cal-tooltip {
    position: fixed;
    z-index: 9999;
    background: var(--bs-body-bg, #fff);
    border: 1px solid var(--sh-gray-100);
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.18);
    padding: 0.55rem 0.75rem;
    font-size: 0.78rem;
    max-width: 220px;
    pointer-events: none;
    transition: opacity 0.15s;
}
.sh-cal-tooltip .tt-name { font-weight: 700; color: var(--sh-text); margin-bottom: 2px; }
.sh-cal-tooltip .tt-type { margin-bottom: 2px; }
.sh-cal-tooltip .tt-date { color: var(--sh-text-muted); }
</style>
<div id="calTooltip" class="sh-cal-tooltip" style="display:none;opacity:0;"></div>
<script>
const LEAVES_FOR_DAY_URL = '{{ route("kalender.leaves-for-day") }}';
const MONTHS_ID = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const DAYS_ID   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

let dayModalInstance = null;

function openDayModal(dateStr) {
    if (!dayModalInstance) {
        dayModalInstance = new bootstrap.Modal(document.getElementById('dayDetailModal'));
    }

    // Format date display
    const d = new Date(dateStr + 'T00:00:00');
    const dayName = DAYS_ID[d.getDay()];
    const formattedDate = dayName + ', ' + d.getDate() + ' ' + MONTHS_ID[d.getMonth()] + ' ' + d.getFullYear();
    document.getElementById('modalDateTitle').textContent = formattedDate;

    // Show spinner
    document.getElementById('modalDayBody').innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-success" role="status"></div>
            <div class="text-muted mt-2" style="font-size:0.85rem;">Memuat data...</div>
        </div>`;

    dayModalInstance.show();

    // Fetch data
    fetch(LEAVES_FOR_DAY_URL + '?date=' + dateStr, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => renderDayModal(data))
    .catch(() => {
        document.getElementById('modalDayBody').innerHTML =
            '<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>';
    });
}

function renderDayModal(data) {
    let html = '';

    // Holiday / Cuti Bersama banner
    if (data.holiday) {
        const cb   = data.holiday.is_cuti_bersama;
        const bg   = cb ? '#eff6ff' : '#fee2e2';
        const col  = cb ? '#1d4ed8' : '#dc2626';
        const icon = cb ? 'ti-calendar-check' : 'ti-flag-filled';
        const label = cb
            ? '<span class="badge ms-2" style="background:#1d4ed8;font-size:0.72rem;">Cuti Bersama</span>'
            : '<span class="badge bg-danger ms-2" style="font-size:0.72rem;">Hari Libur Nasional</span>';
        html += `<div style="background:${bg};color:${col};padding:0.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-weight:600;border-left:4px solid ${col};">
            <i class="ti ${icon} me-2"></i>${escHtml(data.holiday.keterangan)}${label}
        </div>`;
    }

    // Dinas Luar section
    if (data.dinasLuar && data.dinasLuar.length > 0) {
        html += `<div class="fw-bold mb-2 mt-1" style="color:#ea580c;font-size:0.88rem;">
            <i class="ti ti-briefcase me-1"></i>${data.dinasLuar.length} Pegawai Dinas Luar
        </div>`;
        data.dinasLuar.forEach(dl => {
            html += `
            <div style="display:flex;gap:0.75rem;padding:0.75rem;background:#fff7ed;border-radius:10px;margin-bottom:0.6rem;align-items:center;border-left:3px solid #ea580c;">
                <div style="width:40px;height:40px;border-radius:10px;background:#ea580c;color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;flex-shrink:0;">
                    ${escHtml(dl.initials)}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        ${escHtml(dl.name)}
                    </div>
                    <div style="color:#9a3412;font-size:0.78rem;">
                        ${escHtml(dl.jabatan)}
                    </div>
                    <div style="margin-top:4px;display:flex;flex-wrap:wrap;gap:4px;align-items:center;">
                        <span class="badge" style="background:#ea580c;font-size:0.72rem;"><i class="ti ti-map-pin me-1" style="font-size:0.65rem;"></i>${escHtml(dl.tujuan)}</span>
                        <span style="color:#9a3412;font-size:0.75rem;">
                            ${escHtml(dl.start_date)} – ${escHtml(dl.end_date)}
                        </span>
                        <span style="color:#9a3412;font-size:0.75rem;">(${dl.durasi} hari)</span>
                    </div>
                    <div style="color:#9a3412;font-size:0.75rem;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        ${escHtml(dl.keperluan)}
                    </div>
                </div>
            </div>`;
        });
        if (data.leaves.length > 0) {
            html += `<hr style="margin:0.75rem 0;">`;
        }
    }

    // Leave list
    if (data.leaves.length === 0) {
        if (!data.holiday && !(data.dinasLuar && data.dinasLuar.length > 0)) {
            html += `<div class="text-center py-3 text-muted">
                <i class="ti ti-beach" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:0.5rem;"></i>
                Tidak ada cuti pada hari ini
            </div>`;
        } else if (!data.dinasLuar || data.dinasLuar.length === 0) {
            html += `<div class="text-muted small text-center py-2">
                Tidak ada pegawai yang cuti pada hari ini
            </div>`;
        }
    } else {
        html += `<div class="fw-bold mb-3" style="color:var(--sh-success);font-size:0.88rem;">
            <i class="ti ti-users me-1"></i>${data.leaves.length} Pegawai Cuti pada Hari Ini
        </div>`;
        data.leaves.forEach(lv => {
            html += `
            <div style="display:flex;gap:0.75rem;padding:0.75rem;background:var(--sh-success-light);border-radius:10px;margin-bottom:0.6rem;align-items:center;">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--sh-success);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;flex-shrink:0;">
                    ${escHtml(lv.initials)}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        ${escHtml(lv.name)}
                    </div>
                    <div style="color:var(--sh-text-muted);font-size:0.78rem;">
                        ${escHtml(lv.jabatan)} &bull; ${escHtml(lv.unit_kerja)}
                    </div>
                    <div style="margin-top:4px;display:flex;flex-wrap:wrap;gap:4px;align-items:center;">
                        <span class="badge" style="background:var(--sh-success);font-size:0.72rem;">${escHtml(lv.type_label)}</span>
                        <span style="color:var(--sh-text-muted);font-size:0.75rem;">
                            ${escHtml(lv.start_date)} – ${escHtml(lv.end_date)}
                        </span>
                        ${lv.total_hari ? `<span style="color:var(--sh-text-muted);font-size:0.75rem;">(${lv.total_hari} hari kerja)</span>` : ''}
                    </div>
                </div>
            </div>`;
        });
    }

    document.getElementById('modalDayBody').innerHTML = html;
}

function escHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
}

// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(el => new bootstrap.Tooltip(el));
});

// #22 View toggle (month / week)
function setCalView(mode) {
    const monthEl = document.getElementById('monthView');
    const weekEl  = document.getElementById('weekView');
    const btnM    = document.getElementById('btnMonthView');
    const btnW    = document.getElementById('btnWeekView');
    if (!monthEl || !weekEl) return;
    if (mode === 'week') {
        monthEl.style.display = 'none';
        weekEl.style.display  = 'block';
        btnM && btnM.classList.replace('btn-primary', 'btn-outline-secondary');
        btnW && btnW.classList.replace('btn-outline-secondary', 'btn-primary');
        localStorage.setItem('sh_cal_view', 'week');
    } else {
        monthEl.style.display = '';
        weekEl.style.display  = 'none';
        btnM && btnM.classList.replace('btn-outline-secondary', 'btn-primary');
        btnW && btnW.classList.replace('btn-primary', 'btn-outline-secondary');
        localStorage.setItem('sh_cal_view', 'month');
    }
}
// Restore saved view
(function() {
    const saved = localStorage.getItem('sh_cal_view');
    if (saved === 'week') setCalView('week');
})();

// #26 Filter per pegawai
function filterCalPegawai(userId) {
    document.querySelectorAll('.sh-cal-leave').forEach(function(el) {
        if (!userId) {
            el.style.display = '';
        } else {
            el.style.display = el.dataset.userId == userId ? '' : 'none';
        }
    });
}

// #24 Hover tooltip
(function() {
    const tooltip = document.getElementById('calTooltip');
    if (!tooltip) return;

    function showTooltip(el, e) {
        const name  = el.dataset.leaveName  || '';
        const type  = el.dataset.leaveType  || '';
        const start = el.dataset.leaveStart || '';
        const end   = el.dataset.leaveEnd   || '';
        const days  = el.dataset.leaveDays  || '';
        if (!name) return;
        tooltip.innerHTML = `
            <div class="tt-name">${escHtml(name)}</div>
            <div class="tt-type">${escHtml(type)}</div>
            <div class="tt-date"><i class="ti ti-calendar me-1"></i>${escHtml(start)} – ${escHtml(end)}</div>
            ${days !== '—' && days ? `<div class="tt-date"><i class="ti ti-clock me-1"></i>${escHtml(days)} hari kerja</div>` : ''}
        `;
        tooltip.style.display = 'block';
        posTooltip(e);
        setTimeout(() => { tooltip.style.opacity = '1'; }, 10);
    }

    function posTooltip(e) {
        const vw = window.innerWidth;
        const vh = window.innerHeight;
        let x = e.clientX + 14;
        let y = e.clientY + 14;
        const tw = tooltip.offsetWidth || 220;
        const th = tooltip.offsetHeight || 80;
        if (x + tw > vw - 8) x = e.clientX - tw - 14;
        if (y + th > vh - 8) y = e.clientY - th - 14;
        tooltip.style.left = x + 'px';
        tooltip.style.top  = y + 'px';
    }

    function hideTooltip() {
        tooltip.style.opacity = '0';
        setTimeout(() => { tooltip.style.display = 'none'; }, 150);
    }

    document.addEventListener('mouseover', function(e) {
        const el = e.target.closest('.sh-cal-leave[data-leave-name]');
        if (el) showTooltip(el, e);
    });
    document.addEventListener('mousemove', function(e) {
        if (tooltip.style.display !== 'none') posTooltip(e);
    });
    document.addEventListener('mouseout', function(e) {
        const el = e.target.closest('.sh-cal-leave[data-leave-name]');
        if (el) hideTooltip();
    });
})();

// #34 Swipe gesture for month navigation (mobile)
(function() {
    var calGrid = document.querySelector('.card.sh-card');
    if (!calGrid) return;
    var startX = 0, startY = 0;
    var prevUrl = @json(route('kalender', ['year' => $prevYear, 'month' => $prevMonth]));
    var nextUrl = @json(route('kalender', ['year' => $nextYear, 'month' => $nextMonth]));

    calGrid.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    }, { passive: true });

    calGrid.addEventListener('touchend', function(e) {
        var dx = e.changedTouches[0].clientX - startX;
        var dy = e.changedTouches[0].clientY - startY;
        if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy) * 1.5) {
            if (dx < 0) {
                // Swipe left → next month
                window.location.href = nextUrl;
            } else {
                // Swipe right → prev month
                window.location.href = prevUrl;
            }
        }
    }, { passive: true });
})();
</script>
@endpush
@endsection
