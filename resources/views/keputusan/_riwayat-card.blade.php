<div class="card sc-history-card status-{{ $req->status }} mb-3">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="d-flex align-items-center gap-2">
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
        @if($req->catatan_pejabat)
        <div style="font-size:0.82rem;color:var(--sc-muted);">
            <i class="ti ti-message me-1"></i> {{ Str::limit($req->catatan_pejabat, 100) }}
        </div>
        @endif
        @if(isset($req->decided_at) && $req->decided_at)
        <div class="mt-1" style="font-size:0.78rem;color:var(--sc-muted);">
            <i class="ti ti-calendar-check me-1"></i> Diputuskan {{ $req->decided_at->diffForHumans() }}
        </div>
        @endif
        <div class="mt-2">
            <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:0.8rem;">
                <i class="ti ti-eye me-1"></i> Detail
            </a>
        </div>
    </div>
</div>
