# Sprint A — Form & Flow: Design Spec

**Tanggal:** 2026-03-17
**Status:** Draft
**Scope:** 7 fitur UX pada form pengajuan cuti dan kalender

---

## Tujuan

Meningkatkan pengalaman pegawai saat mengajukan cuti melalui enhancement pada form `/leave/create` dan kalender `/kalender`, tanpa refactor besar pada struktur yang sudah ada.

## Pendekatan: Progressive Enhancement

Semua fitur dibangun di atas kode yang ada. Alpine.js (sudah tersedia) digunakan untuk state management. Tidak ada perubahan pada approval workflow atau model `LeaveRequest`.

---

## Perubahan Database

### Tabel Baru: `leave_reason_templates`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `label` | string | Judul template (tampil di dropdown) |
| `body` | text | Isi alasan yang akan mengisi textarea |
| `is_active` | boolean | Default true |
| `sort_order` | integer | Urutan tampil di dropdown |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

## File yang Dimodifikasi

| File | Perubahan |
|---|---|
| `resources/views/leave/create.blade.php` | Alpine.js sections + auto-save + preview modal + baca `?start=` untuk pre-fill tanggal |
| `app/Http/Controllers/LeaveRequestController.php` | `create()`: tambah baca `$request->query('start')` untuk pre-fill; `selectType()`: teruskan `?start=` ke link jenis cuti |
| `resources/views/kalender/index.blade.php` | Slide-in sidebar panel |
| `resources/views/admin/settings/index.blade.php` | Tab "Template Alasan" (link ke halaman tersendiri) |
| `app/Http/Controllers/HariLiburController.php` | Tambah method `apiByYear()` yang return JSON |
| `routes/web.php` | Routes template alasan + 2 endpoint JSON baru |

## File Baru

| File | Fungsi |
|---|---|
| `app/Models/LeaveReasonTemplate.php` | Model Eloquent |
| `app/Http/Controllers/LeaveReasonTemplateController.php` | CRUD template alasan (terpisah dari SystemSettingController) |
| `database/migrations/..._create_leave_reason_templates_table.php` | Migrasi tabel |
| `resources/views/components/leave-sidebar-panel.blade.php` | Blade component panel kalender |
| `resources/views/admin/leave-reason-templates/index.blade.php` | Halaman CRUD template alasan |

---

## Detail Fitur

### Fitur 1 & 2: Auto-save Draft + Form Hybrid Collapsed/Expanded

Form dibungkus Alpine.js component `x-data="leaveForm()"` dengan state:

```js
{
  step: 1,          // section aktif yang terbuka
  draft: {},        // data form
  saved: false,     // status tersimpan di localStorage
  preview: false,   // tampilkan modal preview
  conflict: null    // hasil conflict check
}
```

**Tiga section collapsed/expanded:**
1. **Jenis Cuti** — default open
2. **Tanggal** — expand otomatis setelah jenis dipilih
3. **Alasan & Dokumen** — expand otomatis setelah tanggal valid

**Auto-save (localStorage):**
- Key: `sicair_draft_leave`
- Trigger: setiap event `input` pada form
- Restore: saat halaman `/leave/create` dibuka, cek localStorage → tampil banner "Ada draft tersimpan, lanjutkan?" dengan tombol **Lanjutkan** dan **Abaikan**
- Cleanup: `localStorage.removeItem('sicair_draft_leave')` otomatis setelah submit berhasil
- Fallback: jika localStorage tidak tersedia atau penuh → silent fail, form tetap berjalan normal

---

### Fitur 3: Preview Sebelum Submit

- Tombol "Review Pengajuan" muncul setelah Section 3 terisi lengkap
- Klik → modal Bootstrap tampil dengan ringkasan:
  - Jenis cuti
  - Tanggal mulai & selesai
  - Jumlah hari kerja
  - Alasan
- Dua tombol di modal: **Edit** (tutup modal) dan **Kirim Sekarang** (submit form)

---

### Fitur 4: Template Alasan Cuti

**Frontend:**
- Dropdown "Gunakan Template" di atas textarea alasan
- Pilih template → textarea terisi otomatis, masih bisa diedit bebas
- Data dari: `GET /leave-reason-templates/api` → JSON response, route di `routes/web.php` di bawah middleware `auth` (accessible semua role), **hanya mengembalikan record dengan `is_active = true`**, di-cache browser 1 jam
- Jika endpoint gagal → dropdown disembunyikan, textarea tetap bisa diisi manual

**Backend — Admin CRUD:**
- Controller baru: `LeaveReasonTemplateController` (terpisah dari `SystemSettingController`)
- Halaman: `/admin/leave-reason-templates` (bukan tab settings — settings sudah cukup kompleks)
- Link ke halaman ini ditambahkan di sidebar admin
- Operasi: tambah, edit, hapus, toggle aktif, ubah `sort_order` (input angka manual — **tidak ada drag-and-drop**)
- Route group di bawah middleware `role:admin`

**Response shape `GET /leave-reason-templates/api`:**
```json
[
  { "id": 1, "label": "Keperluan Keluarga", "body": "Saya perlu..." },
  { "id": 2, "label": "Sakit Anggota Keluarga", "body": "..." }
]
```

