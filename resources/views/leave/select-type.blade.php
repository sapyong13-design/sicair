@extends('layouts.app')

@section('title', 'Pilih Jenis Cuti - SiHEALING')

@section('content')
{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;">
            <i class="ti ti-arrow-left" style="font-size: 1.2rem;"></i>
        </a>
        <div>
            <h2 class="sh-page-title mb-0">Ajukan Cuti</h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Pilih jenis cuti yang ingin Anda ajukan
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Cuti Tahunan --}}
    <div class="col-md-6 animate-in">
        <a href="{{ route('leave.create', ['type' => 'cuti_tahunan']) }}" class="text-decoration-none">
            <div class="card sh-card sh-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sh-leave-type-icon" style="background: var(--sh-primary-light); color: var(--sh-primary);">
                            <i class="ti ti-calendar-stats"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.05rem;">Cuti Tahunan</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                12 hari kerja/tahun. Syarat: bekerja min 1 tahun.
                            </p>
                            <span class="btn btn-sm sh-btn-primary">
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
        <a href="{{ route('leave.create', ['type' => 'cuti_besar']) }}" class="text-decoration-none">
            <div class="card sh-card sh-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sh-leave-type-icon" style="background: #f3e8ff; color: #7c3aed;">
                            <i class="ti ti-calendar-month"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.05rem;">Cuti Besar</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                Max 3 bulan. Syarat: masa kerja 5 tahun.
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
        <a href="{{ route('leave.create', ['type' => 'cuti_sakit']) }}" class="text-decoration-none">
            <div class="card sh-card sh-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sh-leave-type-icon" style="background: var(--sh-danger-light); color: var(--sh-danger);">
                            <i class="ti ti-stethoscope"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.05rem;">Cuti Sakit</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                Max 1 tahun. Wajib surat dokter.
                            </p>
                            <span class="btn btn-sm sh-btn-danger">
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
        <a href="{{ route('leave.create', ['type' => 'cuti_melahirkan']) }}" class="text-decoration-none">
            <div class="card sh-card sh-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sh-leave-type-icon" style="background: #fce7f3; color: #db2777;">
                            <i class="ti ti-baby-carriage"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.05rem;">Cuti Melahirkan</h4>
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
        <a href="{{ route('leave.create', ['type' => 'cuti_alasan_penting']) }}" class="text-decoration-none">
            <div class="card sh-card sh-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sh-leave-type-icon" style="background: var(--sh-warning-light); color: var(--sh-warning);">
                            <i class="ti ti-urgent"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.05rem;">Cuti Alasan Penting</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                Max 1 bulan. Keluarga sakit/meninggal, perkawinan, dll.
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
        <a href="{{ route('leave.create', ['type' => 'cuti_luar_tanggungan']) }}" class="text-decoration-none">
            <div class="card sh-card sh-leave-type-card h-100" style="cursor: pointer;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="sh-leave-type-icon" style="background: #f1f5f9; color: #64748b;">
                            <i class="ti ti-world"></i>
                        </div>
                        <div class="flex-fill">
                            <h4 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.05rem;">Cuti di Luar Tanggungan Negara</h4>
                            <p class="text-muted mb-3" style="font-size: 0.85rem; line-height: 1.5;">
                                Max 3 tahun. Tanpa penghasilan.
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
    .sh-leave-type-icon {
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
    .sh-leave-type-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .sh-leave-type-card:hover {
        border-color: var(--sh-primary-light);
    }
    .sh-leave-type-card:hover .sh-leave-type-icon {
        transform: scale(1.1);
    }
</style>
@endpush
@endsection
