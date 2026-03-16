@extends('layouts.app')

@section('title', 'Rekap Cuti Per Unit Kerja - SiCAIR')

@section('content')
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Rekap Cuti Per Unit Kerja</span>
</nav>

<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div>
            <h2 class="sc-page-title mb-0">
                <i class="ti ti-building me-1" style="color:var(--sc-primary);"></i>
                Rekap Cuti Per Unit Kerja
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Rekapitulasi hari cuti disetujui per unit kerja
                tahun {{ $year }}{{ $bulan ? ' — ' . $bulanList[$bulan] : '' }}
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card sc-card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('laporan.unit-kerja') }}">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" style="font-weight:600;font-size:0.8rem;">Tahun</label>
                    <select name="year" class="form-select" style="border-radius:10px;border:2px solid #e2e8f0;height:46px;">
                        @foreach($years as $y)
                        <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" style="font-weight:600;font-size:0.8rem;">Bulan (Opsional)</label>
                    <select name="bulan" class="form-select" style="border-radius:10px;border:2px solid #e2e8f0;height:46px;">
                        <option value="">Semua Bulan</option>
                        @foreach($bulanList as $num => $nama)
                        <option value="{{ $num }}" {{ (int)$bulan === $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn sc-btn-primary flex-fill" style="height:46px;">
                            <i class="ti ti-search me-1"></i> Filter
                        </button>
                        @if($bulan)
                        <a href="{{ route('laporan.unit-kerja', ['year' => $year]) }}"
                           class="btn btn-outline-secondary"
                           style="border-radius:10px;height:46px;display:flex;align-items:center;">
                            <i class="ti ti-x"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card sc-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">
            <i class="ti ti-table me-2" style="color:var(--sc-primary);"></i>
            Rekap per Unit Kerja &mdash; {{ $year }}{{ $bulan ? ' / ' . $bulanList[$bulan] : '' }}
        </h3>
        <span class="text-muted" style="font-size:0.8rem;">{{ count($rows) }} unit kerja</span>
    </div>

    @if(empty($rows))
    <div class="card-body py-5 text-center">
        <div class="sc-empty-icon"><i class="ti ti-report-off"></i></div>
        <p class="text-muted mb-0">Tidak ada data cuti yang disetujui untuk periode ini.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-striped sc-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Unit Kerja</th>
                    <th class="text-center">Jml Pegawai</th>
                    @foreach($typeLabels as $type => $label)
                    <th class="text-center" title="{{ \App\Models\LeaveRequest::typeLabels()[$type] ?? $type }}">{{ $label }}</th>
                    @endforeach
                    <th class="text-center">TOTAL</th>
                    <th class="text-center">Rata-rata/Pegawai</th>
                </tr>
            </thead>
            <tbody>
            @foreach($rows as $i => $row)
                <tr>
                    <td class="text-muted" style="font-size:0.82rem;">{{ $i + 1 }}</td>
                    <td style="font-weight:600;font-size:0.88rem;">{{ $row['unit_kerja'] }}</td>
                    <td class="text-center">{{ $row['jumlah_pegawai'] ?: '-' }}</td>
                    @foreach($targetTypes as $type)
                    <td class="text-center">
                        @if(($row['types'][$type] ?? 0) > 0)
                            <span class="fw-semibold">{{ $row['types'][$type] }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="text-center">
                        <span class="fw-bold" style="color:var(--sc-primary);">{{ $row['total'] }}</span>
                    </td>
                    <td class="text-center text-muted" style="font-size:0.85rem;">
                        {{ $row['rata_rata'] > 0 ? $row['rata_rata'] . ' hr' : '-' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr class="table-active fw-bold">
                    <td colspan="2" class="text-end" style="font-size:0.88rem;">TOTAL</td>
                    <td class="text-center">
                        {{ collect($rows)->sum('jumlah_pegawai') ?: '-' }}
                    </td>
                    @foreach($targetTypes as $type)
                    <td class="text-center">{{ $totalPerType[$type] > 0 ? $totalPerType[$type] : '-' }}</td>
                    @endforeach
                    <td class="text-center" style="color:var(--sc-primary);">{{ $grandTotal }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <div class="card-body pt-2 pb-3">
        <div class="text-muted" style="font-size:0.75rem;">
            <i class="ti ti-info-circle me-1"></i>
            Keterangan: CT = Cuti Tahunan &nbsp;|&nbsp; CS = Cuti Sakit &nbsp;|&nbsp;
            CB = Cuti Besar &nbsp;|&nbsp; CAP = Cuti Alasan Penting &nbsp;|&nbsp;
            CLTN = Cuti Luar Tanggungan Negara.
            Angka menunjukkan jumlah hari kerja cuti yang telah disetujui.
        </div>
    </div>
</div>
@endsection
