# SiHEALING — 50 UI/UX Improvements Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Implement ~35 new UI/UX improvements and bug fixes (15 already exist: dark mode, skeleton, toast, bottom nav, NProgress, empty states, etc.)

**Architecture:** Pure frontend-first (CSS + vanilla JS in Blade templates). Backend changes only where necessary (bug fixes, profile features). No new npm packages unless unavoidable. All changes in `resources/views/` and `public/`.

**Tech Stack:** Laravel Blade, Tailwind CSS v4, Tabler CSS/Icons (CDN), Vanilla JS, Axios

---

## PHASE 1 — Quick Wins (Independent, ~2 hrs)

---

### Task 1: Compress gedung.png → WebP

**Files:**
- Modify: `public/gedung.png` → convert to `public/gedung.webp`
- Modify: `resources/views/auth/login.blade.php`

**Step 1: Convert to WebP using PHP**
```bash
cd /c/Users/faris/sihealing
php -r "
  \$img = imagecreatefrompng('public/gedung.png');
  imagewebp(\$img, 'public/gedung.webp', 80);
  imagedestroy(\$img);
  echo 'Done: ' . round(filesize('public/gedung.webp')/1024) . 'KB';
"
```
Expected: file `public/gedung.webp` ~200-300KB (was 892KB)

**Step 2: Update login.blade.php to use WebP with PNG fallback**

Find in `resources/views/auth/login.blade.php`:
```css
background-image: url('/gedung.png');
```
Replace with:
```css
background-image: url('/gedung.webp');
```

Also add `<picture>` fallback in HTML if needed (for old browsers — but since this is CSS background-image, WebP is fine for modern browsers).

**Step 3: Commit**
```bash
cd /c/Users/faris/sihealing
rtk git add public/gedung.webp resources/views/auth/login.blade.php
rtk git commit -m "perf(login): compress background image PNG→WebP (892KB→~200KB)"
```

---

### Task 2: Fix font size 16px on all inputs (prevent iOS zoom)

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

iOS Safari auto-zooms when input font-size < 16px. Find the CSS section (around line 400-600) and add:

```css
/* Prevent iOS auto-zoom on input focus */
@media (max-width: 768px) {
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="number"],
    input[type="date"],
    input[type="search"],
    select,
    textarea {
        font-size: 16px !important;
    }
}
```

Add this in the `<style>` block inside the layout, after existing mobile media queries.

