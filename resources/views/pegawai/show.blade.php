@extends('layouts.app')

@section('title', 'Detail Pegawai — SiCAIR')

@section('content')
{{-- Breadcrumb (#10) --}}
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <a href="{{ route('pegawai.index') }}">Kelola Pegawai</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">{{ Str::limit($pegawai->name, 20) }}</span>
</nav>

{{-- Page Header --}}
<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0;" aria-label="Kembali ke daftar pegawai">
                <i class="ti ti-arrow-left" style="font-size: 1.2rem;" aria-hidden="true"></i>
            </a>
            <div>
                <h2 class="sc-page-title mb-0">{{ $pegawai->name }}</h2>
                <div class="text-muted" style="font-size: 0.85rem;">
                    NIP: {{ $pegawai->nip }}
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.leave.create', $pegawai) }}" class="btn sc-btn-success text-white">
                <i class="ti ti-file-plus me-1"></i> Tambah Cuti
            </a>
            <a href="{{ route('pegawai.edit', $pegawai) }}" class="btn sc-btn-primary">
                <i class="ti ti-edit me-1"></i> Edit Pegawai
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left Column: Info --}}
    <div class="col-lg-5">
        {{-- Profile Card --}}
        <div class="card sc-card mb-4">
            <div class="card-body p-4 text-center">
                <div class="sc-user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 1.8rem; background: var(--sc-primary-light); color: var(--sc-primary); border: none; border-radius: 20px;">
                    {{ strtoupper(substr($pegawai->name, 0, 2)) }}
                </div>
                <h3 class="fw-bold mb-1">{{ $pegawai->name }}</h3>
                <div class="text-muted mb-2" style="font-size: 0.88rem;">{{ $pegawai->jabatan ?? '-' }}</div>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    @php
                        $roleBadgeClass = match($pegawai->role) {
                            'admin' => 'sc-badge-approved',
                            'ketua' => 'sc-badge-rejected',
                            'panitera', 'sekretaris', 'atasan' => 'sc-badge-pending',
                            default => 'sc-badge-approved',
                        };
                        $roleLabelShow = match($pegawai->role) {
                            'admin' => 'Admin',
                            'ketua' => 'Ketua PN',
                            'panitera' => 'Panitera',
                            'sekretaris' => 'Sekretaris',
                            'atasan' => 'Atasan',
                            'hakim' => 'Hakim',
                            'hakim_ad_hoc' => 'Hakim Ad Hoc',
                            'pegawai' => 'Pegawai',
                            default => ucfirst($pegawai->role),
                        };
                    @endphp
                    <span class="sc-badge {{ $roleBadgeClass }}" style="font-size: 0.75rem;">
                        {{ $roleLabelShow }}
                    </span>
                    <span class="badge" style="background: #f1f5f9; color: #475569; border-radius: 50px; font-weight: 600; font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                        {{ ucfirst($pegawai->status_pegawai ?? '-') }}
                    </span>
                    @if($pegawai->lokasi_terpencil)
                    <span class="badge" style="background: var(--sc-warning-light); color: var(--sc-warning); border-radius: 50px; font-weight: 600; font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                        <i class="ti ti-map-pin"></i> Terpencil
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Detail Info --}}
        <div class="card sc-card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-info-circle me-2" style="color: var(--sc-primary);"></i>
                    Informasi Lengkap
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table mb-0" style="font-size: 0.88rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 40%; padding: 0.75rem 1rem;">NIP</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->nip }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Jenis Kelamin</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Golongan/Ruang</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->golongan_ruang ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Unit Kerja</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->unit_kerja ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Masa Kerja</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->masa_kerja_format ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">TMT</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->masa_kerja_mulai ? $pegawai->masa_kerja_mulai->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Atasan Langsung</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">
                                @if($pegawai->atasan)
                                    <a href="{{ route('pegawai.show', $pegawai->atasan) }}" style="color: var(--sc-primary);">{{ $pegawai->atasan->name }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Jumlah Anak</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->jumlah_anak ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Telepon</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->telepon ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 0.75rem 1rem;">Alamat</td>
                            <td class="fw-semibold" style="padding: 0.75rem 1rem;">{{ $pegawai->alamat ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        {{-- Cuti Balance --}}
        <div class="card sc-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-calendar-stats me-2" style="color: var(--sc-success);"></i>
                    Sisa Cuti Tahunan
                </h3>
            </div>
            <div class="card-body p-4">
                @php $lb = $pegawai->leave_balance ?? 0; @endphp
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span style="font-size: 2.5rem; font-weight: 800; color: {{ $lb <= 0 ? 'var(--sc-danger)' : ($lb <= 3 ? 'var(--sc-warning)' : 'var(--sc-primary)') }};">{{ $lb }}</span>
                    <span class="text-muted">hari tersisa</span>
                    @if($lb <= 0)
                    <span class="badge" style="background: var(--sc-danger-light); color: var(--sc-danger); font-size: 0.75rem; border-radius: 50px; padding: 0.25rem 0.6rem;">Habis</span>
                    @elseif($lb <= 3)
                    <span class="badge" style="background: var(--sc-warning-light); color: var(--sc-warning); font-size: 0.75rem; border-radius: 50px; padding: 0.25rem 0.6rem;">Rendah</span>
                    @endif
                </div>
                <div style="height: 8px; border-radius: 4px; background: var(--sc-gray-100);">
                    <div style="height: 100%; border-radius: 4px; background: linear-gradient(90deg, {{ $lb <= 3 ? 'var(--sc-danger)' : 'var(--sc-primary)' }}, {{ $lb <= 3 ? '#f87171' : '#22c55e' }}); width: {{ min(100, ($lb / 12) * 100) }}%;"></div>
                </div>
            </div>
        </div>

        {{-- Notification Channels (admin only) --}}
        @if(auth()->user()->isAdmin())
        <div class="card sc-card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="ti ti-bell me-2"></i>Channel Notifikasi</h6>
            </div>
            <div class="card-body py-2">
                <form method="POST" action="{{ route('pegawai.update-channels', $pegawai->id) }}">
                    @csrf @method('PATCH')
                    <div class="d-flex gap-3 align-items-center flex-wrap">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="channels[email]" value="1"
                                id="ch_email_{{ $pegawai->id }}"
                                {{ ($pegawai->notification_channels['email'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="ch_email_{{ $pegawai->id }}">Email</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="channels[whatsapp]" value="1"
                                id="ch_wa_{{ $pegawai->id }}"
                                {{ ($pegawai->notification_channels['whatsapp'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="ch_wa_{{ $pegawai->id }}">WhatsApp</label>
                        </div>
                        <button type="submit" class="btn btn-xs btn-outline-primary btn-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Right Column: Records --}}
    <div class="col-lg-7">
        {{-- Kelola Saldo Cuti (3 tahun, inline edit untuk admin) --}}
        <div class="card sc-card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0">
                    <i class="ti ti-chart-bar me-2" style="color: #7c3aed;"></i>
                    Kelola Saldo Cuti
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table sc-table mb-0">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Hak</th>
                            <th>Diambil</th>
                            <th>Sisa</th>
                            <th>Carry Over</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($saldoYears as $yr => $data)
                        <tr>
                            <td class="fw-bold">
                                {{ $yr }}
                                @if($data['is_current'])
                                <span class="badge ms-1" style="background: var(--sc-primary-light); color: var(--sc-primary); font-size: 0.7rem; border-radius: 50px; padding: 0.2rem 0.55rem;">Saat Ini</span>
                                @endif
                            </td>
                            <td>{{ $data['hak_cuti'] + $data['carry_over'] + $data['tambahan_terpencil'] }} hari</td>
                            <td>{{ $data['cuti_diambil'] }} hari</td>
                            <td><span class="fw-bold" style="color: var(--sc-primary);">{{ $data['sisa_cuti'] }} hari</span></td>
                            <td>{{ $data['carry_over'] > 0 ? '+' . $data['carry_over'] : '—' }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    style="border-radius: 7px; font-size: 0.78rem; padding: 0.2rem 0.65rem;"
                                    onclick="openSaldoModal({{ $yr }})">
                                    <i class="ti ti-edit me-1"></i>Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @php
                $currentYear = (int) date('Y');
                $olderRecords = $pegawai->cutiRecords->filter(fn($r) => $r->tahun < $currentYear - 2);
            @endphp
            @if($olderRecords->isNotEmpty())
            <div class="px-3 pb-3">
                <details>
                    <summary class="text-muted" style="font-size: 0.82rem; cursor: pointer;">Lihat rekap tahun sebelumnya ({{ $olderRecords->count() }} tahun)</summary>
                    <div class="table-responsive">
                    <table class="table table-sm mt-2 mb-0" style="font-size: 0.82rem;">
                        <thead><tr><th>Tahun</th><th>Hak</th><th>Diambil</th><th>Sisa</th></tr></thead>
                        <tbody>
                        @foreach($olderRecords as $rec)
                            <tr>
                                <td>{{ $rec->tahun }}</td>
                                <td>{{ $rec->total_hak }}</td>
                                <td>{{ $rec->cuti_diambil }}</td>
                                <td>{{ $rec->sisa_cuti }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                </details>
            </div>
            @endif
            <div class="px-3 pb-3">
                <small class="text-muted"><i class="ti ti-info-circle me-1"></i>Gunakan Edit untuk koreksi langsung. Untuk audit trail formal, gunakan <a href="{{ route('balance-adjustment.create', $pegawai) }}">Penyesuaian Saldo</a>.</small>
            </div>
        </div>

        {{-- Modal Edit Saldo Cuti --}}
        <div class="modal fade" id="saldoEditModal" tabindex="-1" aria-labelledby="saldoEditModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px; border: none;">
                    <div class="modal-header" style="border-bottom: 1px solid var(--sc-gray-100);">
                        <h5 class="modal-title fw-bold" id="saldoEditModalLabel">
                            <i class="ti ti-edit me-2" style="color: var(--sc-primary);"></i>
                            Edit Saldo Cuti <span id="saldoModalYear"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <form id="saldoEditForm" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div id="saldoCurrentYearWarning" class="alert alert-warning d-none" style="border-radius: 10px; font-size: 0.85rem;">
                                <i class="ti ti-alert-triangle me-1"></i>
                                Ini adalah tahun berjalan. Perubahan sisa cuti juga akan memperbarui saldo cuti aktif pegawai.
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Hak Cuti Dasar <span class="text-muted fw-normal">(hari)</span></label>
                                <input type="number" id="saldo_hak" name="hak_cuti" class="form-control" min="0" max="60" oninput="autoHitungSisa()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Carry Over <span class="text-muted fw-normal">(hari)</span></label>
                                <input type="number" id="saldo_co" name="carry_over" class="form-control" min="0" max="24" oninput="autoHitungSisa()">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tambahan Terpencil <span class="text-muted fw-normal">(0–12 hari)</span></label>
                                <input type="number" id="saldo_tp" name="tambahan_terpencil" class="form-control" min="0" max="12" oninput="autoHitungSisa()">
                            </div>
                            <hr style="border-color: var(--sc-gray-100);">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted">Cuti Diambil (aktual, read-only)</label>
                                <div class="form-control bg-light" id="saldo_diambil_display" data-val="0" style="color: #64748b;">0 hari</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Sisa Cuti <span class="text-muted fw-normal">(hari)</span></label>
                                <div class="input-group">
                                    <input type="number" id="saldo_sisa" name="sisa_cuti" class="form-control" min="0" max="60">
                                    <button type="button" class="btn btn-outline-secondary" onclick="autoHitungSisa()" title="Auto-hitung sisa">
                                        <i class="ti ti-refresh"></i> Auto
                                    </button>
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label fw-semibold">Keterangan <span class="text-muted fw-normal">(opsional)</span></label>
                                <textarea id="saldo_keterangan" name="keterangan" class="form-control" rows="2" maxlength="500" placeholder="Catatan koreksi saldo..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid var(--sc-gray-100);">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn sc-btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Leave History --}}
        <div class="card sc-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0">
                    <i class="ti ti-history me-2" style="color: #d97706;"></i>
                    Riwayat Pengajuan Cuti
                </h3>
                @if($pegawai->leaveRequests->isNotEmpty())
                <span class="text-muted" style="font-size: 0.8rem;">{{ $pegawai->leaveRequests->count() }} pengajuan</span>
                @endif
            </div>

            @if($pegawai->leaveRequests->isEmpty())
            <div class="card-body py-5">
                <div class="text-center">
                    <div class="sc-empty-icon">
                        <i class="ti ti-calendar-off"></i>
                    </div>
                    <p class="text-muted mb-0">Belum ada riwayat pengajuan cuti.</p>
                </div>
            </div>
            @else
            <div class="card-body p-3">
                @foreach($pegawai->leaveRequests as $req)
                <div class="card sc-history-card status-{{ $req->status }} mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fw-bold" style="font-size: 0.9rem;">{{ $req->type_label }}</span>
                                <div class="text-muted" style="font-size: 0.78rem;">
                                    <i class="ti ti-calendar me-1"></i>
                                    {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                                    &middot; {{ $req->total_days }} hari
                                </div>
                            </div>
                            @php
                                $badgeClass = match(true) {
                                    $req->isApproved() => 'sc-badge-approved',
                                    $req->isRejected() => 'sc-badge-rejected',
                                    default => 'sc-badge-pending',
                                };
                            @endphp
                            <span class="sc-badge {{ $badgeClass }}">{{ $req->status_label }}</span>
                        </div>
                        @if($req->reason)
                        <div style="font-size: 0.82rem; color: #475569;" class="mb-2">{{ Str::limit($req->reason, 100) }}</div>
                        @endif
                        {{-- Admin Actions --}}
                        <div class="d-flex gap-2 mt-1">
                            <a href="{{ route('leave.show', $req) }}"
                               class="btn btn-sm btn-outline-secondary" style="border-radius: 7px; font-size: 0.78rem; padding: 0.2rem 0.65rem;">
                                <i class="ti ti-eye me-1"></i>Detail
                            </a>
                            <a href="{{ route('admin.leave.edit', $req) }}"
                               class="btn btn-sm btn-outline-primary" style="border-radius: 7px; font-size: 0.78rem; padding: 0.2rem 0.65rem;">
                                <i class="ti ti-edit me-1"></i>Edit
                            </a>
                            <form method="POST" action="{{ route('admin.leave.destroy', $req) }}"
                                  onsubmit="return confirm('Hapus riwayat cuti ini? Saldo cuti tahunan akan dikembalikan jika status disetujui.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger" style="border-radius: 7px; font-size: 0.78rem; padding: 0.2rem 0.65rem;">
                                    <i class="ti ti-trash me-1"></i>Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const saldoData = @json($saldoYears);
const saldoBaseUrl = "{{ route('pegawai.saldo-cuti', [$pegawai, '__YEAR__']) }}";
const currentYear = {{ (int) date('Y') }};

function openSaldoModal(year) {
    const d = saldoData[year];
    if (!d) return;

    document.getElementById('saldoModalYear').textContent = year + ' — {{ $pegawai->name }}';
    document.getElementById('saldo_hak').value = d.hak_cuti;
    document.getElementById('saldo_co').value = d.carry_over;
    document.getElementById('saldo_tp').value = d.tambahan_terpencil;
    document.getElementById('saldo_sisa').value = d.sisa_cuti;
    document.getElementById('saldo_keterangan').value = d.keterangan || '';

    const diambilEl = document.getElementById('saldo_diambil_display');
    diambilEl.dataset.val = d.cuti_diambil;
    diambilEl.textContent = d.cuti_diambil + ' hari';

    const warning = document.getElementById('saldoCurrentYearWarning');
    warning.classList.toggle('d-none', year !== currentYear);

    const form = document.getElementById('saldoEditForm');
    form.action = saldoBaseUrl.replace('__YEAR__', year);

    new bootstrap.Modal(document.getElementById('saldoEditModal')).show();
}

function autoHitungSisa() {
    const hak = parseInt(document.getElementById('saldo_hak').value) || 0;
    const co  = parseInt(document.getElementById('saldo_co').value) || 0;
    const tp  = parseInt(document.getElementById('saldo_tp').value) || 0;
    const di  = parseInt(document.getElementById('saldo_diambil_display').dataset.val) || 0;
    document.getElementById('saldo_sisa').value = Math.max(0, hak + co + tp - di);
}
</script>
@endpush
