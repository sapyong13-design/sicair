@extends('layouts.app')

@section('title', 'Kalender Cuti - SiHEALING')

@section('content')
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sh-page-title mb-1">
                <i class="ti ti-calendar me-1" style="color: var(--sh-primary);"></i>
                Kalender Cuti
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Visualisasi jadwal cuti &amp; hari libur
            </div>
        </div>
    </div>
</div>

{{-- Month Navigator --}}
@php
    $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $prevMonth = $month == 1 ? 12 : $month - 1;
    $prevYear = $month == 1 ? $year - 1 : $year;
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $nextYear = $month == 12 ? $year + 1 : $year;
@endphp

<div class="card sh-card mb-4">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('kalender', ['year' => $prevYear, 'month' => $prevMonth]) }}" class="btn btn-outline-secondary" style="border-radius: 10px;">
                <i class="ti ti-chevron-left"></i>
            </a>
            <div class="text-center">
                <h3 class="fw-bold mb-0" style="color: var(--sh-primary);">{{ $months[$month - 1] }} {{ $year }}</h3>
            </div>
            <a href="{{ route('kalender', ['year' => $nextYear, 'month' => $nextMonth]) }}" class="btn btn-outline-secondary" style="border-radius: 10px;">
                <i class="ti ti-chevron-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Calendar Grid --}}
