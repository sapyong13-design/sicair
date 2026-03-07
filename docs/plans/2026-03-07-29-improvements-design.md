# SiHEALING — 29 Improvements Design Document

**Date:** 2026-03-07
**Scope:** Security, Backend, Features, Testing, Architecture, DevOps

---

## Kategori A — Keamanan (6 item)

### A1. Rate Limiting Login (Brute Force Protection)
Tambahkan `ThrottleRequests` middleware ke POST `/login` — max 5 percobaan per menit per IP. Response 429 dengan pesan jelas. Built-in Laravel, nol library tambahan.

### A2. Forgot Password / Reset Password
Form request reset, tabel `password_reset_tokens` (sudah ada di Laravel default), email link dengan token expiry 60 menit, form set password baru. Route: `/forgot-password`, `/reset-password/{token}`.

### A3. Form Request Classes
Pindahkan semua `$request->validate([...])` dari controller ke dedicated `app/Http/Requests/` classes. Target: `StoreLeaveRequestRequest`, `StoreUserRequest`, `UpdateUserRequest`, `StoreHariLiburRequest`, dll.

### A4. Authorization Policies
Buat `app/Policies/LeaveRequestPolicy`, `UserPolicy`, `BalanceAdjustmentPolicy`. Register di `AppServiceProvider`. Ganti manual `if ($user->isAdmin())` di controller dengan `$this->authorize()`.

### A5. Security Headers Middleware
Buat `app/Http/Middleware/SecurityHeaders.php` yang inject: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`. Daftarkan di `bootstrap/app.php`.

### A6. Log Login Gagal ke Audit Log
Di `AuthController::login()`, jika autentikasi gagal, panggil `AuditLog::log('login_failed', ...)` dengan username yang dicoba + IP address. Tidak ada perubahan model.

---

## Kategori B — Backend & Performa (7 item)

### B1. Email Async via Queue
Ganti semua `Mail::send()` / `Mail::to()->send()` dengan `Mail::to()->queue()`. Aktifkan `QUEUE_CONNECTION=database` di `.env`. Jalankan `php artisan queue:work` sebagai background process. Tabel `jobs` sudah ada.

### B2. Perbaiki N+1 Queries
Audit semua query di controller yang return list data. Tambahkan eager loading: `LeaveRequest::with(['user', 'atasanReviewer', 'pejabat'])`, `User::with(['cutiRecords', 'leaveRequests'])`. Target: dashboard admin, laporan bulanan, pegawai index.

### B3. Database Indexes
Tambah migration baru dengan indexes di: `leave_requests(status, user_id, start_date)`, `audit_logs(user_id, created_at, action)`, `notifications(user_id, is_read, created_at)`, `cuti_records(user_id, year)`.

### B4. Cache Analytics & Laporan
Wrap query mahal di `AnalyticsService` dan `LaporanBulananController` dengan `Cache::remember('analytics_'.date('Y-m'), 3600, fn() => ...)`. Invalidate cache saat ada approval baru.

### B5. PHP 8.1 Enums
Buat `app/Enums/LeaveStatus.php` (`Pending, Approved, Rejected, Cancelled, Revised, PertimbanganAtasan, Ditangguhkan`) dan `app/Enums/UserRole.php`. Ganti magic strings secara bertahap.

### B6. Hapus Duplicate Mail Classes
`LeaveRequestApproved.php` vs `LeaveRequestApprovedMail.php` — 4 pasang duplikat. Audit mana yang dipakai, hapus yang tidak dipakai, update semua referensi.

### B7. Conflict Detection di Controller/DB Level
Di `LeaveRequestController::store()`, tambahkan query cek tumpang tindih sebelum save: `LeaveRequest::where('user_id', $user->id)->where('status', '!=', 'rejected')->overlapping($start, $end)->exists()`. Jika ada, return error 422.

---

## Kategori C — Fitur Bisnis (8 item)

### C1. Delegasi Atasan
Tambah kolom `delegate_atasan_id` dan `delegate_period_start/end` ke tabel `users`. Saat atasan mengajukan cuti, sistem otomatis set delegate. Logic approval: cek dulu apakah atasan asli sedang cuti aktif, jika iya gunakan delegate. UI: atasan bisa set delegate dari profile.

### C2. Bulk Approval untuk Ketua/Pejabat
Tambah checkbox di tabel pending approvals. Tombol "Setujui Semua Dipilih" kirim array IDs ke endpoint baru `POST /leave/bulk-decide`. Loop approve/reject dengan satu note yang sama. Update saldo batch.

### C3. Notification Channel Management
**Skema:** Tambah kolom `notification_channels` (JSON) ke `users` table: `{"email": true, "whatsapp": false}`. Admin bisa set per-user dari halaman pegawai.

**WhatsApp Gateway:** Integrasi Fonnte API (`https://api.fonnte.com/send`). Config di `config/services.php`: `fonnte.token`. Service class `app/Services/WhatsAppService.php` dengan method `send($nomor, $pesan)`.

**Toggle Global:** Setting di admin panel (bisa pakai Laravel config atau database settings table sederhana) untuk enable/disable WhatsApp globally. Default: off.

