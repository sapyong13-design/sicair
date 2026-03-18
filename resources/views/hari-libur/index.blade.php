@extends('layouts.app')

@section('title', 'Hari Libur — SiCAIR')

@section('content')
{{-- Breadcrumb (#10) --}}
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Hari Libur</span>
</nav>

<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="sc-page-title mb-0">
            <i class="ti ti-calendar-off me-1" style="color: var(--sc-danger);" aria-hidden="true"></i> Hari Libur {{ $tahun }}
        </h2>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" class="d-flex gap-2 align-items-center">
                <label for="tahun-select" class="visually-hidden">Pilih Tahun</label>
                <select name="tahun" id="tahun-select" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0; width: auto; height: 40px;" onchange="this.form.submit()">
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
            <form method="POST" action="{{ route('hari-libur.import-api') }}" class="d-inline"
                  onsubmit="return confirm('Import hari libur nasional Indonesia tahun {{ date('Y') }} dari internet?\nProses ini membutuhkan koneksi internet.')">
                @csrf
                <input type="hidden" name="year" value="{{ date('Y') }}">
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-cloud-download me-1"></i> Import Nasional {{ date('Y') }}
                </button>
            </form>
            <a href="{{ route('hari-libur.create') }}" class="btn sc-btn-primary">
                <i class="ti ti-plus me-1" aria-hidden="true"></i> Tambah
            </a>
        </div>
    </div>
</div>

<div class="card sc-card">
    @if($hariLibur->isEmpty())
    <div class="card-body py-5 text-center">
        <div class="sc-empty-icon"><i class="ti ti-calendar-off" aria-hidden="true"></i></div>
        <h4 class="fw-bold text-dark mb-1">Belum Ada Data</h4>
        <p class="text-muted mb-3">Belum ada hari libur yang terdaftar untuk tahun {{ $tahun }}.</p>
        <a href="{{ route('hari-libur.create') }}" class="btn sc-btn-primary"><i class="ti ti-plus me-1" aria-hidden="true"></i> Tambah Hari Libur</a>
    </div>
    @else
    <div class="table-responsive">
        <table class="table sc-table mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Tanggal</th>
                    <th>Hari</th>
                    <th>Keterangan</th>
                    <th>Jenis</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($hariLibur as $i => $hl)
                <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td class="fw-semibold">{{ \Carbon\Carbon::parse($hl->tanggal)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($hl->tanggal)->translatedFormat('l') }}</td>
                    <td>{{ $hl->keterangan }}</td>
                    <td>
                        @if($hl->is_cuti_bersama)
                            <span class="sc-badge" style="background: var(--sc-accent-light, #fef9c3); color: var(--sc-accent);">Cuti Bersama</span>
                        @else
                            <span class="sc-badge sc-badge-rejected">Libur Nasional</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('hari-libur.edit', $hl) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;" aria-label="Edit {{ $hl->keterangan }}"><i class="ti ti-edit" aria-hidden="true"></i></a>
                            {{-- Modal-based delete confirmation (#2) --}}
                            <button class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#deleteHolidayModal{{ $hl->id }}" aria-label="Hapus {{ $hl->keterangan }}">
                                <i class="ti ti-trash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted text-center" style="font-size: 0.82rem;">
        Total: {{ $hariLibur->count() }} hari ({{ $hariLibur->where('is_cuti_bersama', true)->count() }} cuti bersama, {{ $hariLibur->where('is_cuti_bersama', false)->count() }} libur nasional)
    </div>
    @endif
</div>

{{-- Delete Modals for Holidays (#2) --}}
@if(isset($hariLibur) && $hariLibur->isNotEmpty())
@foreach($hariLibur as $hl)
<div class="modal modal-blur fade" id="deleteHolidayModal{{ $hl->id }}" tabindex="-1" aria-labelledby="deleteHolidayLabel{{ $hl->id }}" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <form method="POST" action="{{ route('hari-libur.destroy', $hl) }}">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4 text-center">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--sc-danger-light); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="ti ti-alert-triangle" style="font-size: 2rem; color: var(--sc-danger);" aria-hidden="true"></i>
                    </div>
                    <h3 class="fw-bold mb-1" id="deleteHolidayLabel{{ $hl->id }}">Hapus Hari Libur?</h3>
                    <p class="text-muted mb-1">Anda yakin ingin menghapus:</p>
                    <p class="mb-0">
                        <strong class="text-dark">{{ $hl->keterangan }}</strong><br>
                        <span class="text-muted" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($hl->tanggal)->format('d M Y') }}</span>
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4" style="justify-content: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;">Batal</button>
                    <button type="submit" class="btn sc-btn-danger" style="min-width: 100px;">
                        <i class="ti ti-trash me-1" aria-hidden="true"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif
@endsection
