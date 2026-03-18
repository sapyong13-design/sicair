@extends('layouts.app')

@section('title', 'Template Alasan Cuti — SiCAIR')

@section('content')
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Template Alasan Cuti</span>
</nav>

<div class="sc-page-header">
    <h2 class="sc-page-title mb-0">
        <i class="ti ti-file-text me-2" style="color: var(--sc-primary);"></i> Template Alasan Cuti
    </h2>
    <div class="text-muted" style="font-size: 0.85rem;">Kelola template alasan pengajuan cuti pegawai</div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius: 12px;">
    <i class="ti ti-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    {{-- Left column: Add new template form --}}
    <div class="col-lg-4">
        <div class="card sc-card">
            <div class="card-header border-0 py-3" style="background: transparent;">
                <h6 class="mb-0 fw-bold">
                    <i class="ti ti-plus me-2" style="color: var(--sc-primary);"></i>
                    Tambah Template Baru
                </h6>
            </div>
            <div class="card-body pt-0">
                <form method="POST" action="{{ route('admin.leave-reason-templates.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem;">Label</label>
                        <input type="text" name="label" value="{{ old('label') }}"
                               class="form-control @error('label') is-invalid @enderror"
                               placeholder="Contoh: Cuti Sakit Ringan"
                               style="border-radius: 10px; border: 2px solid #e2e8f0; height: 42px;">
                        @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem;">Isi Template</label>
                        <textarea name="body" rows="5"
                                  class="form-control @error('body') is-invalid @enderror"
                                  placeholder="Tuliskan isi template alasan cuti..."
                                  style="border-radius: 10px; border: 2px solid #e2e8f0; font-size: 0.85rem;">{{ old('body') }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem;">Urutan</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                               min="0"
                               class="form-control @error('sort_order') is-invalid @enderror"
                               style="border-radius: 10px; border: 2px solid #e2e8f0; max-width: 140px; height: 42px;">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px; height: 42px; font-weight: 600;">
                        <i class="ti ti-plus me-1"></i> Tambah Template
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right column: Templates table --}}
    <div class="col-lg-8">
        <div class="card sc-card">
            <div class="card-header border-0 py-3 d-flex align-items-center justify-content-between" style="background: transparent;">
                <h6 class="mb-0 fw-bold">
                    <i class="ti ti-list me-2" style="color: var(--sc-primary);"></i>
                    Daftar Template ({{ $templates->count() }})
                </h6>
            </div>
            <div class="card-body pt-0 p-0">
                @if($templates->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-file-off" style="font-size: 2.5rem; opacity: 0.4;"></i>
                        <p class="mt-2 mb-0" style="font-size: 0.9rem;">Belum ada template. Tambahkan yang pertama!</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                            <thead style="background: var(--sc-surface); border-bottom: 2px solid #e2e8f0;">
                                <tr>
                                    <th class="ps-4" style="width: 70px;">Urutan</th>
                                    <th>Label</th>
                                    <th>Isi</th>
                                    <th style="width: 90px;">Status</th>
                                    <th style="width: 110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $tpl)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-secondary-lt" style="font-size: 0.8rem; border-radius: 6px;">
                                            {{ $tpl->sort_order }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $tpl->label }}</span>
                                    </td>
                                    <td class="text-muted" style="max-width: 220px;">
                                        <span title="{{ $tpl->body }}">
                                            {{ Str::limit($tpl->body, 60) }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.leave-reason-templates.toggle', $tpl) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm {{ $tpl->is_active ? 'btn-success' : 'btn-outline-secondary' }}"
                                                    style="border-radius: 8px; font-size: 0.75rem; padding: 3px 10px;"
                                                    title="{{ $tpl->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="ti {{ $tpl->is_active ? 'ti-toggle-right' : 'ti-toggle-left' }}"></i>
                                                {{ $tpl->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            {{-- Edit button triggers modal --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    style="border-radius: 8px; padding: 3px 8px;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $tpl->id }}"
                                                    title="Edit">
                                                <i class="ti ti-pencil"></i>
                                            </button>

                                            {{-- Delete form --}}
                                            <form method="POST"
                                                  action="{{ route('admin.leave-reason-templates.destroy', $tpl) }}"
                                                  onsubmit="return confirm('Hapus template \'{{ addslashes($tpl->label) }}\'?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        style="border-radius: 8px; padding: 3px 8px;"
                                                        title="Hapus">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Edit Modals (one per template) --}}
@foreach($templates as $tpl)
<div class="modal fade" id="editModal{{ $tpl->id }}" tabindex="-1"
     aria-labelledby="editModalLabel{{ $tpl->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="editModalLabel{{ $tpl->id }}">
                    <i class="ti ti-pencil me-2" style="color: var(--sc-primary);"></i>
                    Edit Template
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="{{ route('admin.leave-reason-templates.update', $tpl) }}">
                @csrf
                @method('PUT')
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem;">Label</label>
                        <input type="text" name="label" value="{{ $tpl->label }}"
                               class="form-control" required
                               style="border-radius: 10px; border: 2px solid #e2e8f0; height: 42px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem;">Isi Template</label>
                        <textarea name="body" rows="5" required
                                  class="form-control"
                                  style="border-radius: 10px; border: 2px solid #e2e8f0; font-size: 0.85rem;">{{ $tpl->body }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem;">Urutan</label>
                        <input type="number" name="sort_order" value="{{ $tpl->sort_order }}"
                               min="0"
                               class="form-control"
                               style="border-radius: 10px; border: 2px solid #e2e8f0; max-width: 140px; height: 42px;">
                    </div>

                    <div class="mb-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   name="is_active" value="1"
                                   id="isActiveEdit{{ $tpl->id }}"
                                   {{ $tpl->is_active ? 'checked' : '' }}
                                   style="width: 2.5rem; height: 1.3rem;">
                            <label class="form-check-label fw-semibold" for="isActiveEdit{{ $tpl->id }}"
                                   style="font-size: 0.85rem;">
                                Aktif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary"
                            style="border-radius: 10px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"
                            style="border-radius: 10px; font-weight: 600;">
                        <i class="ti ti-device-floppy me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
