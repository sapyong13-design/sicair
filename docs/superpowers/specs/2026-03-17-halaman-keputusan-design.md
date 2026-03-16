# Design Spec: Halaman Keputusan Tersendiri

**Date:** 2026-03-17
**Status:** Approved
**Scope:** Tambah route `/keputusan` dengan konten role-aware + sederhanakan dashboard

---

## Latar Belakang

Saat ini menu "Keputusan" di navbar hanya mengarah ke `/dashboard#needs-decision` — isinya identik dengan dashboard, hanya di-scroll ke section tertentu. Ini membingungkan karena tidak ada halaman tersendiri yang fokus pada keputusan. Selain itu, bawahan dan atasan tidak punya tempat khusus untuk melihat riwayat keputusan atas pengajuan mereka.

---

## Tujuan

1. Buat halaman `/keputusan` tersendiri yang kontennya disesuaikan per role
2. Dashboard tetap menampilkan preview keputusan (ringkas, maks 5 item), halaman Keputusan menampilkan data lengkap + filter + riwayat
3. Semua role (ketua, atasan, pegawai, hakim, admin) punya akses ke halaman Keputusan dengan konten yang relevan

---

## Arsitektur

### Route Baru
```
GET /keputusan → KeputusanController@index  (name: 'keputusan.index')
```
Tidak perlu middleware role khusus — semua user yang sudah login (`auth`) bisa akses.

> **Catatan penamaan:** Gunakan `keputusan.index` (bukan `keputusan`) agar konsisten dengan konvensi namespace route lain di project (`leave.show`, `pegawai.index`, dll.) dan extensible untuk masa depan.

### Controller Baru: `KeputusanController`
- Satu method `index(Request $request)`
- Cek role user dengan `isAdmin()`, `isKetua()`, `isAtasan()` — urutan sama seperti `DashboardController::index()`
- Tidak ada logic aksi — semua approve/reject tetap di `LeaveRequestController`
- Query menggunakan `->paginate(15)->withQueryString()`
- Selalu gunakan konstanta status (`LeaveRequest::STATUS_PERTIMBANGAN`, `LeaveRequest::STATUS_DIAJUKAN`, dll.), **tidak pernah raw string**

### View Baru: `resources/views/keputusan/index.blade.php`
- Extend `layouts.app`
- Konten di-render dengan `@if` sesuai role (urutan: admin → ketua → atasan → fallthrough pegawai/hakim)
- Filter sebagai GET form (query string)
- Ketua: include partial `partials.pejabat-decision-modal` yang sudah ada (tidak duplikasi kode modal)

---

## Konten Per Role

### Admin
Tampilan sama seperti Ketua (tab "Menunggu Keputusan" + "Riwayat Keputusan"), karena admin memiliki akses approve/reject via backward-compat routes. Admin menggunakan `isAdmin()` check terlebih dahulu, sebelum `isKetua()`.

### Ketua
**Tab 1 — "Menunggu Keputusan"**
- Query: `LeaveRequest::where('status', LeaveRequest::STATUS_PERTIMBANGAN)`
- Bulk action: setujui / tolak / tangguhkan — scope per halaman (pagination 15), UI harus jelas bahwa bulk action hanya berlaku untuk item yang terlihat di halaman saat ini
- Filter: jenis cuti, rentang tanggal

**Tab 2 — "Riwayat Keputusan"**
- Query: `LeaveRequest::where('pejabat_id', $user->id)` — hanya keputusan yang dibuat oleh ketua ini
- Tampilkan: nama pegawai, jenis cuti, tanggal, keputusan (disetujui/ditolak), catatan pejabat, tanggal diputuskan
- Filter: status, jenis cuti, rentang tanggal
- Pagination 15 per halaman

> **Edge case:** Item yang diputuskan via route admin (`leave.approve`, `leave.reject`) mungkin memiliki `pejabat_id` milik user admin, bukan ketua — item tersebut tidak akan muncul di Tab 2 ketua. Ini adalah perilaku yang benar.

### Atasan
`isAtasan()` berlaku untuk role: `atasan`, `panitera`, `sekretaris`, `wakil_ketua`. Selalu gunakan method `$user->isAtasan()`, tidak hardcode nama role.

