@extends('layouts.app')

@section('title', 'Laporan Saldo Cuti - SiHEALING')

@section('content')
<nav class="sh-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sh-breadcrumb-sep"><i class="ti ti-chevron-right" style="font-size:0.7rem;"></i></span>
    <span class="sh-breadcrumb-current">Laporan Saldo Cuti</span>
</nav>

<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sh-page-title mb-1">
                <i class="ti ti-report me-1" style="color:var(--sh-primary);"></i>
                Laporan Saldo Cuti
            </h2>
            <div class="text-muted" style="font-size:0.85rem;">Sisa cuti tahunan seluruh pegawai tahun {{ $year }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('laporan-saldo-cuti.export', array_merge(request()->query(), ['format' => 'excel'])) }}"
               class="btn btn-outline-success sh-export-btn"
               style="border-radius:10px;">
                <i class="ti ti-file-spreadsheet me-1"></i> Export Excel
            </a>
            <a href="{{ route('laporan-saldo-cuti.export', request()->query()) }}"
               class="btn btn-outline-secondary sh-export-btn"
               style="border-radius:10px;">
                <i class="ti ti-download me-1"></i> Export CSV
            </a>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card sh-card mb-4">
    <div class="card-body p-3">
        <form method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" style="font-weight:600;font-size:0.8rem;">Cari Pegawai</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama atau NIP..." value="{{ $search }}" style="border-radius:10px;border:2px solid #e2e8f0;height:46px;">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" style="font-weight:600;font-size:0.8rem;">Unit Kerja</label>
                    <select name="unit_kerja" class="form-select" style="border-radius:10px;border:2px solid #e2e8f0;height:46px;">
                        <option value="">Semua Unit</option>
                        @foreach($unitList as $unit)
                        <option value="{{ $unit }}" {{ $unitKerja === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn sh-btn-primary flex-fill" style="height:46px;">
                            <i class="ti ti-search me-1"></i> Filter
                        </button>
                        @if($search || $unitKerja)
                        <a href="{{ route('laporan-saldo-cuti.index') }}" class="btn btn-outline-secondary" style="border-radius:10px;height:46px;display:flex;align-items:center;">
                            <i class="ti ti-x"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card sh-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">
            <i class="ti ti-calendar-stats me-2" style="color:var(--sh-primary);"></i>
            Saldo Cuti Tahunan {{ $year }}
        </h3>
        <span class="text-muted" style="font-size:0.8rem;">{{ count($saldoData) }} pegawai</span>
    </div>

    @if(empty($saldoData))
    <div class="card-body py-5 text-center">
        <div class="sh-empty-icon"><i class="ti ti-report-off"></i></div>
        <p class="text-muted mb-0">Tidak ada data pegawai ditemukan.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table sh-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pegawai</th>
                    <th>Unit Kerja</th>
                    <th class="text-center">Hak Cuti</th>
                    <th class="text-center">C/O</th>
                    <th class="text-center">Terpencil</th>
                    <th class="text-center">Total Hak</th>
                    <th class="text-center">Diambil</th>
                    <th class="text-center">Sisa</th>
                    <th class="text-center" title="Estimasi sisa cuti pada 31 Desember berdasarkan rata-rata penggunaan s/d bulan ini">
                        Prediksi Sisa <i class="ti ti-info-circle text-muted" style="font-size:0.75rem;"></i>
                    </th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
            @foreach($saldoData as $i => $row)
                @php $sisa = $row['sisa_cuti']; @endphp
                <tr>
                    <td class="text-muted" style="font-size:0.82rem;">{{ $i + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($row['user']->photo)
                            <img src="{{ Storage::url($row['user']->photo) }}" alt="" style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                            @else
                            <div class="sh-user-avatar" style="width:34px;height:34px;font-size:0.7rem;background:var(--sh-primary-light);color:var(--sh-primary);border:none;border-radius:50%;">
                                {{ strtoupper(substr($row['user']->name, 0, 2)) }}
                            </div>
                            @endif
                            <div>
                                <div class="fw-bold" style="font-size:0.88rem;">{{ $row['user']->name }}</div>
                                <div class="text-muted" style="font-size:0.75rem;">{{ $row['user']->jabatan ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:0.85rem;">{{ $row['user']->unit_kerja ?? '-' }}</td>
                    <td class="text-center">{{ $row['hak_cuti'] }}</td>
                    <td class="text-center text-muted">{{ $row['carry_over'] ?: '-' }}</td>
                    <td class="text-center text-muted">{{ $row['tambahan_terpencil'] ?: '-' }}</td>
                    <td class="text-center fw-semibold">{{ $row['total_hak'] }}</td>
                    <td class="text-center">{{ $row['cuti_diambil'] }}</td>
                    <td class="text-center">
                        <span class="fw-bold" style="color:{{ $sisa <= 3 ? 'var(--sh-danger)' : ($sisa <= 6 ? 'var(--sh-warning)' : 'var(--sh-primary)') }};">
                            {{ $sisa }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $row['prediksi_sisa'] >= 5 ? 'bg-success-subtle text-success' : ($row['prediksi_sisa'] >= 2 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') }}">
                            {{ $row['prediksi_sisa'] }} hari
                        </span>
                        @if($row['rata_per_bulan'] > 0)
                        <div class="text-muted" style="font-size:0.7rem;">~{{ $row['rata_per_bulan'] }} hr/bln</div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($sisa <= 0)
                        <span class="sh-badge" style="background:var(--sh-danger-light);color:var(--sh-danger);">Habis</span>
                        @elseif($sisa <= 3)
                        <span class="sh-badge" style="background:var(--sh-warning-light);color:var(--sh-warning);">Rendah</span>
                        @else
                        <span class="sh-badge" style="background:var(--sh-success-light);color:var(--sh-success);">Normal</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @if(!empty($saldoData))
    <div class="card-body pt-0 pb-3">
        <div class="text-muted mt-3" style="font-size:0.75rem;">
            <i class="ti ti-calculator me-1"></i>
            * Prediksi berdasarkan rata-rata penggunaan s/d {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}. Belum termasuk cuti yang sudah diajukan tapi belum disetujui.
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
// Progress bar for export button
document.querySelectorAll('.sh-export-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var icon = btn.querySelector('i');
        if (icon) icon.className = 'spinner-border spinner-border-sm me-1';
        setTimeout(function() { if (icon) icon.className = 'ti ti-download me-1'; }, 3000);
    });
});
</script>
@endpush
@endsection
