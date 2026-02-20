{{-- Pejabat (Ketua PN) Decision Modal --}}
<div class="modal modal-blur fade" id="decisionModal{{ $req->id }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <form method="POST" action="{{ route('leave.decide', $req) }}" id="decisionForm{{ $req->id }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--sh-primary-light); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                            <i class="ti ti-gavel" style="font-size: 2rem; color: var(--sh-primary);"></i>
                        </div>
                        <h3 class="fw-bold mb-1">Keputusan Pejabat Berwenang</h3>
                        <p class="text-muted mb-0" style="font-size: 0.88rem;">
                            Keputusan atas pengajuan cuti
                            <strong class="text-dark">{{ $req->user->name }}</strong>
                        </p>
                    </div>

                    {{-- Request Summary --}}
                    <div class="mb-3" style="background: var(--sh-gray-50); border-radius: 12px; padding: 0.85rem 1rem;">
                        <div class="row g-2" style="font-size: 0.85rem;">
                            <div class="col-6">
                                <div class="text-muted mb-1">Jenis Cuti</div>
                                <div class="fw-semibold">{{ $req->type_label }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1">Durasi</div>
                                <div class="fw-semibold">{{ $req->total_days }} hari</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted mb-1">Periode</div>
                                <div class="fw-semibold">{{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}</div>
                            </div>
                            @if($req->reason)
                            <div class="col-12">
                                <div class="text-muted mb-1">Alasan</div>
                                <div style="color: #475569;">{{ Str::limit($req->reason, 120) }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Pertimbangan Atasan Info --}}
                    @if($req->atasanReviewer)
                    <div class="mb-3" style="background: var(--sh-primary-light); border-radius: 12px; padding: 0.85rem 1rem;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-user-check" style="color: var(--sh-primary);"></i>
                            <span class="fw-bold" style="font-size: 0.85rem; color: var(--sh-primary);">Pertimbangan Atasan Langsung</span>
                        </div>
                        <div class="row g-2" style="font-size: 0.85rem;">
                            <div class="col-6">
                                <div class="text-muted mb-1">Atasan</div>
                                <div class="fw-semibold">{{ $req->atasanReviewer->name }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1">Pertimbangan</div>
                                <div class="fw-semibold">
                                    @if($req->pertimbangan_atasan === 'setuju')
                                        <span style="color: var(--sh-success);"><i class="ti ti-circle-check"></i> Disetujui</span>
                                    @elseif($req->pertimbangan_atasan === 'ubah')
                                        <span style="color: var(--sh-primary);"><i class="ti ti-edit"></i> Perubahan</span>
                                    @elseif($req->pertimbangan_atasan === 'tangguhkan')
                                        <span style="color: var(--sh-warning);"><i class="ti ti-clock-pause"></i> Ditangguhkan</span>
                                    @elseif($req->pertimbangan_atasan === 'tolak')
                                        <span style="color: var(--sh-danger);"><i class="ti ti-circle-x"></i> Tidak Disetujui</span>
                                    @else
                                        <span class="text-muted">{{ $req->pertimbangan_atasan ?? '-' }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($req->catatan_atasan)
                            <div class="col-12">
                                <div class="text-muted mb-1">Catatan</div>
                                <div style="color: #475569;">{{ $req->catatan_atasan }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="mb-3" style="background: #ecfdf5; border-radius: 12px; padding: 0.85rem 1rem;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-arrow-forward" style="color: var(--sh-success);"></i>
                            <span class="fw-bold" style="font-size: 0.85rem; color: var(--sh-success);">Pengajuan Langsung</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.82rem;">
                            {{ $req->user->jabatan }} &mdash; langsung ke Pejabat Berwenang tanpa pertimbangan atasan
                        </div>
                    </div>
                    @endif

                    {{-- Keputusan Options --}}
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">
                            Keputusan <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-column gap-2" id="keputusanGroup{{ $req->id }}">
                            <label class="form-check keputusan-label" style="background: var(--sh-success-light); border-radius: 10px; padding: 0.65rem 0.85rem; cursor: pointer; margin: 0; border: 2px solid transparent; transition: border-color 0.2s;">
                                <input class="form-check-input keputusan-input" type="radio" name="keputusan" value="setuju" required>
                                <span class="form-check-label fw-semibold" style="color: var(--sh-success);">
                                    <i class="ti ti-circle-check me-1"></i> Disetujui
                                </span>
                            </label>
                            <label class="form-check keputusan-label" style="background: var(--sh-primary-light); border-radius: 10px; padding: 0.65rem 0.85rem; cursor: pointer; margin: 0; border: 2px solid transparent; transition: border-color 0.2s;">
                                <input class="form-check-input keputusan-input" type="radio" name="keputusan" value="ubah">
                                <span class="form-check-label fw-semibold" style="color: var(--sh-primary);">
                                    <i class="ti ti-edit me-1"></i> Perubahan
                                </span>
                            </label>
                            <label class="form-check keputusan-label" style="background: var(--sh-warning-light); border-radius: 10px; padding: 0.65rem 0.85rem; cursor: pointer; margin: 0; border: 2px solid transparent; transition: border-color 0.2s;">
                                <input class="form-check-input keputusan-input" type="radio" name="keputusan" value="tangguhkan">
                                <span class="form-check-label fw-semibold" style="color: var(--sh-warning);">
                                    <i class="ti ti-clock-pause me-1"></i> Ditangguhkan
                                </span>
                            </label>
                            <label class="form-check keputusan-label" style="background: var(--sh-danger-light); border-radius: 10px; padding: 0.65rem 0.85rem; cursor: pointer; margin: 0; border: 2px solid transparent; transition: border-color 0.2s;">
                                <input class="form-check-input keputusan-input" type="radio" name="keputusan" value="tolak">
                                <span class="form-check-label fw-semibold" style="color: var(--sh-danger);">
                                    <i class="ti ti-circle-x me-1"></i> Tidak Disetujui
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Catatan --}}
                    <div class="mb-0">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Pejabat Berwenang</label>
                        <textarea name="catatan_pejabat" class="form-control" rows="2" placeholder="Catatan atau keterangan keputusan..." style="border-radius: 10px; border: 2px solid #e2e8f0;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4" style="justify-content: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;" id="cancelBtn{{ $req->id }}">Batal</button>
                    <button type="submit" class="btn sh-btn-primary text-white" style="min-width: 130px;" id="submitBtn{{ $req->id }}">
                        <i class="ti ti-gavel me-1"></i> <span id="submitText{{ $req->id }}">Tetapkan Keputusan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('decisionForm{{ $req->id }}');
    const submitBtn = document.getElementById('submitBtn{{ $req->id }}');
    const submitText = document.getElementById('submitText{{ $req->id }}');
    const modal = document.getElementById('decisionModal{{ $req->id }}');
    const keputusanGroup = document.getElementById('keputusanGroup{{ $req->id }}');
    let isSubmitting = false;

    // Handle radio button visual feedback for keputusan
    if (keputusanGroup) {
        const radioInputs = keputusanGroup.querySelectorAll('.keputusan-input');
        radioInputs.forEach(radio => {
            const label = radio.closest('.keputusan-label');

            // Initial state
            if (radio.checked) {
                label.style.borderColor = 'currentColor';
            }

            // Change event
            radio.addEventListener('change', function(e) {
                e.stopPropagation();
                radioInputs.forEach(r => {
                    r.closest('.keputusan-label').style.borderColor = 'transparent';
                });
                if (this.checked) {
                    label.style.borderColor = 'currentColor';
                }
            });

            // Click on label should work smoothly
            label.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!radio.checked) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            // Prevent double submission
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }

            isSubmitting = true;

            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-loader me-1" style="animation: spin 1s linear infinite;"></i> <span id="submitText{{ $req->id }}">Memproses...</span>';

            // Add style for spinner animation if not exists
            if (!document.getElementById('spinnerStyle')) {
                const style = document.createElement('style');
                style.id = 'spinnerStyle';
                style.textContent = `
                    @keyframes spin {
                        from { transform: rotate(0deg); }
                        to { transform: rotate(360deg); }
                    }
                `;
                document.head.appendChild(style);
            }
        });
    }

    // Reset form when modal is closed without submission
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            isSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitText.textContent = 'Tetapkan Keputusan';
                submitBtn.innerHTML = '<i class="ti ti-gavel me-1"></i> <span id="submitText{{ $req->id }}">Tetapkan Keputusan</span>';
            }
        });
    }
});
</script>
