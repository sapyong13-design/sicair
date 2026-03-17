@if($items->isEmpty())
<div class="card sc-card"><div class="card-body py-5 text-center">
    <div class="sc-empty-icon"><i class="ti ti-file-off"></i></div>
    <h4 class="fw-bold">Belum Ada Pengajuan</h4>
    <p class="text-muted mb-0">Anda belum pernah mengajukan cuti.</p>
</div></div>
@else
@foreach($items as $req)
<div class="card sc-history-card status-{{ $req->status }} mb-3">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="fw-bold" style="font-size:0.95rem;">{{ $req->type_label ?? ucfirst($req->type) }}</div>
                <div class="text-muted" style="font-size:0.78rem;">
                    {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                    &nbsp;·&nbsp; {{ $req->total_days ?? $req->total_hari_kerja ?? '-' }} hari
                </div>
            </div>
            <span class="sc-badge sc-badge-{{ in_array($req->status, ['disetujui','approved']) ? 'approved' : (in_array($req->status, ['ditolak','rejected']) ? 'rejected' : 'pending') }}">
                {{ $req->status_label ?? ucfirst($req->status) }}
            </span>
        </div>
        @if($req->pejabat)
        <div style="font-size:0.82rem;background:var(--sc-gray-50);border-radius:8px;padding:0.45rem 0.75rem;" class="mb-2">
            <i class="ti ti-gavel me-1" style="color:var(--sc-warning);"></i>
            <strong>{{ $req->pejabat->name }}</strong>:
            {{ ucfirst($req->keputusan_pejabat ?? 'Belum diputuskan') }}
            @if($req->catatan_pejabat) &mdash; {{ Str::limit($req->catatan_pejabat, 80) }} @endif
        </div>
        @endif
        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:0.8rem;">
            <i class="ti ti-eye me-1"></i> Detail
        </a>
    </div>
</div>
@endforeach
{{ $items->links() }}
@endif
