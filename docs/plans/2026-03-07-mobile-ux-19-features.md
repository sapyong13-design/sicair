# Mobile UX — 19 Features Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Implement login mobile background (glass card) + 18 mobile UX improvements untuk SiHEALING.

**Architecture:** Pure frontend — CSS media queries, vanilla JS touch events, Web APIs (Share, Vibration, IntersectionObserver). PWA via manifest.json + minimal service worker. Semua perubahan di `resources/views/` dan `public/`. Tidak ada perubahan backend/PHP.

**Tech Stack:** Laravel Blade, CSS (media queries, backdrop-filter, CSS variables), Vanilla JS, Web Share API, IntersectionObserver API, PWA (manifest + SW)

---

## BATCH A — Login Mobile Background

### Task 1: Full-screen glass card login di mobile

**Files:**
- Modify: `resources/views/auth/login.blade.php`

**Step 1: Read the file** to find the `<style>` block — specifically `.login-form-panel` and `body` rules.

**Step 2: Add mobile override CSS** inside the `<style>` block, just before `</style>`:

```css
/* Mobile: full-screen background + glass card */
@media (max-width: 991px) {
    body {
        background-image: url('/gedung.webp');
        background-size: cover;
        background-position: center center;
        background-attachment: fixed;
        position: relative;
    }
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background: rgba(10, 40, 24, 0.45);
        z-index: 0;
    }
    .login-form-panel {
        background: transparent !important;
        position: relative;
        z-index: 1;
        min-height: 100vh;
        padding: 1.5rem 1rem;
    }
    .login-card {
        background: rgba(255, 255, 255, 0.88) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        border-top: 4px solid #166534 !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
    }
    .mobile-header {
        position: relative;
        z-index: 1;
    }
    .mobile-institution {
        color: #ffffff !important;
        text-shadow: 0 1px 4px rgba(0,0,0,0.5);
    }
    .mobile-subtitle {
        color: rgba(255,255,255,0.85) !important;
        text-shadow: 0 1px 3px rgba(0,0,0,0.4);
    }
    /* Dark mode override for glass card */
    [data-bs-theme="dark"] .login-card {
        background: rgba(15, 23, 42, 0.88) !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }
}
```

**Step 3: Commit**
```bash
cd /c/Users/faris/sihealing
rtk git add resources/views/auth/login.blade.php
rtk git commit -m "feat(login): full-screen gedung background + glass card on mobile"
```

---

## BATCH B — CSS & Meta Tags (app.blade.php)

### Task 2: Theme-color meta + safe area + landscape hint + system dark mode auto

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

**Step 1: Read** lines 1-20 of the file (the `<head>` section).

**Step 2: Add meta tags** in `<head>`, after the existing meta viewport tag:

```html
    <!-- Mobile: theme color & safe area -->
    <meta name="theme-color" content="#166534" id="sh-theme-color-meta">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <!-- PWA manifest -->
    <link rel="manifest" href="/manifest.json">
```

**Step 3: Add safe area CSS** in the `<style>` block — find the `/* UI/UX IMPROVEMENTS` section and add BEFORE it:

```css
/* Safe area insets (notch + home bar) */
.sh-bottom-nav {
    padding-bottom: calc(0.5rem + env(safe-area-inset-bottom));
    padding-left: env(safe-area-inset-left);
    padding-right: env(safe-area-inset-right);
}
.navbar {
    padding-top: max(0.5rem, env(safe-area-inset-top));
}
body {
    padding-bottom: env(safe-area-inset-bottom);
}

/* Landscape hint on mobile */
@media (max-height: 500px) and (max-width: 900px) and (orientation: landscape) {
    .sh-landscape-hint {
        display: flex !important;
    }
}
.sh-landscape-hint {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(10, 40, 24, 0.95);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: white;
    text-align: center;
    gap: 1rem;
}
```

**Step 4: Add landscape hint HTML** just after `<body`:

