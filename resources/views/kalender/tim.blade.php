@extends('layouts.app')

@section('title', 'Kalender Tim - SiCAIR')

@section('content')
{{-- Breadcrumb --}}
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <a href="{{ route('kalender') }}">Kalender Cuti</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Kalender Tim</span>
</nav>

@php
    $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $monthName  = $monthNames[$month - 1];

    // Build a lookup: day -> collection of leaves active on that day
    $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
    $dayLeaves   = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $date = \Carbon\Carbon::create($year, $month, $d);
        $dayLeaves[$d] = $leaves->filter(function ($lv) use ($date) {
            return $lv->start_date->lte($date) && $lv->end_date->gte($date);
        });
    }

    // First day of month (0=Sun, 1=Mon, ..., 6=Sat) — shift so Monday=0
    $firstDow = \Carbon\Carbon::create($year, $month, 1)->dayOfWeek; // 0=Sun
    $startOffset = ($firstDow === 0) ? 6 : $firstDow - 1; // Monday-based grid

    // Colour palette for employee avatars
    $palette = ['#4361ee','#3a86ff','#7209b7','#f72585','#4cc9f0','#06d6a0','#fb8500','#8338ec','#e63946','#2d6a4f'];
    $userColours = [];
    $colIdx = 0;
    foreach ($leaves->pluck('user')->unique('id')->filter() as $u) {
        $userColours[$u->id] = $palette[$colIdx % count($palette)];
        $colIdx++;
    }
@endphp

<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sc-page-title mb-1">
                <i class="ti ti-users me-1" style="color: var(--sc-primary);" aria-hidden="true"></i>
                Kalender Tim
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Jadwal cuti bawahan yang telah disetujui — {{ $monthName }} {{ $year }}
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{-- Month navigation --}}
            <a href="{{ route('kalender.tim', ['month' => $prevMonth, 'year' => $prevYear]) }}"
               class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;" title="Bulan sebelumnya">
                <i class="ti ti-chevron-left"></i>
            </a>
            <span class="fw-semibold" style="font-size: 0.95rem; min-width: 130px; text-align: center;">{{ $monthName }} {{ $year }}</span>
            <a href="{{ route('kalender.tim', ['month' => $nextMonth, 'year' => $nextYear]) }}"
               class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;" title="Bulan berikutnya">
                <i class="ti ti-chevron-right"></i>
            </a>
            <a href="{{ route('kalender.tim', ['month' => now()->month, 'year' => now()->year]) }}"
               class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-size: 0.82rem;">
                Bulan Ini
            </a>
            <a href="{{ route('kalender') }}" class="btn btn-sm btn-outline-secondary ms-1" style="border-radius: 8px; font-size: 0.82rem;">
                <i class="ti ti-calendar me-1"></i>Kalender Saya
            </a>
        </div>
    </div>
</div>

{{-- Summary stats --}}
@php
    $totalPegawai = $leaves->pluck('user_id')->unique()->count();
    $totalCuti    = $leaves->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-body py-3 text-center">
                <div class="fw-bold" style="font-size: 1.6rem; color: var(--sc-primary);">{{ $totalPegawai }}</div>
                <div class="text-muted" style="font-size: 0.8rem;">Pegawai Cuti Bulan Ini</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-body py-3 text-center">
                <div class="fw-bold" style="font-size: 1.6rem; color: #3a86ff;">{{ $totalCuti }}</div>
                <div class="text-muted" style="font-size: 0.8rem;">Total Pengajuan Cuti</div>
            </div>
        </div>
    </div>
</div>

