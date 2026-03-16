{{-- Atasan Review Modal - Custom Implementation (No Flicker) --}}

{{-- Modal Overlay - Custom (not Bootstrap) --}}
<div class="sc-modal-overlay" id="reviewModalOverlay{{ $req->id }}" data-modal-id="{{ $req->id }}">
    {{-- Modal Container --}}
    <div class="sc-modal-container" data-modal-container>
        <div class="sc-modal-content">
            <form method="POST" action="{{ route('leave.review', $req) }}" id="reviewForm{{ $req->id }}">
                @csrf
                <div class="sc-modal-body">
                    {{-- Header --}}
                    <div class="text-center mb-3">
                        <div class="sc-modal-icon">
                            <i class="ti ti-checklist"></i>
                        </div>
                        <h3 class="fw-bold mb-1">Pertimbangan Atasan</h3>
                        <p class="text-muted mb-0" style="font-size: 0.88rem;">
                            Berikan pertimbangan untuk pengajuan cuti
                            <strong class="text-dark">{{ $req->user->name }}</strong>
                        </p>
                    </div>

                    {{-- Request Summary --}}
                    <div class="sc-modal-summary">
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
                        <label class="form-label fw-bold" style="font-size: 0.85rem;">
                            Pertimbangan <span class="text-danger">*</span>
                        </label>
                        <div class="sc-pertimbangan-options" id="pertimbanganGroup{{ $req->id }}">
                            {{-- Setuju --}}
                            <label class="sc-option sc-option-success" data-option="setuju">
                                <input type="radio" name="pertimbangan" value="setuju" id="pertimbangan_setuju{{ $req->id }}" required>
                                <span class="sc-option-indicator"></span>
                                <span class="sc-option-content">
                                    <i class="ti ti-circle-check"></i> Disetujui
                                </span>
                            </label>

                            {{-- Ubah --}}
                            <label class="sc-option sc-option-primary" data-option="ubah">
                                <input type="radio" name="pertimbangan" value="ubah" id="pertimbangan_ubah{{ $req->id }}">
                                <span class="sc-option-indicator"></span>
                                <span class="sc-option-content">
                                    <i class="ti ti-edit"></i> Perubahan
                                </span>
                            </label>

                            {{-- Tangguhkan --}}
                            <label class="sc-option sc-option-warning" data-option="tangguhkan">
                                <input type="radio" name="pertimbangan" value="tangguhkan" id="pertimbangan_tangguhkan{{ $req->id }}">
                                <span class="sc-option-indicator"></span>
                                <span class="sc-option-content">
                                    <i class="ti ti-clock-pause"></i> Ditangguhkan
                                </span>
                            </label>

                            {{-- Tolak --}}
                            <label class="sc-option sc-option-danger" data-option="tolak">
                                <input type="radio" name="pertimbangan" value="tolak" id="pertimbangan_tolak{{ $req->id }}">
                                <span class="sc-option-indicator"></span>
                                <span class="sc-option-content">
                                    <i class="ti ti-circle-x"></i> Tidak Disetujui
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Catatan --}}
                    <div class="mb-0">
                        <label class="form-label fw-semibold" style="font-size: 0.85rem;">Catatan Atasan</label>
                        <textarea name="catatan_atasan" class="form-control" rows="2"
                                  placeholder="Catatan atau keterangan tambahan..."
                                  style="border-radius: 10px; border: 2px solid #e2e8f0;"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="sc-modal-footer">
                    <button type="button" class="btn btn-secondary sc-modal-cancel"
                            data-modal-close="{{ $req->id }}" style="border-radius: 10px; min-width: 100px;">
                        Batal
                    </button>
                    <button type="submit" class="btn sc-btn-primary text-white"
                            style="min-width: 120px;" id="submitBtn{{ $req->id }}">
                        <i class="ti ti-send me-1"></i> <span>Kirim Pertimbangan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Custom Modal Styles (Inline for scoping) --}}
<style>
/* === Modal Overlay - Custom System (No Layout Shift) === */
.sc-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, visibility 0.25s ease;
    padding: 1rem;
    /* Prevent scrolling without changing body */
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}

/* Modal Open State */
.sc-modal-overlay.sc-modal-active {
    opacity: 1;
    visibility: visible;
    background: rgba(0, 0, 0, 0.5);
}

/* Modal Container */
.sc-modal-container {
    max-width: 520px;
    width: 100%;
    margin: auto;
    transform: scale(0.95) translateY(20px);
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
}

.sc-modal-active .sc-modal-container {
    transform: scale(1) translateY(0);
}

/* Modal Content */
.sc-modal-content {
    background: var(--sc-card-bg);
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

/* Modal Body */
.sc-modal-body {
    padding: 1.5rem;
}

/* Modal Icon */
.sc-modal-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: var(--sc-primary-light);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
    font-size: 2rem;
    color: var(--sc-primary);
}

/* Modal Summary */
.sc-modal-summary {
    background: var(--sc-gray-50);
    border-radius: 12px;
    padding: 0.85rem 1rem;
    margin-bottom: 1rem;
}

/* Pertimbangan Options */
.sc-pertimbangan-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

/* Option Item */
.sc-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    user-select: none;
}

/* Hide native radio */
.sc-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}

/* Custom Radio Indicator */
.sc-option-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid currentColor;
    flex-shrink: 0;
    position: relative;
    transition: all 0.2s ease;
}

/* Radio Indicator - Checked State */
.sc-option input[type="radio"]:checked + .sc-option-indicator {
    background: currentColor;
    box-shadow: inset 0 0 0 4px var(--sc-card-bg);
}

/* Option Content */
.sc-option-content {
    font-weight: 600;
    flex: 1;
}

