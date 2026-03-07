# Mobile UX Enhancements — Design Document

**Date:** 2026-03-07
**Scope:** SiHEALING — Login mobile background + 18 mobile UX features

---

## 1. Login Mobile Background (Full Screen + Glass Card)

**Mobile only (`@media max-width: 991px`):**
- `body` background: `url('/gedung.webp')` cover center
- Pseudo-element overlay: `rgba(10, 40, 24, 0.45)`
- `.login-form-panel`: transparent, centered
- `.login-card`: `backdrop-filter: blur(12px)`, `background: rgba(255,255,255,0.88)`
- Desktop: tidak berubah

---

## 2. The 18 Mobile Features

### High Impact
1. **Bottom sheet** — Ganti modal popup jadi slide-up dari bawah di mobile
2. **Smart date shortcuts** — Tombol cepat "Besok", "Minggu depan", "Akhir bulan" di form cuti
3. **Quick filter chips** — Filter list cuti by status dengan chip tap-able
4. **Swipe bulan kalender** — Swipe kiri/kanan ganti bulan di halaman kalender
(#5 haptic feedback — SKIP)

### Native Feel
5. **PWA / Add to Home Screen** — manifest.json + service worker + install banner
6. **Offline fallback page** — Halaman offline branded saat tidak ada internet
7. **System dark mode auto** — Follow OS `prefers-color-scheme` otomatis
8. **Safe area support** — `env(safe-area-inset-*)` untuk notch & home bar
9. **Landscape hint** — Hint "Putar balik" saat landscape di mobile

### Speed & Comfort
(#11 skeleton per section — SKIP)
10. **Infinite scroll riwayat** — Load-more scroll di list cuti mobile
11. **Pinch-to-zoom kalender** — Zoom in/out kalender
12. **Long press preview** — Peek detail cuti tanpa buka halaman
13. **Compact/Expanded toggle** — Toggle tampilan list vs card

### Polish & Delight
14. **Lottie animasi sukses** — Animasi saat pengajuan berhasil
15. **Swipe dismiss notifikasi** — Swipe kiri untuk mark as read
16. **Status bar color** — `theme-color` meta tag ikut tema app
17. **Share status cuti** — Share ke WhatsApp / clipboard
18. **Floating back button** — Tombol ← mengambang di halaman dalam

---

## Tech Stack
- CSS: media queries, backdrop-filter, CSS custom properties
- JS: Vanilla JS (touch events, IntersectionObserver, Web Share API)
- PWA: manifest.json, service worker (minimal)
- No new npm packages
