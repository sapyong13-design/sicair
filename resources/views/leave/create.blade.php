@extends('layouts.app')

@section('title', 'Ajukan Cuti - SiHEALING')

@php
    $typeLabels = \App\Models\LeaveRequest::typeLabels();
    $capLabels = \App\Models\LeaveRequest::capLabels();
    $typeLabel = $typeLabels[$type] ?? 'Cuti';
    $user = Auth::user();
@endphp

@section('content')
{{-- Breadcrumb (#10) --}}
<nav class="sh-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sh-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <a href="{{ route('leave.select-type') }}">Pilih Jenis Cuti</a>
    <span class="sh-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sh-breadcrumb-current">{{ $typeLabel }}</span>
</nav>

{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('leave.select-type') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;" aria-label="Kembali ke pilih jenis cuti">
            <i class="ti ti-arrow-left" style="font-size: 1.2rem;" aria-hidden="true"></i>
        </a>
        <div>
            <h2 class="sh-page-title mb-0">Ajukan {{ $typeLabel }}</h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Isi formulir pengajuan cuti dengan lengkap
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Balance info card (Cuti Tahunan) --}}
        @if($type === 'cuti_tahunan' && $cutiInfo)
        <div class="card sh-stat-card stat-primary mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="sh-stat-label mb-1">Sisa Cuti Tahunan {{ date('Y') }}</div>
                        <div class="d-flex align-items-baseline gap-1">
                            <span class="sh-stat-number" style="color: var(--sh-primary);">{{ $cutiInfo['sisa'] ?? $user->leave_balance }}</span>
                            <span class="text-muted" style="font-size: 0.85rem;">/ {{ $cutiInfo['total_hak'] ?? 12 }} hari</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.78rem;">
                            Hak: {{ $cutiInfo['hak_dasar'] ?? 12 }}
                            @if(($cutiInfo['carry_over'] ?? 0) > 0) + CO: {{ $cutiInfo['carry_over'] }} @endif
                            @if(($cutiInfo['tambahan_terpencil'] ?? 0) > 0)
                            + Terpencil: {{ $cutiInfo['tambahan_terpencil'] }}
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                  title="Tambahan cuti tahunan bagi pegawai yang bertugas di daerah terpencil: 10 hari untuk sangat terpencil, 6 hari untuk terpencil."
                                  style="cursor:help;">
                                <i class="ti ti-info-circle" style="font-size:0.75rem;color:var(--sh-primary);"></i>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="sh-stat-icon icon-primary">
                        <i class="ti ti-calendar-stats"></i>
                    </div>
                </div>
                <div style="height: 6px; border-radius: 3px; background: var(--sh-gray-100); margin-top: 0.75rem;">
                    @php $hakTotal = $cutiInfo['total_hak'] ?? 12; @endphp
                    <div style="height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--sh-primary), #22c55e); width: {{ $hakTotal > 0 ? (($cutiInfo['sisa_cuti'] ?? $user->leave_balance) / $hakTotal) * 100 : 0 }}%;"></div>
                </div>
            </div>
        </div>
        @endif

        {{-- Type Info Banner --}}
        @php
            $typeInfo = match($type) {
                'cuti_tahunan' => ['icon' => 'ti-calendar-stats', 'color' => 'var(--sh-primary)', 'bg' => 'var(--sh-primary-light)', 'desc' => '12 hari kerja/tahun. Pengajuan minimal 5 hari kerja sebelum pelaksanaan.'],
                'cuti_besar' => ['icon' => 'ti-calendar-month', 'color' => '#7c3aed', 'bg' => '#f3e8ff', 'desc' => 'Maksimal 3 bulan. Syarat: masa kerja 5 tahun. Pengajuan minimal 14 hari sebelumnya.'],
                'cuti_sakit' => ['icon' => 'ti-stethoscope', 'color' => 'var(--sh-danger)', 'bg' => 'var(--sh-danger-light)', 'desc' => 'Maksimal 1 tahun. Wajib melampirkan surat keterangan dokter.'],
                'cuti_melahirkan' => ['icon' => 'ti-baby-carriage', 'color' => '#db2777', 'bg' => '#fce7f3', 'desc' => '3 bulan kalender. Berlaku untuk kelahiran anak ke-1, 2, 3 saat PNS.'],
                'cuti_alasan_penting' => ['icon' => 'ti-urgent', 'color' => 'var(--sh-warning)', 'bg' => 'var(--sh-warning-light)', 'desc' => 'Maksimal 1 bulan. Untuk keluarga sakit/meninggal, perkawinan, musibah, dll.'],
                'cuti_luar_tanggungan' => ['icon' => 'ti-world', 'color' => '#64748b', 'bg' => '#f1f5f9', 'desc' => 'Maksimal 3 tahun. Tanpa penghasilan. Syarat: masa kerja 5 tahun, pengajuan 3 bulan sebelumnya.'],
                default => ['icon' => 'ti-calendar', 'color' => 'var(--sh-primary)', 'bg' => 'var(--sh-primary-light)', 'desc' => ''],
            };
        @endphp
        <div class="mb-4" style="background: {{ $typeInfo['bg'] }}; border-radius: 14px; padding: 1rem 1.25rem;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: {{ $typeInfo['color'] }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="ti {{ $typeInfo['icon'] }}" style="color: #fff; font-size: 1.2rem;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="color: {{ $typeInfo['color'] }};">{{ $typeLabel }}</div>
                    <div style="font-size: 0.82rem; color: #475569;">{{ $typeInfo['desc'] }}</div>
                </div>
            </div>
        </div>

        {{-- #22 Personal Usage Summary --}}
        <div class="mb-4 p-3" style="background: var(--sh-gray-50); border-radius: 14px; border: 1px solid var(--sh-border);">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="ti ti-chart-bar" style="color: var(--sh-primary);"></i>
                <span class="fw-bold" style="font-size: 0.85rem;">Riwayat Penggunaan Cuti Anda</span>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <div style="font-size: 0.78rem; color: var(--sh-text-muted);">Tahun {{ date('Y') }}</div>
                    <div class="fw-bold" style="color: var(--sh-primary);">{{ $thisYearDays ?? 0 }} hari</div>
                </div>
                <div class="col-6">
                    <div style="font-size: 0.78rem; color: var(--sh-text-muted);">Tahun {{ date('Y') - 1 }}</div>
                    <div class="fw-bold" style="color: var(--sh-text-muted);">{{ $lastYearDays ?? 0 }} hari</div>
                </div>
            </div>
        </div>

        {{-- #21 Re-apply Notice --}}
        @if(isset($reapplyData))
        <div class="mb-4 p-3" style="background: var(--sh-warning-light); border-radius: 14px; border: 1px solid var(--sh-warning);">
            <div class="d-flex align-items-center gap-2">
                <i class="ti ti-refresh" style="color: var(--sh-warning);"></i>
                <div style="font-size: 0.85rem; color: var(--sh-warning);">
                    <strong>Pengajuan Ulang</strong> — Data dari pengajuan sebelumnya sudah diisi ulang. Silakan periksa dan sesuaikan.
                </div>
            </div>
        </div>
        @endif

        {{-- Form Card --}}
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-file-plus me-2" style="color: var(--sh-primary);"></i>
                    Formulir Pengajuan
                </h3>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert mb-4" style="background: var(--sh-danger-light); color: var(--sh-danger); border-radius: 12px; border: none;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="ti ti-alert-circle" style="font-size: 1.2rem; margin-top: 2px;"></i>
                        <ul class="mb-0 ps-0" style="list-style: none;">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data" id="leave-form">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">

                    {{-- Date fields --}}
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-calendar-event me-1" style="color: var(--sh-primary);"></i>
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', isset($reapplyData) ? $reapplyData->start_date->format('Y-m-d') : '') }}"
                                   min="{{ date('Y-m-d') }}" required
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-calendar-event me-1" style="color: var(--sh-primary);"></i>
                                Tanggal Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date', isset($reapplyData) ? $reapplyData->end_date->format('Y-m-d') : '') }}"
                                   min="{{ date('Y-m-d') }}" required
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Conflict Banner (#2) --}}
                    <div class="d-none mb-3" id="conflict-banner">
                        <div class="d-flex align-items-center gap-2 p-3" style="background: var(--sh-warning-light); border: 2px solid var(--sh-warning); border-radius: 12px;">
                            <i class="ti ti-alert-triangle" style="color: var(--sh-warning); font-size: 1.2rem; flex-shrink: 0;"></i>
                            <div style="font-size: 0.85rem; color: var(--sh-warning);" id="conflict-message"></div>
                        </div>
                    </div>

                    {{-- Duration preview --}}
                    <div class="d-none mb-4" id="duration-preview">
                        <div class="d-flex align-items-center gap-3 p-3" id="duration-box" style="border-radius: 12px; background: var(--sh-primary-light); border: 2px solid #bbf7d0;">
                            <div id="duration-icon-box" style="width: 44px; height: 44px; border-radius: 12px; background: var(--sh-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="ti ti-hourglass" style="color: #fff; font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <div class="fw-bold" id="duration-label" style="color: var(--sh-primary);">Durasi: <span id="duration-days">0</span> hari</div>
                                <div class="text-muted" style="font-size: 0.8rem;" id="duration-sub"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Alasan CAP (Cuti Alasan Penting only) --}}
                    @if($type === 'cuti_alasan_penting')
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            <i class="ti ti-list me-1" style="color: var(--sh-warning);"></i>
                            Kategori Alasan Penting <span class="text-danger">*</span>
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                  title="Pilih kategori yang paling sesuai dengan alasan cuti Anda. Setiap kategori memiliki ketentuan berbeda."
                                  style="cursor:help;">
                                <i class="ti ti-info-circle" style="font-size:0.9rem;color:#94a3b8;"></i>
                            </span>
                        </label>
                        <select name="alasan_cap" class="form-select @error('alasan_cap') is-invalid @enderror" required
                                style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($capLabels as $key => $label)
                            <option value="{{ $key }}" {{ old('alasan_cap') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('alasan_cap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @endif

                    {{-- Kelahiran Ke (Cuti Melahirkan only) --}}
                    @if($type === 'cuti_melahirkan')
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            <i class="ti ti-baby-carriage me-1" style="color: #db2777;"></i>
                            Kelahiran Anak Ke- <span class="text-danger">*</span>
                        </label>
                        <select name="kelahiran_ke" class="form-select @error('kelahiran_ke') is-invalid @enderror" required
                                style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            <option value="">-- Pilih --</option>
                            <option value="1" {{ old('kelahiran_ke') == '1' ? 'selected' : '' }}>Anak ke-1</option>
                            <option value="2" {{ old('kelahiran_ke') == '2' ? 'selected' : '' }}>Anak ke-2</option>
                            <option value="3" {{ old('kelahiran_ke') == '3' ? 'selected' : '' }}>Anak ke-3</option>
                        </select>
                        @error('kelahiran_ke') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-hint mt-1" style="font-size: 0.78rem; color: #94a3b8;">
                            Kelahiran anak ke-4 dan seterusnya menggunakan Cuti Besar.
                        </div>
                    </div>
                    @endif

                    {{-- Reason --}}
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            <i class="ti ti-writing me-1" style="color: var(--sh-primary);"></i>
                            Alasan Cuti <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason"
                                  id="reason-textarea"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  rows="3" required
                                  maxlength="500"
                                  placeholder="{{ match($type) {
                                      'cuti_tahunan' => 'Contoh: Keperluan keluarga, liburan, urusan pribadi...',
                                      'cuti_sakit' => 'Contoh: Diagnosa dokter, kondisi kesehatan...',
                                      'cuti_besar' => 'Contoh: Ibadah haji, keperluan keluarga...',
                                      'cuti_melahirkan' => 'Contoh: Persiapan dan pemulihan persalinan...',
                                      'cuti_alasan_penting' => 'Jelaskan alasan secara detail...',
                                      'cuti_luar_tanggungan' => 'Contoh: Menemani suami/istri tugas di luar negeri...',
                                      default => 'Jelaskan alasan cuti Anda...',
                                  } }}"
                                  style="border-radius: 12px; border: 2px solid #e2e8f0; resize: none; max-height: 180px; overflow-y: auto;">{{ old('reason', isset($reapplyData) ? $reapplyData->reason : '') }}</textarea>
                        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        {{-- Character counter (#14) --}}
                        <div class="d-flex justify-content-between mt-1">
                            <div class="form-hint" style="font-size: 0.78rem; color: #94a3b8;">Jelaskan alasan dengan jelas dan singkat.</div>
                            <div class="sh-char-counter" id="reason-counter">0 / 500 karakter</div>
                        </div>
                    </div>

                    {{-- Dokumen Pendukung (for types that need it) — Drag & Drop (#10) --}}
                    @if(in_array($type, ['cuti_sakit', 'cuti_besar', 'cuti_alasan_penting']))
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            <i class="ti ti-paperclip me-1" style="color: #7c3aed;"></i>
                            Dokumen Pendukung
                            @if($type === 'cuti_sakit')
                            <span class="text-danger">* (Surat Dokter)</span>
                            @endif
                        </label>
                        <div id="drop-zone" style="border: 2px dashed #c4b5fd; border-radius: 12px; padding: 1.5rem; text-align: center; cursor: pointer; transition: background 0.2s; position: relative;"
                             ondragover="event.preventDefault(); this.style.background='var(--sh-primary-light)';"
                             ondragleave="this.style.background=''"
                             ondrop="handleFileDrop(event)">
                            <i class="ti ti-upload" style="font-size: 2rem; color: #7c3aed; opacity: 0.6;"></i>
                            <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.4rem;">
                                Seret & lepas file di sini, atau <strong style="color:#7c3aed;">klik untuk pilih</strong>
                            </div>
                            <div id="drop-file-name" style="font-size: 0.82rem; color: var(--sh-primary); margin-top: 0.25rem;"></div>
                            <input type="file" name="dokumen_pendukung" id="dokumen-input"
                                   class="@error('dokumen_pendukung') is-invalid @enderror"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                        </div>
                        @error('dokumen_pendukung') <div class="text-danger mt-1" style="font-size:0.82rem;">{{ $message }}</div> @enderror
                        <div class="form-hint mt-1" style="font-size: 0.78rem; color: #94a3b8;">
                            Format: PDF, JPG, PNG. Maks 5MB.
                            @if($type === 'cuti_sakit') Surat keterangan dokter wajib dilampirkan. @endif
                        </div>
                    </div>
                    @endif

                    {{-- Alamat & Telepon Selama Cuti --}}
                    <div class="row g-3 mb-4">
                        <div class="col-sm-8">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-map-pin me-1" style="color: #64748b;"></i>
                                Alamat Selama Cuti
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                      title="Alamat lengkap tempat Anda tinggal selama cuti — diperlukan untuk keperluan dinas darurat"
                                      style="cursor:help;">
                                    <i class="ti ti-info-circle" style="font-size:0.9rem;color:#94a3b8;"></i>
                                </span>
                            </label>
                            <input type="text" name="alamat_cuti"
                                   class="form-control @error('alamat_cuti') is-invalid @enderror"
                                   value="{{ old('alamat_cuti') }}"
                                   placeholder="Alamat yang bisa dihubungi selama cuti"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('alamat_cuti') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-phone me-1" style="color: #64748b;"></i>
                                Telepon
                            </label>
                            <input type="text" name="telepon_cuti"
                                   class="form-control @error('telepon_cuti') is-invalid @enderror"
                                   value="{{ old('telepon_cuti') }}"
                                   placeholder="08xxxxxxxxxx"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('telepon_cuti') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2 flex-column flex-sm-row">
                        <button type="submit" id="submit-btn" class="btn btn-primary sh-btn-primary btn-lg flex-fill">
                            <span id="submit-label"><i class="ti ti-send me-2"></i> Kirim Pengajuan</span>
                            <span id="submit-loading" class="d-none"><span class="spinner-border spinner-border-sm me-2" role="status"></span> Mengirim...</span>
                        </button>
                        <a href="{{ route('leave.select-type') }}" class="btn btn-outline-secondary btn-lg" style="border-radius: 10px;">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Sprint 9 #50: Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
});

// Sprint 2 #10: Handle drag & drop file upload
function handleFileDrop(event) {
    event.preventDefault();
    event.currentTarget.style.background = '';
    var files = event.dataTransfer.files;
    if (files.length > 0) {
        var file = files[0];
        if (file.size > 5 * 1024 * 1024) {
            if (window.shToast) shToast('File terlalu besar (maks. 5 MB)', 'error');
            return;
        }
        var input = document.getElementById('dokumen-input');
        if (input) {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            document.getElementById('drop-file-name').textContent = '✓ ' + file.name;
            event.currentTarget.style.background = 'var(--sh-primary-light)';
        }
    }
}
var dokumenInput = document.getElementById('dokumen-input');
if (dokumenInput) {
    dokumenInput.addEventListener('change', function() {
        var file = this.files[0];
        var label = document.getElementById('drop-file-name');
        if (!file) { if (label) label.textContent = ''; return; }
        if (file.size > 5 * 1024 * 1024) {
            if (window.shToast) shToast('File terlalu besar (maks. 5 MB)', 'error');
            this.value = '';
            if (label) label.textContent = '';
            return;
        }
        if (label) label.textContent = '✓ ' + file.name;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const preview = document.getElementById('duration-preview');
    const daysLabel = document.getElementById('duration-days');
    const durationBox = document.getElementById('duration-box');
    const iconBox = document.getElementById('duration-icon-box');
    const durationLabel = document.getElementById('duration-label');
    const durationSub = document.getElementById('duration-sub');
    const conflictBanner = document.getElementById('conflict-banner');
    const conflictMsg = document.getElementById('conflict-message');
    const submitBtn = document.querySelector('button[type="submit"]');
    const type = '{{ $type }}';
    const balance = {{ $type === 'cuti_tahunan' ? ($cutiInfo['sisa'] ?? $user->leave_balance) : 0 }};

    let isValid = false;

    function validateDates() {
        if (!startDate.value || !endDate.value) {
            return true; // Allow empty until both filled
        }

        const start = new Date(startDate.value);
        const end = new Date(endDate.value);

        // End date must be >= start date
        if (end < start) {
            endDate.classList.add('is-invalid');
            return false;
        } else {
            endDate.classList.remove('is-invalid');
            return true;
        }
    }

    // Sprint 1 #2: Conflict check debounce
    var conflictTimer = null;
    function checkConflict(start, end) {
        if (conflictTimer) clearTimeout(conflictTimer);
        conflictTimer = setTimeout(function() {
            fetch('/leave/check-conflict?start=' + start + '&end=' + end, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.conflict) {
                    conflictBanner.classList.remove('d-none');
                    conflictMsg.textContent = data.message;
                } else {
                    conflictBanner.classList.add('d-none');
                }
            })
            .catch(function() {
                conflictBanner.classList.add('d-none');
                if (window.shToast) shToast('Gagal memeriksa konflik jadwal cuti', 'warning');
            });
        }, 400);
    }

    function calcDays() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

            if (diff > 0) {
                daysLabel.textContent = diff;
                preview.classList.remove('d-none');
                isValid = true;

                // Sprint 1 #2: Check conflict
                checkConflict(startDate.value, endDate.value);

                if (type === 'cuti_tahunan' && diff > balance) {
                    durationBox.style.background = 'var(--sh-danger-light)';
                    durationBox.style.borderColor = '#fca5a5';
                    iconBox.style.background = 'var(--sh-danger)';
                    durationLabel.style.color = 'var(--sh-danger)';
                    durationSub.innerHTML = '<strong style="color: var(--sh-danger);">⚠️ Melebihi sisa cuti Anda (' + balance + ' hari)!</strong>';
                    isValid = false;
                } else {
                    durationBox.style.background = 'var(--sh-primary-light)';
                    durationBox.style.borderColor = '#bbf7d0';
                    iconBox.style.background = 'var(--sh-primary)';
                    durationLabel.style.color = 'var(--sh-primary)';
                    if (type === 'cuti_tahunan') {
                        // Sprint 1 #1: Show sisa cuti after leave
                        var sisaSetelah = balance - diff;
                        var sisaColor = sisaSetelah < 0 ? 'var(--sh-danger)' : (sisaSetelah <= 3 ? 'var(--sh-warning)' : 'var(--sh-primary)');
                        durationSub.innerHTML = 'Estimasi <strong>' + diff + '</strong> hari kalender &mdash; hari kerja dihitung otomatis &bull; <strong style="color:' + sisaColor + '">Sisa setelah: ' + sisaSetelah + ' hari</strong>';
                    } else {
                        durationSub.innerHTML = diff + ' hari kalender &mdash; hari kerja dihitung otomatis';
                    }
                    isValid = true;
                }
            } else {
                preview.classList.add('d-none');
                isValid = false;
                conflictBanner.classList.add('d-none');
            }
        } else {
            isValid = false;
            conflictBanner.classList.add('d-none');
        }

        // Update submit button state
        updateSubmitButton();
    }

    function updateSubmitButton() {
        if (submitBtn) {
            submitBtn.disabled = !isValid || !startDate.value || !endDate.value;
            submitBtn.style.opacity = submitBtn.disabled ? '0.5' : '1';
            submitBtn.title = submitBtn.disabled ? 'Lengkapi dan validasi tanggal terlebih dahulu' : '';
        }
    }

    startDate.addEventListener('change', function() {
        if (this.value) {
            endDate.min = this.value;
            // Auto-set end_date if it's earlier than start_date
            if (endDate.value && endDate.value < this.value) {
                endDate.value = this.value;
            }
        }
        validateDates();
        calcDays();
    });

    endDate.addEventListener('change', function() {
        validateDates();
        calcDays();
    });

    // Initial state
    updateSubmitButton();

    // Real-time validation helpers (#4)
    function showValidation(input, message) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        var fb = input.parentElement.querySelector('.invalid-feedback');
        if (!fb) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback';
            input.parentElement.appendChild(fb);
        }
        fb.textContent = message;
        fb.style.display = 'block';
    }
    function clearValidation(input) {
        input.classList.remove('is-invalid');
        var fb = input.parentElement.querySelector('.invalid-feedback');
        if (fb && !fb.dataset.server) fb.style.display = 'none';
    }
    function markValid(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }

    // Real-time date validation (#4)
    [startDate, endDate].forEach(function(input) {
        input.addEventListener('blur', function() {
            if (!input.value && input.required) {
                showValidation(input, 'Tanggal wajib diisi.');
            } else if (input.value) {
                markValid(input);
            }
        });
    });

    // Trigger initial calculation if dates pre-filled (e.g. after validation error)
    if (startDate.value && endDate.value) {
        validateDates();
        calcDays();
    }

    // Sprint 2 #9: Auto-save draft to localStorage
    var draftKey = 'cuti_draft_{{ $type }}';
    var draftBanner = null;

    function saveDraft() {
        var draft = {
            start_date: startDate.value,
            end_date: endDate.value,
            reason: document.querySelector('[name="reason"]') ? document.querySelector('[name="reason"]').value : '',
            alamat_cuti: document.querySelector('[name="alamat_cuti"]') ? document.querySelector('[name="alamat_cuti"]').value : '',
            telepon_cuti: document.querySelector('[name="telepon_cuti"]') ? document.querySelector('[name="telepon_cuti"]').value : '',
            timestamp: Date.now()
        };
        localStorage.setItem(draftKey, JSON.stringify(draft));
    }

    function loadDraft() {
        var raw = localStorage.getItem(draftKey);
        if (!raw) return;
        try {
            var draft = JSON.parse(raw);
            var age = Date.now() - (draft.timestamp || 0);
            if (age > 24 * 60 * 60 * 1000) { localStorage.removeItem(draftKey); return; }
            // Only show banner if fields are empty (not pre-filled by old() or reapply)
            if (!startDate.value && !endDate.value && draft.start_date) {
                showDraftBanner(draft);
            }
        } catch(e) {}
    }

    function showDraftBanner(draft) {
        var banner = document.createElement('div');
        banner.className = 'mb-3';
        banner.innerHTML = '<div class="d-flex align-items-center gap-2 p-3" style="background:var(--sh-primary-light);border:2px solid var(--sh-primary);border-radius:12px;">' +
            '<i class="ti ti-restore" style="color:var(--sh-primary);font-size:1.2rem;flex-shrink:0;"></i>' +
            '<div style="flex:1;font-size:0.85rem;color:var(--sh-primary);">' +
            '<strong>Lanjutkan draft?</strong> Anda punya draft tersimpan untuk cuti ini.' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" id="dismiss-draft" style="border-radius:8px;flex-shrink:0;">Abaikan</button>' +
            '<button type="button" class="btn btn-sm sh-btn-primary ms-2" id="restore-draft" style="flex-shrink:0;">Lanjutkan</button>' +
            '</div>';
        var form = document.getElementById('leave-form');
        form.insertBefore(banner, form.firstChild);
        draftBanner = banner;

        document.getElementById('restore-draft').addEventListener('click', function() {
            if (draft.start_date) startDate.value = draft.start_date;
            if (draft.end_date) endDate.value = draft.end_date;
            if (draft.reason) { var ta = document.querySelector('[name="reason"]'); if (ta) ta.value = draft.reason; }
            if (draft.alamat_cuti) { var al = document.querySelector('[name="alamat_cuti"]'); if (al) al.value = draft.alamat_cuti; }
            if (draft.telepon_cuti) { var te = document.querySelector('[name="telepon_cuti"]'); if (te) te.value = draft.telepon_cuti; }
            validateDates(); calcDays();
            if (draftBanner) draftBanner.remove();
        });
        document.getElementById('dismiss-draft').addEventListener('click', function() {
            localStorage.removeItem(draftKey);
            if (draftBanner) draftBanner.remove();
        });
    }

    // Auto-save on input
    ['start_date', 'end_date', 'reason', 'alamat_cuti', 'telepon_cuti'].forEach(function(fieldName) {
        var el = document.querySelector('[name="' + fieldName + '"]');
        if (el) el.addEventListener('input', saveDraft);
    });
    [startDate, endDate].forEach(function(el) {
        el.addEventListener('change', saveDraft);
    });

    // Clear draft on successful form submit
    document.getElementById('leave-form').addEventListener('submit', function() {
        localStorage.removeItem(draftKey);
    });

    // Load draft if no old() values
    loadDraft();

    // Prevent double-submit: show loading state on submit
    document.getElementById('leave-form').addEventListener('submit', function() {
        var btn = document.getElementById('submit-btn');
        if (btn && !btn.disabled) {
            btn.disabled = true;
            document.getElementById('submit-label').classList.add('d-none');
            document.getElementById('submit-loading').classList.remove('d-none');
        }
    });

    // Character counter for reason textarea (#14)
    var reasonTextarea = document.getElementById('reason-textarea');
    var reasonCounter = document.getElementById('reason-counter');

    if (reasonTextarea && reasonCounter) {
        function updateCounter() {
            var len = reasonTextarea.value.length;
            reasonCounter.textContent = len + ' / 500 karakter';
            reasonCounter.className = 'sh-char-counter';
            if (len >= 450) reasonCounter.classList.add('sh-char-danger');
            else if (len >= 350) reasonCounter.classList.add('sh-char-warning');
        }
        reasonTextarea.addEventListener('input', updateCounter);
        updateCounter(); // run on page load for old() value
    }

    // Validate reason on blur (#4)
    if (reasonTextarea) {
        reasonTextarea.addEventListener('blur', function() {
            if (!reasonTextarea.value.trim()) {
                showValidation(reasonTextarea, 'Alasan cuti wajib diisi.');
            } else if (reasonTextarea.value.trim().length < 10) {
                showValidation(reasonTextarea, 'Alasan terlalu singkat (min. 10 karakter).');
            } else {
                markValid(reasonTextarea);
            }
        });
        reasonTextarea.addEventListener('input', function() {
            if (reasonTextarea.value.trim().length >= 10) clearValidation(reasonTextarea);
        });
    }
});
</script>
<style>
/* Custom invalid-feedback styling */
.invalid-feedback {
    background: var(--sh-danger-light, #fee2e2);
    color: var(--sh-danger, #dc2626);
    border-radius: 8px;
    padding: 0.35rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 0.35rem;
    display: block;
}
/* Input transition on focus */
.form-control, .form-select {
    transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
}
</style>
@endpush
@endsection