```html
<!-- Landscape hint -->
<div class="sh-landscape-hint" id="sh-landscape-hint">
    <i class="ti ti-rotate" style="font-size: 3rem; animation: sh-rotate-hint 1.5s ease-in-out infinite alternate;"></i>
    <p style="font-size: 1rem; font-weight: 600; margin: 0;">Putar perangkat untuk tampilan optimal</p>
</div>
<style>
@keyframes sh-rotate-hint {
    from { transform: rotate(-30deg); }
    to   { transform: rotate(30deg); }
}
</style>
```

**Step 5: Add system dark mode auto JS** — find the existing dark mode toggle JS and ADD this BEFORE it (so it runs first):

```javascript
// System dark mode auto-follow
(function() {
    var html = document.documentElement;
    // Only auto-follow if user hasn't set a manual preference
    if (!localStorage.getItem('sh-theme')) {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            html.setAttribute('data-bs-theme', 'dark');
        }
        // Listen for OS changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (!localStorage.getItem('sh-theme')) {
                html.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
            }
        });
    }
})();
```

**Step 6: Add theme-color sync JS** — after the dark mode toggle JS:

```javascript
// Sync theme-color meta with dark/light mode
(function() {
    var meta = document.getElementById('sh-theme-color-meta');
    function syncMeta() {
        if (!meta) return;
        meta.content = document.documentElement.getAttribute('data-bs-theme') === 'dark'
            ? '#0f172a' : '#166534';
    }
    syncMeta();
    var toggle = document.getElementById('themeToggle');
    if (toggle) toggle.addEventListener('click', function() { setTimeout(syncMeta, 50); });
})();
```

**Step 7: Commit**
```bash
cd /c/Users/faris/sihealing
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "feat(mobile): theme-color meta, safe area, landscape hint, system dark mode auto"
```

---

## BATCH C — PWA (manifest + service worker + offline page)

### Task 3: PWA manifest.json

**Files:**
- Create: `public/manifest.json`

```json
{
    "name": "SiHEALING — PN Natuna",
    "short_name": "SiHEALING",
    "description": "Sistem Informasi Hak Elektronik Cuti — Pengadilan Negeri Natuna",
    "start_url": "/dashboard",
    "display": "standalone",
    "background_color": "#ffffff",
    "theme_color": "#166534",
    "orientation": "portrait-primary",
    "icons": [
        {
            "src": "/images/favicon-pn.png",
            "sizes": "192x192",
            "type": "image/png",
            "purpose": "any maskable"
        },
        {
            "src": "/favicon.png",
            "sizes": "32x32",
            "type": "image/png"
        }
    ],
    "categories": ["productivity", "utilities"],
    "lang": "id"
}
```

### Task 4: Service worker + offline page

**Files:**
- Create: `public/sw.js`
- Create: `resources/views/errors/offline.blade.php`

**sw.js:**
```javascript
var CACHE = 'sihealing-v1';
var OFFLINE_URL = '/offline';

// Install: cache offline page
self.addEventListener('install', function(e) {
    e.waitUntil(
        caches.open(CACHE).then(function(c) {
            return c.addAll([OFFLINE_URL, '/gedung.webp']);
        })
    );
    self.skipWaiting();
});

// Activate: clean old caches
self.addEventListener('activate', function(e) {
    e.waitUntil(
        caches.keys().then(function(keys) {
            return Promise.all(keys.filter(function(k) { return k !== CACHE; }).map(function(k) { return caches.delete(k); }));
        })
    );
    self.clients.claim();
});

// Fetch: network first, fallback to offline page for navigation
self.addEventListener('fetch', function(e) {
    if (e.request.mode === 'navigate') {
        e.respondWith(
            fetch(e.request).catch(function() {
                return caches.match(OFFLINE_URL);
            })
        );
    }
});
```

