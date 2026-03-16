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
2. Dashboard tetap menampilkan preview keputusan (ringkas), halaman Keputusan menampilkan data lengkap + filter + riwayat
3. Semua role (ketua, atasan, pegawai) punya akses ke halaman Keputusan dengan konten yang relevan

---

## Arsitektur

### Route Baru
```
GET /keputusan → KeputusanController@index  (name: 'keputusan')
```
Tidak perlu middleware role khusus — semua user yang sudah login (`auth`) bisa akses.

### Controller Baru: `KeputusanController`
- Satu method `index(Request $request)`
- Cek role user, ambil data sesuai role dengan filter + pagination
- Tidak ada logic aksi — semua approve/reject tetap di `LeaveRequestController`
- Query menggunakan `->paginate(15)->withQueryString()`

### View Baru: `resources/views/keputusan/index.blade.php`
- Extend `layouts.app`
- Konten di-render dengan `@if` sesuai role
- Filter sebagai GET form (query string)
- Ketua: include partial `partials.pejabat-decision-modal` yang sudah ada (tidak duplikasi kode modal)

---

## Konten Per Role

### Ketua
**Tab 1 — "Menunggu Keputusan"**
- Semua `LeaveRequest` dengan status `pertimbangan`
- Bulk action: setujui / tolak / tangguhkan semua
- Filter: jenis cuti, rentang tanggal
- Identik dengan section dashboard tapi tanpa batas 5 item

**Tab 2 — "Riwayat Keputusan"**
- Semua `LeaveRequest` yang sudah diputuskan ketua (`pejabat_id = user->id`)
- Tampilkan: nama pegawai, jenis cuti, tanggal, keputusan, catatan pejabat, tanggal diputuskan
- Filter: status (disetujui/ditolak), jenis cuti, rentang tanggal
- Pagination 15 per halaman

### Atasan (panitera, sekretaris, wakil_ketua)
**Tab 1 — "Review Saya"**
- Semua `LeaveRequest` yang sudah di-review oleh atasan ini (`atasan_reviewer_id = user->id`)
- Tampilkan: nama pegawai, jenis cuti, tanggal, hasil pertimbangan atasan, status akhir dari ketua
- Filter: status akhir, jenis cuti, rentang tanggal

**Tab 2 — "Pengajuan Saya"**
- Riwayat cuti milik atasan sendiri (`user_id = user->id`)
- Tampilkan: jenis cuti, tanggal, status, diputuskan oleh siapa, catatan pejabat
- Filter: status, jenis cuti, rentang tanggal

### Pegawai
**Tampilan tunggal (tanpa tab)**
- Semua `LeaveRequest` milik pegawai ini
- Tampilkan: jenis cuti, tanggal, status, diputuskan oleh siapa, catatan pejabat
- Filter: status, jenis cuti, rentang tanggal
- Pagination 15 per halaman

---

## Filter (Semua Role)

| Filter | Opsi |
|--------|------|
| Status | Semua / Menunggu / Disetujui / Ditolak / Pertimbangan |
| Jenis Cuti | Semua / Tahunan / Sakit / Melahirkan / dll |
| Rentang Tanggal | Date picker dari–sampai |

Filter dikirim sebagai GET query string. Tombol "Reset" untuk hapus semua filter.

---

## Perubahan di Dashboard

### `dashboard.blade.php`
- Section "Menunggu Keputusan" ketua: tambah `->take(5)` di query, tambah tombol **"Lihat semua →"** yang mengarah ke `route('keputusan')`
- Role lain: tidak ada perubahan

### `DashboardController.php`
- `ketuaDashboard()`: query `needsDecision` sudah ada, cukup tambah `->take(5)` agar dashboard hanya tampilkan 5 item teratas

---

## Perubahan di Navbar

### `layouts/app.blade.php`
- Link "Keputusan" diubah dari `/dashboard#needs-decision` → `route('keputusan')`
- Tambah `active` class saat `request()->is('keputusan*')`
- Menu tetap hanya tampil untuk ketua (dan perlu ditambah untuk role lain yang relevan)

> **Catatan:** Perlu diputuskan apakah menu "Keputusan" di navbar juga ditampilkan untuk atasan dan pegawai, atau halaman hanya bisa diakses langsung via URL. Rekomendasi: tampilkan untuk semua role agar consistent.

---

## Yang Tidak Berubah

- Semua form aksi (modal keputusan, bulk decide) tetap di `LeaveRequestController`
- Partial `partials/pejabat-decision-modal.blade.php` digunakan ulang di view keputusan
- Logic approve/reject tidak duplikasi

---

## File yang Dibuat / Diubah

| File | Action |
|------|--------|
| `app/Http/Controllers/KeputusanController.php` | **Baru** |
| `resources/views/keputusan/index.blade.php` | **Baru** |
| `routes/web.php` | Tambah route `/keputusan` |
| `resources/views/layouts/app.blade.php` | Update link + active state navbar |
| `resources/views/dashboard.blade.php` | Limit needsDecision ke 5 + tombol "Lihat semua" |
| `app/Http/Controllers/DashboardController.php` | Tambah `->take(5)` di ketuaDashboard |
