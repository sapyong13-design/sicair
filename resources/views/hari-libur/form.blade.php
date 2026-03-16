@extends('layouts.app')

@section('title', ($hariLibur ? 'Edit' : 'Tambah') . ' Hari Libur - SiCAIR')

@section('content')
{{-- Breadcrumb (#10) --}}
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <a href="{{ route('hari-libur.index') }}">Hari Libur</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">{{ $hariLibur ? 'Edit' : 'Tambah' }}</span>
</nav>

<div class="sc-page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('hari-libur.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;" aria-label="Kembali ke daftar hari libur">
            <i class="ti ti-arrow-left" style="font-size: 1.2rem;" aria-hidden="true"></i>
        </a>
        <h2 class="sc-page-title mb-0">{{ $hariLibur ? 'Edit Hari Libur' : 'Tambah Hari Libur' }}</h2>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card sc-card">
            <div class="card-body p-4">
                {{-- Error display (#3) --}}
                @if($errors->any())
                <div class="alert mb-4" role="alert" style="background: var(--sc-danger-light); color: var(--sc-danger); border-radius: 12px; border: none;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="ti ti-alert-circle" style="font-size: 1.2rem; margin-top: 2px;" aria-hidden="true"></i>
                        <div>
                            <div class="fw-bold mb-1">Terjadi Kesalahan</div>
                            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ $hariLibur ? route('hari-libur.update', $hariLibur) : route('hari-libur.store') }}">
                    @csrf
                    @if($hariLibur) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                               value="{{ old('tanggal', $hariLibur?->tanggal?->format('Y-m-d')) }}" required
                               style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                        @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Keterangan <span class="text-danger">*</span></label>
                        <input type="text" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                               value="{{ old('keterangan', $hariLibur?->keterangan) }}" required placeholder="Contoh: Hari Raya Idul Fitri"
                               style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                        @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-check" style="cursor: pointer;">
                            <input class="form-check-input" type="checkbox" name="is_cuti_bersama" value="1"
                                   {{ old('is_cuti_bersama', $hariLibur?->is_cuti_bersama) ? 'checked' : '' }}>
                            <span class="form-check-label fw-semibold" style="font-size: 0.85rem;">
                                <i class="ti ti-users me-1" style="color: var(--sc-accent);"></i> Cuti Bersama
                            </span>
                        </label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn sc-btn-primary flex-fill">
                            <i class="ti ti-device-floppy me-1"></i> {{ $hariLibur ? 'Simpan' : 'Tambah' }}
                        </button>
                        <a href="{{ route('hari-libur.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