**Step: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "fix(mobile): set 16px min font-size on inputs to prevent iOS zoom"
```

---

### Task 3: Touch target minimum 44px for all nav/action elements

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

Add in the `<style>` block:
```css
/* Touch target minimum 44px (WCAG 2.5.5) */
@media (max-width: 768px) {
    .sh-bottom-nav-item,
    .btn-sm,
    .navbar-toggler,
    .sh-pw-toggle,
    .page-link,
    .dropdown-item {
        min-height: 44px;
        min-width: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-sm {
        padding: 0.5rem 0.85rem;
    }
}
```

**Step: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "fix(mobile): ensure 44px minimum touch targets for accessibility"
```

---

### Task 4: FAB — Floating Action Button "Ajukan Cuti"

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

**Step 1: Add FAB CSS** in the `<style>` block:
```css
/* FAB — Floating Action Button */
.sh-fab {
    position: fixed;
    bottom: 80px; /* above bottom nav */
    right: 1.25rem;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #14532d, #166534);
    color: #fff;
    border: none;
    box-shadow: 0 4px 16px rgba(20,83,45,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    z-index: 1040;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    text-decoration: none;
}
.sh-fab:hover, .sh-fab:focus {
    transform: scale(1.08);
    box-shadow: 0 6px 24px rgba(20,83,45,0.55);
    color: #fff;
}
.sh-fab:active {
    transform: scale(0.96);
}
/* Only show FAB on mobile */
@media (min-width: 992px) {
    .sh-fab { display: none; }
}
```

**Step 2: Add FAB HTML** just before `</body>`:
```html
{{-- FAB: Ajukan Cuti (mobile only, non-admin) --}}
@auth
    @if(auth()->user()->bolehCuti() && !auth()->user()->isAdmin())
    <a href="{{ route('leave.select-type') }}"
       class="sh-fab"
       aria-label="Ajukan Cuti"
       title="Ajukan Cuti">
        <i class="ti ti-file-plus"></i>
    </a>
    @endif
@endauth
```

**Step 3: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "feat(mobile): add FAB floating action button untuk ajukan cuti"
```

---

### Task 5: Progress bar visual untuk saldo cuti

**Files:**
- Modify: `resources/views/components/leave-balance-card.blade.php`

Read the file first, then add a visual progress bar after the numeric saldo display:

```html
{{-- Progress bar saldo --}}
@php
    $pct = $hakTotal > 0 ? min(100, round(($terpakai / $hakTotal) * 100)) : 0;
    $barColor = $pct >= 80 ? '#dc2626' : ($pct >= 50 ? '#d97706' : '#166534');
@endphp
<div class="mt-2">
    <div style="background: #e2e8f0; border-radius: 99px; height: 8px; overflow: hidden;">
        <div style="width: {{ $pct }}%; background: {{ $barColor }}; height: 100%; border-radius: 99px; transition: width 0.6s ease;"></div>
    </div>
    <div class="d-flex justify-content-between mt-1" style="font-size: 0.75rem; color: #64748b;">
        <span>Terpakai: {{ $terpakai }} hari</span>
        <span>{{ $pct }}%</span>
    </div>
</div>
```

**Step: Commit**
```bash
rtk git add resources/views/components/leave-balance-card.blade.php
rtk git commit -m "feat(ui): tambah progress bar visual pada saldo cuti"
```

---

### Task 6: Avatar inisial pegawai

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

**Step 1: Add CSS** for initials avatar:
```css
/* Avatar inisial */
.sh-avatar-initials {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8rem;
    color: #fff;
    flex-shrink: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
```

**Step 2: Add Blade helper in layout** — find where user avatar is rendered and wrap with:
```html
@php
    $initials = collect(explode(' ', $user->name ?? ''))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
    $avatarColors = ['#166534','#1d4ed8','#7c3aed','#b45309','#0f766e','#9f1239'];
    $avatarBg = $avatarColors[crc32($user->name ?? '') % count($avatarColors)];
@endphp
```

Then replace any `<img src="{{ $user->avatar }}"` fallback with:
```html
@if($user->avatar && file_exists(public_path($user->avatar)))
    <img src="{{ asset($user->avatar) }}" class="rounded-circle" width="36" height="36" alt="{{ $user->name }}">
@else
    <span class="sh-avatar-initials" style="background: {{ $avatarBg }};">{{ $initials }}</span>
@endif
```

**Step: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "feat(ui): tambah avatar inisial warna untuk pegawai tanpa foto"
```

---

### Task 7: Status badge warna konsisten

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

Ensure these CSS classes exist and are consistent throughout the app:
```css
/* Status badges — konsisten di semua halaman */
.sh-badge-pending   { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
.sh-badge-approved  { background: #dcfce7; color: #14532d; border: 1px solid #86efac; }
.sh-badge-rejected  { background: #fee2e2; color: #7f1d1d; border: 1px solid #fca5a5; }
.sh-badge-cancelled { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
.sh-badge-revised   { background: #fff7ed; color: #7c2d12; border: 1px solid #fdba74; }
```

Check if these already exist; only add what's missing.

**Step: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "fix(ui): standardize status badge colors across all pages"
```

---

### Task 8: Custom error pages 404 & 403

**Files:**
- Create: `resources/views/errors/404.blade.php`
- Create: `resources/views/errors/403.blade.php`

**404.blade.php:**
```blade
@extends('layouts.app')
@section('title', '404 — Halaman Tidak Ditemukan')
@section('content')
<div class="text-center py-5">
    <div style="font-size: 5rem; color: var(--sh-primary); opacity: 0.3;">404</div>
    <h2 class="fw-bold mb-2" style="color: var(--sh-text);">Halaman Tidak Ditemukan</h2>
    <p class="text-muted mb-4">Halaman yang Anda cari tidak ada atau sudah dipindahkan.</p>
    <a href="{{ route('dashboard') }}" class="btn sh-btn-primary">
        <i class="ti ti-home me-1"></i> Kembali ke Dashboard
    </a>
</div>
@endsection
```

**403.blade.php:**
```blade
@extends('layouts.app')
@section('title', '403 — Akses Ditolak')
@section('content')
<div class="text-center py-5">
    <div style="font-size: 5rem; color: #dc2626; opacity: 0.3;">403</div>
    <h2 class="fw-bold mb-2" style="color: var(--sh-text);">Akses Ditolak</h2>
    <p class="text-muted mb-4">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
    <a href="{{ route('dashboard') }}" class="btn sh-btn-primary">
        <i class="ti ti-home me-1"></i> Kembali ke Dashboard
    </a>
</div>
@endsection
```

**Step: Commit**
```bash
rtk git add resources/views/errors/
rtk git commit -m "feat(ux): tambah custom error pages 404 dan 403"
```

---

### Task 9: Print-friendly CSS untuk halaman laporan

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

Add at the end of the `<style>` block:
```css
/* ===== PRINT STYLES ===== */
@media print {
    .navbar, .sh-bottom-nav, .sh-fab, .sh-sidebar,
    .btn:not(.btn-print), .sh-breadcrumb,
    .sh-page-header .btn, aside, .dropdown-menu,
    .sh-toast-container, #nprogress { display: none !important; }

    body { background: white !important; color: black !important; }
    .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    .sh-stat-card, .card { break-inside: avoid; }
    .container-xl { max-width: 100% !important; padding: 0 !important; }
    a { color: inherit !important; text-decoration: none !important; }
    table { border-collapse: collapse !important; }
    th, td { border: 1px solid #dee2e6 !important; padding: 0.4rem !important; }
}
```

**Step: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "feat(print): tambah print-friendly CSS untuk halaman laporan"
```

---

## PHASE 2 — Form UX (Sequential, ~3 hrs)

---

### Task 10: Auto-hitung durasi cuti dari tanggal

**Files:**
- Modify: `resources/views/leave/create.blade.php`

**Step 1: Read `leave/create.blade.php`** to find the date input fields (cari `start_date`, `end_date`)

**Step 2: Add duration calculator JS** via `@push('scripts')`:
```javascript
(function() {
    const startInput = document.getElementById('start_date');
    const endInput   = document.getElementById('end_date');
    const durInfo    = document.getElementById('sh-duration-info');
    if (!startInput || !endInput || !durInfo) return;

    function countWorkdays(start, end) {
        let count = 0;
        let cur = new Date(start);
        const fin = new Date(end);
        while (cur <= fin) {
            const day = cur.getDay();
            if (day !== 0 && day !== 6) count++;
            cur.setDate(cur.getDate() + 1);
        }
        return count;
    }

    function update() {
        const s = startInput.value, e = endInput.value;
        if (!s || !e || s > e) { durInfo.textContent = ''; return; }
        const days = countWorkdays(s, e);
        durInfo.innerHTML = `<i class="ti ti-calendar-check me-1" style="color:var(--sh-primary)"></i>
            <strong>${days} hari kerja</strong> (Sabtu & Minggu tidak dihitung)`;
    }

    startInput.addEventListener('change', update);
    endInput.addEventListener('change', update);
})();
```

**Step 3: Add duration display div** after the end_date input:
```html
<div id="sh-duration-info" class="mt-2 text-sm" style="font-size:0.85rem; color:#475569; min-height:1.5rem;"></div>
```

**Step 4: Commit**
```bash
rtk git add resources/views/leave/create.blade.php
rtk git commit -m "feat(form): auto-hitung durasi hari kerja dari tanggal cuti"
```

---

### Task 11: Validasi saldo real-time sebelum submit

**Files:**
- Modify: `resources/views/leave/create.blade.php`

Read the file to find where `leave_balance` / saldo is available in Blade. Add JS that checks:
- If jenis cuti = cuti_tahunan AND durasi > saldo → tampilkan warning merah
- Warning hilang otomatis jika durasi ≤ saldo

Add after Task 10's JS:
```javascript
(function() {
    const saldo = parseInt('{{ $user->leave_balance ?? 0 }}');
    const warn  = document.getElementById('sh-saldo-warning');
    const durEl = document.getElementById('sh-duration-info');
    if (!warn) return;

    const observer = new MutationObserver(() => {
        const match = durEl?.textContent.match(/(\d+)\s*hari kerja/);
        const days = match ? parseInt(match[1]) : 0;
        const jenisCuti = document.querySelector('[name="type"]')?.value || 'cuti_tahunan';
        if (jenisCuti === 'cuti_tahunan' && days > saldo) {
            warn.innerHTML = `<i class="ti ti-alert-triangle me-1"></i>
                Durasi <strong>${days} hari</strong> melebihi saldo Anda (<strong>${saldo} hari</strong>)`;
            warn.style.display = 'block';
        } else {
            warn.style.display = 'none';
        }
    });
    if (durEl) observer.observe(durEl, { childList: true, subtree: true, characterData: true });
})();
```

Add HTML:
```html
<div id="sh-saldo-warning" class="sh-alert-error mt-2" style="display:none; font-size:0.85rem;"></div>
```

**Step: Commit**
```bash
rtk git add resources/views/leave/create.blade.php
rtk git commit -m "feat(form): validasi saldo cuti real-time sebelum submit"
```

---

### Task 12: Draft otomatis form cuti (localStorage)

**Files:**
- Modify: `resources/views/leave/create.blade.php`

Add JS to auto-save and restore form:
```javascript
(function() {
    const DRAFT_KEY = 'sihealing_cuti_draft';
    const form = document.getElementById('leave-form') || document.querySelector('form[action*="leave"]');
    if (!form) return;

    const fields = ['start_date','end_date','reason','address_during_leave','phone_during_leave'];

    // Restore draft
    const saved = JSON.parse(localStorage.getItem(DRAFT_KEY) || '{}');
    fields.forEach(name => {
        const el = form.querySelector(`[name="${name}"]`);
        if (el && saved[name]) el.value = saved[name];
    });

    // Auto-save on change
    form.addEventListener('input', () => {
        const data = {};
        fields.forEach(name => {
            const el = form.querySelector(`[name="${name}"]`);
            if (el) data[name] = el.value;
        });
        localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
    });

    // Clear draft on submit
    form.addEventListener('submit', () => localStorage.removeItem(DRAFT_KEY));
})();
```

Show draft restore notification if draft exists:
```javascript
const hasDraft = Object.keys(JSON.parse(localStorage.getItem('sihealing_cuti_draft') || '{}')).some(k => k);
if (hasDraft && typeof showToast === 'function') {
    showToast('Draft cuti sebelumnya telah dipulihkan.', 'info');
}
```

**Step: Commit**
```bash
rtk git add resources/views/leave/create.blade.php
rtk git commit -m "feat(form): auto-save draft form cuti ke localStorage"
```

---

### Task 13: Konfirmasi modal sebelum submit form cuti

**Files:**
- Modify: `resources/views/leave/create.blade.php`

**Step 1:** Change form's submit button to trigger modal instead:
```html
<button type="button" class="btn sh-btn-primary w-100" id="sh-confirm-btn">
    <i class="ti ti-send me-1"></i> Ajukan Cuti
</button>
```

**Step 2:** Add confirmation modal:
```html
<div class="modal fade" id="sh-confirm-modal" tabindex="-1" aria-labelledby="sh-confirm-modal-label">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="sh-confirm-modal-label">
                    <i class="ti ti-file-check me-2" style="color:var(--sh-primary)"></i>Konfirmasi Pengajuan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="sh-confirm-body">
                {{-- filled by JS --}}
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn sh-btn-primary" id="sh-confirm-submit">
                    <i class="ti ti-check me-1"></i> Ya, Ajukan
                </button>
            </div>
        </div>
    </div>
</div>
```

**Step 3:** Add JS to populate modal and submit:
```javascript
document.getElementById('sh-confirm-btn')?.addEventListener('click', function() {
    const form = this.closest('form') || document.querySelector('form[action*="leave"]');
    const start = form.querySelector('[name="start_date"]')?.value || '-';
    const end   = form.querySelector('[name="end_date"]')?.value || '-';
    const dur   = document.getElementById('sh-duration-info')?.textContent || '-';
    const body  = document.getElementById('sh-confirm-body');
    if (body) body.innerHTML = `
        <div class="list-group list-group-flush">
            <div class="list-group-item px-0"><span class="text-muted">Tanggal Mulai</span><strong class="float-end">${start}</strong></div>
            <div class="list-group-item px-0"><span class="text-muted">Tanggal Selesai</span><strong class="float-end">${end}</strong></div>
            <div class="list-group-item px-0"><span class="text-muted">Durasi</span><strong class="float-end" style="color:var(--sh-primary)">${dur}</strong></div>
        </div>`;
    const modal = new bootstrap.Modal(document.getElementById('sh-confirm-modal'));
    modal.show();
});
document.getElementById('sh-confirm-submit')?.addEventListener('click', function() {
    const form = document.querySelector('form[action*="leave"]');
    if (form) form.submit();
});
```

**Step: Commit**
```bash
rtk git add resources/views/leave/create.blade.php
rtk git commit -m "feat(form): tambah modal konfirmasi sebelum submit pengajuan cuti"
```

---

### Task 14: Character counter untuk textarea alasan

**Files:**
- Modify: `resources/views/leave/create.blade.php`

Find the `reason` textarea. Add `maxlength="500"` and counter below:
```html
<textarea name="reason" id="reason_input" maxlength="500" ...></textarea>
<div class="d-flex justify-content-between mt-1" style="font-size: 0.78rem; color: #94a3b8;">
    <span>Jelaskan alasan pengajuan cuti Anda</span>
    <span><span id="reason-count">0</span>/500</span>
</div>
```

Add JS:
```javascript
const reasonEl = document.getElementById('reason_input');
const countEl  = document.getElementById('reason-count');
if (reasonEl && countEl) {
    const update = () => {
        countEl.textContent = reasonEl.value.length;
        countEl.style.color = reasonEl.value.length > 450 ? '#dc2626' : '#94a3b8';
    };
    reasonEl.addEventListener('input', update);
    update();
}
```

**Step: Commit**
```bash
rtk git add resources/views/leave/create.blade.php
rtk git commit -m "feat(form): tambah character counter pada textarea alasan cuti"
```

---

### Task 15: Template alasan cuti (quick-fill dropdown)

**Files:**
- Modify: `resources/views/leave/create.blade.php`

Add above the reason textarea:
```html
<div class="mb-2">
    <label class="form-label" style="font-size:0.8rem; color:#64748b;">Pilih template (opsional)</label>
    <select id="reason-template" class="form-select form-select-sm" style="border-radius:8px;">
        <option value="">-- Pilih template alasan --</option>
        <option value="Keperluan keluarga yang mendesak dan tidak dapat ditunda.">Keperluan keluarga mendesak</option>
        <option value="Melaksanakan ibadah haji/umrah sesuai jadwal yang telah ditetapkan.">Ibadah haji/umrah</option>
        <option value="Istirahat dan pemulihan kondisi kesehatan.">Istirahat/pemulihan kesehatan</option>
        <option value="Menghadiri acara pernikahan anggota keluarga inti.">Pernikahan keluarga</option>
        <option value="Keperluan pribadi yang tidak dapat ditinggalkan.">Keperluan pribadi</option>
    </select>
</div>
```

Add JS:
```javascript
document.getElementById('reason-template')?.addEventListener('change', function() {
    if (this.value) {
        const ta = document.getElementById('reason_input');
        if (ta) { ta.value = this.value; ta.dispatchEvent(new Event('input')); }
        this.value = '';
    }
});
```

**Step: Commit**
```bash
rtk git add resources/views/leave/create.blade.php
rtk git commit -m "feat(form): tambah template alasan cuti untuk isi cepat"
```

---

### Task 16: Double submit prevention (audit semua form)

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

Add global JS (in the layout's script section) to prevent double submit on ALL forms:
```javascript
// Global double-submit prevention
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.dataset.submitting) { e.preventDefault(); return; }
    form.dataset.submitting = '1';
    const btn = form.querySelector('[type="submit"]');
    if (btn) {
        btn.disabled = true;
        const orig = btn.innerHTML;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...`;
        // Re-enable after 10s as safety net
        setTimeout(() => { btn.disabled = false; btn.innerHTML = orig; delete form.dataset.submitting; }, 10000);
    }
}, true);
```

**Step: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "fix(form): global double-submit prevention untuk semua form"
```

---

### Task 17: Session timeout warning (15 menit)

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

Add modal HTML before `</body>`:
```html
@auth
<div class="modal fade" id="sh-session-modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-body text-center p-4">
                <i class="ti ti-clock-exclamation mb-3" style="font-size:3rem; color:#d97706;"></i>
                <h5 class="fw-bold mb-2">Sesi Hampir Habis</h5>
                <p class="text-muted mb-3" style="font-size:0.9rem;">Sesi Anda akan berakhir dalam <strong id="sh-countdown">5:00</strong>. Perpanjang sesi?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button onclick="window.location.reload()" class="btn sh-btn-primary btn-sm">
                        <i class="ti ti-refresh me-1"></i> Perpanjang
                    </button>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-outline-secondary btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endauth
```

Add JS:
```javascript
@auth
(function() {
    const WARNING_MS = 15 * 60 * 1000; // 15 menit
    const COUNTDOWN_MS = 5 * 60 * 1000; // countdown 5 menit terakhir
    let countdownInterval;

    function startCountdown() {
        const modal = new bootstrap.Modal(document.getElementById('sh-session-modal'), {backdrop:'static'});
        modal.show();
        let secs = COUNTDOWN_MS / 1000;
        countdownInterval = setInterval(() => {
            secs--;
            const m = Math.floor(secs/60).toString().padStart(2,'0');
            const s = (secs%60).toString().padStart(2,'0');
            const el = document.getElementById('sh-countdown');
            if (el) el.textContent = `${m}:${s}`;
            if (secs <= 0) { clearInterval(countdownInterval); window.location.href = '{{ route("login") }}'; }
        }, 1000);
    }

    // Reset timer on any user interaction
    let timer = setTimeout(startCountdown, WARNING_MS);
    ['click','keydown','mousemove','touchstart'].forEach(ev => {
        document.addEventListener(ev, () => { clearTimeout(timer); clearInterval(countdownInterval); timer = setTimeout(startCountdown, WARNING_MS); }, { passive: true });
    });
})();
@endauth
```

**Step: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "feat(auth): tambah session timeout warning 15 menit"
```

---

## PHASE 3 — Mobile UX (Sequential, ~2 hrs)

---

### Task 18: Responsive tabel → card list di mobile

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

Add CSS:
```css
/* Responsive table to card on mobile */
@media (max-width: 767px) {
    .sh-table-responsive-cards thead { display: none; }
    .sh-table-responsive-cards tbody tr {
        display: block;
        margin-bottom: 1rem;
        border-radius: 12px;
        border: 1px solid var(--sh-border);
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        padding: 0.75rem;
        background: var(--sh-card-bg);
    }
    .sh-table-responsive-cards td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.35rem 0;
        border: none;
        border-bottom: 1px solid var(--sh-border-light);
        font-size: 0.88rem;
    }
    .sh-table-responsive-cards td:last-child { border-bottom: none; }
    .sh-table-responsive-cards td::before {
        content: attr(data-label);
        font-weight: 600;
        color: var(--sh-muted);
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-right: 0.5rem;
        flex-shrink: 0;
    }
}
```

Then add `class="sh-table-responsive-cards"` and `data-label="..."` attributes to the main leave list table in `resources/views/leave/index.blade.php` (or the primary listing page).

**Step: Read and find the main leave index page, add class and data-labels**

**Step: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php resources/views/leave/
rtk git commit -m "feat(mobile): responsive table-to-card on mobile screens"
```