**offline.blade.php:**
```blade
@extends('layouts.app')
@section('title', 'Offline — SiHEALING')
@section('content')
<div class="text-center py-5">
    <i class="ti ti-wifi-off" style="font-size: 4rem; color: var(--sh-primary); opacity: 0.4; display: block; margin-bottom: 1rem;"></i>
    <h2 class="fw-bold mb-2">Tidak Ada Koneksi</h2>
    <p class="text-muted mb-4" style="max-width: 360px; margin: 0 auto 1.5rem;">
        Periksa koneksi internet Anda dan coba lagi.
    </p>
    <button onclick="window.location.reload()" class="btn btn-primary" style="border-radius: 12px;">
        <i class="ti ti-refresh me-1"></i> Coba Lagi
    </button>
</div>
@endsection
```

**Add route** in `routes/web.php`:
```php
Route::get('/offline', function () {
    return view('errors.offline');
})->name('offline');
```

**Register SW** in `app.blade.php` — add in JS section:
```javascript
// Register service worker (PWA)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').catch(function() {});
    });
}
```

**Commit:**
```bash
cd /c/Users/faris/sihealing
rtk git add public/manifest.json public/sw.js resources/views/errors/offline.blade.php routes/web.php resources/views/layouts/app.blade.php
rtk git commit -m "feat(pwa): manifest.json, service worker, offline page"
```

---

## BATCH D — Bottom Sheet (CSS + JS global)

### Task 5: Bottom sheet component

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

**Add CSS** in style block:
```css
/* Bottom Sheet (mobile modal replacement) */
.sh-bottom-sheet-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1050;
    opacity: 0;
    transition: opacity 0.25s ease;
}
.sh-bottom-sheet-backdrop.active {
    display: block;
    opacity: 1;
}
.sh-bottom-sheet {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1051;
    background: var(--sh-card-bg, #fff);
    border-radius: 20px 20px 0 0;
    padding: 0 1.25rem 1.5rem;
    padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));
    transform: translateY(100%);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 -8px 32px rgba(0,0,0,0.15);
}
.sh-bottom-sheet.active {
    transform: translateY(0);
}
.sh-bottom-sheet-handle {
    width: 40px;
    height: 4px;
    background: var(--sh-border, #d1e7d8);
    border-radius: 2px;
    margin: 0.75rem auto 1rem;
}
.sh-bottom-sheet-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--sh-text, #1e293b);
    margin-bottom: 1rem;
}
@media (min-width: 768px) {
    /* On desktop, bottom sheet behaves like normal modal */
    .sh-bottom-sheet {
        position: relative;
        transform: none;
        border-radius: 16px;
        max-height: none;
        box-shadow: none;
        padding: 1.25rem;
    }
}
```

**Add JS** (global bottom sheet API):
```javascript
// Bottom Sheet API
window.SHBottomSheet = {
    _backdrop: null,
    _sheet: null,
    open: function(id) {
        var sheet = document.getElementById(id);
        if (!sheet) return;
        if (!this._backdrop) {
            this._backdrop = document.createElement('div');
            this._backdrop.className = 'sh-bottom-sheet-backdrop';
            this._backdrop.addEventListener('click', function() { SHBottomSheet.close(); });
            document.body.appendChild(this._backdrop);
        }
        this._sheet = sheet;
        this._backdrop.style.display = 'block';
        requestAnimationFrame(function() {
            SHBottomSheet._backdrop.classList.add('active');
            sheet.classList.add('active');
        });
        document.body.style.overflow = 'hidden';
    },
    close: function() {
        if (this._sheet) this._sheet.classList.remove('active');
        if (this._backdrop) {
            this._backdrop.classList.remove('active');
            setTimeout(function() {
                if (SHBottomSheet._backdrop) SHBottomSheet._backdrop.style.display = 'none';
            }, 300);
        }
        document.body.style.overflow = '';
    }
};
// Close on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') SHBottomSheet.close();
});
```

**Commit:**
```bash
cd /c/Users/faris/sihealing
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "feat(mobile): bottom sheet component global API"
```

---

## BATCH E — Form Cuti Enhancements

### Task 6: Smart date shortcuts di form cuti

