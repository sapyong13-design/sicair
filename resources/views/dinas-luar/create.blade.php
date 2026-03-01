@extends('layouts.app')

@section('title', 'Tambah Dinas Luar - SiHEALING')

@section('content')
<nav class="sh-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sh-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <a href="{{ route('dinas-luar.index') }}">Dinas Luar</a>
    <span class="sh-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sh-breadcrumb-current">Tambah</span>
</nav>

<div class="sh-page-header">
    <h2 class="sh-page-title mb-0">
        <i class="ti ti-briefcase me-1" style="color: #ea580c;" aria-hidden="true"></i> Tambah Dinas Luar
    </h2>
</div>

<div class="card sh-card" style="max-width: 720px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('dinas-luar.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="user_id" class="form-label fw-semibold">Pegawai <span class="text-danger">*</span></label>
                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                    <option value="">— Pilih Pegawai —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}@if($u->jabatan) — {{ $u->jabatan }}@endif
                    </option>
                    @endforeach
                </select>
                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="tujuan" class="form-label fw-semibold">Tujuan Dinas <span class="text-danger">*</span></label>
                <input type="text" name="tujuan" id="tujuan" class="form-control @error('tujuan') is-invalid @enderror"
                       value="{{ old('tujuan') }}" placeholder="Contoh: Jakarta, Batam, Tanjungpinang" required>
                @error('tujuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="keperluan" class="form-label fw-semibold">Keperluan / Perihal <span class="text-danger">*</span></label>
                <textarea name="keperluan" id="keperluan" rows="3"
                          class="form-control @error('keperluan') is-invalid @enderror"
                          placeholder="Uraikan keperluan dinas luar" required>{{ old('keperluan') }}</textarea>
                @error('keperluan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="start_date" class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="start_date"
                           class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date') }}" required>
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label for="end_date" class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" id="end_date"
                           class="form-control @error('end_date') is-invalid @enderror"
                           value="{{ old('end_date') }}" required>
                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="keterangan" class="form-label fw-semibold">Keterangan <span class="text-muted fw-normal">(opsional)</span></label>
                <textarea name="keterangan" id="keterangan" rows="2"
                          class="form-control @error('keterangan') is-invalid @enderror"
                          placeholder="Catatan tambahan jika ada">{{ old('keterangan') }}</textarea>
                @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="dokumen" class="form-label fw-semibold">
                    Bukti / Dokumen <span class="text-muted fw-normal">(opsional)</span>
                </label>
                <input type="file" name="dokumen" id="dokumen"
                       class="form-control @error('dokumen') is-invalid @enderror"
                       accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text">Format: PDF, JPG, PNG. Maksimal 5 MB.</div>
                @error('dokumen')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn sh-btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Simpan
                </button>
                <a href="{{ route('dinas-luar.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