---

### Task 19: Sticky saldo cuti bar di halaman leave

**Files:**
- Modify: `resources/views/leave/create.blade.php`
- Modify: `resources/views/leave/select-type.blade.php`

Add sticky bar CSS in `@push('styles')` or inline:
```css
.sh-sticky-balance {
    position: sticky;
    top: 60px;
    z-index: 100;
    background: linear-gradient(135deg, #14532d, #166534);
    color: white;
    padding: 0.6rem 1rem;
    border-radius: 0 0 12px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.85rem;
    box-shadow: 0 4px 12px rgba(20,83,45,0.2);
    margin-bottom: 1rem;
}
@media (min-width: 992px) { .sh-sticky-balance { display: none; } }
```

Add HTML at top of `@section('content')`:
```html
@if(!$user->isAdmin())
<div class="sh-sticky-balance">
    <span><i class="ti ti-calendar-stats me-1"></i> Saldo Cuti Tahunan</span>
    <strong>{{ $user->leave_balance ?? 0 }} hari tersisa</strong>
</div>
@endif
```

**Step: Commit**
```bash
rtk git add resources/views/leave/
rtk git commit -m "feat(mobile): sticky saldo cuti bar di halaman pengajuan"
```

---

### Task 20: Pull-to-refresh untuk daftar cuti & notifikasi

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

