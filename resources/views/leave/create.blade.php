@extends('layouts.app')

@section('title', 'Ajukan Cuti - SiHEALING')

@section('content')
<div class="page-header d-print-none mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <a href="/dashboard" class="btn btn-outline-secondary btn-sm me-2">
                <i class="ti ti-arrow-left"></i>
            </a>
        </div>
        <div class="col">
            <h2 class="page-title">Ajukan Cuti Baru</h2>
            <div class="text-muted mt-1">Sisa cuti Anda: <strong class="text-primary">{{ Auth::user()->leave_balance }} hari</strong></div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-file-plus me-2"></i>Form Pengajuan Cuti</h3>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('leave.store') }}">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label class="form-label required">Tanggal Mulai</label>
                            <input type="date"
                                   name="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6 mt-3 mt-sm-0">
                            <label class="form-label required">Tanggal Selesai</label>
                            <input type="date"
                                   name="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Alasan Cuti</label>
                        <textarea name="reason"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Jelaskan alasan pengajuan cuti Anda..."
                                  required>{{ old('reason') }}</textarea>
                        @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Duration preview --}}
                    <div class="alert alert-info d-none" id="duration-preview">
                        <i class="ti ti-info-circle me-2"></i>
                        Durasi cuti: <strong id="duration-days">0</strong> hari
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-send me-2"></i> Kirim Pengajuan
                        </button>
                        <a href="/dashboard" class="btn btn-secondary">Batal</a>
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

    function calcDays() {
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
            if (diff > 0) {
                daysLabel.textContent = diff;
                preview.classList.remove('d-none');
                const balance = {{ Auth::user()->leave_balance }};
                if (diff > balance) {
                    preview.classList.remove('alert-info');
                    preview.classList.add('alert-danger');
                    daysLabel.parentElement.innerHTML = '<i class="ti ti-alert-circle me-2"></i>Durasi cuti: <strong>' + diff + '</strong> hari — <strong>Melebihi sisa cuti Anda (' + balance + ' hari)!</strong>';
                } else {
                    preview.classList.remove('alert-danger');
                    preview.classList.add('alert-info');
                    daysLabel.parentElement.innerHTML = '<i class="ti ti-info-circle me-2"></i>Durasi cuti: <strong>' + diff + '</strong> hari';
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
