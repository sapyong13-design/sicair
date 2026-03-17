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
| `resources/views/leave/saya.blade.php` | Pada "Pengajuan Terbaru" (`@forelse($recentLeaves as $leave)`): ganti blok `<div>` yang berisi `@php $badgeClass = ...` + `<span class="sc-badge ...">` dengan `<x-leave-status-stepper :leave="$leave" />` |
| `resources/views/dashboard.blade.php` | Fix 2 baris badge hardcoded `sc-badge-pending` di admin pending requests section |

## File Baru

| File | Fungsi |
|---|---|
| `resources/views/components/leave-status-stepper.blade.php` | Blade component — mini stepper horizontal 3-step |

---

## Detail: Komponen `<x-leave-status-stepper>`

### Props

```blade
@props(['leave'])
{{-- $leave: App\Models\LeaveRequest --}}
@isset($leave)
{{-- ... body component ... --}}
@endisset
```

Guard `@isset` di baris pertama (setelah `@props`) — menangani kasus `$leave` null atau tidak di-pass.

### Struktur Visual

```
[●]————[●]————[●]
Diajukan  Atasan  Ketua
```

Tiga dot kecil dihubungkan garis horizontal. Setiap dot memiliki warna dan ikon sesuai state.

### State Mapping

Implementer harus menggunakan `in_array($leave->status, [...])` untuk mengelompokkan nilai status sinonim. Logika PHP di dalam component (gunakan `@php ... @endphp` directive Blade):

```blade
@php
$status = $leave->status;

$step1 = 'done'; // selalu done

$step2 = match(true) {
    in_array($status, [
        LeaveRequest::STATUS_PERTIMBANGAN,   // 'pertimbangan_atasan'
        LeaveRequest::STATUS_DISETUJUI,      // 'disetujui'
        LeaveRequest::STATUS_APPROVED,       // 'approved'
        LeaveRequest::STATUS_DITOLAK,        // 'ditolak'
        LeaveRequest::STATUS_REJECTED,       // 'rejected'
        LeaveRequest::STATUS_DITANGGUHKAN,   // 'ditangguhkan'
    ]) => 'done',
    $status === LeaveRequest::STATUS_DIUBAH => 'warn',  // 'diubah'
    default => 'active', // diajukan, pending
};

$step3 = match(true) {
    in_array($status, [
        LeaveRequest::STATUS_DISETUJUI,
        LeaveRequest::STATUS_APPROVED,
    ]) => 'done',
    in_array($status, [
        LeaveRequest::STATUS_DITOLAK,
        LeaveRequest::STATUS_REJECTED,
    ]) => 'rejected',
    $status === LeaveRequest::STATUS_DITANGGUHKAN => 'warn',
    in_array($status, [
        LeaveRequest::STATUS_PERTIMBANGAN,
    ]) => 'active',
    default => 'pending', // diajukan, pending, diubah
};
@endphp
```

**Nilai state per dot:** `done` | `active` | `rejected` | `warn` | `pending`

**Tabel ringkas:**

| `$leave->status` | Dot 1 | Dot 2 | Dot 3 |
|---|---|---|---|
| `diajukan` / `pending` | done | active | pending |
| `pertimbangan_atasan` | done | done | active |
| `disetujui` / `approved` | done | done | done |
| `ditolak` / `rejected` | done | done | rejected |
| `ditangguhkan` | done | done | warn |
| `diubah` | done | warn | pending |

### Ukuran & CSS

- Dot diameter: 20px
- Garis penghubung: `height: 2px`. Ada dua connector: connector 1-2 (antara dot 1 dan dot 2) dan connector 2-3 (antara dot 2 dan dot 3). **Warna connector ditentukan oleh dot tujuan (dot N+1):** jika dot N+1 = `done` maka connector hijau (`var(--sc-success)`), selainnya abu (`var(--sc-gray-100)`).
- Label: font-size 0.65rem, di bawah dot
- Total tinggi component: ~40px
- Tidak ada JS, tidak ada Alpine.js
- Menggunakan CSS variables yang sudah ada: `var(--sc-success)`, `var(--sc-danger)`, `var(--sc-primary)`, `var(--sc-warning)`, `var(--sc-gray-100)`, `var(--sc-text-muted)`

### Dot state classes (internal, defined in component `<style>`)

`sc-pulse` tidak ada secara global di `app.blade.php` — definisikan di dalam `<style>` component ini.

```css
@keyframes sc-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
    50%       { box-shadow: 0 0 0 5px rgba(37, 99, 235, 0); }
}
/* Catatan: --sc-primary-rgb tidak tersedia secara global; gunakan nilai hardcoded rgba. */

.sc-step-done     { background: var(--sc-success); color: #fff; }
.sc-step-active   { background: var(--sc-primary); color: #fff; animation: sc-pulse 2s infinite; }
.sc-step-rejected { background: var(--sc-danger);  color: #fff; }
.sc-step-warn     { background: var(--sc-warning);  color: #fff; }  /* dipakai untuk: warn state (ditangguhkan, diubah) */
.sc-step-pending  { background: var(--sc-gray-100); color: var(--sc-text-muted); }
```