Add lightweight pull-to-refresh (vanilla JS):
```javascript
// Pull-to-refresh (mobile only)
(function() {
    if (window.innerWidth > 768) return;
    let startY = 0, pulling = false;
    const indicator = document.createElement('div');
    indicator.id = 'sh-ptr';
    indicator.style.cssText = 'position:fixed;top:0;left:0;right:0;height:4px;background:var(--sh-primary);transform:scaleX(0);transform-origin:left;transition:transform 0.2s;z-index:9999;';
    document.body.appendChild(indicator);

    document.addEventListener('touchstart', e => {
        if (window.scrollY === 0) { startY = e.touches[0].clientY; pulling = true; }
    }, { passive: true });

    document.addEventListener('touchmove', e => {
        if (!pulling) return;
        const dist = Math.min((e.touches[0].clientY - startY) / 80, 1);
        if (dist > 0) indicator.style.transform = `scaleX(${dist})`;
    }, { passive: true });

    document.addEventListener('touchend', e => {
        if (!pulling) return;
        pulling = false;
        const dist = (e.changedTouches[0].clientY - startY) / 80;
        if (dist >= 1) { indicator.style.transform = 'scaleX(1)'; setTimeout(() => window.location.reload(), 200); }
        else { indicator.style.transform = 'scaleX(0)'; }
    });
})();
```

