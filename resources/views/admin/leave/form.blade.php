@extends('layouts.app')

@section('title', ($leaveRequest ? 'Edit' : 'Tambah') . ' Riwayat Cuti - ' . $pegawai->name)

@section('content')

{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('pegawai.show', $pegawai) }}"
               class="btn btn-outline-secondary"
               style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;">
                <i class="ti ti-arrow-left" style="font-size: 1.2rem;"></i>
            </a>
            <div>
                <h2 class="sh-page-title mb-0">
                    {{ $leaveRequest ? 'Edit Riwayat Cuti' : 'Tambah Riwayat Cuti' }}
                </h2>
                <div class="text-muted" style="font-size: 0.85rem;">
                    Untuk: <strong>{{ $pegawai->name }}</strong>
                    &mdash; NIP {{ $pegawai->nip }}
                </div>
            </div>
        </div>
        {{-- Saldo Cuti Tahunan --}}
        <div class="d-flex align-items-center gap-2 px-3 py-2"
             style="background: var(--sh-primary-light); border-radius: 12px;">
            <i class="ti ti-calendar-stats" style="color: var(--sh-primary); font-size: 1.3rem;"></i>
            <div>
                <div style="font-size: 0.72rem; color: var(--sh-text-muted); font-weight: 600; text-transform: uppercase;">Saldo Cuti Tahunan</div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--sh-primary); line-height: 1;">{{ $pegawai->leave_balance }} hari</div>
            </div>
        </div>
    </div>
</div>

{{-- Info Banner --}}
<div class="alert sh-alert mb-4" style="background: var(--sh-warning-light); color: var(--sh-warning); border-left: 4px solid var(--sh-warning);">
    <div class="d-flex align-items-start gap-2">
        <i class="ti ti-alert-triangle" style="font-size: 1.3rem; flex-shrink: 0; margin-top: 2px;"></i>
        <div>
            <div class="fw-bold mb-1">Mode Admin — Bypass Validasi Bisnis</div>
            <div style="font-size: 0.875rem;">
                Entry ini <strong>melewati semua validasi</strong> (tanggal retroaktif, masa kerja, kuota, dll).
                Gunakan untuk memasukkan <strong>riwayat cuti historis</strong> atau koreksi data.
                Jika status disetujui & jenis Cuti Tahunan, <strong>saldo otomatis dikurangi</strong>.
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-file-plus me-2" style="color: var(--sh-primary);"></i>
                    {{ $leaveRequest ? 'Edit Data Cuti' : 'Input Riwayat Cuti' }}
                </h3>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert sh-alert alert-danger mb-4" style="background: var(--sh-danger-light); color: var(--sh-danger);">
                    <i class="ti ti-alert-circle me-2"></i>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST"
                      action="{{ $leaveRequest
                          ? route('admin.leave.update', $leaveRequest)
                          : route('admin.leave.store', $pegawai) }}">
                    @csrf
                    @if($leaveRequest)
                        @method('PUT')
                    @endif

                    <div class="row g-3">

                        {{-- Jenis Cuti --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Jenis Cuti <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="leaveType" class="form-select" required>
                                @foreach(\App\Models\LeaveRequest::typeLabels() as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('type', $leaveRequest?->type) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tanggal Mulai & Selesai --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="start_date" class="form-control"
                                   value="{{ old('start_date', $leaveRequest?->start_date?->format('Y-m-d')) }}"
                                   required>
                            <div class="form-text">Boleh tanggal lampau (retroaktif)</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Tanggal Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="end_date" class="form-control"
                                   value="{{ old('end_date', $leaveRequest?->end_date?->format('Y-m-d')) }}"
                                   required>
                        </div>

                        {{-- Status --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Status Cuti <span class="text-danger">*</span>
                            </label>
                            <select name="status" class="form-select" required>
                                @php
                                    $statusOptions = [
                                        'diajukan'              => 'Diajukan',
                                        'pertimbangan_atasan'   => 'Pertimbangan Atasan',
                                        'disetujui'             => 'Disetujui ✓',
                                        'diubah'                => 'Diubah',
                                        'ditangguhkan'          => 'Ditangguhkan',
                                        'ditolak'               => 'Ditolak',
                                    ];
                                @endphp
                                @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('status', $leaveRequest?->status ?? 'disetujui') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Pilih "Disetujui" untuk riwayat cuti yang sudah terlaksana.
                                Saldo cuti tahunan akan otomatis dikurangi jika disetujui.
                            </div>
                        </div>

                        {{-- Alasan --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Alasan / Keterangan <span class="text-danger">*</span>
                            </label>
                            <textarea name="reason" class="form-control" rows="3"
                                      placeholder="Contoh: Cuti tahunan Januari 2025 (data historis)..." required>{{ old('reason', $leaveRequest?->reason) }}</textarea>
                        </div>

                        {{-- Alasan CAP (conditional) --}}
                        <div class="col-12" id="capSection" style="display: none;">
                            <label class="form-label fw-semibold">Alasan CAP</label>
                            <select name="alasan_cap" class="form-select">
                                <option value="">-- Pilih Alasan --</option>
                                @foreach(\App\Models\LeaveRequest::capLabels() as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('alasan_cap', $leaveRequest?->alasan_cap) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kelahiran ke (conditional) --}}
                        <div class="col-12" id="kelahiranSection" style="display: none;">
                            <label class="form-label fw-semibold">Kelahiran ke-</label>
                            <input type="number" name="kelahiran_ke" class="form-control"
                                   min="1" max="3" placeholder="1, 2, atau 3"
                                   value="{{ old('kelahiran_ke', $leaveRequest?->kelahiran_ke) }}">
                        </div>

                        {{-- Alamat & Telepon --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Alamat Selama Cuti</label>
                            <input type="text" name="alamat_cuti" class="form-control"
                                   placeholder="Opsional..."
                                   value="{{ old('alamat_cuti', $leaveRequest?->alamat_cuti) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telepon Selama Cuti</label>
                            <input type="text" name="telepon_cuti" class="form-control"
                                   placeholder="Opsional..."
                                   value="{{ old('telepon_cuti', $leaveRequest?->telepon_cuti) }}">
                        </div>

                        {{-- Catatan Admin --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan Admin</label>
                            <textarea name="catatan_admin" class="form-control" rows="2"
                                      placeholder="Catatan internal admin (tidak ditampilkan ke pegawai)...">{{ old('catatan_admin') }}</textarea>
                        </div>

                        {{-- Divider --}}
                        <div class="col-12"><hr class="my-1"></div>

                        {{-- Action Buttons --}}
                        <div class="col-12 d-flex gap-2 justify-content-end">
                            <a href="{{ route('pegawai.show', $pegawai) }}"
                               class="btn btn-outline-secondary" style="border-radius: 10px; min-width: 100px;">
                                Batal
                            </a>
                            <button type="submit" class="btn sh-btn-primary text-white" style="min-width: 150px;">
                                <i class="ti ti-device-floppy me-1"></i>
                                {{ $leaveRequest ? 'Simpan Perubahan' : 'Tambah Riwayat' }}
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const typeSelect  = document.getElementById('leaveType');
    const capSection  = document.getElementById('capSection');
    const birthSec    = document.getElementById('kelahiranSection');

    function toggleConditional() {
        const val = typeSelect.value;
        capSection.style.display  = val === 'cuti_alasan_penting' ? '' : 'none';
        birthSec.style.display    = val === 'cuti_melahirkan'      ? '' : 'none';
    }

    typeSelect.addEventListener('change', toggleConditional);
    toggleConditional(); // run on load
})();
</script>
@endpush

@endsection
