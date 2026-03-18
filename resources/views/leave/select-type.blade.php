@extends('layouts.app')

@section('title', 'Pilih Jenis Cuti — SiCAIR')

@section('content')
{{-- T19: Sticky saldo bar (mobile only) --}}
@if(!Auth::user()->isAdmin())
<div class="sc-sticky-balance">
    <span><i class="ti ti-calendar-stats me-1"></i> Saldo Cuti Tahunan</span>
    <strong>{{ Auth::user()->leave_balance ?? 0 }} hari tersisa</strong>
</div>
@endif
{{-- Breadcrumb (#10) --}}
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Pilih Jenis Cuti</span>
</nav>

{{-- Page Header --}}
<div class="sc-page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;" aria-label="Kembali ke dashboard">
            <i class="ti ti-arrow-left" style="font-size: 1.2rem;" aria-hidden="true"></i>
        </a>
        <div>
            <h2 class="sc-page-title mb-0">Ajukan Cuti</h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Pilih jenis cuti yang ingin Anda ajukan
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Cuti Tahunan --}}
    <div class="col-md-6 animate-in">
        <a href="{{ route('leave.create', array_filter(['type' => 'cuti_tahunan', 'start' => $prefillStart ?? null])) }}" class="text-decoration-none" aria-label="Ajukan Cuti Tahunan">
            <div class="card sc-card sc-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sc-leave-type-icon" style="background: var(--sc-primary-light); color: var(--sc-primary);">
                            <i class="ti ti-calendar-stats"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: var(--sc-text); font-size: 1.05rem;">Cuti Tahunan</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                12 hari kerja/tahun. Syarat: bekerja min 1 tahun.
                            </p>
                            <span class="btn btn-sm sc-btn-primary">
                                <i class="ti ti-arrow-right me-1"></i> Ajukan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Cuti Besar --}}
    <div class="col-md-6 animate-in">
        <a href="{{ route('leave.create', array_filter(['type' => 'cuti_besar', 'start' => $prefillStart ?? null])) }}" class="text-decoration-none" aria-label="Ajukan Cuti Besar">
            <div class="card sc-card sc-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sc-leave-type-icon" style="background: #f3e8ff; color: #7c3aed;">
                            <i class="ti ti-calendar-month"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: var(--sc-text); font-size: 1.05rem;">Cuti Besar</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                Maks. 3 bulan. Syarat: masa kerja 5 tahun.
                            </p>
                            <span class="btn btn-sm" style="background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: #fff; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);">
                                <i class="ti ti-arrow-right me-1"></i> Ajukan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Cuti Sakit --}}
    <div class="col-md-6 animate-in">
        <a href="{{ route('leave.create', array_filter(['type' => 'cuti_sakit', 'start' => $prefillStart ?? null])) }}" class="text-decoration-none" aria-label="Ajukan Cuti Sakit">
            <div class="card sc-card sc-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sc-leave-type-icon" style="background: var(--sc-danger-light); color: var(--sc-danger);">
                            <i class="ti ti-stethoscope"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: var(--sc-text); font-size: 1.05rem;">Cuti Sakit</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                Maks. 1 tahun. Wajib surat dokter.
                            </p>
                            <span class="btn btn-sm sc-btn-danger">
                                <i class="ti ti-arrow-right me-1"></i> Ajukan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Cuti Melahirkan --}}
    <div class="col-md-6 animate-in">
        <a href="{{ route('leave.create', array_filter(['type' => 'cuti_melahirkan', 'start' => $prefillStart ?? null])) }}" class="text-decoration-none" aria-label="Ajukan Cuti Melahirkan">
            <div class="card sc-card sc-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sc-leave-type-icon" style="background: #fce7f3; color: #db2777;">
                            <i class="ti ti-baby-carriage"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: var(--sc-text); font-size: 1.05rem;">Cuti Melahirkan</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                3 bulan kalender. Anak ke-1, 2, 3 saat PNS.
                            </p>
                            <span class="btn btn-sm" style="background: linear-gradient(135deg, #db2777, #ec4899); color: #fff; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 8px rgba(219, 39, 119, 0.3);">
                                <i class="ti ti-arrow-right me-1"></i> Ajukan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Cuti Alasan Penting --}}
    <div class="col-md-6 animate-in">
        <a href="{{ route('leave.create', array_filter(['type' => 'cuti_alasan_penting', 'start' => $prefillStart ?? null])) }}" class="text-decoration-none" aria-label="Ajukan Cuti Alasan Penting">
            <div class="card sc-card sc-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sc-leave-type-icon" style="background: var(--sc-warning-light); color: var(--sc-warning);">
                            <i class="ti ti-urgent"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: var(--sc-text); font-size: 1.05rem;">Cuti Alasan Penting</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                Maks. 1 bulan. Keluarga sakit/meninggal, perkawinan, dll.
                            </p>
                            <span class="btn btn-sm" style="background: linear-gradient(135deg, #d97706, #f59e0b); color: #fff; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 8px rgba(217, 119, 6, 0.3);">
                                <i class="ti ti-arrow-right me-1"></i> Ajukan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Cuti di Luar Tanggungan Negara --}}
    <div class="col-md-6 animate-in">
        <a href="{{ route('leave.create', array_filter(['type' => 'cuti_luar_tanggungan', 'start' => $prefillStart ?? null])) }}" class="text-decoration-none" aria-label="Ajukan Cuti di Luar Tanggungan Negara">
            <div class="card sc-card sc-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sc-leave-type-icon" style="background: #f1f5f9; color: #64748b;">
                            <i class="ti ti-world"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: var(--sc-text); font-size: 1.05rem;">Cuti di Luar Tanggungan Negara</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                Maks. 3 tahun. Tanpa penghasilan.
                            </p>
                            <span class="btn btn-sm" style="background: linear-gradient(135deg, #475569, #64748b); color: #fff; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 8px rgba(71, 85, 105, 0.3);">
                                <i class="ti ti-arrow-right me-1"></i> Ajukan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

@push('scripts')
<style>
    .sc-leave-type-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }
    .sc-leave-type-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        cursor: pointer;
    }
    .sc-leave-type-card:hover,
    .sc-leave-type-card:focus-within {
        border-color: var(--sc-primary);
        box-shadow: 0 6px 20px rgba(22, 101, 52, 0.14);
        transform: translateY(-2px);
    }
    .sc-leave-type-card:hover .sc-leave-type-icon,
    .sc-leave-type-card:focus-within .sc-leave-type-icon {
        transform: scale(1.15) rotate(-5deg);
    }
    .sc-leave-type-card:active {
        transform: translateY(-1px);
    }
</style>
@endpush
@endsection
