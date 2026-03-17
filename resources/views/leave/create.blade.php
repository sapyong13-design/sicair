@extends('layouts.app')

@section('title', 'Ajukan Cuti - SiCAIR')

@php
    $typeLabels = \App\Models\LeaveRequest::typeLabels();
    $capLabels = \App\Models\LeaveRequest::capLabels();
    $typeLabel = $typeLabels[$type] ?? 'Cuti';
    $user = Auth::user();
    // Use CutiTahunanCalculator for accurate balance if $cutiInfo not passed
    $sisaCutiAkurat = $cutiInfo['sisa_cuti'] ?? $cutiInfo['sisa'] ?? $user->leave_balance ?? 0;
@endphp

@section('content')
{{-- T19: Sticky saldo bar (mobile only) --}}
@if(!$user->isAdmin())
<div class="sc-sticky-balance">
    <span><i class="ti ti-calendar-stats me-1"></i> Saldo Cuti Tahunan</span>
    <strong>{{ $sisaCutiAkurat }} hari tersisa</strong>
</div>
@endif
{{-- Breadcrumb (#10) --}}
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <a href="{{ route('leave.select-type') }}">Pilih Jenis Cuti</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">{{ $typeLabel }}</span>
</nav>

{{-- Page Header --}}
<div class="sc-page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('leave.select-type') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;" aria-label="Kembali ke pilih jenis cuti">
            <i class="ti ti-arrow-left" style="font-size: 1.2rem;" aria-hidden="true"></i>
        </a>
        <div>
            <h2 class="sc-page-title mb-0">Ajukan {{ $typeLabel }}</h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Isi formulir pengajuan cuti dengan lengkap
            </div>
        </div>
    </div>
</div>

{{-- Wizard Step Indicator --}}
<div class="sc-wizard-nav mb-4" id="wizardNav">
    <div class="wiz-step active" data-step="1">
        <div class="wiz-num">1</div>
        <div class="wiz-label">Info Cuti</div>
    </div>
    <div class="wiz-connector"></div>
    <div class="wiz-step" data-step="2">
        <div class="wiz-num">2</div>
        <div class="wiz-label">Tanggal &amp; Lokasi</div>
    </div>
    <div class="wiz-connector"></div>
    <div class="wiz-step" data-step="3">
        <div class="wiz-num">3</div>
        <div class="wiz-label">Review &amp; Kirim</div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Balance info card (Cuti Tahunan) --}}
        @if($type === 'cuti_tahunan' && $cutiInfo)
        <div class="card sc-stat-card stat-primary mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="sc-stat-label mb-1">Sisa Cuti Tahunan {{ date('Y') }}</div>
                        <div class="d-flex align-items-baseline gap-1">
                            <span class="sc-stat-number" style="color: var(--sc-primary);">{{ $cutiInfo['sisa_cuti'] ?? $cutiInfo['sisa'] ?? $user->leave_balance }}</span>
                            <span class="text-muted" style="font-size: 0.85rem;">/ {{ $cutiInfo['total_hak'] ?? 12 }} hari</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.78rem;">
                            Hak: {{ $cutiInfo['hak_cuti'] ?? $cutiInfo['hak_dasar'] ?? 12 }}
                            @if(($cutiInfo['carry_over'] ?? 0) > 0) + CO: {{ $cutiInfo['carry_over'] }} @endif
                            @if(($cutiInfo['tambahan_terpencil'] ?? 0) > 0)
                            + Terpencil: {{ $cutiInfo['tambahan_terpencil'] }}
                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                  title="Tambahan cuti tahunan bagi pegawai yang bertugas di daerah terpencil: 10 hari untuk sangat terpencil, 6 hari untuk terpencil."
                                  style="cursor:help;">
                                <i class="ti ti-info-circle" style="font-size:0.75rem;color:var(--sc-primary);"></i>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="sc-stat-icon icon-primary">
                        <i class="ti ti-calendar-stats"></i>
                    </div>
                </div>
                <div style="height: 6px; border-radius: 3px; background: var(--sc-gray-100); margin-top: 0.75rem;">
                    @php $hakTotal = $cutiInfo['total_hak'] ?? 12; @endphp
                    <div style="height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--sc-primary), #22c55e); width: {{ $hakTotal > 0 ? (($cutiInfo['sisa_cuti'] ?? $cutiInfo['sisa'] ?? $user->leave_balance) / $hakTotal) * 100 : 0 }}%;"></div>
                </div>
            </div>
        </div>
        @endif

        {{-- Type Info Banner --}}
        @php
            $typeInfo = match($type) {
                'cuti_tahunan' => ['icon' => 'ti-calendar-stats', 'color' => 'var(--sc-primary)', 'bg' => 'var(--sc-primary-light)', 'desc' => '12 hari kerja/tahun. Pengajuan minimal 5 hari kerja sebelum pelaksanaan.'],
                'cuti_besar' => ['icon' => 'ti-calendar-month', 'color' => '#7c3aed', 'bg' => '#f3e8ff', 'desc' => 'Maksimal 3 bulan. Syarat: masa kerja 5 tahun. Pengajuan minimal 14 hari sebelumnya.'],
                'cuti_sakit' => ['icon' => 'ti-stethoscope', 'color' => 'var(--sc-danger)', 'bg' => 'var(--sc-danger-light)', 'desc' => 'Maksimal 1 tahun. Wajib melampirkan surat keterangan dokter.'],
                'cuti_melahirkan' => ['icon' => 'ti-baby-carriage', 'color' => '#db2777', 'bg' => '#fce7f3', 'desc' => '3 bulan kalender. Berlaku untuk kelahiran anak ke-1, 2, 3 saat PNS.'],
                'cuti_alasan_penting' => ['icon' => 'ti-urgent', 'color' => 'var(--sc-warning)', 'bg' => 'var(--sc-warning-light)', 'desc' => 'Maksimal 1 bulan. Untuk keluarga sakit/meninggal, perkawinan, musibah, dll.'],
                'cuti_luar_tanggungan' => ['icon' => 'ti-world', 'color' => '#64748b', 'bg' => '#f1f5f9', 'desc' => 'Maksimal 3 tahun. Tanpa penghasilan. Syarat: masa kerja 5 tahun, pengajuan 3 bulan sebelumnya.'],
                default => ['icon' => 'ti-calendar', 'color' => 'var(--sc-primary)', 'bg' => 'var(--sc-primary-light)', 'desc' => ''],
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
        <div class="mb-4 p-3" style="background: var(--sc-gray-50); border-radius: 14px; border: 1px solid var(--sc-border);">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="ti ti-chart-bar" style="color: var(--sc-primary);"></i>
                <span class="fw-bold" style="font-size: 0.85rem;">Riwayat Penggunaan Cuti Anda</span>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <div style="font-size: 0.78rem; color: var(--sc-text-muted);">Tahun {{ date('Y') }}</div>
                    <div class="fw-bold" style="color: var(--sc-primary);">{{ $thisYearDays ?? 0 }} hari</div>
                </div>
                <div class="col-6">
                    <div style="font-size: 0.78rem; color: var(--sc-text-muted);">Tahun {{ date('Y') - 1 }}</div>
                    <div class="fw-bold" style="color: var(--sc-text-muted);">{{ $lastYearDays ?? 0 }} hari</div>
                </div>
            </div>
        </div>

        {{-- #21 Re-apply Notice --}}
        @if(isset($reapplyData))
        @php
            $reapplyStatusLabel = $reapplyData->status === 'diubah' ? 'diminta diubah' : 'ditolak';
        @endphp
        <div class="mb-4 p-3" style="background: var(--sc-warning-light); border-radius: 14px; border: 1px solid var(--sc-warning);">
            <div class="d-flex align-items-start gap-2">
                <i class="ti ti-refresh" style="color: var(--sc-warning); margin-top: 2px;"></i>
                <div style="font-size: 0.85rem;">
                    <div class="fw-bold" style="color: var(--sc-warning);">
                        Mengajukan ulang dari pengajuan #{{ $reapplyData->id }} yang {{ $reapplyStatusLabel }}.
                    </div>
                    <div class="text-muted mt-1">
                        Pastikan Anda mengubah tanggal atau alasan sebelum mengirim ulang.
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Form Card --}}
        <div class="card sc-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-file-plus me-2" style="color: var(--sc-primary);"></i>
                    Formulir Pengajuan
                </h3>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert mb-4" style="background: var(--sc-danger-light); color: var(--sc-danger); border-radius: 12px; border: none;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="ti ti-alert-circle" style="font-size: 1.2rem; margin-top: 2px;"></i>
                        <ul class="mb-0 ps-0" style="list-style: none;">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <script>
                // Jika ada validation error, bypass wizard dan tampilkan semua panel
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('.wiz-panel').forEach(function(p) { p.style.display = 'block'; });
                    var nav = document.getElementById('wizardNav');
                    if (nav) nav.style.display = 'none';
                    var btnRow = document.getElementById('wizBtnRow');
                    if (btnRow) btnRow.style.display = 'none';
                });
                </script>
                @endif

                <form method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data" id="leave-form">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">

                    {{-- ===== WIZARD PANEL 1: Info Cuti ===== --}}
                    <div class="wiz-panel active" id="wiz-panel-1">

                    {{-- Alasan CAP (Cuti Alasan Penting only) — Panel 1 --}}
                    @if($type === 'cuti_alasan_penting')
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            <i class="ti ti-list me-1" style="color: var(--sc-warning);"></i>
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

                    {{-- Kelahiran Ke (Cuti Melahirkan only) — Panel 1 --}}
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
                        <div class="mb-2">
                            <select id="sc-reason-template" class="form-select form-select-sm" style="border-radius:8px; font-size:0.85rem; display:none;">
                                <option value="">-- Gunakan Template Alasan --</option>
                            </select>
                        </div>
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            <i class="ti ti-writing me-1" style="color: var(--sc-primary);"></i>
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
                            <div class="sc-char-counter" id="reason-counter">0 / 500 karakter</div>
                        </div>
                        <div class="d-flex justify-content-between mt-1" style="font-size:0.78rem; color:#94a3b8;">
                            <span>Jelaskan alasan pengajuan cuti Anda</span>
                            <span id="sc-reason-count">0</span>/500
                        </div>
                    </div>

                    {{-- Dokumen Pendukung (for types that need it) — Drag & Drop --}}
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
                             ondragover="event.preventDefault(); this.style.background='var(--sc-primary-light)';"
                             ondragleave="this.style.background=''"
                             ondrop="handleFileDrop(event)">
                            <i class="ti ti-upload" style="font-size: 2rem; color: #7c3aed; opacity: 0.6;"></i>
                            <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.4rem;">
                                Seret &amp; lepas file di sini, atau <strong style="color:#7c3aed;">klik untuk pilih</strong>
                            </div>
                            <div id="drop-file-name" style="font-size: 0.82rem; color: var(--sc-primary); margin-top: 0.25rem;"></div>
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

                    {{-- ===== END WIZARD PANEL 1 ===== --}}
                    </div>{{-- /wiz-panel-1 --}}

                    {{-- ===== WIZARD PANEL 2: Tanggal & Lokasi ===== --}}
                    <div class="wiz-panel" id="wiz-panel-2">

                    {{-- Date fields --}}
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-calendar-event me-1" style="color: var(--sc-primary);"></i>
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ $prefillStart ?? old('start_date', isset($reapplyData) ? $reapplyData->start_date->format('Y-m-d') : '') }}"
                                   min="{{ date('Y-m-d') }}" required
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            {{-- Smart date shortcuts --}}
                            <div class="d-flex flex-wrap gap-1 mt-2" id="sc-date-shortcuts">
                                <button type="button" class="sc-date-chip" data-offset="1">Besok</button>
                                <button type="button" class="sc-date-chip" data-offset="7">Minggu depan</button>
                                <button type="button" class="sc-date-chip" data-type="next-monday">Senin depan</button>
                                <button type="button" class="sc-date-chip" data-type="end-month">Akhir bulan</button>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-calendar-event me-1" style="color: var(--sc-primary);"></i>
                                Tanggal Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date', isset($reapplyData) ? $reapplyData->end_date->format('Y-m-d') : '') }}"
                                   min="{{ date('Y-m-d') }}" required
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            <div id="workingDaysBadge" class="mt-1" style="font-size:0.82rem; display:none;"></div>
                            <div id="holidayWarning" class="alert alert-warning py-1 px-2 mt-1" style="font-size:0.82rem; display:none; border-radius:8px;">
                                <i class="ti ti-alert-triangle me-1"></i>
                                <span id="holidayWarningText"></span>
                            </div>
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div id="sc-duration-info" class="mt-2" style="font-size:0.85rem; color:#475569; min-height:1.4rem; display:none!important;"></div>
                            <div id="sc-saldo-warning" class="mt-2 p-2" style="display:none; font-size:0.85rem; background:#fef2f2; border:1px solid #fecaca; color:#dc2626; border-radius:10px;"></div>
                        </div>
                    </div>

                    {{-- Conflict Banner (#2) --}}
                    <div class="d-none mb-3" id="conflict-banner">
                        <div class="d-flex align-items-center gap-2 p-3" style="background: var(--sc-warning-light); border: 2px solid var(--sc-warning); border-radius: 12px;">
                            <i class="ti ti-alert-triangle" style="color: var(--sc-warning); font-size: 1.2rem; flex-shrink: 0;"></i>
                            <div style="font-size: 0.85rem; color: var(--sc-warning);" id="conflict-message"></div>
                        </div>
                    </div>

                    {{-- Duration preview --}}
                    <div class="d-none mb-4" id="duration-preview" style="display:none!important;">
                        <div class="d-flex align-items-center gap-3 p-3" id="duration-box" style="border-radius: 12px; background: var(--sc-primary-light); border: 2px solid #bbf7d0;">
                            <div id="duration-icon-box" style="width: 44px; height: 44px; border-radius: 12px; background: var(--sc-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="ti ti-hourglass" style="color: #fff; font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <div class="fw-bold" id="duration-label" style="color: var(--sc-primary);">Durasi: <span id="duration-days">0</span> hari</div>
                                <div class="text-muted" style="font-size: 0.8rem;" id="duration-sub"></div>
                            </div>
                        </div>
                    </div>

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

                    </div>{{-- /wiz-panel-2 --}}

                    {{-- ===== WIZARD PANEL 3: Review & Kirim ===== --}}
                    <div class="wiz-panel" id="wiz-panel-3">
                        <div class="card sc-card">
                            <div class="card-body">
                                <h5 class="fw-bold mb-3"><i class="ti ti-check me-2 text-success"></i>Konfirmasi Pengajuan</h5>
                                <p class="text-muted mb-3">Periksa kembali detail pengajuan cuti Anda sebelum mengirim.</p>
                                <div id="wiz-review-summary" class="border rounded p-3 bg-light">
                                    <em class="text-muted">Mengisi ringkasan...</em>
                                </div>
                            </div>
                        </div>
                    </div>{{-- /wiz-panel-3 --}}

                    {{-- Wizard Navigation Buttons --}}
                    <div class="d-flex justify-content-between mt-4" id="wizBtnRow">
                        <button type="button" id="btnWizPrev" class="btn btn-outline-secondary" onclick="wizPrev()" style="display:none;">
                            <i class="ti ti-arrow-left me-1"></i> Sebelumnya
                        </button>
                        <div class="ms-auto d-flex gap-2">
                            <button type="button" id="btnWizNext" class="btn sc-btn-primary" onclick="wizNext()">
                                Selanjutnya <i class="ti ti-arrow-right ms-1"></i>
                            </button>
                            <button type="submit" id="btnWizSubmit" class="btn sc-btn-primary" style="display:none;">
                                <i class="ti ti-send me-1"></i> Ajukan Cuti
                            </button>
                        </div>
                    </div>

                    {{-- Hidden confirm button (kept for modal compatibility) --}}
                    <button type="button" id="sc-confirm-btn" class="d-none">
                        <span id="submit-label"></span>
                        <span id="submit-loading" class="d-none"><span class="spinner-border spinner-border-sm me-2" role="status"></span> Mengirim...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Submit --}}
<div class="modal fade" id="sc-confirm-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="ti ti-file-check me-2" style="color:var(--sc-primary,#166534)"></i>Konfirmasi Pengajuan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="sc-confirm-body"></div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="sc-confirm-submit" style="border-radius:10px;">
                    <i class="ti ti-check me-1"></i> Ya, Ajukan
                </button>
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
            if (window.scToast) scToast('File terlalu besar (maks. 5 MB)', 'error');
            return;
        }
        var input = document.getElementById('dokumen-input');
        if (input) {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            document.getElementById('drop-file-name').textContent = '✓ ' + file.name;
            event.currentTarget.style.background = 'var(--sc-primary-light)';
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
            if (window.scToast) scToast('File terlalu besar (maks. 5 MB)', 'error');
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
    const balance = {{ $type === 'cuti_tahunan' ? ($cutiInfo['sisa_cuti'] ?? $cutiInfo['sisa'] ?? $user->leave_balance) : 0 }};

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
                if (window.scToast) scToast('Gagal memeriksa konflik jadwal cuti', 'warning');
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
                    durationBox.style.background = 'var(--sc-danger-light)';
                    durationBox.style.borderColor = '#fca5a5';
                    iconBox.style.background = 'var(--sc-danger)';
                    durationLabel.style.color = 'var(--sc-danger)';
                    durationSub.innerHTML = '<strong style="color: var(--sc-danger);">⚠️ Melebihi sisa cuti Anda (' + balance + ' hari)!</strong>';
                    isValid = false;
                } else {
                    durationBox.style.background = 'var(--sc-primary-light)';
                    durationBox.style.borderColor = '#bbf7d0';
                    iconBox.style.background = 'var(--sc-primary)';
                    durationLabel.style.color = 'var(--sc-primary)';
                    if (type === 'cuti_tahunan') {
                        // Sprint 1 #1: Show sisa cuti after leave
                        var sisaSetelah = balance - diff;
                        var sisaColor = sisaSetelah < 0 ? 'var(--sc-danger)' : (sisaSetelah <= 3 ? 'var(--sc-warning)' : 'var(--sc-primary)');
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
        banner.innerHTML = '<div class="d-flex align-items-center gap-2 p-3" style="background:var(--sc-primary-light);border:2px solid var(--sc-primary);border-radius:12px;">' +
            '<i class="ti ti-restore" style="color:var(--sc-primary);font-size:1.2rem;flex-shrink:0;"></i>' +
            '<div style="flex:1;font-size:0.85rem;color:var(--sc-primary);">' +
            '<strong>Lanjutkan draft?</strong> Anda punya draft tersimpan untuk cuti ini.' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" id="dismiss-draft" style="border-radius:8px;flex-shrink:0;">Abaikan</button>' +
            '<button type="button" class="btn btn-sm sc-btn-primary ms-2" id="restore-draft" style="flex-shrink:0;">Lanjutkan</button>' +
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

    // Also handle wizard submit button
    var wizSubmitBtn = document.getElementById('btnWizSubmit');
    if (wizSubmitBtn) {
        document.getElementById('leave-form').addEventListener('submit', function() {
            wizSubmitBtn.disabled = true;
            wizSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
        });
    }

    // Character counter for reason textarea (#14)
    var reasonTextarea = document.getElementById('reason-textarea');
    var reasonCounter = document.getElementById('reason-counter');

    if (reasonTextarea && reasonCounter) {
        function updateCounter() {
            var len = reasonTextarea.value.length;
            reasonCounter.textContent = len + ' / 500 karakter';
            reasonCounter.className = 'sc-char-counter';
            if (len >= 450) reasonCounter.classList.add('sc-char-danger');
            else if (len >= 350) reasonCounter.classList.add('sc-char-warning');
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
<script>
(function() {
    // --- Feature 1: Auto-hitung durasi ---
    var startEl = document.querySelector('[name="start_date"]');
    var endEl   = document.querySelector('[name="end_date"]');
    var durEl   = document.getElementById('sc-duration-info');

    function countWorkdays(s, e) {
        var count = 0, cur = new Date(s), fin = new Date(e);
        while (cur <= fin) { var d = cur.getDay(); if (d !== 0 && d !== 6) count++; cur.setDate(cur.getDate()+1); }
        return count;
    }
    function updateDur() {
        if (!startEl || !endEl || !durEl) return;
        var s = startEl.value, e = endEl.value;
        if (!s || !e || s > e) { durEl.innerHTML = ''; return; }
        var days = countWorkdays(s, e);
        durEl.innerHTML = '<i class="ti ti-calendar-check me-1" style="color:#166534"></i><strong>' + days + ' hari kerja</strong> (Sabtu & Minggu tidak dihitung)';
        checkSaldo(days);
    }
    if (startEl) startEl.addEventListener('change', updateDur);
    if (endEl)   endEl.addEventListener('change', updateDur);

    // --- Feature 2: Validasi saldo ---
    var saldo = parseInt('{{ $sisaCutiAkurat }}');
    var warnEl = document.getElementById('sc-saldo-warning');
    function checkSaldo(days) {
        if (!warnEl) return;
        var type = document.querySelector('[name="type"]');
        var jenis = type ? type.value : 'cuti_tahunan';
        if (jenis === 'cuti_tahunan' && days > saldo && saldo > 0) {
            warnEl.innerHTML = '<i class="ti ti-alert-triangle me-1"></i>Durasi <strong>' + days + ' hari</strong> melebihi saldo Anda (<strong>' + saldo + ' hari</strong>)';
            warnEl.style.display = 'block';
        } else {
            warnEl.style.display = 'none';
        }
    }

    // --- Feature 3: Character counter ---
    var reasonEl = document.querySelector('[name="reason"], [name="alasan"]');
    var countEl  = document.getElementById('sc-reason-count');
    if (reasonEl && countEl) {
        var updateCount = function() {
            countEl.textContent = reasonEl.value.length;
            countEl.style.color = reasonEl.value.length > 450 ? '#dc2626' : '#94a3b8';
        };
        reasonEl.addEventListener('input', updateCount);
        updateCount();
    }

    // --- Feature 4: Template alasan ---
    var tmplEl = document.getElementById('sc-reason-template');
    if (tmplEl) {
        tmplEl.addEventListener('change', function() {
            if (this.value && reasonEl) {
                reasonEl.value = this.value;
                reasonEl.dispatchEvent(new Event('input'));
                this.value = '';
            }
        });
    }


    // --- Feature 6: Konfirmasi modal ---
    var confirmBtn = document.getElementById('sc-confirm-btn');
    var confirmSubmit = document.getElementById('sc-confirm-submit');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            var body = document.getElementById('sc-confirm-body');
            if (body) {
                var g = function(name) { var el = document.querySelector('[name="' + name + '"]'); return el ? (el.value || '-') : '-'; };
                var daysBadge = document.getElementById('workingDaysBadge');
                var daysText = daysBadge ? daysBadge.textContent.trim() : '-';
                var reason = g('reason');
                body.innerHTML =
                    '<div class="p-3" style="background:var(--sc-gray-50);border-radius:12px;">' +
                    '<table class="table table-borderless mb-0" style="font-size:0.9rem;">' +
                    '<tr><td class="text-muted" style="width:40%">Jenis Cuti</td><td class="fw-semibold">{{ $typeLabel }}</td></tr>' +
                    '<tr><td class="text-muted">Tanggal Mulai</td><td class="fw-semibold">' + g('start_date') + '</td></tr>' +
                    '<tr><td class="text-muted">Tanggal Selesai</td><td class="fw-semibold">' + g('end_date') + '</td></tr>' +
                    '<tr><td class="text-muted">Hari Kerja</td><td class="fw-semibold">' + daysText + '</td></tr>' +
                    '<tr><td class="text-muted">Alasan</td><td class="fw-semibold" style="white-space:pre-line;">' + (reason.substring(0,100) + (reason.length > 100 ? '…' : '')) + '</td></tr>' +
                    '</table></div>';
            }
            var modal = new bootstrap.Modal(document.getElementById('sc-confirm-modal'));
            modal.show();
        });
    }
    if (confirmSubmit) {
        confirmSubmit.addEventListener('click', function() {
            var f = document.querySelector('form');
            if (f) { this.disabled = true; f.submit(); }
        });
    }
})();
</script>
<script>
// Smart date shortcuts
document.querySelectorAll('.sc-date-chip').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var d = new Date();
        var offset = parseInt(this.dataset.offset);
        var type = this.dataset.type;
        if (!isNaN(offset)) {
            d.setDate(d.getDate() + offset);
        } else if (type === 'next-monday') {
            var day = d.getDay();
            d.setDate(d.getDate() + ((8 - day) % 7 || 7));
        } else if (type === 'end-month') {
            d = new Date(d.getFullYear(), d.getMonth() + 1, 0);
        }
        var iso = d.toISOString().split('T')[0];
        var startInput = document.querySelector('[name="start_date"]');
        if (startInput) {
            startInput.value = iso;
            startInput.dispatchEvent(new Event('change'));
        }
    });
});
</script>
<style>
/* Smart date shortcut chips */
.sc-date-chip {
    font-size: 0.75rem;
    padding: 0.25rem 0.65rem;
    border-radius: 99px;
    border: 1px solid var(--sc-primary, #166534);
    color: var(--sc-primary, #166534);
    background: transparent;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
}
.sc-date-chip:hover, .sc-date-chip:active {
    background: var(--sc-primary, #166534);
    color: white;
}
/* Custom invalid-feedback styling */
.invalid-feedback {
    background: var(--sc-danger-light, #fee2e2);
    color: var(--sc-danger, #dc2626);
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
/* ===== Wizard Styles ===== */
.sc-wizard-nav { display: flex; align-items: center; justify-content: center; gap: 0; }
.wiz-step { display: flex; flex-direction: column; align-items: center; gap: 0.25rem; }
.wiz-num { width: 36px; height: 36px; border-radius: 50%; background: var(--sc-gray-200, #e5e7eb); color: var(--sc-gray-600, #4b5563); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; transition: all 0.25s; }
.wiz-step.active .wiz-num { background: var(--sc-primary, #16a34a); color: white; }
.wiz-step.completed .wiz-num { background: var(--sc-success, #22c55e); color: white; }
.wiz-label { font-size: 0.75rem; color: var(--sc-gray-500, #6b7280); white-space: nowrap; }
.wiz-step.active .wiz-label { color: var(--sc-primary, #16a34a); font-weight: 600; }
.wiz-connector { flex: 1; height: 2px; background: var(--sc-gray-200, #e5e7eb); min-width: 40px; margin-bottom: 1.2rem; }
.wiz-panel { display: none; }
.wiz-panel.active { display: block; }
</style>
<script>
var wizCurrent = 1;
var wizTotal = 3;

function wizGoTo(step) {
    // Update panels
    document.querySelectorAll('.wiz-panel').forEach(function(p, i) {
        p.classList.toggle('active', i + 1 === step);
    });
    // Update step indicators
    document.querySelectorAll('.wiz-step').forEach(function(s, i) {
        s.classList.remove('active', 'completed');
        if (i + 1 < step) s.classList.add('completed');
        if (i + 1 === step) s.classList.add('active');
    });
    // Update buttons
    document.getElementById('btnWizPrev').style.display = step === 1 ? 'none' : '';
    document.getElementById('btnWizNext').style.display = step === wizTotal ? 'none' : '';
    document.getElementById('btnWizSubmit').style.display = step === wizTotal ? '' : 'none';
    wizCurrent = step;
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function wizNext() {
    if (wizCurrent < wizTotal) {
        // Validate required fields in current panel
        var panel = document.getElementById('wiz-panel-' + wizCurrent);
        var fields = panel.querySelectorAll('[required]');
        var valid = true;
        fields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        if (!valid) {
            var errEl = document.getElementById('wiz-step-error');
            if (!errEl) {
                errEl = document.createElement('div');
                errEl.id = 'wiz-step-error';
                errEl.className = 'alert alert-danger py-2 mt-3';
                errEl.style.fontSize = '0.85rem';
                errEl.style.borderRadius = '10px';
                panel.appendChild(errEl);
            }
            errEl.textContent = 'Harap lengkapi semua field yang wajib diisi (bertanda *) sebelum melanjutkan.';
            errEl.style.display = '';
            return;
        }
        // Clear error if previously shown
        var errEl = document.getElementById('wiz-step-error');
        if (errEl) errEl.style.display = 'none';

        if (wizCurrent === 2) updateReviewPanel();
        wizGoTo(wizCurrent + 1);
    }
}

function wizPrev() {
    if (wizCurrent > 1) wizGoTo(wizCurrent - 1);
}

function updateReviewPanel() {
    var summary = document.getElementById('wiz-review-summary');
    if (!summary) return;
    var reason = document.querySelector('[name="reason"]');
    var startDate = document.querySelector('[name="start_date"]');
    var endDate = document.querySelector('[name="end_date"]');
    var alamat = document.querySelector('[name="alamat_cuti"]');
    var telepon = document.querySelector('[name="telepon_cuti"]');
    var html = '<dl class="row mb-0">';
    if (reason && reason.value) html += '<dt class="col-sm-4">Alasan</dt><dd class="col-sm-8">' + reason.value + '</dd>';
    if (startDate && startDate.value) html += '<dt class="col-sm-4">Tgl Mulai</dt><dd class="col-sm-8">' + startDate.value + '</dd>';
    if (endDate && endDate.value) html += '<dt class="col-sm-4">Tgl Selesai</dt><dd class="col-sm-8">' + endDate.value + '</dd>';
    if (alamat && alamat.value) html += '<dt class="col-sm-4">Alamat Cuti</dt><dd class="col-sm-8">' + alamat.value + '</dd>';
    if (telepon && telepon.value) html += '<dt class="col-sm-4">Telepon</dt><dd class="col-sm-8">' + telepon.value + '</dd>';
    html += '</dl>';
    summary.innerHTML = html;
}

// Init wizard on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    wizGoTo(1);
    // Clear is-invalid when user fills a required field
    document.querySelectorAll('[required]').forEach(function(field) {
        field.addEventListener('input', function() {
            if (this.value.trim()) this.classList.remove('is-invalid');
        });
        field.addEventListener('change', function() {
            if (this.value.trim()) this.classList.remove('is-invalid');
        });
    });
});
</script>
<script>
// Load template alasan dari API (menggantikan opsi hardcoded)
(async function() {
    try {
        var res = await fetch('/leave-reason-templates/api', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error();
        var templates = await res.json();
        var sel = document.getElementById('sc-reason-template');
        if (!sel || !templates.length) return;
        templates.forEach(function(t) {
            var opt = document.createElement('option');
            opt.value = t.body;
            opt.textContent = t.label;
            sel.appendChild(opt);
        });
        sel.style.display = ''; // tampilkan sekarang ada data
    } catch(e) {
        // Gagal — dropdown tetap tersembunyi, user isi manual
    }
})();

// Date picker cerdas — hitung hari kerja (skip weekend + libur nasional)
(async function() {
    var holidays = [];
    var year = new Date().getFullYear();
    try {
        var res = await fetch('/hari-libur/api?year=' + year, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (res.ok) holidays = await res.json();
    } catch(e) { /* fallback: hanya skip weekend */ }

    // Use manual date string to avoid timezone UTC offset issues (WIB = UTC+7)
    function toDateStr(d) {
        return d.getFullYear() + '-' +
               String(d.getMonth() + 1).padStart(2, '0') + '-' +
               String(d.getDate()).padStart(2, '0');
    }
    function isHoliday(dateStr) { return holidays.includes(dateStr); }
    function isWeekend(dateStr) { var d = new Date(dateStr + 'T00:00:00'); return d.getDay() === 0 || d.getDay() === 6; }
    function countWorkingDays(start, end) {
        var count = 0;
        var cur = new Date(start + 'T00:00:00');
        var endDate = new Date(end + 'T00:00:00');
        while (cur <= endDate) {
            var s = toDateStr(cur);
            if (!isWeekend(s) && !isHoliday(s)) count++;
            cur.setDate(cur.getDate() + 1);
        }
        return count;
    }

    function updateDateInfo() {
        var startEl = document.querySelector('[name="start_date"]');
        var endEl   = document.querySelector('[name="end_date"]');
        var badge   = document.getElementById('workingDaysBadge');
        var warn    = document.getElementById('holidayWarning');
        var warnTxt = document.getElementById('holidayWarningText');
        if (!startEl || !endEl || !badge) return;
        var s = startEl.value, e = endEl.value;

        // Warning hari libur
        if (s && isHoliday(s)) {
            if (warn) { warn.style.display = ''; warnTxt.textContent = 'Tanggal mulai adalah hari libur.'; }
        } else if (e && isHoliday(e)) {
            if (warn) { warn.style.display = ''; warnTxt.textContent = 'Tanggal selesai adalah hari libur.'; }
        } else {
            if (warn) warn.style.display = 'none';
        }

        // Badge hari kerja
        if (s && e && s <= e) {
            var days = countWorkingDays(s, e);
            badge.style.display = '';
            badge.innerHTML = '<span class="badge bg-primary-subtle text-primary">' +
                '<i class="ti ti-calendar-check me-1"></i>' + days + ' hari kerja</span>';
        } else {
            badge.style.display = 'none';
        }
    }

    var startEl = document.querySelector('[name="start_date"]');
    var endEl = document.querySelector('[name="end_date"]');
    if (startEl) startEl.addEventListener('change', updateDateInfo);
    if (endEl) endEl.addEventListener('change', updateDateInfo);
    updateDateInfo(); // run on load jika ada prefill
})();
</script>
@endpush
@endsection