**Trigger:** Kirim via channel yang aktif saat: pengajuan masuk, disetujui, ditolak, butuh review atasan.

### C4. Quota Cuti Otomatis per Golongan
Command `php artisan cuti:generate-quota {year}` yang loop semua user aktif, hitung quota via `CutiTahunanCalculator`, create/update `CutiRecord`. Jadwalkan di `routes/console.php` setiap 1 Januari. Admin bisa trigger manual dari UI.

### C5. Kalender Tim untuk Atasan
Halaman baru `/kalender/tim` (hanya untuk role atasan/ketua). Tampilkan semua approved leave request dari user yang `atasan_id = auth()->id()` dalam bulan berjalan. Gunakan same calendar component yang sudah ada.

### C6. Statistik Personal Pegawai
Tambah section di dashboard pegawai: chart bar "Penggunaan Cuti per Bulan" (12 bulan terakhir) menggunakan Chart.js (sudah ada di project?). Data dari `LeaveRequest` group by month. Juga tampilkan: total hari diambil tahun ini, rata-rata per bulan, perbandingan saldo.

### C7. Reminder Cuti Kadaluarsa
Command `php artisan cuti:remind-expiry` yang cek sisa cuti carry-over yang akan expired (31 Maret). Kirim notifikasi H-30 dan H-7. Jadwalkan di Scheduler. Gunakan notification channel yang aktif (email/WA).

### C8. Export Excel
Install `maatwebsite/excel` package. Tambah export Excel di: laporan bulanan, laporan saldo cuti, daftar pegawai. Buat `app/Exports/` classes. Tambah tombol "Export Excel" di samping tombol PDF yang sudah ada.

---

## Kategori D — Testing (4 item)

### D1. Feature Tests Approval Workflow
`tests/Feature/LeaveApprovalWorkflowTest.php` — cover: submit → pending, atasan approve → pertimbangan, pejabat setujui → approved (saldo berkurang), pejabat tolak → rejected (saldo tidak berubah).

### D2. Unit Tests Kalkulasi Cuti
`tests/Unit/CutiTahunanCalculatorTest.php` dan `tests/Unit/HariKerjaCalculatorTest.php` — test berbagai skenario: golongan berbeda, masa kerja berbeda, cuti melewati hari libur nasional, cuti lintas bulan.

### D3. Feature Tests Saldo Cuti
`tests/Feature/LeaveBalanceTest.php` — cover: carry-over, adjustment (tambah/kurang), pengurangan saat approve, pengembalian saat cancel/reject.

### D4. Factory & Seeder Lengkap
`database/factories/UserFactory.php` dengan states per role, `LeaveRequestFactory` dengan states per status, `DatabaseSeeder` yang seed minimal: 1 admin, 1 ketua, 2 atasan, 10 pegawai, 50 leave requests berbagai status.

---

## Kategori E — Arsitektur & Code Quality (4 item)

### E1. Background Job untuk PDF/DOCX Besar
Buat `app/Jobs/GenerateReportJob.php`. Untuk laporan yang butuh >2 detik (semua pegawai, annual), dispatch job, simpan file ke `storage/app/reports/`, notifikasi user dengan link download. Endpoint: `GET /reports/{token}/download`.

### E2. REST API dengan Laravel Sanctum
Tambah `routes/api.php` dengan endpoint: `POST /api/login` (return token), `GET /api/leave-requests`, `POST /api/leave-requests`, `GET /api/leave-balance`. Auth via `Laravel\Sanctum`. Berguna untuk integrasi masa depan.

### E3. Error Tracking
Integrasi Sentry PHP SDK (`sentry/sentry-laravel`). Config DSN di `.env`. Capture unhandled exceptions otomatis. Atau alternatif ringan: pastikan semua error masuk Laravel log dengan level yang benar + setup log channel ke file dengan rotation.

### E4. Health Check Endpoint
Route `GET /health` (public, no auth) yang return JSON: `{"status": "ok", "database": "ok", "queue": "ok", "disk_free_mb": 1234, "app_version": "1.0"}`. Setup UptimeRobot (gratis) ping setiap 5 menit.

---

## Tech Stack

- **No new frontend deps** — semua fitur pakai existing Tabler/Bootstrap/Vanilla JS
- **New PHP packages:** `maatwebsite/excel` (Excel export), `sentry/sentry-laravel` (error tracking, opsional)
- **WhatsApp:** Fonnte API (HTTP POST, no SDK needed)
- **Queue:** Database driver (tabel `jobs` sudah ada)
- **Sanctum:** Sudah include di Laravel 11 default

## Prioritas Implementasi

| Prioritas | Items | Estimasi Effort |
|-----------|-------|-----------------|
| 🔴 Kritis | A1, A2, A3, B1, B7 | Kecil-sedang |
| 🟠 Tinggi | A4, A6, B2, B3, C2, C3, C4, D1, D2 | Sedang |
| 🟡 Sedang | A5, B4, B5, B6, C1, C5, C6, C7, C8, D3, D4 | Sedang-besar |
| 🟢 Jangka panjang | E1, E2, E3, E4 | Besar |
