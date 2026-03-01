@extends('layouts.app')

@section('title', 'Profil Saya - SiHEALING')

@section('content')
{{-- Breadcrumb (#10) --}}
<nav class="sh-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sh-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sh-breadcrumb-current">Profil Saya</span>
</nav>

<div class="sh-page-header">
    <h2 class="sh-page-title mb-0">
        <i class="ti ti-user-circle me-1" style="color: var(--sh-primary);" aria-hidden="true"></i> Profil Saya
    </h2>
</div>

<div class="row g-4">
    {{-- Left: Profile Card --}}
    <div class="col-lg-4">
        <div class="card sh-card mb-4">
            <div class="card-body p-4 text-center">
                <div class="sh-user-avatar mx-auto mb-3" style="width: clamp(64px, 12vw, 96px); height: clamp(64px, 12vw, 96px); font-size: clamp(1.4rem, 4vw, 2rem); background: var(--sh-primary-light); color: var(--sh-primary); border: 3px solid var(--sh-accent); border-radius: 20px; flex-shrink: 0;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h3 class="fw-bold mb-1">{{ $user->name }}</h3>
                <div class="text-muted mb-2" style="font-size: 0.88rem;">{{ $user->jabatan ?? '-' }}</div>
                <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                    <span class="sh-badge sh-badge-approved" style="font-size: 0.75rem;">{{ ucfirst($user->role) }}</span>
                    <span class="badge" style="background: #f1f5f9; color: #475569; border-radius: 50px; font-weight: 600; font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                        {{ ucfirst($user->status_pegawai ?? '-') }}
                    </span>
                    @if($user->lokasi_terpencil)
                    <span class="badge" style="background: var(--sh-warning-light); color: var(--sh-warning); border-radius: 50px; font-weight: 600; font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                        <i class="ti ti-map-pin"></i> Terpencil
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Cuti Balance --}}
        <div class="card sh-card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-calendar-stats me-2" style="color: var(--sh-primary);"></i>
                    Sisa Cuti Tahunan
                </h3>
            </div>
            <div class="card-body p-4">
                @if($cutiInfo)
                @php $sisaProfile = $cutiInfo['sisa'] ?? 0; @endphp
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span style="font-size: 2.5rem; font-weight: 800; color: {{ $sisaProfile <= 0 ? 'var(--sh-danger)' : ($sisaProfile <= 3 ? 'var(--sh-warning)' : 'var(--sh-primary)') }};">{{ $sisaProfile }}</span>
                    <span class="text-muted">/ {{ $cutiInfo['total_hak'] }} hari</span>
                    @if($sisaProfile <= 0)
                    <span class="badge" style="background: var(--sh-danger-light); color: var(--sh-danger); border-radius: 50px; font-size: 0.7rem;" title="Tidak ada saldo cuti tersisa">Habis</span>
                    @elseif($sisaProfile <= 3)
                    <span class="badge" style="background: var(--sh-warning-light); color: var(--sh-warning); border-radius: 50px; font-size: 0.7rem;">Rendah</span>
                    @endif
                </div>
                <div style="height: 8px; border-radius: 4px; background: var(--sh-gray-100); margin-bottom: 0.75rem;">
                    @php $pct = $cutiInfo['total_hak'] > 0 ? ($cutiInfo['sisa'] / $cutiInfo['total_hak']) * 100 : 0; @endphp
                    <div style="height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--sh-primary), #22c55e); width: {{ $pct }}%;"></div>
                </div>
                <div style="font-size: 0.8rem; color: #64748b;">
                    Hak: {{ $cutiInfo['hak_dasar'] }}
                    @if(($cutiInfo['carry_over'] ?? 0) > 0) + CO: {{ $cutiInfo['carry_over'] }} @endif
                    @if(($cutiInfo['tambahan_terpencil'] ?? 0) > 0) + Terpencil: {{ $cutiInfo['tambahan_terpencil'] }} @endif
                    &mdash; Terpakai: {{ $cutiInfo['cuti_diambil'] }}
                </div>
                @else
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span style="font-size: 2.5rem; font-weight: 800; color: var(--sh-primary);">{{ $user->leave_balance }}</span>
                    <span class="text-muted">hari</span>
                </div>
                <div style="height: 8px; border-radius: 4px; background: var(--sh-gray-100);">
                    <div style="height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--sh-primary), #22c55e); width: {{ min(100, ($user->leave_balance / 12) * 100) }}%;"></div>
                </div>
                @endif
            </div>
        </div>

        {{-- Kepegawaian Info --}}
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-briefcase me-2" style="color: var(--sh-accent);"></i>
                    Data Kepegawaian
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0" style="font-size: 0.88rem;">
                    <tbody>
                        <tr><td class="text-muted" style="width: 40%; padding: 0.7rem 1rem;">NIP</td><td class="fw-semibold" style="padding: 0.7rem 1rem;">{{ $user->nip }}</td></tr>
                        <tr><td class="text-muted" style="padding: 0.7rem 1rem;">Golongan</td><td class="fw-semibold" style="padding: 0.7rem 1rem;">{{ $user->golongan_ruang ?? '-' }}</td></tr>
                        <tr><td class="text-muted" style="padding: 0.7rem 1rem;">Unit Kerja</td><td class="fw-semibold" style="padding: 0.7rem 1rem;">{{ $user->unit_kerja ?? '-' }}</td></tr>
                        <tr><td class="text-muted" style="padding: 0.7rem 1rem;">Masa Kerja</td><td class="fw-semibold" style="padding: 0.7rem 1rem;">{{ $user->masa_kerja_format ?? '-' }}</td></tr>
                        <tr><td class="text-muted" style="padding: 0.7rem 1rem;">TMT</td><td class="fw-semibold" style="padding: 0.7rem 1rem;">{{ $user->masa_kerja_mulai ? $user->masa_kerja_mulai->format('d M Y') : '-' }}</td></tr>
                        <tr><td class="text-muted" style="padding: 0.7rem 1rem;">Atasan</td><td class="fw-semibold" style="padding: 0.7rem 1rem;">{{ $user->atasan->name ?? '-' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right: Edit Forms --}}
    <div class="col-lg-8">
        {{-- Edit Data Pribadi --}}
        <div class="card sh-card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-edit me-2" style="color: var(--sh-primary);"></i>
                    Edit Data Pribadi
                </h3>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Telepon</label>
                            <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror"
                                   value="{{ old('telepon', $user->telepon) }}" placeholder="08xxxxxxxxxx"
                                   style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px;">
                            @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Alamat</label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2"
                                      placeholder="Alamat tempat tinggal"
                                      style="border-radius: 10px; border: 2px solid #e2e8f0;">{{ old('alamat', $user->alamat) }}</textarea>
                            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn sh-btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- #48: Preferensi Notifikasi --}}
        <div class="card sh-card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-bell me-2" style="color: var(--sh-accent);"></i>
                    Preferensi Notifikasi Email
                </h3>
            </div>
            <div class="card-body p-4">
                @php $prefs = auth()->user()->notification_preferences ?? []; @endphp
                <p class="text-muted mb-3" style="font-size:0.875rem;">
                    Pilih jenis notifikasi yang ingin Anda terima melalui email. Notifikasi in-app selalu aktif.
                </p>
                <form method="POST" action="{{ route('profile.notifications') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        @php
                            $notifOptions = [
                                'email_on_approve'  => ['label' => 'Cuti Disetujui', 'icon' => 'ti-circle-check', 'color' => '#16a34a', 'desc' => 'Email saat pengajuan cuti Anda disetujui'],
                                'email_on_reject'   => ['label' => 'Cuti Ditolak', 'icon' => 'ti-circle-x', 'color' => '#dc2626', 'desc' => 'Email saat pengajuan cuti Anda ditolak'],
                                'email_on_pending'  => ['label' => 'Ada Pengajuan Baru', 'icon' => 'ti-file-plus', 'color' => '#d97706', 'desc' => 'Email saat ada bawahan mengajukan cuti (untuk atasan)'],
                                'email_on_decision' => ['label' => 'Keputusan Pejabat', 'icon' => 'ti-gavel', 'color' => 'var(--sh-primary)', 'desc' => 'Email saat ketua/pejabat memberikan keputusan final'],
                            ];
                        @endphp
                        @foreach($notifOptions as $key => $opt)
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3 p-3 rounded" style="background:var(--sh-gray-50);border:1px solid var(--sh-border);">
                                <div style="width:36px;height:36px;border-radius:10px;background:{{ $opt['color'] }}1a;color:{{ $opt['color'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="ti {{ $opt['icon'] }}"></i>
                                </div>
                                <div class="flex-fill">
                                    <label class="d-flex align-items-center justify-content-between gap-2 mb-0" style="cursor:pointer;">
                                        <div>
                                            <div class="fw-semibold" style="font-size:0.875rem;color:var(--sh-text);">{{ $opt['label'] }}</div>
                                            <div class="text-muted" style="font-size:0.78rem;">{{ $opt['desc'] }}</div>
                                        </div>
                                        <div class="form-check form-switch ms-2 mb-0">
                                            <input class="form-check-input" type="checkbox" name="{{ $key }}" id="{{ $key }}"
                                                   value="1" {{ ($prefs[$key] ?? true) ? 'checked' : '' }}
                                                   style="cursor:pointer;width:2.5em;height:1.25em;">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn sh-btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Preferensi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Ganti Password --}}
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-lock me-2" style="color: var(--sh-danger);"></i>
                    Ganti Password
                </h3>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Password Lama <span class="text-danger">*</span></label>
                            <div style="position:relative;">
                                <input type="password" name="current_password" id="pw_old" class="form-control @error('current_password') is-invalid @enderror" required
                                       autocomplete="current-password"
                                       style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px; padding-right: 44px; transition: border-color 0.2s;">
                                <button type="button" class="sh-pw-eye" onclick="togglePw('pw_old', this)" title="Tampilkan/sembunyikan" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;font-size:1.1rem;"><i class="ti ti-eye"></i></button>
                            </div>
                            @error('current_password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Password Baru <span class="text-danger">*</span></label>
                            <div style="position:relative;">
                                <input type="password" name="password" id="pw_new" class="form-control @error('password') is-invalid @enderror" required
                                       placeholder="Min. 6 karakter"
                                       autocomplete="new-password"
                                       style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px; padding-right: 44px; transition: border-color 0.2s;">
                                <button type="button" class="sh-pw-eye" onclick="togglePw('pw_new', this)" title="Tampilkan/sembunyikan" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;font-size:1.1rem;"><i class="ti ti-eye"></i></button>
                            </div>
                            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Konfirmasi <span class="text-danger">*</span></label>
                            <div style="position:relative;">
                                <input type="password" name="password_confirmation" id="pw_confirm" class="form-control" required
                                       autocomplete="new-password"
                                       style="border-radius: 10px; border: 2px solid #e2e8f0; height: 46px; padding-right: 44px; transition: border-color 0.2s;">
                                <button type="button" class="sh-pw-eye" onclick="togglePw('pw_confirm', this)" title="Tampilkan/sembunyikan" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;font-size:1.1rem;"><i class="ti ti-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn sh-btn-danger">
                            <i class="ti ti-key me-1"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function togglePw(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon  = btn.querySelector('i');
    if (!input) return;
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    icon.className = show ? 'ti ti-eye-off' : 'ti ti-eye';
    btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
}
</script>
@endpush
@endsection
