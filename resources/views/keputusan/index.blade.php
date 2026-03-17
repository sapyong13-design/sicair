@extends('layouts.app')

@section('title', 'Keputusan — SiCAIR')

@section('content')
<div class="container-xl py-4">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-0" style="font-size: 1.4rem;">
                <i class="ti ti-gavel me-2" style="color: var(--sc-warning);"></i>Keputusan
            </h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                @if(Auth::user()->isAdmin() || Auth::user()->isKetua())
                    Pengajuan menunggu keputusan &amp; riwayat keputusan Anda
                @elseif(Auth::user()->isAtasan())
                    Riwayat review Anda &amp; pengajuan cuti Anda sendiri
                @else
                    Riwayat pengajuan cuti dan keputusan atasannya
                @endif
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
            <i class="ti ti-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    {{-- ADMIN / KETUA VIEW --}}
    @if(Auth::user()->isAdmin() || Auth::user()->isKetua())

    <ul class="nav nav-tabs mb-3" style="border-bottom: 2px solid var(--sc-gray-200);">
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? 'menunggu') === 'menunggu' ? 'active fw-semibold' : '' }}"
               href="{{ route('keputusan.index', ['tab' => 'menunggu']) }}"
               style="{{ ($tab ?? 'menunggu') === 'menunggu' ? 'color: var(--sc-primary); border-bottom: 2px solid var(--sc-primary);' : '' }}">
                <i class="ti ti-clock me-1"></i> Menunggu Keputusan
                @if(isset($menunggu) && method_exists($menunggu, 'total') && $menunggu->total() > 0)
                <span class="badge ms-1" style="background: var(--sc-warning); font-size: 0.7rem; border-radius: 50px;">
                    {{ $menunggu->total() }}
                </span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'riwayat' ? 'active fw-semibold' : '' }}"
               href="{{ route('keputusan.index', ['tab' => 'riwayat']) }}"
               style="{{ ($tab ?? '') === 'riwayat' ? 'color: var(--sc-primary); border-bottom: 2px solid var(--sc-primary);' : '' }}">
                <i class="ti ti-history me-1"></i> Riwayat Keputusan
            </a>
        </li>
    </ul>

    @include('keputusan._filter', [
        'showStatus'     => ($tab ?? 'menunggu') === 'riwayat',
        'showType'       => true,
        'showDate'       => true,
        'showNameSearch' => true,
    ])

    {{-- Tab: Menunggu Keputusan --}}
    @if(($tab ?? 'menunggu') === 'menunggu')
        @if(!isset($menunggu) || (method_exists($menunggu, 'isEmpty') ? $menunggu->isEmpty() : $menunggu->count() === 0))
        <div class="card sc-card"><div class="card-body py-5 text-center">
            <div class="sc-empty-icon"><i class="ti ti-mood-happy"></i></div>
            <h4 class="fw-bold">Tidak Ada Antrian</h4>
            <p class="text-muted mb-0">Tidak ada pengajuan yang menunggu keputusan Anda.</p>
        </div></div>
        @else
        <form method="POST" action="{{ route('leave.bulk-decide') }}">
            @csrf
            <div id="sc-bulk-actions" style="display:none;" class="mb-3">
                <div class="p-3" style="background:var(--sc-primary-light);border-radius:10px;">
                    <div class="d-flex gap-2 align-items-center flex-wrap mb-2">
                        <span class="text-muted small" id="sc-bulk-count">0 dipilih</span>
                        <select name="decision" class="form-select form-select-sm" style="width:auto;" required>
                            <option value="">-- Pilih Keputusan --</option>
                            <option value="setuju">Setujui Semua</option>
                            <option value="tolak">Tolak Semua</option>
                            <option value="tangguhkan">Tangguhkan Semua</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ti ti-check me-1"></i> Terapkan
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="sc-bulk-clear">Batal</button>
                    </div>
                    <div class="form-text mb-2" style="color: var(--sc-warning); font-size:0.8rem;">
                        <i class="ti ti-info-circle me-1"></i>
                        Bulk action hanya berlaku untuk item yang ditampilkan di halaman ini ({{ $menunggu->count() }} dari {{ $menunggu->total() }}).
                    </div>
                    <textarea name="catatan" class="form-control form-control-sm" rows="2"
                        placeholder="Catatan untuk semua pengajuan yang dipilih (opsional)..."></textarea>
                </div>
            </div>

            @foreach($menunggu as $req)
            <div class="card sc-history-card status-{{ $req->status }} mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" name="ids[]" value="{{ $req->id }}"
                                class="sc-bulk-cb form-check-input" style="width:18px;height:18px;flex-shrink:0;margin-top:0;">
                            <div class="sc-user-avatar" style="width:40px;height:40px;font-size:0.8rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:10px;">
                                {{ strtoupper(substr(optional($req->user)->name ?? 'N/A', 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:0.95rem;">{{ optional($req->user)->name ?? 'N/A' }}</div>
                                <div class="text-muted" style="font-size:0.78rem;">{{ optional($req->user)->jabatan ?? optional($req->user)->nip ?? '-' }}</div>
                            </div>
                        </div>
                        <span class="sc-badge sc-badge-pending">{{ $req->type_label ?? ucfirst($req->type) }}</span>
                    </div>
                    <div class="row g-2 mb-2" style="font-size:0.82rem;">
                        <div class="col-sm-6">
                            <i class="ti ti-calendar me-1 text-muted"></i>
                            {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                        </div>
                        <div class="col-sm-6">
                            <i class="ti ti-clock me-1 text-muted"></i>
                            {{ $req->total_days ?? $req->total_hari_kerja ?? '-' }} hari
                            @if($req->total_hari_kerja) ({{ $req->total_hari_kerja }} hari kerja) @endif
                        </div>
                    </div>
                    @if($req->atasanReviewer)
                    <div class="mb-2" style="background:var(--sc-primary-light);border-radius:8px;padding:0.5rem 0.75rem;font-size:0.82rem;">
                        <i class="ti ti-user-check me-1" style="color:var(--sc-primary);"></i>
                        <strong>Pertimbangan {{ $req->atasanReviewer->name }}:</strong>
                        <span style="color:{{ $req->pertimbangan_atasan === 'setuju' ? 'var(--sc-success)' : 'var(--sc-warning)' }}">
                            {{ ucfirst($req->pertimbangan_atasan) }}
                        </span>
                        @if($req->catatan_atasan) &mdash; {{ Str::limit($req->catatan_atasan, 80) }} @endif
                    </div>
                    @else
                    <div class="mb-2" style="background:#ecfdf5;border-radius:8px;padding:0.5rem 0.75rem;font-size:0.82rem;">
                        <i class="ti ti-arrow-forward me-1" style="color:var(--sc-success);"></i>
                        <strong>Pengajuan Langsung</strong> &mdash; tanpa pertimbangan atasan
                    </div>
                    @endif
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm sc-btn-primary flex-fill"
                            data-bs-toggle="modal" data-bs-target="#decisionModal{{ $req->id }}">
                            <i class="ti ti-gavel me-1"></i> Beri Keputusan
                        </button>
                        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @include('partials.pejabat-decision-modal', ['req' => $req])
            @endforeach
        </form>
        {{ $menunggu->links() }}
        @endif
    @endif

    {{-- Tab: Riwayat Keputusan --}}
    @if(($tab ?? '') === 'riwayat')
        @if(!isset($riwayat) || (method_exists($riwayat, 'isEmpty') ? $riwayat->isEmpty() : $riwayat->count() === 0))
        <div class="card sc-card"><div class="card-body py-5 text-center">
            <div class="sc-empty-icon"><i class="ti ti-history"></i></div>
            <h4 class="fw-bold">Belum Ada Riwayat</h4>
            <p class="text-muted mb-0">Belum ada keputusan yang Anda buat.</p>
        </div></div>
        @else
        @foreach($riwayat as $req)
        @include('keputusan._riwayat-card', ['req' => $req])
        @endforeach
        {{ $riwayat->links() }}
        @endif
    @endif

    {{-- ATASAN VIEW --}}
    @elseif(Auth::user()->isAtasan())

    <ul class="nav nav-tabs mb-3" style="border-bottom: 2px solid var(--sc-gray-200);">
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? 'review') === 'review' ? 'active fw-semibold' : '' }}"
               href="{{ route('keputusan.index', ['tab' => 'review']) }}"
               style="{{ ($tab ?? 'review') === 'review' ? 'color: var(--sc-primary); border-bottom: 2px solid var(--sc-primary);' : '' }}">
                <i class="ti ti-checklist me-1"></i> Review Saya
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'pengajuan' ? 'active fw-semibold' : '' }}"
               href="{{ route('keputusan.index', ['tab' => 'pengajuan']) }}"
               style="{{ ($tab ?? '') === 'pengajuan' ? 'color: var(--sc-primary); border-bottom: 2px solid var(--sc-primary);' : '' }}">
                <i class="ti ti-file-text me-1"></i> Pengajuan Saya
            </a>
        </li>
    </ul>

    @include('keputusan._filter', ['showStatus' => true, 'showType' => true, 'showDate' => true, 'showNameSearch' => true])

    @if(($tab ?? 'review') === 'review')
        @if(!isset($review) || (method_exists($review, 'isEmpty') ? $review->isEmpty() : $review->count() === 0))
        <div class="card sc-card"><div class="card-body py-5 text-center">
            <div class="sc-empty-icon"><i class="ti ti-checklist"></i></div>
            <h4 class="fw-bold">Belum Ada Riwayat Review</h4>
            <p class="text-muted mb-0">Anda belum pernah mereview pengajuan bawahan.</p>
        </div></div>
        @else
        <form method="POST" action="{{ route('leave.bulk-pertimbangan') }}">
            @csrf
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <label class="d-flex align-items-center gap-2" style="font-size:0.85rem;cursor:pointer;">
                    <input type="checkbox"
                           class="form-check-input"
                           style="width:18px;height:18px;"
                           onclick="document.querySelectorAll('.sc-bulk-cb').forEach(cb => cb.checked = this.checked)">
                    Pilih Semua
                </label>
                <button type="submit" class="btn btn-sm sc-btn-primary">
                    <i class="ti ti-send me-1"></i> Teruskan ke Ketua
                </button>
            </div>
        @foreach($review as $req)
        <div class="card sc-history-card status-{{ $req->status }} mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        @if(in_array($req->status, [\App\Models\LeaveRequest::STATUS_DIAJUKAN, \App\Models\LeaveRequest::STATUS_PENDING]))
                        <input type="checkbox" name="ids[]" value="{{ $req->id }}"
                            class="sc-bulk-cb form-check-input" style="width:18px;height:18px;flex-shrink:0;margin-top:0;">
                        @else
                        <div style="width:18px;flex-shrink:0;"></div>
                        @endif
                        <div class="sc-user-avatar" style="width:40px;height:40px;font-size:0.8rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:10px;">
                            {{ strtoupper(substr(optional($req->user)->name ?? 'N/A', 0, 2)) }}
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:0.95rem;">{{ optional($req->user)->name ?? 'N/A' }}</div>
                            <div class="text-muted" style="font-size:0.78rem;">{{ optional($req->user)->jabatan ?? optional($req->user)->nip ?? '-' }}</div>
                        </div>
                    </div>
                    <span class="sc-badge sc-badge-{{ in_array($req->status, ['disetujui','approved']) ? 'approved' : (in_array($req->status, ['ditolak','rejected']) ? 'rejected' : 'pending') }}">
                        {{ $req->status_label ?? ucfirst($req->status) }}
                    </span>
                </div>
                <div class="row g-2 mb-2" style="font-size:0.82rem;">
                    <div class="col-sm-4"><i class="ti ti-tag me-1 text-muted"></i>{{ $req->type_label ?? ucfirst($req->type) }}</div>
                    <div class="col-sm-4"><i class="ti ti-calendar me-1 text-muted"></i>{{ $req->start_date->format('d M Y') }}</div>
                    <div class="col-sm-4"><i class="ti ti-clock me-1 text-muted"></i>{{ $req->total_days ?? $req->total_hari_kerja ?? '-' }} hari</div>
                </div>
                <div style="background:var(--sc-primary-light);border-radius:8px;padding:0.45rem 0.75rem;font-size:0.82rem;">
                    <i class="ti ti-user-check me-1" style="color:var(--sc-primary);"></i>
                    <strong>Pertimbangan Anda:</strong>
                    {{ ucfirst($req->pertimbangan_atasan ?? '-') }}
                    @if($req->catatan_atasan) &mdash; {{ Str::limit($req->catatan_atasan, 80) }} @endif
                </div>
                @if($req->pejabat)
                <div class="mt-2" style="font-size:0.8rem;color:var(--sc-muted);">
                    <i class="ti ti-gavel me-1"></i>
                    Keputusan {{ $req->pejabat->name }}:
                    <strong>{{ ucfirst($req->keputusan_pejabat ?? '-') }}</strong>
                    @if($req->catatan_pejabat) &mdash; {{ Str::limit($req->catatan_pejabat, 60) }} @endif
                </div>
                @endif
                <div class="mt-2">
                    <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:0.8rem;">
                        <i class="ti ti-eye me-1"></i> Detail
                    </a>
                </div>
            </div>
        </div>
        @endforeach
        </form>
        {{ $review->links() }}
        @endif
    @endif

    @if(($tab ?? '') === 'pengajuan')
        @include('keputusan._pengajuan-list', ['items' => $pengajuan ?? collect()])
    @endif

    {{-- PEGAWAI / HAKIM VIEW --}}
    @else

    @include('keputusan._filter', ['showStatus' => true, 'showType' => true, 'showDate' => true])
    @include('keputusan._pengajuan-list', ['items' => $pengajuan ?? collect()])

    @endif

</div>

@push('scripts')
<script>
document.querySelectorAll('.sc-bulk-cb').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var checked = document.querySelectorAll('.sc-bulk-cb:checked').length;
        var bar = document.getElementById('sc-bulk-actions');
        var countEl = document.getElementById('sc-bulk-count');
        if (bar) bar.style.display = checked > 0 ? 'block' : 'none';
        if (countEl) countEl.textContent = checked + ' dipilih';
    });
});
var clearBtn = document.getElementById('sc-bulk-clear');
if (clearBtn) {
    clearBtn.addEventListener('click', function() {
        document.querySelectorAll('.sc-bulk-cb:checked').forEach(function(cb) { cb.checked = false; });
        var bar = document.getElementById('sc-bulk-actions');
        if (bar) bar.style.display = 'none';
    });
}
</script>
@endpush

@endsection