Catatan: `sc-step-warn` digunakan untuk dua kondisi berbeda (`ditangguhkan` dan `diubah`). Satu class cukup karena tampilan identik; diferensiasi via ikon.

**Ikon per state (Tabler Icons, ukuran 0.6rem):**
- `done` → `ti-check`
- `active` → tidak ada ikon, dot kosong
- `rejected` → `ti-x`
- `warn` → pilih berdasarkan nilai `$status`:
  - `$status === LeaveRequest::STATUS_DITANGGUHKAN` → `ti-clock-pause`
  - `$status === LeaveRequest::STATUS_DIUBAH` → `ti-edit`
  - **Catatan penting:** Berdasarkan state mapping, hanya satu dot yang pernah bernilai `warn` untuk setiap `$status`. Kedua kasus (`ditangguhkan` dan `diubah`) tidak pernah aktif bersamaan — `ditangguhkan` membuat dot 3 `warn`, `diubah` membuat dot 2 `warn`. Cek `$status` sekali di level component (bukan per-dot), lalu tentukan ikon sebelum merender.
- `pending` → tidak ada ikon, dot kosong

---

## Detail: Modifikasi `saya.blade.php`

Di dalam `@forelse($recentLeaves as $leave)`, ganti blok berikut:

```blade
{{-- SEBELUM (lines ~133-142): --}}
<div>
    @php
        $badgeClass = match(true) {
            $leave->isApproved() => 'sc-badge-approved',
            $leave->isRejected() => 'sc-badge-rejected',
            default => 'sc-badge-pending',
        };
    @endphp
    <span class="sc-badge {{ $badgeClass }}">{{ $leave->status_label }}</span>
</div>
```

Dengan:

```blade
{{-- SESUDAH: --}}
<x-leave-status-stepper :leave="$leave" />
```

---

## Detail: Badge Consistency Fix (`dashboard.blade.php`)

**Dua lokasi yang perlu difix, keduanya di admin section `$pendingRequests`:**

1. **Line ~541** — Desktop table view, kolom `<td>` status:
   ```blade
   {{-- Sebelum: --}}
   <td>
       <span class="sc-badge sc-badge-pending">{{ $req->status_label }}</span>
   </td>

   {{-- Sesudah: --}}
   <td>
       @php
           $bc = match(true) {
               $req->isApproved() => 'sc-badge-approved',
               $req->isRejected() => 'sc-badge-rejected',
               default            => 'sc-badge-pending',
           };
       @endphp
       <span class="sc-badge {{ $bc }}">{{ $req->status_label }}</span>
   </td>
   ```

2. **Line ~569** — Mobile card view, `<span>` status di pojok kanan card:
   ```blade
   {{-- Sebelum: --}}
   <span class="sc-badge sc-badge-pending">{{ $req->status_label }}</span>

   {{-- Sesudah: --}}
   @php
       $bc = match(true) {
           $req->isApproved() => 'sc-badge-approved',
           $req->isRejected() => 'sc-badge-rejected',
           default            => 'sc-badge-pending',
       };
   @endphp
   <span class="sc-badge {{ $bc }}">{{ $req->status_label }}</span>
   ```

Lokasi ditemukan dengan pencarian `sc-badge-pending.*status_label` di dalam blok `@foreach($pendingRequests as $req)`.

**Catatan:** `dashboard.blade.php` memiliki total ~16 kemunculan `sc-badge-pending`. Semua kemunculan selain 2 di atas berada di dalam blok `@foreach` yang berbeda (atasan review, pejabat review, history) dan beberapa merupakan `@else` branch dari kondisional `@if(isApproved())/@elseif(isRejected())`. Scope fix ini adalah **hanya** 2 baris di dalam `@foreach($pendingRequests as $req)` — jangan sentuh baris di foreach yang lain.

---

## Error Handling

| Skenario | Handling |
|---|---|
| `$leave->status` tidak dikenali | Semua dot tampil `pending` (abu) — graceful degradation via `default` clause |
| `$leave` null atau tidak di-pass | Component tidak merender — gunakan `@isset($leave)` (bukan `@if`) sebagai guard di baris pertama component, karena `$leave` selalu di-pass secara eksplisit; `@isset` menangani kasus null dan undefined sekaligus |

---

## Testing

Tidak ada test baru yang perlu ditambah — kedua halaman sudah tercakup smoke test existing:

| Test yang sudah ada | Assertion |
|---|---|
| `test_leave_saya_page_loads` | GET `/leave/saya` (auth) → 200 |
| `test_dashboard_loads_for_pegawai` | GET `/dashboard` (auth) → 200 |
| `test_dashboard_loads_for_admin` | GET `/dashboard` (auth admin) → 200 |

---

## Out of Scope Sprint B

- Push notification (Web Push / Service Worker)
- Real-time badge via SSE atau WebSocket
- Stepper di halaman selain `/leave/saya`
- Stepper di halaman `/leave/history` atau `/keputusan`
- Perubahan approval workflow