**Step: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "feat(mobile): pull-to-refresh gesture untuk reload halaman"
```

---

## PHASE 4 — Dashboard Enhancements (~2 hrs)

---

### Task 21: Widget "Siapa yang cuti hari ini"

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php` (or equivalent)
- Modify: `resources/views/dashboard.blade.php`

**Step 1: Add query in controller**
```php
// Pegawai yang sedang cuti hari ini
$cutiHariIni = \App\Models\LeaveRequest::with('user')
    ->where('status', 'approved')
    ->whereDate('start_date', '<=', today())
    ->whereDate('end_date', '>=', today())
    ->get();
```

Pass `$cutiHariIni` to view.

**Step 2: Add widget in dashboard.blade.php** (after stats row, for non-admin):
```html
@if($cutiHariIni->count() > 0)
<div class="card sh-card mb-4 animate-in">
    <div class="card-body p-3">
        <h6 class="fw-bold mb-3" style="color:var(--sh-text);">
            <i class="ti ti-user-off me-1" style="color:var(--sh-accent)"></i>
            Sedang Cuti Hari Ini ({{ $cutiHariIni->count() }})
        </h6>
        <div class="d-flex flex-wrap gap-2">
            @foreach($cutiHariIni as $c)
            <div class="d-flex align-items-center gap-2 px-3 py-2" style="background:var(--sh-primary-light);border-radius:99px;">
                <span class="sh-avatar-initials" style="width:28px;height:28px;font-size:0.7rem;background:var(--sh-primary);">
                    {{ collect(explode(' ',$c->user->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('') }}
                </span>
                <span style="font-size:0.85rem;font-weight:600;color:var(--sh-primary);">{{ $c->user->name }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
```