{{-- Month grid calendar --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px; overflow: hidden;">
    <div class="card-header d-flex align-items-center justify-content-between py-3 px-4" style="background: var(--sc-primary); color: #fff; border-bottom: none;">
        <span class="fw-semibold" style="font-size: 1rem;">
            <i class="ti ti-calendar-month me-2"></i>{{ $monthName }} {{ $year }}
        </span>
        <span class="badge bg-white text-primary" style="font-size: 0.78rem;">{{ $totalCuti }} pengajuan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0" style="table-layout: fixed; min-width: 700px;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $dayLabel)
                        <th class="text-center py-2" style="font-size: 0.78rem; font-weight: 600; color: #6c757d; width: 14.28%;">{{ $dayLabel }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $cell = 0;
                        $dayNum = 1;
                        $totalCells = $startOffset + $daysInMonth;
                        $rows = (int) ceil($totalCells / 7);
                    @endphp
                    @for($row = 0; $row < $rows; $row++)
                    <tr>
                        @for($col = 0; $col < 7; $col++)
                            @php
                                $cellIndex = $row * 7 + $col;
                                $isBlank   = ($cellIndex < $startOffset) || ($dayNum > $daysInMonth);
                                $currentDay = $isBlank ? null : $dayNum;
                                $isToday    = $currentDay && \Carbon\Carbon::create($year, $month, $currentDay)->isToday();
                                $isWeekend  = ($col >= 5); // Sat=5, Sun=6
                                $cellLeaves = $currentDay ? $dayLeaves[$currentDay] : collect();
                            @endphp
                            <td style="vertical-align: top; height: 90px; padding: 4px 5px; background: {{ $isBlank ? '#fafafa' : ($isToday ? '#eff6ff' : ($isWeekend ? '#f9f9fb' : '#fff')) }}; border-color: #e9ecef;">
                                @if($currentDay)
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span style="font-size: 0.78rem; font-weight: {{ $isToday ? '700' : '500' }}; color: {{ $isToday ? 'var(--sc-primary)' : ($isWeekend ? '#adb5bd' : '#343a40') }};">
                                        {{ $currentDay }}
                                    </span>
                                    @if($cellLeaves->isNotEmpty())
                                    <span class="badge" style="font-size: 0.62rem; background: var(--sc-primary); border-radius: 20px; padding: 2px 5px;">{{ $cellLeaves->count() }}</span>
                                    @endif
                                </div>
                                @if($cellLeaves->isNotEmpty())
                                <div class="d-flex flex-column gap-1" style="max-height: 65px; overflow-y: auto;">
                                    @foreach($cellLeaves->take(3) as $lv)
                                    @php
                                        $colour = $userColours[$lv->user_id] ?? '#4361ee';
                                        $initials = strtoupper(substr($lv->user->name ?? '?', 0, 2));
                                    @endphp
                                    <span class="d-flex align-items-center gap-1" style="font-size: 0.66rem; color: #fff; background: {{ $colour }}; border-radius: 4px; padding: 1px 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $lv->user->name ?? '-' }} — {{ $lv->type_label }}">
                                        <span style="font-weight: 700; font-size: 0.6rem; background: rgba(0,0,0,0.15); border-radius: 2px; padding: 0 2px;">{{ $initials }}</span>
                                        <span style="overflow: hidden; text-overflow: ellipsis;">{{ $lv->user->name ?? '-' }}</span>
                                    </span>
                                    @endforeach
                                    @if($cellLeaves->count() > 3)
                                    <span style="font-size: 0.62rem; color: #6c757d; text-align: center;">+{{ $cellLeaves->count() - 3 }} lainnya</span>
                                    @endif
                                </div>
                                @endif
                                @php if ($currentDay) $dayNum++; @endphp
                                @endif
                            </td>
                        @endfor
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Detail list per leave request --}}
<div class="card border-0 shadow-sm" style="border-radius: 14px;">
    <div class="card-header py-3 px-4" style="border-bottom: 1px solid #f0f0f0; background: #fff; border-radius: 14px 14px 0 0;">
        <h6 class="mb-0 fw-semibold" style="font-size: 0.9rem;">
            <i class="ti ti-list me-2" style="color: var(--sc-primary);"></i>
            Daftar Cuti — {{ $monthName }} {{ $year }}
        </h6>
    </div>
    @if($leaves->isEmpty())
    <div class="card-body text-center py-5 text-muted">
        <i class="ti ti-calendar-off" style="font-size: 2.5rem; opacity: 0.3;"></i>
        <p class="mt-2 mb-0" style="font-size: 0.9rem;">Tidak ada cuti bawahan yang disetujui pada bulan ini.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th class="py-2 px-3" style="font-weight: 600; color: #6c757d; width: 30%;">Pegawai</th>
                    <th class="py-2 px-3" style="font-weight: 600; color: #6c757d;">Jenis Cuti</th>
                    <th class="py-2 px-3" style="font-weight: 600; color: #6c757d;">Tanggal Mulai</th>
                    <th class="py-2 px-3" style="font-weight: 600; color: #6c757d;">Tanggal Selesai</th>
                    <th class="py-2 px-3 text-center" style="font-weight: 600; color: #6c757d;">Hari Kerja</th>
                    <th class="py-2 px-3" style="font-weight: 600; color: #6c757d;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaves as $lv)
                @php
                    $colour   = $userColours[$lv->user_id] ?? '#4361ee';
                    $initials = strtoupper(substr($lv->user->name ?? '?', 0, 2));
                @endphp
                <tr>
                    <td class="py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $colour }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; flex-shrink: 0;">{{ $initials }}</div>
                            <div>
                                <div class="fw-semibold" style="line-height: 1.2;">{{ $lv->user->name ?? '-' }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $lv->user->jabatan ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-2 px-3">
                        <span class="badge" style="background: {{ $colour }}1a; color: {{ $colour }}; border: 1px solid {{ $colour }}33; border-radius: 6px; font-size: 0.75rem; font-weight: 500;">
                            {{ $lv->type_label }}
                        </span>
                    </td>
                    <td class="py-2 px-3">{{ $lv->start_date->format('d M Y') }}</td>
                    <td class="py-2 px-3">{{ $lv->end_date->format('d M Y') }}</td>
                    <td class="py-2 px-3 text-center">
                        <span class="badge bg-light text-dark" style="border-radius: 20px; font-size: 0.78rem;">{{ $lv->total_hari_kerja ?? '-' }} hari</span>
                    </td>
                    <td class="py-2 px-3 text-muted" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $lv->reason ?? '-' }}">
                        {{ $lv->reason ? \Illuminate\Support\Str::limit($lv->reason, 50) : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
