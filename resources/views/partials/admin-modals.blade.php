{{-- Approve Modal --}}
<div class="modal modal-blur fade" id="approveModal{{ $req->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <form method="POST" action="{{ route('leave.approve', $req) }}">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--sh-success-light); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="ti ti-circle-check" style="font-size: 2rem; color: var(--sh-success);"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Setujui Cuti?</h3>
                    <p class="text-muted mb-3">
                        Setujui cuti <strong class="text-dark">{{ $req->user->name }}</strong> selama
                        <strong class="text-dark">{{ $req->total_days }} hari</strong>
                        <br><span style="font-size: 0.82rem;">({{ $req->start_date->format('d M Y') }} - {{ $req->end_date->format('d M Y') }})</span>
                    </p>
                    <div class="alert mb-3" style="background: var(--sh-primary-light); border: none; border-radius: 10px; color: var(--sh-primary); font-size: 0.85rem;">
                        <i class="ti ti-info-circle me-1"></i>
                        Sisa cuti pegawai akan otomatis berkurang {{ $req->total_days }} hari.
                    </div>
                    <div class="text-start mb-0">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan (opsional)</label>
                        <textarea name="admin_note" class="form-control" rows="2" placeholder="Catatan untuk pegawai..." style="border-radius: 10px; border: 2px solid #e2e8f0;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4" style="justify-content: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;">Batal</button>
                    <button type="submit" class="btn sh-btn-success" style="min-width: 100px;">
                        <i class="ti ti-check me-1"></i> Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal modal-blur fade" id="rejectModal{{ $req->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <form method="POST" action="{{ route('leave.reject', $req) }}">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--sh-danger-light); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="ti ti-circle-x" style="font-size: 2rem; color: var(--sh-danger);"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Tolak Pengajuan?</h3>
                    <p class="text-muted mb-3">
                        Tolak cuti <strong class="text-dark">{{ $req->user->name }}</strong> selama
                        <strong class="text-dark">{{ $req->total_days }} hari</strong>?
                    </p>
                    <div class="text-start mb-0">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="admin_note" class="form-control" rows="2" placeholder="Jelaskan alasan penolakan..." required style="border-radius: 10px; border: 2px solid #e2e8f0;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4" style="justify-content: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;">Batal</button>
                    <button type="submit" class="btn sh-btn-danger" style="min-width: 100px;">
                        <i class="ti ti-x me-1"></i> Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