**Step: Commit**
```bash
rtk git add app/Http/Controllers/ resources/views/dashboard.blade.php
rtk git commit -m "feat(dashboard): tambah widget siapa yang cuti hari ini"
```

---

### Task 22: Animasi page transition

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

**Step 1:** Add CSS:
```css
/* Page transition */
.sh-page-transition {
    animation: sh-fade-in 0.25s ease;
}
@keyframes sh-fade-in {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
```

**Step 2:** Wrap main content area with the class, or add via JS:
```javascript
document.addEventListener('DOMContentLoaded', () => {
    const main = document.querySelector('main, .sh-main-content, [data-page-content]');
    if (main) main.classList.add('sh-page-transition');
});
```

**Step: Commit**
```bash
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "feat(ui): tambah animasi fade-in pada transisi halaman"
```

---

## PHASE 5 — Profile & Auth Features (~2 hrs)

---

### Task 23: Ganti password di halaman profil

**Files:**
- Modify: `resources/views/profile/` (find the profile edit view)
- Modify: `app/Http/Controllers/ProfileController.php`

**Step 1: Read the profile view** to understand current structure.

**Step 2: Add password change form** in the profile page:
```html
<div class="card sh-card mt-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3"><i class="ti ti-lock me-2" style="color:var(--sh-primary)"></i>Ubah Password</h5>
        <form action="{{ route('profile.password') }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Password Lama</label>
                <input type="password" name="current_password" class="form-control sh-input" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="new_password" class="form-control sh-input" minlength="8" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="new_password_confirmation" class="form-control sh-input" required>
            </div>
            <button type="submit" class="btn sh-btn-primary">
                <i class="ti ti-check me-1"></i> Ubah Password
            </button>
        </form>
    </div>
</div>
```

