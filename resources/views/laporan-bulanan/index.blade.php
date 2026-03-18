@extends('layouts.app')

@section('title', 'Laporan Bulanan — SiCAIR')

@section('content')
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Laporan Bulanan</span>
</nav>

<div class="sc-page-header">
    <div>
        <h2 class="sc-page-title mb-1">
            <i class="ti ti-file-spreadsheet me-1" style="color: var(--sc-primary);" aria-hidden="true"></i>
            Laporan Bulanan
        </h2>
        <div class="text-muted" style="font-size: 0.85rem;">
            Export rekap cuti &amp; dinas luar ke Excel (.xlsx)
        </div>
    </div>
</div>

<div class="row g-4" style="max-width: 720px;">
    <div class="col-12">
        <div class="card sc-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-download me-2" style="color: var(--sc-primary);"></i>
                    Pilih Periode Laporan
                </h3>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="{{ route('laporan-bulanan.export') }}">
                    <div class="row g-3">
                        <div class="col-sm-5">
                            <label class="form-label fw-semibold">Bulan</label>
                            <select name="month" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0;">
                                @foreach(['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $num => $nama)
                                <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-semibold">Tahun</label>
                            <select name="year" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0;">
                                @for($y = date('Y') + 1; $y >= 2022; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-sm-4 d-flex align-items-end">
                            <button type="submit" class="btn sc-btn-primary w-100" style="height: 42px;">
                                <i class="ti ti-file-spreadsheet me-2"></i> Download Excel
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-4 p-3" style="background: var(--sc-gray-50); border-radius: 12px; border: 1px solid var(--sc-border);">
                    <div class="fw-semibold mb-2" style="font-size: 0.85rem; color: var(--sc-text);">
                        <i class="ti ti-info-circle me-1" style="color: var(--sc-primary);"></i>
                        Konten laporan Excel:
                    </div>
                    <ul class="mb-0" style="font-size: 0.82rem; color: var(--sc-text-muted); padding-left: 1.2rem; line-height: 1.8;">
                        <li><strong style="color: var(--sc-success);">Sheet 1 — Cuti Disetujui:</strong> Nama, NIP, Jabatan, Unit Kerja, Jenis Cuti, Tanggal, Jumlah Hari Kerja</li>
                        <li><strong style="color: #ea580c;">Sheet 2 — Dinas Luar:</strong> Nama, NIP, Jabatan, Tujuan Dinas, Tanggal, Durasi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
