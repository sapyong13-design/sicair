@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Buat Perubahan Saldo Cuti</h2>
            <p class="text-muted">Pegawai: <strong>{{ $user->name }}</strong></p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('balance-adjustment.store', $user) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Tahun</label>
                            <input type="number" name="year" class="form-control @error('year') is-invalid @enderror"
                                   value="{{ old('year', date('Y')) }}" min="{{ date('Y') - 5 }}" max="{{ date('Y') + 2 }}">
                            @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Perubahan</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="">-- Pilih --</option>
                                <option value="addition" @selected(old('type') === 'addition')>Penambahan Hari</option>
                                <option value="deduction" @selected(old('type') === 'deduction')>Pengurangan Hari</option>
                                <option value="correction" @selected(old('type') === 'correction')>Koreksi Data</option>
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah Hari</label>
                            <input type="number" name="adjustment_days" class="form-control @error('adjustment_days') is-invalid @enderror"
                                   value="{{ old('adjustment_days') }}" placeholder="Contoh: 5">
                            <small class="text-muted">
                                Gunakan angka positif. Sistem akan menyesuaikan tanda (+/-) berdasarkan jenis perubahan.
                            </small>
                            @error('adjustment_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan</label>
                            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                                      rows="4" placeholder="Jelaskan alasan perubahan saldo cuti...">{{ old('reason') }}</textarea>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Informasi Saat Ini</label>
                            @if($cutiRecord)
                                <div class="alert alert-info">
                                    <strong>Saldo Cuti Tahunan {{ $currentYear }}:</strong><br>
                                    Alokasi Awal: <strong>{{ $cutiRecord->alokasi_awal ?? 12 }}</strong> hari<br>
                                    Terpakai: <strong>{{ $cutiRecord->terpakai ?? 0 }}</strong> hari<br>
                                    Sisa: <strong>{{ $cutiRecord->sisa ?? 12 }}</strong> hari
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    Belum ada data cuti untuk tahun ini.
                                </div>
                            @endif
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('balance-adjustment.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Buat Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">Panduan</h5>
                    <ul class="small mb-0">
                        <li><strong>Penambahan:</strong> Menambah saldo cuti pegawai</li>
                        <li><strong>Pengurangan:</strong> Mengurangi saldo cuti pegawai</li>
                        <li><strong>Koreksi:</strong> Memperbaiki data yang salah</li>
                        <li class="mt-2">Perubahan memerlukan persetujuan admin lain sebelum diterapkan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