/* Option Variants */
.sc-option-success {
    background: var(--sc-success-light);
    color: var(--sc-success);
}

.sc-option-primary {
    background: var(--sc-primary-light);
    color: var(--sc-primary);
}

.sc-option-warning {
    background: var(--sc-warning-light);
    color: var(--sc-warning);
}

.sc-option-danger {
    background: var(--sc-danger-light);
    color: var(--sc-danger);
}

/* Hover States */
.sc-option:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
}

.sc-option input[type="radio"]:checked ~ .sc-option-content {
    font-weight: 700;
}

/* Selected State */
.sc-option:has(input[type="radio"]:checked) {
    border-color: currentColor;
    filter: brightness(1.1);
}

/* Modal Footer */
.sc-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 2px solid var(--sc-gray-100);
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

/* Scroll Lock Body (applied via JS) */
body.sc-scroll-locked {
    /* Important: Don't change overflow or padding - use fixed wrapper instead */
    position: relative;
}

/* Scroll Lock Wrapper - prevents scrolling without layout shift */
.sc-scroll-lock-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9998;
    overflow: hidden;
    pointer-events: none;
}

.sc-scroll-lock-wrapper.sc-active {
    pointer-events: auto;
}

/* Mobile Responsive */
@media (max-width: 576px) {
    .sc-modal-overlay {
        padding: 0.5rem;
    }

    .sc-modal-body {
        padding: 1rem;
    }

    .sc-modal-footer {
        padding: 0.75rem 1rem;
        flex-direction: column;
    }

    .sc-modal-footer button {
        width: 100%;
    }
}

/* Animation Keyframes */
@keyframes sc-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes sc-fadeOut {
    from { opacity: 1; transform: translateY(0); }
    to { opacity: 0; transform: translateY(-10px); }
}
</style>

{{-- Custom Modal JavaScript --}}
<script>
(function() {
    const modalId = '{{ $req->id }}';
    const overlay = document.getElementById('reviewModalOverlay' + modalId);
    const form = document.getElementById('reviewForm' + modalId);
    const submitBtn = document.getElementById('submitBtn' + modalId);
    let isSubmitting = false;
    let scrollLockWrapper = null;

    if (!overlay || !form || !submitBtn) return;

    // Open Modal Function - No Layout Shift
    window['openReviewModal' + modalId] = function() {
        // Create scroll lock wrapper
        scrollLockWrapper = document.createElement('div');
        scrollLockWrapper.className = 'sc-scroll-lock-wrapper';
        document.body.appendChild(scrollLockWrapper);

        // Force reflow
        void overlay.offsetHeight;

        // Activate (no body manipulation = no layout shift)
        requestAnimationFrame(() => {
            overlay.classList.add('sc-modal-active');
            scrollLockWrapper.classList.add('sc-active');
            document.body.classList.add('sc-scroll-locked');
        });
    };

    // Close Modal Function
    window['closeReviewModal' + modalId] = function() {
        overlay.classList.remove('sc-modal-active');
        document.body.classList.remove('sc-scroll-locked');

        setTimeout(() => {
            if (scrollLockWrapper && scrollLockWrapper.parentNode) {
                scrollLockWrapper.parentNode.removeChild(scrollLockWrapper);
                scrollLockWrapper = null;
            }
        }, 250);
    };

    // Close button handler
    const closeBtn = overlay.querySelector('[data-modal-close="' + modalId + '"]');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window['closeReviewModal' + modalId]();
        });
    }

    // Close on overlay click (outside modal)
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            window['closeReviewModal' + modalId]();
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('sc-modal-active')) {
            window['closeReviewModal' + modalId]();
        }
    });

    // Form submission with AJAX
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (isSubmitting) return false;

        isSubmitting = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ti ti-loader me-1" style="animation: sc-spin 1s linear infinite;"></i> Mengirim...';

        const formData = new FormData(form);

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
            window['closeReviewModal' + modalId]();

            // Remove request card from DOM
            const requestCard = document.querySelector('[data-request-id="{{ $req->id }}"]');
            if (requestCard) {
                requestCard.style.animation = 'sc-fadeOut 0.3s ease-out';
                setTimeout(() => requestCard.remove(), 300);
            }

            // Show success message
            showToast('success', '<i class="ti ti-circle-check me-2"></i> Pertimbangan berhasil dikirim!');

            isSubmitting = false;
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ti ti-send me-1"></i> <span>Kirim Pertimbangan</span>';
            isSubmitting = false;

            showToast('error', '<i class="ti ti-alert-circle me-2"></i> Gagal mengirim pertimbangan. Silahkan coba lagi.');
        });

        return false;
    });

    // Reset form when modal closes
    overlay.addEventListener('transitionend', function(e) {
        if (e.target === overlay && !overlay.classList.contains('sc-modal-active')) {
            if (!isSubmitting) {
                form.reset();
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ti ti-send me-1"></i> <span>Kirim Pertimbangan</span>';
            }
        }
    });

    // Toast notification helper
    function showToast(type, message) {
        const toast = document.createElement('div');
        toast.className = 'alert alert-' + type;
        toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 99999; min-width: 300px; border-radius: 12px; border: none; padding: 1rem; box-shadow: 0 10px 40px rgba(0,0,0,0.15);';

        if (type === 'success') {
            toast.style.background = 'var(--sc-success-light)';
            toast.style.color = 'var(--sc-success)';
        } else {
            toast.style.background = 'var(--sc-danger-light)';
            toast.style.color = 'var(--sc-danger)';
        }

        toast.innerHTML = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'sc-fadeOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, type === 'success' ? 3000 : 5000);
    }
})();
</script>
