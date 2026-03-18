@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi Tahunan — SiCAIR')

@section('content')
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Laporan Tahunan</span>
</nav>

<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div>
            <h2 class="sc-page-title mb-0">Laporan Rekapitulasi Cuti Tahunan</h2>
            <div class="text-muted" style="font-size: 0.85rem;">Rekap cuti disetujui per pegawai tahun {{ $year }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('laporan.tahunan.export', array_filter(['year' => $year, 'unit_kerja' => $unitKerja])) }}"
               class="btn sc-btn-primary" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="ti ti-file-spreadsheet me-1"></i> Export CSV
            </a>
            <a href="{{ route('laporan.tahunan.export', array_merge(array_filter(['year' => $year, 'unit_kerja' => $unitKerja]), ['format' => 'excel'])) }}"
               class="btn btn-sm btn-success" style="border-radius: 10px; font-size: 0.85rem;">
                <i class="ti ti-file-spreadsheet me-1"></i> Excel
            </a>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card sc-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Tahun</label>
                <select name="year" class="form-select form-select-sm">
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold" style="font-size: 0.82rem;">Unit Kerja</label>
                <select name="unit_kerja" class="form-select form-select-sm">
                    <option value="">Semua Unit Kerja</option>
                    @foreach($unitList as $unit)
                        <option value="{{ $unit }}" @selected($unitKerja == $unit)>{{ $unit }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn sc-btn-primary btn-sm flex-fill">
                    <i class="ti ti-filter me-1"></i> Tampilkan
                </button>
                <a href="{{ route('laporan.tahunan') }}" class="btn btn-outline-secondary btn-sm flex-fill">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card sc-card">
    <div class="card-body p-0">
        @if($pegawaiList->isEmpty() || $leaveTypes->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="ti ti-file-off" style="font-size:3rem;opacity:0.3;"></i>
            <p class="mt-2">Tidak ada data cuti untuk tahun {{ $year }}.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table sc-table mb-0" style="font-size: 0.82rem; white-space: nowrap;">
                <thead>
                    <tr>
                        <th class="sticky-col" style="min-width: 180px;">Nama Pegawai</th>
                        <th style="min-width: 130px;">Unit Kerja</th>
                        @foreach($leaveTypes as $type)
                            <th class="text-center" style="min-width: 80px;">{{ $typeLabels[$type] ?? $type }}</th>
                        @endforeach
                        <th class="text-center fw-bold">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pegawaiList as $p)
                    @php
                        $userPivot = $pivot[$p->id] ?? [];
                        $rowTotal = array_sum($userPivot);
                    @endphp
                    @if($rowTotal > 0 || true)
                    <tr>
                        <td class="fw-semibold">{{ $p->name }}</td>
                        <td class="text-muted">{{ $p->unit_kerja ?? '-' }}</td>
                        @foreach($leaveTypes as $type)
                            <td class="text-center">
                                @if(isset($userPivot[$type]) && $userPivot[$type] > 0)
                                    <span style="color: var(--sc-primary); font-weight: 600;">{{ $userPivot[$type] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-center fw-bold" style="color: var(--sc-primary);">
                            {{ $rowTotal ?: '-' }}
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="2" class="fw-bold">Total</td>
                        @foreach($leaveTypes as $type)
                            @php $colTotal = array_sum(array_column(array_map(fn($p) => $pivot[$p->id] ?? [], $pegawaiList->all()), $type)); @endphp
                            <td class="text-center fw-bold" style="color: var(--sc-primary);">{{ $colTotal ?: '-' }}</td>
                        @endforeach
                        @php $grandTotal = collect($pivot)->map(fn($row) => array_sum($row))->sum(); @endphp
                        <td class="text-center fw-bold" style="color: var(--sc-primary);">{{ $grandTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>

<style>
.sticky-col {
    position: sticky;
    left: 0;
    background: var(--sc-card-bg);
    z-index: 1;
    box-shadow: 2px 0 4px rgba(0,0,0,0.05);
}
</style>
@endsection