**Step 3: Add route** in `routes/web.php`:
```php
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
```

**Step 4: Add method** in ProfileController:
```php
public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => ['required', 'current_password'],
        'new_password' => ['required', 'min:8', 'confirmed'],
    ]);
    auth()->user()->update(['password' => bcrypt($request->new_password)]);
    return back()->with('success', 'Password berhasil diubah.');
}
```

**Step: Commit**
```bash
rtk git add app/Http/Controllers/ProfileController.php routes/web.php resources/views/profile/
rtk git commit -m "feat(profile): tambah form ubah password di halaman profil"
```

---

### Task 24: Log aktivitas pengguna (login terakhir)

**Files:**
- Modify: `app/Http/Controllers/Auth/AuthenticatedSessionController.php` (or Login controller)
- Modify: `resources/views/profile/` (show last login)

**Step 1: Save last_login_at on login** in the login controller's store method:
```php
auth()->user()->update(['last_login_at' => now()]);
```

**Step 2: Add migration** if `last_login_at` column doesn't exist:
```bash
php artisan make:migration add_last_login_at_to_users_table --table=users
```

Migration content:
```php
$table->timestamp('last_login_at')->nullable();
```

**Step 3: Show in profile view:**
```html
@if($user->last_login_at)
<div class="text-muted mt-2" style="font-size:0.82rem;">
    <i class="ti ti-clock me-1"></i>
    Login terakhir: {{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}
</div>
@endif
```