**Tab 1 — "Review Saya"** *(history view — bukan action queue)*
- Ini adalah **riwayat** review yang sudah pernah dilakukan, bukan antrian yang menunggu tindakan
- Antrian review yang belum dilakukan tetap ada di dashboard section "Pertimbangan"
- Query: `LeaveRequest::where('atasan_reviewer_id', $user->id)` — field ini hanya terisi setelah atasan submit review, sehingga item yang belum di-review tidak muncul di sini (by design)
- Tampilkan: nama pegawai, jenis cuti, tanggal, hasil pertimbangan atasan, status akhir dari ketua
- Filter: status akhir, jenis cuti, rentang tanggal

**Tab 2 — "Pengajuan Saya"**
- Query: `$user->leaveRequests()`
- Tampilkan: jenis cuti, tanggal, status, diputuskan oleh siapa, catatan pejabat
- Filter: status, jenis cuti, rentang tanggal

### Pegawai & Hakim
`hakim` tidak memenuhi `isAtasan()` — fallthrough ke blok ini sama seperti di `DashboardController`. Tampilan tunggal tanpa tab.

- Query: `$user->leaveRequests()`
- Tampilkan: jenis cuti, tanggal, status, diputuskan oleh siapa, catatan pejabat
- Filter: status, jenis cuti, rentang tanggal
- Pagination 15 per halaman

---

## Filter

Filter dikirim sebagai GET query string. Tombol "Reset" untuk hapus semua filter.

| Label Filter | Mapping ke Status Konstanta |
|---|---|
| Semua | (tidak filter) |
| Menunggu Review | `STATUS_DIAJUKAN` = `'diajukan'` |
| Pertimbangan Ketua | `STATUS_PERTIMBANGAN` = `'pertimbangan_atasan'` |
| Disetujui | `whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])` |
| Ditolak | `whereIn('status', [LeaveRequest::STATUS_DITOLAK, LeaveRequest::STATUS_REJECTED])` |

Filter jenis cuti: dropdown semua tipe dari `LeaveRequest::typeLabels()`.
Filter rentang tanggal: input `start_date` dan `end_date`.

---

## Perubahan di Dashboard

### `DashboardController.php` — `ketuaDashboard()`
- Query `needsDecision` dipisah menjadi **dua variabel**:
  - `$needsDecisionCount` — `->count()` tanpa limit, untuk badge "N antrian" yang tetap akurat
  - `$needsDecision` — `->take(5)->get()` untuk preview di dashboard
- Kedua variabel dikirim ke view

### `dashboard.blade.php`
- Badge "N antrian" menggunakan `$needsDecisionCount` (bukan `$needsDecision->count()`)
- Section "Menunggu Keputusan" menampilkan maks 5 item dari `$needsDecision`
- Tambah tombol **"Lihat semua →"** yang mengarah ke `route('keputusan.index')`

---

## Perubahan di Navbar

### `layouts/app.blade.php`
- Link "Keputusan" diubah dari `/dashboard#needs-decision` → `route('keputusan.index')`
- Tambah `active` class saat `request()->is('keputusan*')`
- Menu "Keputusan" ditampilkan untuk **semua role** (tidak hanya ketua) agar konsisten
- Badge `$navNeedsDecision` (jumlah pertimbangan menunggu) di samping link "Keputusan" diperluas ke admin: guard diubah dari `isKetua()` menjadi `isKetua() || isAdmin()` di blok `@php` navbar
- Menu "Pertimbangan" milik atasan (yang saat ini mengarah ke `/dashboard#pending-review`) **tetap dipertahankan** — tidak digabung dengan Keputusan, karena Pertimbangan merujuk ke aksi review bawahan (bukan riwayat keputusan)

---

## Yang Tidak Berubah

- Semua form aksi (modal keputusan, bulk decide) tetap di `LeaveRequestController`
- Partial `partials/pejabat-decision-modal.blade.php` digunakan ulang, tidak diduplikasi
- Logic approve/reject tidak dipindah

---

## File yang Dibuat / Diubah

| File | Action |
|------|--------|
| `app/Http/Controllers/KeputusanController.php` | **Baru** |
| `resources/views/keputusan/index.blade.php` | **Baru** |
| `routes/web.php` | Tambah route `GET /keputusan` → `keputusan.index` |
| `resources/views/layouts/app.blade.php` | Update link + active state + tampilkan untuk semua role |
| `resources/views/dashboard.blade.php` | Pisah `$needsDecisionCount` + limit 5 + tombol "Lihat semua" |
| `app/Http/Controllers/DashboardController.php` | Tambah `$needsDecisionCount` terpisah di `ketuaDashboard()` |
