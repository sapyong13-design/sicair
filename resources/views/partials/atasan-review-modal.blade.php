{{-- Atasan Review Modal --}}
<div class="modal modal-blur fade" id="reviewModal{{ $req->id }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <form method="POST" action="{{ route('leave.review', $req) }}" id="reviewForm{{ $req->id }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--sh-primary-light); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                            <i class="ti ti-checklist" style="font-size: 2rem; color: var(--sh-primary);"></i>
                        </div>
                        <h3 class="fw-bold mb-1">Pertimbangan Atasan</h3>
                        <p class="text-muted mb-0" style="font-size: 0.88rem;">
                            Berikan pertimbangan untuk pengajuan cuti
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

                    {{-- Pertimbangan Options --}}
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.85rem;">
                            Pertimbangan <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-column gap-2" id="pertimbanganGroup{{ $req->id }}">
                            <div style="display: flex; align-items: center; background: var(--sh-success-light); border-radius: 10px; padding: 0.65rem 0.85rem; margin: 0; border: 2px solid transparent; cursor: pointer; transition: opacity 0.2s ease, filter 0.2s ease; will-change: opacity, filter; position: relative; gap: 0.75rem; padding-left: 2.5rem;">
                                <input class="form-check-input pertimbangan-input" type="radio" name="pertimbangan" value="setuju" id="pertimbangan_setuju{{ $req->id }}" required style="opacity: 0; width: 0; height: 0; margin: 0; padding: 0; position: absolute; pointer-events: none;">
                                <label for="pertimbangan_setuju{{ $req->id }}" class="form-check-label fw-semibold" style="color: var(--sh-success); margin: 0; cursor: pointer; flex: 1; user-select: none;">
                                    <i class="ti ti-circle-check me-1"></i> Disetujui
                                </label>
                            </div>
                            <div style="display: flex; align-items: center; background: var(--sh-primary-light); border-radius: 10px; padding: 0.65rem 0.85rem; margin: 0; border: 2px solid transparent; cursor: pointer; transition: opacity 0.2s ease, filter 0.2s ease; will-change: opacity, filter; position: relative; gap: 0.75rem; padding-left: 2.5rem;">
                                <input class="form-check-input pertimbangan-input" type="radio" name="pertimbangan" value="ubah" id="pertimbangan_ubah{{ $req->id }}" style="opacity: 0; width: 0; height: 0; margin: 0; padding: 0; position: absolute; pointer-events: none;">
                                <label for="pertimbangan_ubah{{ $req->id }}" class="form-check-label fw-semibold" style="color: var(--sh-primary); margin: 0; cursor: pointer; flex: 1; user-select: none;">
                                    <i class="ti ti-edit me-1"></i> Perubahan
                                </label>
                            </div>
                            <div style="display: flex; align-items: center; background: var(--sh-warning-light); border-radius: 10px; padding: 0.65rem 0.85rem; margin: 0; border: 2px solid transparent; cursor: pointer; transition: opacity 0.2s ease, filter 0.2s ease; will-change: opacity, filter; position: relative; gap: 0.75rem; padding-left: 2.5rem;">
                                <input class="form-check-input pertimbangan-input" type="radio" name="pertimbangan" value="tangguhkan" id="pertimbangan_tangguhkan{{ $req->id }}" style="opacity: 0; width: 0; height: 0; margin: 0; padding: 0; position: absolute; pointer-events: none;">
                                <label for="pertimbangan_tangguhkan{{ $req->id }}" class="form-check-label fw-semibold" style="color: var(--sh-warning); margin: 0; cursor: pointer; flex: 1; user-select: none;">
                                    <i class="ti ti-clock-pause me-1"></i> Ditangguhkan
                                </label>
                            </div>
                            <div style="display: flex; align-items: center; background: var(--sh-danger-light); border-radius: 10px; padding: 0.65rem 0.85rem; margin: 0; border: 2px solid transparent; cursor: pointer; transition: opacity 0.2s ease, filter 0.2s ease; will-change: opacity, filter; position: relative; gap: 0.75rem; padding-left: 2.5rem;">
                                <input class="form-check-input pertimbangan-input" type="radio" name="pertimbangan" value="tolak" id="pertimbangan_tolak{{ $req->id }}" style="opacity: 0; width: 0; height: 0; margin: 0; padding: 0; position: absolute; pointer-events: none;">
                                <label for="pertimbangan_tolak{{ $req->id }}" class="form-check-label fw-semibold" style="color: var(--sh-danger); margin: 0; cursor: pointer; flex: 1; user-select: none;">
                                    <i class="ti ti-circle-x me-1"></i> Tidak Disetujui
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Catatan --}}
                    <div class="mb-0">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Atasan</label>
                        <textarea name="catatan_atasan" class="form-control" rows="2" placeholder="Catatan atau keterangan tambahan..." style="border-radius: 10px; border: 2px solid #e2e8f0;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4" style="justify-content: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; min-width: 100px;" id="cancelBtn{{ $req->id }}">Batal</button>
                    <button type="submit" class="btn sh-btn-primary text-white" style="min-width: 120px;" id="submitBtn{{ $req->id }}">
                        <i class="ti ti-send me-1"></i> <span id="submitText{{ $req->id }}">Kirim Pertimbangan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reviewForm{{ $req->id }}');
    const submitBtn = document.getElementById('submitBtn{{ $req->id }}');
    const modal = document.getElementById('reviewModal{{ $req->id }}');
    const pertimbanganGroup = document.getElementById('pertimbanganGroup{{ $req->id }}');
    let isSubmitting = false;

    // Handle radio button visual feedback with CSS classes
    if (pertimbanganGroup) {
        const radioInputs = pertimbanganGroup.querySelectorAll('.pertimbangan-input');

        radioInputs.forEach(radio => {
            const container = radio.parentElement;

            // Set initial class if checked
            if (radio.checked) {
                container.classList.add('pertimbangan-selected');
            }

            // Change event - only toggle class, let CSS do the animation
            radio.addEventListener('change', function(e) {
                e.stopPropagation();

                // Remove class from all
                radioInputs.forEach(r => {
                    r.parentElement.classList.remove('pertimbangan-selected');
                });

                // Add class to selected
                if (this.checked) {
                    container.classList.add('pertimbangan-selected');
                }
            });

            // Click on container to select radio
            container.addEventListener('click', function(e) {
                if (e.target !== radio && !e.target.closest('label')) {
                    e.preventDefault();
                    e.stopPropagation();
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                    radio.focus();
                }
            });
        });
    }

    if (form && submitBtn && modal) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Prevent double submission
            if (isSubmitting) {
                return false;
            }

            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ti ti-loader me-1" style="animation: spin 1s linear infinite;"></i> Mengirim...';

            const formData = new FormData(form);

            // Send AJAX request
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Close modal
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();

                // Remove the request card from DOM
                const requestCard = document.querySelector('[data-request-id="{{ $req->id }}"]');
                if (requestCard) {
                    requestCard.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => {
                        requestCard.remove();
                    }, 300);
                }

                // Show success message
                const successMsg = document.createElement('div');
                successMsg.className = 'alert alert-success';
                successMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; background: var(--sh-success-light); color: var(--sh-success); border-radius: 12px; border: none; padding: 1rem;';
                successMsg.innerHTML = '<i class="ti ti-circle-check me-2"></i> Pertimbangan berhasil dikirim!';
                document.body.appendChild(successMsg);

                // Remove success message after 3 seconds
                setTimeout(() => {
                    successMsg.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => {
                        successMsg.remove();
                    }, 300);
                }, 3000);

                isSubmitting = false;
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ti ti-send me-1"></i> Kirim Pertimbangan';
                isSubmitting = false;

                // Show error message
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-danger';
                errorMsg.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; background: var(--sh-danger-light); color: var(--sh-danger); border-radius: 12px; border: none; padding: 1rem;';
                errorMsg.innerHTML = '<i class="ti ti-alert-circle me-2"></i> Gagal mengirim pertimbangan. Silahkan coba lagi.';
                document.body.appendChild(errorMsg);

                setTimeout(() => {
                    errorMsg.remove();
                }, 5000);
            });

            return false;
        });

        // Add styles if not exists
        if (!document.getElementById('reviewModalStyles')) {
            const style = document.createElement('style');
            style.id = 'reviewModalStyles';
            style.textContent = `
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
                @keyframes fadeOut {
                    from { opacity: 1; transform: translateY(0); }
                    to { opacity: 0; transform: translateY(-10px); }
                }

                /* Custom radio button styling - Option 1 */
                [id*="pertimbanganGroup"] > div::before {
                    content: '';
                    display: block;
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    border: 2px solid currentColor;
                    flex-shrink: 0;
                    position: absolute;
                    left: 0.65rem;
                    top: 50%;
                    transform: translateY(-50%);
                    transition: all 0.2s ease;
                    background-color: transparent;
                }

                /* Selected state - filled circle */
                [id*="pertimbanganGroup"] > div.pertimbangan-selected::before {
                    background-color: currentColor;
                    box-shadow: inset 0 0 0 3px;
                }

                /* Fade effect for radio options */
                .pertimbangan-selected {
                    opacity: 1 !important;
                    filter: brightness(1.1) !important;
                }

                /* Unselected state - dimmed */
                [id*="pertimbanganGroup"] > div:not(.pertimbangan-selected) {
                    opacity: 0.9;
                    filter: brightness(0.95);
                }

                /* Hover effect - unselected */
                [id*="pertimbanganGroup"] > div:hover:not(.pertimbangan-selected) {
                    opacity: 0.95;
                    filter: brightness(0.98);
                }

                /* Hover effect - selected */
                [id*="pertimbanganGroup"] > div.pertimbangan-selected:hover {
                    opacity: 1;
                    filter: brightness(1.15);
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Reset form when modal is closed
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            if (!isSubmitting && form) {
                form.reset();
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ti ti-send me-1"></i> Kirim Pertimbangan';
            }
        });
    }
});
</script>