**Step: Commit**
```bash
rtk git add app/ database/ resources/views/profile/
rtk git commit -m "feat(profile): catat dan tampilkan waktu login terakhir"
```

---

## PHASE 6 — Bug Fixes (~1.5 hrs)

---

### Task 25: Fix back button setelah submit (PRG pattern)

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php`

Ensure all successful form submissions return `redirect()` not `view()`. Check the `store()` method:
```php
// ✅ Benar (PRG pattern):
return redirect()->route('leave.show', $leave)->with('success', 'Pengajuan cuti berhasil disubmit.');

// ❌ Salah (causes re-submit on back):
return view('leave.show', compact('leave'));
```

Read the controller and fix any `return view(...)` after form processing.

**Step: Commit**
```bash
rtk git add app/Http/Controllers/LeaveRequestController.php
rtk git commit -m "fix(form): gunakan PRG pattern untuk cegah re-submit saat back"
```

---

### Task 26: Fix edge case cuti lintas tahun baru

**Files:**
- Modify: `app/Services/CutiTahunanCalculator.php`

Find the duration/workday calculation logic. Ensure it handles Dec → Jan correctly:
```php
// Pastikan loop tidak berhenti di akhir tahun
$current = $startDate->copy();
while ($current->lte($endDate)) {
    if (!$current->isWeekend() && !$this->isHoliday($current)) {
        $count++;
    }
    $current->addDay();
}
```

The key fix: use `lte($endDate)` not `lt($endDate->year)` or similar year-based checks.

**Step: Commit**
```bash
rtk git add app/Services/CutiTahunanCalculator.php
rtk git commit -m "fix(calc): perbaiki perhitungan cuti yang melewati pergantian tahun"
```

---

### Task 27: Audit trail — catat siapa yang approve/reject

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php` (approve/reject methods)

Add to every approve/reject action:
```php
\Log::channel('daily')->info('Leave approval action', [
    'actor_id'   => auth()->id(),
    'actor_name' => auth()->user()->name,
    'leave_id'   => $leaveRequest->id,
    'action'     => 'approved', // or 'rejected'
    'ip'         => request()->ip(),
    'timestamp'  => now()->toIso8601String(),
]);
```

Or if there's an existing `AuditLog` model:
```php
AuditLog::create([
    'user_id'    => auth()->id(),
    'action'     => 'leave.approved',
    'target_id'  => $leaveRequest->id,
    'ip_address' => request()->ip(),
]);
```

**Step: Commit**
```bash
rtk git add app/Http/Controllers/LeaveRequestController.php
rtk git commit -m "feat(audit): catat audit trail setiap aksi approve/reject cuti"
```

---

## Summary

| Phase | Tasks | Type | Approx Time |
|-------|-------|------|-------------|
| 1 — Quick Wins | 1-9 | CSS + Blade | 2 hrs |
| 2 — Form UX | 10-17 | JS + Blade | 3 hrs |
| 3 — Mobile UX | 18-20 | CSS + JS | 2 hrs |
| 4 — Dashboard | 21-22 | PHP + Blade | 1.5 hrs |
| 5 — Profile | 23-24 | PHP + Blade | 2 hrs |
| 6 — Bug Fixes | 25-27 | PHP | 1.5 hrs |

**Already implemented (skip):** Dark mode, skeleton loading, toast notifications, bottom nav, NProgress, empty states, notification center, leave calendar, leave status timeline.

**Total new tasks:** 27 tasks covering the 35 truly-new features from the original 50-item list.