**Files:**
- Modify: `resources/views/leave/create.blade.php`

**Step 1: Read** the file to find where `start_date` input is.

**Step 2: Add shortcut buttons HTML** right AFTER the `start_date` input field:

```html
{{-- Smart date shortcuts --}}
<div class="d-flex flex-wrap gap-1 mt-2" id="sh-date-shortcuts">
    <button type="button" class="sh-date-chip" data-offset="1">Besok</button>
    <button type="button" class="sh-date-chip" data-offset="7">Minggu depan</button>
    <button type="button" class="sh-date-chip" data-type="next-monday">Senin depan</button>
    <button type="button" class="sh-date-chip" data-type="end-month">Akhir bulan</button>
</div>
```

**Step 3: Add CSS** in `@push('scripts')` (or inline `<style>`):
```css
.sh-date-chip {
    font-size: 0.75rem;
    padding: 0.25rem 0.65rem;
    border-radius: 99px;
    border: 1px solid var(--sh-primary, #166534);
    color: var(--sh-primary, #166534);
    background: transparent;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
}
.sh-date-chip:hover, .sh-date-chip:active {
    background: var(--sh-primary, #166534);
    color: white;
}
```

**Step 4: Add JS** in `@push('scripts')`:
```javascript
// Smart date shortcuts
document.querySelectorAll('.sh-date-chip').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var d = new Date();
        var offset = parseInt(this.dataset.offset);
        var type = this.dataset.type;
        if (!isNaN(offset)) {
            d.setDate(d.getDate() + offset);
        } else if (type === 'next-monday') {
            var day = d.getDay();
            d.setDate(d.getDate() + ((8 - day) % 7 || 7));
        } else if (type === 'end-month') {
            d = new Date(d.getFullYear(), d.getMonth() + 1, 0);
        }
        var iso = d.toISOString().split('T')[0];
        var startInput = document.querySelector('[name="start_date"]');
        if (startInput) {
            startInput.value = iso;
            startInput.dispatchEvent(new Event('change'));
        }
    });
});
```

**Commit:**
```bash
cd /c/Users/faris/sihealing
rtk git add resources/views/leave/create.blade.php
rtk git commit -m "feat(form): smart date shortcuts - besok, minggu depan, akhir bulan"
```

---

## BATCH F — Leave List Enhancements

### Task 7: Quick filter chips + compact/expanded toggle

