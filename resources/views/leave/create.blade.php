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
                            <span class="sh-stat-number" style="color: var(--sh-primary);">{{ $cutiInfo['sisa_cuti'] ?? $user->leave_balance }}</span>
                            <span class="text-muted" style="font-size: 0.85rem;">/ {{ $cutiInfo['total_hak'] ?? 12 }} hari</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.78rem;">
                            Hak: {{ $cutiInfo['hak_cuti'] ?? 12 }}
                            @if(($cutiInfo['carry_over'] ?? 0) > 0) + CO: {{ $cutiInfo['carry_over'] }} @endif
                            @if(($cutiInfo['tambahan_terpencil'] ?? 0) > 0) + Terpencil: {{ $cutiInfo['tambahan_terpencil'] }} @endif
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

                <form method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data">
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
                                   value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required
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
                                   value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                  style="border-radius: 12px; border: 2px solid #e2e8f0; resize: vertical;">{{ old('reason') }}</textarea>
                        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        {{-- Character counter (#14) --}}
                        <div class="d-flex justify-content-between mt-1">
                            <div class="form-hint" style="font-size: 0.78rem; color: #94a3b8;">Jelaskan alasan dengan jelas dan singkat.</div>
                            <div class="sh-char-counter" id="reason-counter">0 / 500 karakter</div>
                        </div>
                    </div>

                    {{-- Dokumen Pendukung (for types that need it) --}}
                    @if(in_array($type, ['cuti_sakit', 'cuti_besar', 'cuti_alasan_penting']))
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            <i class="ti ti-paperclip me-1" style="color: #7c3aed;"></i>
                            Dokumen Pendukung
                            @if($type === 'cuti_sakit')
                            <span class="text-danger">* (Surat Dokter)</span>
                            @endif
                        </label>
                        <input type="file" name="dokumen_pendukung"
                               class="form-control @error('dokumen_pendukung') is-invalid @enderror"
                               accept=".pdf,.jpg,.jpeg,.png"
                               style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                        @error('dokumen_pendukung') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <button type="submit" class="btn btn-primary sh-btn-primary btn-lg flex-fill">
                            <i class="ti ti-send me-2"></i> Kirim Pengajuan
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
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const preview = document.getElementById('duration-preview');
    const daysLabel = document.getElementById('duration-days');
    const durationBox = document.getElementById('duration-box');
    const iconBox = document.getElementById('duration-icon-box');
    const durationLabel = document.getElementById('duration-label');
    const durationSub = document.getElementById('duration-sub');
    const submitBtn = document.querySelector('button[type="submit"]');
    const type = '{{ $type }}';
    const balance = {{ $type === 'cuti_tahunan' ? ($cutiInfo['sisa_cuti'] ?? $user->leave_balance) : 0 }};

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

    function calcDays() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

            if (diff > 0) {
                daysLabel.textContent = diff;
                preview.classList.remove('d-none');
                isValid = true;

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
                        durationSub.innerHTML = 'Sisa cuti setelah ini: <strong>' + Math.max(0, balance - diff) + '</strong> hari';
                    } else {
                        durationSub.innerHTML = diff + ' hari kalender';
                    }
                    isValid = true;
                }
            } else {
                preview.classList.add('d-none');
                isValid = false;
            }
        } else {
            isValid = false;
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
@endpush
@endsection
