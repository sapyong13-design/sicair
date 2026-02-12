@extends('layouts.app')

@section('title', 'Ajukan Cuti - SiHEALING')

@section('content')
{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="/dashboard" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;">
            <i class="ti ti-arrow-left" style="font-size: 1.2rem;"></i>
        </a>
        <div>
            <h2 class="sh-page-title mb-0">Ajukan Cuti Baru</h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                Sisa cuti Anda:
                <span class="fw-bold" style="color: var(--sh-primary);">{{ Auth::user()->leave_balance }} hari</span>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Balance info card --}}
        <div class="card sh-stat-card stat-primary mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="sh-stat-label mb-1">Sisa Jatah Cuti Tahunan</div>
                        <div class="d-flex align-items-baseline gap-1">
                            <span class="sh-stat-number" style="color: var(--sh-primary);">{{ Auth::user()->leave_balance }}</span>
                            <span class="text-muted" style="font-size: 0.85rem;">/ 12 hari</span>
                        </div>
                    </div>
                    <div class="sh-stat-icon icon-primary">
                        <i class="ti ti-calendar-stats"></i>
                    </div>
                </div>
                <div style="height: 6px; border-radius: 3px; background: var(--sh-gray-100); margin-top: 0.75rem;">
                    <div style="height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--sh-primary), #3b82f6); width: {{ (Auth::user()->leave_balance / 12) * 100 }}%;"></div>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-file-plus me-2" style="color: var(--sh-primary);"></i>
                    Form Pengajuan Cuti
                </h3>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert sh-alert mb-4" style="background: var(--sh-danger-light); color: var(--sh-danger); border-radius: 12px;">
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

                <form method="POST" action="{{ route('leave.store') }}">
                    @csrf

                    {{-- Date fields --}}
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-calendar-event me-1" style="color: var(--sh-primary);"></i>
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-calendar-event me-1" style="color: var(--sh-primary);"></i>
                                Tanggal Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Duration preview --}}
                    <div class="d-none mb-4" id="duration-preview">
                        <div class="d-flex align-items-center gap-3 p-3" id="duration-box" style="border-radius: 12px; background: var(--sh-primary-light); border: 2px solid #bfdbfe;">
                            <div id="duration-icon-box" style="width: 44px; height: 44px; border-radius: 12px; background: var(--sh-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="ti ti-hourglass" style="color: #fff; font-size: 1.2rem;"></i>
                            </div>
                            <div id="duration-text">
                                <div class="fw-bold" style="color: var(--sh-primary);">Durasi: <span id="duration-days">0</span> hari</div>
                                <div class="text-muted" style="font-size: 0.8rem;" id="duration-sub">Sisa cuti Anda setelah ini: <strong id="remaining-balance">0</strong> hari</div>
                            </div>
                        </div>
                    </div>

                    {{-- Reason --}}
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            <i class="ti ti-writing me-1" style="color: var(--sh-primary);"></i>
                            Alasan Cuti <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Contoh: Acara keluarga, keperluan pribadi, kondisi kesehatan..."
                                  required
                                  style="border-radius: 12px; border: 2px solid #e2e8f0; resize: vertical;">{{ old('reason') }}</textarea>
                        @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-hint mt-1" style="font-size: 0.78rem; color: #94a3b8;">
                            Maksimal 500 karakter. Jelaskan alasan cuti Anda secara singkat.
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2 flex-column flex-sm-row">
                        <button type="submit" class="btn btn-primary sh-btn-primary btn-lg flex-fill">
                            <i class="ti ti-send me-2"></i> Kirim Pengajuan
                        </button>
                        <a href="/dashboard" class="btn btn-outline-secondary btn-lg" style="border-radius: 10px;">
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
    const remainingLabel = document.getElementById('remaining-balance');
    const durationBox = document.getElementById('duration-box');
    const iconBox = document.getElementById('duration-icon-box');
    const balance = {{ Auth::user()->leave_balance }};

    function calcDays() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
            if (diff > 0) {
                daysLabel.textContent = diff;
                remainingLabel.textContent = Math.max(0, balance - diff);
                preview.classList.remove('d-none');

                if (diff > balance) {
                    durationBox.style.background = 'var(--sh-danger-light)';
                    durationBox.style.borderColor = '#fca5a5';
                    iconBox.style.background = 'var(--sh-danger)';
                    durationBox.querySelector('.fw-bold').style.color = 'var(--sh-danger)';
                    document.getElementById('duration-sub').innerHTML = '<strong style="color: var(--sh-danger);">Melebihi sisa cuti Anda (' + balance + ' hari)!</strong>';
                } else {
                    durationBox.style.background = 'var(--sh-primary-light)';
                    durationBox.style.borderColor = '#bfdbfe';
                    iconBox.style.background = 'var(--sh-primary)';
                    durationBox.querySelector('.fw-bold').style.color = 'var(--sh-primary)';
                    document.getElementById('duration-sub').innerHTML = 'Sisa cuti Anda setelah ini: <strong>' + Math.max(0, balance - diff) + '</strong> hari';
                }
            } else {
                preview.classList.add('d-none');
            }
        }
    }

    startDate.addEventListener('change', function() {
        if (endDate.value && endDate.value < startDate.value) {
            endDate.value = startDate.value;
        }
        endDate.min = startDate.value;
        calcDays();
    });
    endDate.addEventListener('change', calcDays);
});
</script>
@endpush
@endsection