**Files:**
- Modify: `resources/views/leave/show.blade.php` (check if there's a list view — if not, find the main leave history page)
- Also check `resources/views/dashboard.blade.php` for leave list section

**Step 1: Read** `resources/views/leave/show.blade.php` — find if it has a list of leave requests or just a single item. If single item, find which view has the leave list (check dashboard or pegawai views).

**Step 2: Add filter chips CSS** in `app.blade.php` style block (in the UI/UX improvements section):

```css
/* Quick filter chips */
.sh-filter-chips {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.sh-filter-chips::-webkit-scrollbar { display: none; }
.sh-filter-chip {
    flex-shrink: 0;
    font-size: 0.78rem;
    padding: 0.3rem 0.85rem;
    border-radius: 99px;
    border: 1.5px solid var(--sh-border, #d1e7d8);
    color: var(--sh-text-muted, #64748b);
    background: transparent;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
    font-weight: 500;
}
.sh-filter-chip.active,
.sh-filter-chip:hover {
    background: var(--sh-primary, #166634);
    border-color: var(--sh-primary, #166634);
    color: white;
}

/* Compact/Expanded toggle */
.sh-view-toggle { display: flex; gap: 0.25rem; }
.sh-view-btn {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: 1.5px solid var(--sh-border, #d1e7d8);
    background: transparent;
    color: var(--sh-text-muted, #64748b);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem;
    transition: all 0.15s;
}
.sh-view-btn.active {
    background: var(--sh-primary, #166634);
    border-color: var(--sh-primary, #166634);
    color: white;
}
.sh-compact-row td { padding: 0.4rem 0.75rem !important; font-size: 0.83rem !important; }
```

**Step 3: Add filter chip HTML + toggle** in the leave list page (above the table/list):

```html
<div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
    <div class="sh-filter-chips">
        <button class="sh-filter-chip active" data-filter="all">Semua</button>
        <button class="sh-filter-chip" data-filter="pending">Menunggu</button>
        <button class="sh-filter-chip" data-filter="approved">Disetujui</button>
        <button class="sh-filter-chip" data-filter="rejected">Ditolak</button>
        <button class="sh-filter-chip" data-filter="cancelled">Dibatalkan</button>
    </div>
    <div class="sh-view-toggle d-none d-md-flex">
        <button class="sh-view-btn active" id="sh-view-expanded" title="Tampilan normal">
            <i class="ti ti-layout-list"></i>
        </button>
        <button class="sh-view-btn" id="sh-view-compact" title="Tampilan kompak">
            <i class="ti ti-layout-rows"></i>
        </button>
    </div>
</div>
```

**Step 4: Add filter + toggle JS** in `@push('scripts')`:
```javascript
// Filter chips
document.querySelectorAll('.sh-filter-chip').forEach(function(chip) {
    chip.addEventListener('click', function() {
        document.querySelectorAll('.sh-filter-chip').forEach(function(c) { c.classList.remove('active'); });
        this.classList.add('active');
        var filter = this.dataset.filter;
        document.querySelectorAll('[data-status]').forEach(function(row) {
            var show = filter === 'all' || row.dataset.status === filter;
            row.style.display = show ? '' : 'none';
        });
    });
});
// View toggle
var viewExp = document.getElementById('sh-view-expanded');
var viewCmp = document.getElementById('sh-view-compact');
if (viewExp && viewCmp) {
    viewCmp.addEventListener('click', function() {
        viewExp.classList.remove('active'); viewCmp.classList.add('active');
        document.querySelectorAll('tbody tr').forEach(function(r) { r.classList.add('sh-compact-row'); });
    });
    viewExp.addEventListener('click', function() {
        viewCmp.classList.remove('active'); viewExp.classList.add('active');
        document.querySelectorAll('tbody tr').forEach(function(r) { r.classList.remove('sh-compact-row'); });
    });
}
```

**Commit:**
```bash
cd /c/Users/faris/sihealing
rtk git add resources/views/layouts/app.blade.php resources/views/leave/
rtk git commit -m "feat(mobile): quick filter chips + compact/expanded view toggle"
```

---

## BATCH G — Kalender Enhancements

### Task 8: Swipe bulan di kalender

**Files:**
- Modify: `resources/views/kalender/index.blade.php`

**Step 1: Read** the file — find `#btnMonthView`, prev/next month buttons (search for "prev", "next", "bulan").

**Step 2: Add swipe JS** in `@push('scripts')`:

```javascript
// Swipe to change month on calendar
(function() {
    var cal = document.getElementById('sh-calendar-wrapper') || document.querySelector('.sh-calendar, [data-calendar]') || document.querySelector('.card');
    if (!cal || window.innerWidth > 768) return;
    var startX = 0, startY = 0;
    cal.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    }, { passive: true });
    cal.addEventListener('touchend', function(e) {
        var dx = e.changedTouches[0].clientX - startX;
        var dy = e.changedTouches[0].clientY - startY;
        if (Math.abs(dx) < 50 || Math.abs(dy) > Math.abs(dx)) return;
        // Find prev/next month buttons
        var prevBtn = document.querySelector('[data-action="prev"], .fc-prev-button, #sh-cal-prev, [aria-label*="prev"], [aria-label*="sebelum"]');
        var nextBtn = document.querySelector('[data-action="next"], .fc-next-button, #sh-cal-next, [aria-label*="next"], [aria-label*="berikut"]');
        if (dx > 50 && prevBtn) prevBtn.click();
        if (dx < -50 && nextBtn) nextBtn.click();
    }, { passive: true });
})();
```

**Commit:**
```bash
cd /c/Users/faris/sihealing
rtk git add resources/views/kalender/index.blade.php
rtk git commit -m "feat(mobile): swipe kiri/kanan untuk ganti bulan di kalender"
```

---

## BATCH H — Long Press Preview + Share + Floating Back

### Task 9: Long press preview pada kartu cuti

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

**Add CSS:**
```css
/* Long press preview tooltip */
.sh-longpress-preview {
    position: fixed;
    z-index: 2000;
    background: var(--sh-card-bg, #fff);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    border: 1px solid var(--sh-border, #d1e7d8);
    border-top: 3px solid var(--sh-primary, #166634);
    padding: 1rem;
    max-width: 280px;
    min-width: 220px;
    pointer-events: none;
    opacity: 0;
    transform: scale(0.95);
    transition: opacity 0.2s, transform 0.2s;
}
.sh-longpress-preview.visible {
    opacity: 1;
    transform: scale(1);
}
.sh-longpress-preview-title {
    font-weight: 700;
    font-size: 0.88rem;
    color: var(--sh-text);
    margin-bottom: 0.5rem;
}
.sh-longpress-preview-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    padding: 0.2rem 0;
    border-bottom: 1px solid var(--sh-border-light, #e8f5e9);
    color: var(--sh-text-muted);
}
.sh-longpress-preview-row:last-child { border-bottom: none; }
```

**Add JS:**
```javascript
// Long press preview
(function() {
    var preview = document.createElement('div');
    preview.className = 'sh-longpress-preview';
    preview.innerHTML = '<div class="sh-longpress-preview-title" id="sh-lp-title"></div><div id="sh-lp-body"></div>';
    document.body.appendChild(preview);

    var timer, activeEl;
    document.addEventListener('touchstart', function(e) {
        var card = e.target.closest('[data-preview]');
        if (!card) return;
        activeEl = card;
        timer = setTimeout(function() {
            try {
                var data = JSON.parse(card.dataset.preview);
                document.getElementById('sh-lp-title').textContent = data.title || 'Detail';
                var body = document.getElementById('sh-lp-body');
                body.innerHTML = Object.entries(data).filter(function(kv) { return kv[0] !== 'title'; }).map(function(kv) {
                    return '<div class="sh-longpress-preview-row"><span>' + kv[0] + '</span><strong>' + kv[1] + '</strong></div>';
                }).join('');
                var rect = card.getBoundingClientRect();
                var top = Math.max(8, rect.top - 8);
                var left = Math.min(window.innerWidth - 296, rect.left);
                preview.style.top = top + 'px';
                preview.style.left = left + 'px';
                preview.classList.add('visible');
            } catch(e) {}
        }, 500);
    }, { passive: true });

    document.addEventListener('touchend', function() {
        clearTimeout(timer);
        preview.classList.remove('visible');
    }, { passive: true });
    document.addEventListener('touchmove', function() {
        clearTimeout(timer);
        preview.classList.remove('visible');
    }, { passive: true });
})();
```

**Commit:**
```bash
cd /c/Users/faris/sihealing
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "feat(mobile): long press preview untuk kartu cuti"
```

---

### Task 10: Share status cuti + Floating back button

**Files:**
- Modify: `resources/views/leave/show.blade.php`
- Modify: `resources/views/layouts/app.blade.php`

**Step 1: Add share button** in `leave/show.blade.php` — find the page header or action buttons area, add:

```html
@if(isset($leaveRequest))
<button id="sh-share-btn" class="btn btn-outline-secondary btn-sm" style="border-radius: 10px;" title="Bagikan status cuti">
    <i class="ti ti-share me-1"></i> Bagikan
</button>
@endif
```

**Step 2: Add share JS** in `@push('scripts')` of `show.blade.php`:

```javascript
var shareBtn = document.getElementById('sh-share-btn');
if (shareBtn && navigator.share) {
    shareBtn.addEventListener('click', function() {
        navigator.share({
            title: 'Status Cuti — SiHEALING',
            text: 'Pengajuan cuti saya: {{ $leaveRequest->leave_type_label ?? "" }} | Status: {{ ucfirst($leaveRequest->status ?? "") }} | {{ $leaveRequest->start_date ?? "" }} s/d {{ $leaveRequest->end_date ?? "" }}',
            url: window.location.href
        }).catch(function() {});
    });
} else if (shareBtn) {
    // Fallback: copy to clipboard
    shareBtn.addEventListener('click', function() {
        var text = 'Pengajuan cuti: {{ $leaveRequest->leave_type_label ?? "" }} | Status: {{ ucfirst($leaveRequest->status ?? "") }} | {{ $leaveRequest->start_date ?? "" }} s/d {{ $leaveRequest->end_date ?? "" }}';
        navigator.clipboard.writeText(text).then(function() {
            if (typeof showToast === 'function') showToast('Link berhasil disalin!', 'success');
        }).catch(function() {});
    });
}
```

**Step 3: Add floating back button CSS** in `app.blade.php`:

```css
/* Floating back button (mobile only) */
.sh-float-back {
    display: none;
}
@media (max-width: 768px) {
    .sh-float-back {
        display: flex;
        position: fixed;
        bottom: 80px;
        left: 1.25rem;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--sh-card-bg, #fff);
        border: 1.5px solid var(--sh-border, #d1e7d8);
        box-shadow: 0 2px 12px rgba(0,0,0,0.12);
        align-items: center;
        justify-content: center;
        color: var(--sh-primary, #166634);
        font-size: 1.1rem;
        z-index: 1039;
        text-decoration: none;
        transition: transform 0.15s;
    }
    .sh-float-back:hover { transform: scale(1.08); color: var(--sh-primary); }
    .sh-float-back:active { transform: scale(0.95); }
}
```

**Step 4: Add floating back HTML** in `app.blade.php` just before `</body>` (after FAB):

```html
{{-- Floating back button (mobile, detail pages only) --}}
@if(request()->is('leave/*') || request()->is('pegawai/*') || request()->is('profile*'))
<a href="javascript:history.back()" class="sh-float-back" aria-label="Kembali">
    <i class="ti ti-arrow-left"></i>
</a>
@endif
```

**Commit:**
```bash
cd /c/Users/faris/sihealing
rtk git add resources/views/leave/show.blade.php resources/views/layouts/app.blade.php
rtk git commit -m "feat(mobile): share status cuti + floating back button"
```

---

## BATCH I — Swipe dismiss notifikasi + Lottie animasi sukses

### Task 11: Swipe dismiss notifikasi

**Files:**
- Modify: `resources/views/components/notification-center.blade.php`

**Step 1: Read** the file — find `.notification-item` structure and any existing dismiss/mark-read JS.

**Step 2: Add swipe dismiss CSS** in the component's `<style>` or inline:

```css
.notification-item {
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, opacity 0.3s ease;
    touch-action: pan-y;
}
.notification-item.swipe-left {
    transform: translateX(-60px);
}
.notification-item .sh-swipe-action {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 60px;
    background: var(--sh-primary, #166634);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    transform: translateX(60px);
    transition: transform 0.3s ease;
}
.notification-item.swipe-left .sh-swipe-action {
    transform: translateX(0);
}
```

**Step 3: Add swipe JS** at the bottom of the component:

```javascript
// Swipe to dismiss notifications
document.querySelectorAll('.notification-item').forEach(function(item) {
    var startX = 0, startY = 0, swiping = false;

    // Add swipe action button
    var action = document.createElement('div');
    action.className = 'sh-swipe-action';
    action.innerHTML = '<i class="ti ti-check"></i>';
    action.addEventListener('click', function() {
        var id = item.dataset.notificationId;
        item.style.opacity = '0';
        item.style.height = item.offsetHeight + 'px';
        setTimeout(function() {
            item.style.height = '0';
            item.style.padding = '0';
            item.style.margin = '0';
        }, 300);
        // Mark as read via fetch if endpoint exists
        if (id) {
            fetch('/notifications/' + id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }}).catch(function(){});
        }
    });
    item.appendChild(action);

    item.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        swiping = true;
    }, { passive: true });

    item.addEventListener('touchmove', function(e) {
        if (!swiping) return;
        var dx = e.touches[0].clientX - startX;
        var dy = e.touches[0].clientY - startY;
        if (Math.abs(dy) > Math.abs(dx)) { swiping = false; return; }
        if (dx < -20) item.classList.add('swipe-left');
        if (dx > 20) item.classList.remove('swipe-left');
    }, { passive: true });

    item.addEventListener('touchend', function() { swiping = false; });
});
```

**Commit:**
```bash
cd /c/Users/faris/sihealing
rtk git add resources/views/components/notification-center.blade.php
rtk git commit -m "feat(mobile): swipe kiri untuk dismiss/mark-read notifikasi"
```

---

### Task 12: Lottie animasi sukses saat pengajuan berhasil

**Files:**
- Modify: `resources/views/leave/show.blade.php` (or wherever success redirect lands)
- Modify: `resources/views/layouts/app.blade.php`

**Step 1: Add Lottie CDN** in `app.blade.php` `<head>`:

```html
<script src="https://cdn.jsdelivr.net/npm/@lottiefiles/lottie-player@2/dist/lottie-player.js" defer></script>
```

**Step 2: Add success animation overlay CSS** in `app.blade.php`:

```css
/* Lottie success overlay */
.sh-success-overlay {
    position: fixed;
    inset: 0;
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(4px);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
}
[data-bs-theme="dark"] .sh-success-overlay {
    background: rgba(15, 23, 42, 0.92);
}
.sh-success-overlay.visible {
    opacity: 1;
    pointer-events: all;
}
.sh-success-overlay p {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--sh-primary, #166634);
    margin-top: 0.5rem;
}
```

**Step 3: Add HTML overlay** in `app.blade.php` before `</body>`:

```html
<!-- Lottie success overlay -->
<div id="sh-success-overlay" class="sh-success-overlay">
    <lottie-player
        src="https://assets9.lottiefiles.com/packages/lf20_jbrw3hcz.json"
        background="transparent"
        speed="1.2"
        style="width: 180px; height: 180px;"
        autoplay
        id="sh-lottie-player">
    </lottie-player>
    <p>Pengajuan Berhasil!</p>
</div>
```

**Step 4: Trigger overlay** — in `app.blade.php` JS section, add:

```javascript
// Show success animation on flash session
@if(session('success') && str_contains(session('success'), 'berhasil'))
(function() {
    var overlay = document.getElementById('sh-success-overlay');
    if (!overlay) return;
    overlay.classList.add('visible');
    setTimeout(function() {
        overlay.style.transition = 'opacity 0.5s';
        overlay.style.opacity = '0';
        setTimeout(function() { overlay.classList.remove('visible'); overlay.style.opacity = ''; }, 500);
    }, 2500);
})();
@endif
```

**Commit:**
```bash
cd /c/Users/faris/sihealing
rtk git add resources/views/layouts/app.blade.php
rtk git commit -m "feat(mobile): lottie animasi sukses saat pengajuan berhasil"
```

---

## Summary

| Batch | Tasks | Files |
|-------|-------|-------|
| A | Login mobile glass card | login.blade.php |
| B | Meta tags, safe area, landscape, dark auto | app.blade.php |
| C | PWA manifest + SW + offline | public/, routes/web.php |
| D | Bottom sheet component | app.blade.php |
| E | Smart date shortcuts | leave/create.blade.php |
| F | Filter chips + compact toggle | app.blade.php, leave views |
| G | Calendar swipe month | kalender/index.blade.php |
| H | Long press + share + float back | app.blade.php, leave/show.blade.php |
| I | Swipe dismiss notif + Lottie | notification-center.blade.php, app.blade.php |