<div class="card sh-card">
    <div class="card-body p-3">
        @php
            $firstDay = mktime(0, 0, 0, $month, 1, $year);
            $daysInMonth = date('t', $firstDay);
            $startDay = date('N', $firstDay); // 1=Mon 7=Sun
            $today = date('Y-m-d');

            // Build leave map: date => [leaves]
            $leaveMap = [];
            foreach ($leaves as $leave) {
                $s = max(strtotime($startOfMonth ?? "{$year}-{$month}-01"), strtotime($leave->start_date));
                $e = min(strtotime($endOfMonth ?? date('Y-m-t', $firstDay)), strtotime($leave->end_date));
                for ($d = $s; $d <= $e; $d += 86400) {
                    $key = date('j', $d);
                    $leaveMap[$key][] = $leave;
                }
            }

            // Build holiday map (with full object to get is_cuti_bersama)
            $holidayMap = [];
            foreach ($holidays as $h) {
                $hDate = \Carbon\Carbon::parse($h->tanggal);
                if ($hDate->month == $month) {
                    $holidayMap[$hDate->day] = $h;
                }
            }
        @endphp

        {{-- Desktop Calendar --}}
        <div class="d-none d-md-block">
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
                                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $dayCount);
                                        $isToday = $dateStr === $today;
                                        $isWeekend = $col >= 6;
                                        $isHoliday = isset($holidayMap[$dayCount]);
                                        $dayLeaves = $leaveMap[$dayCount] ?? [];
                                    @endphp
                                    <td style="vertical-align: top; min-height: 90px; padding: 0.4rem; position: relative; {{ $isToday ? 'background: var(--sh-primary-light);' : ($isWeekend || $isHoliday ? 'background: #fef2f2;' : '') }}">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="fw-bold {{ $isToday ? 'sh-cal-today' : '' }}" style="font-size: 0.85rem; {{ $isWeekend || $isHoliday ? 'color: var(--sh-danger);' : 'color: #1e293b;' }}">
                                                {{ $dayCount }}
                                            </span>
                                        </div>
                                        @if($isHoliday)
                                        @php $holiday = $holidayMap[$dayCount]; @endphp
                                        <div class="sh-cal-event {{ $holiday->is_cuti_bersama ? 'sh-cal-cuti-bersama' : 'sh-cal-holiday' }}" title="{{ $holiday->keterangan }}">
                                            <i class="ti {{ $holiday->is_cuti_bersama ? 'ti-check' : 'ti-flag-filled' }}" style="font-size: 0.65rem;"></i>
                                            {{ Str::limit($holiday->keterangan, 12) }}
                                        </div>
                                        @endif
                                        @foreach(array_slice($dayLeaves, 0, 2) as $lv)
                                        <div class="sh-cal-event sh-cal-leave" title="{{ $lv->user->name }} - {{ $lv->type_label }}">
                                            {{ Str::limit($lv->user->name, 10) }}
                                        </div>
                                        @endforeach
                                        @if(count($dayLeaves) > 2)
                                        <div class="sh-cal-event" style="background: var(--sh-gray-100); color: #64748b; font-size: 0.65rem;">
                                            +{{ count($dayLeaves) - 2 }} lagi
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
                                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $dayCount);
                                $isToday = $dateStr === $today;
                                $isWeekend = $col >= 6;
                                $isHoliday = isset($holidayMap[$dayCount]);
                                $hasLeave = isset($leaveMap[$dayCount]);
                            @endphp
                            <div class="col text-center" style="min-height: 36px;">
                                <div class="d-flex flex-column align-items-center">
                                    <span style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: {{ $isToday ? '800' : '500' }}; {{ $isToday ? 'background: var(--sh-primary); color: #fff;' : ($isWeekend || $isHoliday ? 'color: var(--sh-danger);' : 'color: #1e293b;') }}">
                                        {{ $dayCount }}
                                    </span>
                                    @if($hasLeave)
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--sh-success); margin-top: 2px;"></span>
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
                    if (isset($leaveMap[$d]) || isset($holidayMap[$d])) {
                        $monthEvents[$d] = ['leaves' => $leaveMap[$d] ?? [], 'holiday' => $holidayMap[$d] ?? null];
                    }
                }
            @endphp
            @if(!empty($monthEvents))
            <div class="mt-3 border-top pt-3">
                <div class="fw-bold mb-2" style="font-size: 0.85rem; color: var(--sh-primary);">
                    <i class="ti ti-list me-1"></i> Event Bulan Ini
                </div>
                @foreach($monthEvents as $d => $ev)
                <div class="mb-2 p-2" style="background: var(--sh-gray-50); border-radius: 8px; font-size: 0.82rem;">
                    <div class="fw-bold" style="color: #1e293b;">{{ $d }} {{ $months[$month - 1] }}</div>
                    @if($ev['holiday'])
                    <div style="color: {{ $ev['holiday']->is_cuti_bersama ? 'var(--sh-primary)' : 'var(--sh-danger)' }};">
                        <i class="ti {{ $ev['holiday']->is_cuti_bersama ? 'ti-check' : 'ti-flag-filled' }} me-1" style="font-size: 0.7rem;"></i> {{ $ev['holiday']->keterangan }}
                    </div>
                    @endif
                    @foreach($ev['leaves'] as $lv)
                    <div style="color: var(--sh-success);">
                        <i class="ti ti-beach me-1" style="font-size: 0.7rem;"></i>
                        {{ $lv->user->name }} - {{ $lv->type_label }}
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Legend --}}
    <div class="card-footer" style="background: #fff; border-top: 2px solid var(--sh-gray-100);">
        <div class="d-flex flex-wrap gap-3" style="font-size: 0.78rem;">
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: var(--sh-primary);"></span>
                Hari Ini
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: var(--sh-success);"></span>
                Cuti Disetujui
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: var(--sh-primary-light); border-left: 3px solid var(--sh-primary);"></span>
                Cuti Bersama
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: var(--sh-danger);"></span>
                Hari Libur Nasional
            </div>
            <div class="d-flex align-items-center gap-1">
                <span style="width: 12px; height: 12px; border-radius: 3px; background: #fef2f2; border: 1px solid #fca5a5;"></span>
                Weekend / Libur
            </div>
        </div>
    </div>
</div>

{{-- Legend cards for leaves --}}
@if($leaves->isNotEmpty())
<div class="card sh-card mt-4">
    <div class="card-header">
        <h3 class="card-title mb-0">
            <i class="ti ti-beach me-2" style="color: var(--sh-success);"></i>
            Cuti Bulan {{ $months[$month - 1] }} {{ $year }}
        </h3>
    </div>
    <div class="card-body p-3">
        @foreach($leaves as $lv)
        <div class="d-flex align-items-center gap-3 mb-2 p-2" style="background: var(--sh-success-light); border-radius: 10px;">
            <div class="sh-user-avatar" style="width: 36px; height: 36px; font-size: 0.7rem; background: var(--sh-success); color: #fff; border: none; border-radius: 8px;">
                {{ strtoupper(substr($lv->user->name, 0, 2)) }}
            </div>
            <div class="flex-fill">
                <div class="fw-bold" style="font-size: 0.85rem;">{{ $lv->user->name }}</div>
                <div class="text-muted" style="font-size: 0.75rem;">
                    {{ $lv->type_label }} &middot; {{ $lv->start_date->format('d M') }} - {{ $lv->end_date->format('d M Y') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