---

### Fitur 5: Date Picker Cerdas

- Input tetap `<input type="date">` native
- JS layer tambahan:
  - Load hari libur dari `GET /hari-libur/api?year=YYYY` — **endpoint baru** di `routes/web.php` di bawah middleware `auth` (semua role), method baru `apiByYear()` di `HariLiburController`, return JSON array tanggal libur: `["2026-01-01", "2026-04-10", ...]`
  - Hitung hari kerja: skip Sabtu, Minggu, dan tanggal yang ada di array libur
  - Tampil badge di bawah input "selesai": `"5 hari kerja"`
  - Jika tanggal dipilih adalah hari libur → warning kuning di bawah input
- Fallback jika `/hari-libur/api` gagal: hitung hari kerja tanpa data libur (skip Sabtu & Minggu saja)

---

### Fitur 6: Conflict Warning Real-time

- Endpoint yang digunakan: `GET /leave/check-conflict?start=...&end=...` (sudah ada)
- Response shape endpoint (sudah ada, tidak berubah):
  ```json
  { "conflict": false }
  { "conflict": true, "message": "Anda sudah memiliki pengajuan Cuti Tahunan pada 10/03/2026 – 12/03/2026 (status: diajukan)." }
  ```
  Catatan: endpoint **tidak mengembalikan ID** pengajuan yang konflik — hanya pesan teks.
- Dipanggil via `fetch()` saat tanggal mulai atau selesai berubah (debounce 500ms)
- Hasil ditampilkan di bawah input tanggal:
  - ✅ **Hijau:** "Tanggal tersedia"
  - ⚠️ **Kuning:** Tampilkan `message` dari response (sudah informatif — tidak perlu link, karena ID tidak tersedia)
- Conflict warning bersifat **informatif, tidak blocking** — pegawai tetap bisa submit

---

### Fitur 7: Kalender Sidebar Panel

**Trigger:** klik tanggal di `/kalender` → slide-in panel dari kanan

**Panel spec:**
- Lebar: 320px (desktop), full-width overlay (mobile)
- Animasi: CSS transition `transform: translateX`
- Konten:
  1. Tanggal yang diklik (format: "Senin, 17 Maret 2026")
  2. Label hari libur jika ada
  3. Daftar pegawai yang cuti di tanggal tersebut (dari data tim yang sudah di-load)
  4. Tombol **"Ajukan Cuti Tanggal Ini"** → redirect ke `/leave/select-type?start=YYYY-MM-DD`

**Flow pengajuan dari kalender:**
- Tombol mengarah ke `/leave/select-type?start=YYYY-MM-DD` (**bukan** langsung ke `/leave/create`)
- `selectType()` membaca `$request->query('start')` dan meneruskannya ke setiap link jenis cuti:
  ```php
  // Di selectType(), setiap link jenis cuti berubah dari:
  route('leave.create', ['type' => $type])
  // menjadi:
  route('leave.create', ['type' => $type, 'start' => $request->query('start')])
  ```
- `create()` ditambahkan pembacaan `$start = $request->query('start')` dan diteruskan ke view sebagai `$prefillStart`
- `create.blade.php` menggunakan `value="{{ $prefillStart ?? '' }}"` pada input tanggal mulai
- Dengan flow via `select-type`, user selalu memilih tipe terlebih dahulu — tidak ada defaulting TYPE_TAHUNAN yang tidak disengaja

---

## Error Handling

| Skenario | Handling |
|---|---|
| `localStorage` penuh / disabled | Silent fail, form berjalan normal |
| `/leave/check-conflict` timeout | Spinner hilang, submit tetap aktif |
| `/hari-libur/api` gagal | Date picker fallback ke skip Sabtu/Minggu saja |
| `/leave-reason-templates/api` gagal | Dropdown template disembunyikan |
| Submit berhasil | `localStorage.removeItem('sicair_draft_leave')` |
| 2 tab terbuka bersamaan | Draft di-overwrite tab terakhir — acceptable |

---

## Testing

Tambahan di `tests/Feature/SmokeTest.php`:

| Test | Assertion |
|---|---|
| `test_leave_reason_templates_api_returns_json` | GET `/leave-reason-templates/api` (auth) → 200 + JSON array |
| `test_leave_reason_templates_admin_can_create` | Admin POST `/admin/leave-reason-templates` → record tersimpan di DB |
| `test_leave_reason_templates_admin_can_delete` | Admin DELETE → record terhapus |
| `test_kalender_loads_with_panel_data` | GET `/kalender` → 200 |
| `test_leave_create_accepts_start_query_param` | GET `/leave/create?type=tahunan&start=2026-03-17` (auth) → 200 |
| `test_hari_libur_api_returns_json_by_year` | GET `/hari-libur/api?year=2026` sebagai pegawai biasa (role pegawai) → 200 + JSON array tanggal |

Tidak ada unit test untuk Alpine.js — cukup smoke test server-side.

---

## Out of Scope Sprint A

- Perubahan pada approval workflow
- Perubahan pada model `LeaveRequest`
- Email notification
- Fitur Sprint B–G
