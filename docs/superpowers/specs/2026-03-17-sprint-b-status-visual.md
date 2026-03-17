# Sprint B — Status Visual: Design Spec

**Tanggal:** 2026-03-17
**Status:** Draft
**Scope:** Mini stepper status di `/leave/saya` + badge consistency fix

---

## Tujuan

Menampilkan progress approval pengajuan cuti secara visual (3-step mini stepper) di halaman `/leave/saya`, sehingga pegawai dapat langsung melihat di tahap mana pengajuan mereka berada tanpa harus masuk ke halaman detail. Sekaligus memperbaiki inkonsistensi badge warna di `dashboard.blade.php`.

## Pendekatan: Progressive Enhancement

Tidak ada perubahan DB, tidak ada controller baru, tidak ada route baru. Satu Blade component baru, dua file yang dimodifikasi.

---

## File yang Dimodifikasi

| File | Perubahan |
|---|---|
| `resources/views/leave/saya.blade.php` | Ganti `<span class="sc-badge ...">` pada "Pengajuan Terbaru" dengan `<x-leave-status-stepper :leave="$leave" />` |
| `resources/views/dashboard.blade.php` | Fix 2 baris badge hardcoded `sc-badge-pending` → dynamic `isApproved()/isRejected()` |

## File Baru

| File | Fungsi |
|---|---|
| `resources/views/components/leave-status-stepper.blade.php` | Blade component — mini stepper horizontal 3-step |

---

## Detail: Komponen `<x-leave-status-stepper>`

### Props

```php
@props(['leave'])
// $leave: App\Models\LeaveRequest
```

### Struktur Visual

```
[●]————[●]————[●]
Diajukan  Atasan  Ketua
```

Tiga dot kecil dihubungkan garis horizontal. Setiap dot memiliki warna dan ikon sesuai state.

### State Mapping

| Status `$leave->status` | Dot 1 (Diajukan) | Dot 2 (Atasan) | Dot 3 (Ketua) |
|---|---|---|---|
| `diajukan` / `pending` | ✅ hijau | ⏳ biru pulse | ⬜ abu |
| `pertimbangan_atasan` | ✅ hijau | ✅ hijau | ⏳ biru pulse |
| `disetujui` / `approved` | ✅ hijau | ✅ hijau | ✅ hijau |
| `ditolak` / `rejected` | ✅ hijau | ✅ hijau | ❌ merah |
| `ditangguhkan` | ✅ hijau | ✅ hijau | ⏸ kuning |
| `diubah` | ✅ hijau | ✏️ kuning | ⬜ abu |

### Ukuran & CSS

- Dot diameter: 20px
- Garis penghubung: `height: 2px`, warna abu (`var(--sc-gray-100)`) untuk pending, hijau untuk done
- Label: font-size 0.65rem, di bawah dot
- Total tinggi component: ~40px
- Tidak ada JS, tidak ada Alpine.js
- Menggunakan CSS variables yang sudah ada: `var(--sc-success)`, `var(--sc-danger)`, `var(--sc-primary)`, `var(--sc-warning)`, `var(--sc-gray-100)`
- Pulse animation: `@keyframes sc-pulse` (tambahkan di component jika belum ada di app.blade.php — cek dulu)

### Dot state classes (internal, defined in component `<style>`)

```
.sc-step-done     → background: var(--sc-success); color: #fff
.sc-step-active   → background: var(--sc-primary); color: #fff; animation: sc-pulse 2s infinite
.sc-step-rejected → background: var(--sc-danger);  color: #fff
.sc-step-paused   → background: var(--sc-warning);  color: #fff
.sc-step-edit     → background: var(--sc-warning);  color: #fff
.sc-step-pending  → background: var(--sc-gray-100); color: var(--sc-text-muted)
```

---

## Detail: Badge Consistency Fix (`dashboard.blade.php`)

Dua lokasi di `dashboard.blade.php` menggunakan `sc-badge-pending` hardcoded untuk semua status:

**Sebelum (kedua lokasi):**
```blade
<span class="sc-badge sc-badge-pending">{{ $req->status_label }}</span>
```

**Sesudah:**
```blade
@php
    $bc = match(true) {
        $req->isApproved() => 'sc-badge-approved',
        $req->isRejected() => 'sc-badge-rejected',
        default            => 'sc-badge-pending',
    };
@endphp
<span class="sc-badge {{ $bc }}">{{ $req->status_label }}</span>
```

---

## Error Handling

| Skenario | Handling |
|---|---|
| `$leave->status` tidak dikenali | Semua dot tampil abu (pending state) — graceful degradation |
| `$leave` null | Component tidak merender (guard `@if($leave)`) |

---

## Testing

Tambahan di `tests/Feature/SmokeTest.php`:

| Test | Assertion |
|---|---|
| `test_leave_saya_page_loads` | GET `/leave/saya` (auth) → 200 (sudah ada, cukup pastikan tidak break) |
| `test_dashboard_loads_for_pegawai` | GET `/dashboard` (auth) → 200 (sudah ada, cukup pastikan tidak break) |

Tidak ada test baru yang perlu ditambah — kedua halaman sudah tercakup smoke test existing.

---

## Out of Scope Sprint B

- Push notification (Web Push / Service Worker)
- Real-time badge via SSE atau WebSocket
- Stepper di halaman selain `/leave/saya`
- Stepper di halaman `/leave/history` atau `/keputusan`
- Perubahan approval workflow
