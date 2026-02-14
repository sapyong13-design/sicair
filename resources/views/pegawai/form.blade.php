@extends('layouts.app')

@section('title', ($pegawai ? 'Edit' : 'Tambah') . ' Pegawai - SiHEALING')

@section('content')
{{-- Page Header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;">
            <i class="ti ti-arrow-left" style="font-size: 1.2rem;"></i>
        </a>
        <div>
            <h2 class="sh-page-title mb-0">{{ $pegawai ? 'Edit Pegawai' : 'Tambah Pegawai Baru' }}</h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                {{ $pegawai ? 'Perbarui data pegawai ' . $pegawai->name : 'Isi data lengkap pegawai baru' }}
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
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

        <form method="POST" action="{{ $pegawai ? route('pegawai.update', $pegawai) : route('pegawai.store') }}">
            @csrf
            @if($pegawai) @method('PUT') @endif

            {{-- Section 1: Data Pribadi --}}
            <div class="card sh-card mb-4">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-user me-2" style="color: var(--sh-primary);"></i>
                        Data Pribadi
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Nama Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $pegawai->name ?? '') }}" required placeholder="Nama lengkap pegawai"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                NIP <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror"
                                   value="{{ old('nip', $pegawai->nip ?? '') }}" required placeholder="18 digit NIP" maxlength="18"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Jenis Kelamin <span class="text-danger">*</span>
                            </label>
                            <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required
                                    style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Telepon
                            </label>
                            <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror"
                                   value="{{ old('telepon', $pegawai->telepon ?? '') }}" placeholder="08xxxxxxxxxx"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Jumlah Anak
                            </label>
                            <input type="number" name="jumlah_anak" class="form-control @error('jumlah_anak') is-invalid @enderror"
                                   value="{{ old('jumlah_anak', $pegawai->jumlah_anak ?? 0) }}" min="0"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('jumlah_anak') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Alamat</label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2"
                                      placeholder="Alamat tempat tinggal"
                                      style="border-radius: 10px; border: 2px solid #e2e8f0;">{{ old('alamat', $pegawai->alamat ?? '') }}</textarea>
                            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Data Kepegawaian --}}
            <div class="card sh-card mb-4">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-briefcase me-2" style="color: #7c3aed;"></i>
                        Data Kepegawaian
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Jabatan
                            </label>
                            <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror"
                                   value="{{ old('jabatan', $pegawai->jabatan ?? '') }}" placeholder="Contoh: Panitera Muda, Hakim..."
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Golongan / Ruang
                            </label>
                            <input type="text" name="golongan_ruang" class="form-control @error('golongan_ruang') is-invalid @enderror"
                                   value="{{ old('golongan_ruang', $pegawai->golongan_ruang ?? '') }}" placeholder="Contoh: III/c"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('golongan_ruang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Unit Kerja <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="unit_kerja" class="form-control @error('unit_kerja') is-invalid @enderror"
                                   value="{{ old('unit_kerja', $pegawai->unit_kerja ?? 'Pengadilan Negeri Natuna') }}" required
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('unit_kerja') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Status Pegawai <span class="text-danger">*</span>
                            </label>
                            <select name="status_pegawai" class="form-select @error('status_pegawai') is-invalid @enderror" required
                                    style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                                <option value="">-- Pilih --</option>
                                <option value="hakim" {{ old('status_pegawai', $pegawai->status_pegawai ?? '') === 'hakim' ? 'selected' : '' }}>Hakim</option>
                                <option value="aparatur" {{ old('status_pegawai', $pegawai->status_pegawai ?? '') === 'aparatur' ? 'selected' : '' }}>Aparatur (PNS)</option>
                                <option value="cpns" {{ old('status_pegawai', $pegawai->status_pegawai ?? '') === 'cpns' ? 'selected' : '' }}>CPNS</option>
                                <option value="cakim" {{ old('status_pegawai', $pegawai->status_pegawai ?? '') === 'cakim' ? 'selected' : '' }}>Calon Hakim</option>
                            </select>
                            @error('status_pegawai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Masa Kerja Mulai (TMT)
                            </label>
                            <input type="date" name="masa_kerja_mulai" class="form-control @error('masa_kerja_mulai') is-invalid @enderror"
                                   value="{{ old('masa_kerja_mulai', $pegawai && $pegawai->masa_kerja_mulai ? $pegawai->masa_kerja_mulai->format('Y-m-d') : '') }}"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('masa_kerja_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Atasan Langsung
                            </label>
                            <select name="atasan_id" class="form-select @error('atasan_id') is-invalid @enderror"
                                    style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                                <option value="">-- Tidak ada / Langsung ke Ketua --</option>
                                @foreach($atasanOptions as $atasan)
                                <option value="{{ $atasan->id }}" {{ old('atasan_id', $pegawai->atasan_id ?? '') == $atasan->id ? 'selected' : '' }}>
                                    {{ $atasan->name }} ({{ ucfirst($atasan->role) }})
                                </option>
                                @endforeach
                            </select>
                            @error('atasan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Sisa Cuti Tahunan
                            </label>
                            <input type="number" name="leave_balance" class="form-control @error('leave_balance') is-invalid @enderror"
                                   value="{{ old('leave_balance', $pegawai->leave_balance ?? 12) }}" min="0"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('leave_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center h-100 pt-3">
                                <label class="form-check" style="cursor: pointer;">
                                    <input class="form-check-input" type="checkbox" name="lokasi_terpencil" value="1"
                                           {{ old('lokasi_terpencil', $pegawai->lokasi_terpencil ?? false) ? 'checked' : '' }}>
                                    <span class="form-check-label fw-semibold" style="font-size: 0.85rem;">
                                        <i class="ti ti-map-pin me-1" style="color: var(--sh-warning);"></i>
                                        Lokasi Terpencil (+12 hari cuti)
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Akun & Akses --}}
            <div class="card sh-card mb-4">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="ti ti-lock me-2" style="color: #d97706;"></i>
                        Akun & Hak Akses
                    </h3>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Role / Hak Akses <span class="text-danger">*</span>
                            </label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required
                                    style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                                <option value="">-- Pilih --</option>
                                <option value="pegawai" {{ old('role', $pegawai->role ?? 'pegawai') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                                <option value="atasan" {{ old('role', $pegawai->role ?? '') === 'atasan' ? 'selected' : '' }}>Atasan (Panitera/Sekretaris)</option>
                                <option value="ketua" {{ old('role', $pegawai->role ?? '') === 'ketua' ? 'selected' : '' }}>Ketua PN (Pejabat Berwenang)</option>
                                <option value="admin" {{ old('role', $pegawai->role ?? '') === 'admin' ? 'selected' : '' }}>Admin Kepegawaian</option>
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                                Password {{ $pegawai ? '(kosongkan jika tidak diubah)' : '' }} {{ !$pegawai ? '*' : '' }}
                            </label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   {{ !$pegawai ? 'required' : '' }} placeholder="{{ $pegawai ? 'Biarkan kosong jika tidak diubah' : 'Min. 6 karakter' }}"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2 flex-column flex-sm-row mb-4">
                <button type="submit" class="btn btn-primary sh-btn-primary btn-lg flex-fill">
                    <i class="ti ti-{{ $pegawai ? 'device-floppy' : 'user-plus' }} me-2"></i>
                    {{ $pegawai ? 'Simpan Perubahan' : 'Tambah Pegawai' }}
                </button>
                <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary btn-lg" style="border-radius: 10px;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
