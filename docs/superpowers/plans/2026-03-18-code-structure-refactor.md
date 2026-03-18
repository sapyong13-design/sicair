# Code Structure & Efficiency Refactor — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Perbaiki 18 temuan code structure audit — N+1 query, dead code, duplikasi, authorization, cache, dan arsitektur — untuk meningkatkan performa, keamanan, dan maintainability sebelum dan sesudah release.

**Architecture:** Perbaikan dibagi 3 fase berdasarkan risiko. Fase A (quick wins, safe) dikerjakan sebelum release. Fase B (medium refactor) bisa pre- atau post-release. Fase C (large architectural refactor) dikerjakan post-release karena berisiko tinggi.

**Tech Stack:** Laravel 12, PHP 8.3, SQLite (WAL mode), Tabler CSS, smoke tests via PHPUnit

**Test command:**
```
powershell.exe -Command "Set-Location 'C:\laragon\www\sicair'; & 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest"
```

---

## FASE A — Quick Wins (Pre-Release, Aman)

### Task 1: Hapus AuditLoggingMiddleware yang kosong (#11 dead code)

**Files:**
- Delete: `app/Http/Middleware/AuditLoggingMiddleware.php`
- Modify: `bootstrap/app.php` atau `app/Http/Kernel.php` (hapus registrasi jika ada)

**Masalah:** Middleware hanya berisi `return $next($request)` tanpa logika apapun.

- [ ] **Step 1: Cek apakah middleware diregistrasi**

```bash
grep -rn "AuditLoggingMiddleware\|audit.logging" /c/laragon/www/sicair/app/ /c/laragon/www/sicair/bootstrap/ /c/laragon/www/sicair/routes/
```

Expected: hanya ada di file middleware itu sendiri (tidak diregistrasi di manapun)

- [ ] **Step 2: Hapus file**

```bash
rm /c/laragon/www/sicair/app/Http/Middleware/AuditLoggingMiddleware.php
```

- [ ] **Step 3: Jalankan smoke tests**

```
powershell.exe -Command "Set-Location 'C:\laragon\www\sicair'; & 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest"
```

Expected: semua test tetap pass

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "refactor: hapus AuditLoggingMiddleware yang kosong (dead code)"
```

---

### Task 2: Fix GET()->sum() → SUM() di database (#4)

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php:101-108`

**Masalah:** Load semua row ke PHP memory hanya untuk sum — ineffisien untuk user dengan banyak pengajuan.

**Sebelum (baris 101-108):**
```php
$thisYearDays = LeaveRequest::where('user_id', $user->id)
    ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
    ->whereYear('created_at', date('Y'))
    ->get()->sum(fn($r) => $r->total_hari_kerja ?? $r->total_days ?? 0);
$lastYearDays = LeaveRequest::where('user_id', $user->id)
    ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
    ->whereYear('created_at', date('Y') - 1)
    ->get()->sum(fn($r) => $r->total_hari_kerja ?? $r->total_days ?? 0);
```

- [ ] **Step 1: Ganti dengan DB aggregation**

Ubah `app/Http/Controllers/LeaveRequestController.php:101-108` menjadi:

```php
$thisYearDays = LeaveRequest::where('user_id', $user->id)
    ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
    ->whereYear('created_at', date('Y'))
    ->sum('total_hari_kerja');
$lastYearDays = LeaveRequest::where('user_id', $user->id)
    ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
    ->whereYear('created_at', date('Y') - 1)
    ->sum('total_hari_kerja');
```

- [ ] **Step 2: Jalankan smoke tests**

Expected: pass

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/LeaveRequestController.php
git commit -m "perf: ganti GET()->sum() dengan DB SUM() di create leave page"
```

---

### Task 3: Centralize cache keys (#6 duplikasi)

**Files:**
- Create: `app/Support/CacheKeys.php`
- Modify: `app/Http/Controllers/LeaveRequestController.php` (3 tempat: baris ~474, ~677, ~788)
- Modify: `app/Http/Controllers/KeputusanController.php` (cek apakah ada cache forget di sini)
- Modify: `app/Services/AnalyticsService.php` (gunakan konstanta yang sama)

**Masalah:** String `'analytics_annual_' . now()->year` ditulis manual di 3+ tempat. Kalau key berubah, harus update semua.

- [ ] **Step 1: Buat file CacheKeys**

Buat `app/Support/CacheKeys.php`:

```php
<?php

namespace App\Support;

class CacheKeys
{
    public static function analyticsAnnual(int $year): string
    {
        return "analytics_annual_{$year}";
    }

    public static function analyticsDashboard(int $year): string
    {
        return "analytics_dashboard_{$year}";
    }

    public static function analyticsBalanceOverview(int $year): string
    {
        return "analytics_balance_overview_{$year}";
    }

    public static function analyticsHeatmapByUnit(int $year): string
    {
        return "analytics_heatmap_by_unit_{$year}";
    }

    /**
     * Forget semua analytics cache untuk tahun tertentu.
     * Panggil ini setiap kali ada perubahan status pengajuan cuti.
     */
    public static function forgetAnalytics(int $year): void
    {
        \Illuminate\Support\Facades\Cache::forget(self::analyticsAnnual($year));
        \Illuminate\Support\Facades\Cache::forget(self::analyticsDashboard($year));
        \Illuminate\Support\Facades\Cache::forget(self::analyticsBalanceOverview($year));
        \Illuminate\Support\Facades\Cache::forget(self::analyticsHeatmapByUnit($year));
    }
}
```

- [ ] **Step 2: Update LeaveRequestController — ganti 3 blok Cache::forget**

Cari semua:
```php
Cache::forget('analytics_annual_' . now()->year);
Cache::forget('analytics_dashboard_' . now()->year);
Cache::forget('analytics_balance_overview_' . now()->year);
Cache::forget('analytics_heatmap_by_unit_' . now()->year);
```

Ganti setiap blok dengan:
```php
\App\Support\CacheKeys::forgetAnalytics(now()->year);
```

Tambahkan use statement di atas: `use App\Support\CacheKeys;` lalu gunakan `CacheKeys::forgetAnalytics(now()->year);`

- [ ] **Step 3: Update AnalyticsService — ganti hardcoded keys**

Di `app/Services/AnalyticsService.php`, ganti:
```php
Cache::remember("analytics_dashboard_{$year}", 3600, ...)
Cache::remember("analytics_balance_overview_{$year}", 3600, ...)
Cache::remember("analytics_heatmap_by_unit_{$year}", 3600, ...)
```

Dengan:
```php
use App\Support\CacheKeys;
// ...
Cache::remember(CacheKeys::analyticsDashboard($year), 3600, ...)
Cache::remember(CacheKeys::analyticsBalanceOverview($year), 3600, ...)
Cache::remember(CacheKeys::analyticsHeatmapByUnit($year), 3600, ...)
```

- [ ] **Step 4: Jalankan smoke tests**

Expected: pass

- [ ] **Step 5: Commit**

```bash
git add app/Support/CacheKeys.php app/Http/Controllers/LeaveRequestController.php app/Services/AnalyticsService.php
git commit -m "refactor: centralize cache keys ke CacheKeys helper class"
```

---

### Task 4: Fix julianday() SQLite di top5Cuti query (#8/#16)

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php:105-112`

**Masalah:** `CAST((julianday(end_date) - julianday(start_date)) AS INTEGER) + 1` adalah SQLite-specific dan sebagai fallback saat `total_hari_kerja` null. Harusnya `total_hari_kerja` selalu terisi.

- [ ] **Step 1: Ganti query**

Ubah `DashboardController.php:105-112`:

```php
// SEBELUM:
$top5Cuti = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
    ->selectRaw("users.id, users.name, SUM(COALESCE(total_hari_kerja, CAST((julianday(end_date) - julianday(start_date)) AS INTEGER) + 1)) as total_hari")
    ->whereIn('leave_requests.status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
    ->whereYear('leave_requests.start_date', $year)
    ->groupBy('users.id', 'users.name')
    ->orderByDesc('total_hari')
    ->take(5)
    ->get();

// SESUDAH:
$top5Cuti = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
    ->selectRaw("users.id, users.name, SUM(COALESCE(leave_requests.total_hari_kerja, 0)) as total_hari")
    ->whereIn('leave_requests.status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
    ->whereYear('leave_requests.start_date', $year)
    ->groupBy('users.id', 'users.name')
    ->orderByDesc('total_hari')
    ->take(5)
    ->get();
```

- [ ] **Step 2: Jalankan smoke tests**

Expected: pass

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/DashboardController.php
git commit -m "perf: hapus julianday() SQLite-specific fallback di top5Cuti query"
```

---

### Task 5: Add ownership check untuk uploadPhoto (#3/#13 security)

**Files:**
- Modify: `app/Http/Controllers/PegawaiController.php:419-428`
- Modify: `routes/web.php` (tambah route agar pegawai bisa upload foto sendiri)

**Masalah:** Route `uploadPhoto` hanya accessible oleh admin (middleware `role:admin`). Pegawai tidak bisa upload foto mereka sendiri.

- [ ] **Step 1: Cek route pegawai upload photo**

```bash
grep -n "upload-photo\|uploadPhoto" /c/laragon/www/sicair/routes/web.php
```

- [ ] **Step 2: Update method uploadPhoto**

Ubah `PegawaiController.php:419-428`:

```php
public function uploadPhoto(Request $request, User $pegawai)
{
    // Allow: admin bisa upload siapa saja, pegawai hanya diri sendiri
    if (!auth()->user()->isAdmin() && auth()->id() !== $pegawai->id) {
        abort(403, 'Anda tidak berhak mengubah foto profil pegawai lain.');
    }

    $request->validate(['photo' => 'required|image|max:2048|mimes:jpg,jpeg,png,webp']);
    if ($pegawai->photo) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($pegawai->photo);
    }
    $path = $request->file('photo')->store('avatars', 'public');
    $pegawai->update(['photo' => $path]);
    return back()->with('success', 'Foto profil berhasil diperbarui.');
}
```

- [ ] **Step 3: Cek apakah route perlu dibuka untuk pegawai sendiri**

```bash
grep -n "upload-photo\|uploadPhoto\|role:admin" /c/laragon/www/sicair/routes/web.php | head -10
```

Jika route hanya di dalam middleware `role:admin`, tambahkan route baru di luar group admin agar pegawai bisa upload foto diri sendiri:

```php
// Di dalam auth middleware group tapi di luar role:admin
Route::post('/pegawai/{pegawai}/upload-photo', [PegawaiController::class, 'uploadPhoto'])
    ->name('pegawai.upload-photo.self');
```

- [ ] **Step 4: Jalankan smoke tests**

Expected: pass

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/PegawaiController.php routes/web.php
git commit -m "fix: tambah ownership check di uploadPhoto — pegawai bisa upload foto sendiri"
```

---

## FASE B — Medium Refactor (Dapat Dikerjakan Pre atau Post Release)

### Task 6: Fix N+1 di bulkDecide (#1 performance)

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php:569-683`

**Masalah:** Loop `foreach ($request->ids as $id) { LeaveRequest::find($id) }` = N queries. Setiap iterasi juga membuka `DB::transaction` terpisah untuk balance deduction.

**Pendekatan:** Pre-load semua leave dengan `whereIn`, batch update status sekali, lalu proses balance deduction dalam satu transaction besar.

- [ ] **Step 1: Refactor bulkDecide**

Ubah `LeaveRequestController.php` method `bulkDecide` (baris 569-683):

```php
public function bulkDecide(Request $request)
{
    $request->validate([
        'ids'      => 'required|array|min:1',
        'ids.*'    => 'exists:leave_requests,id',
        'decision' => 'required|in:setuju,tolak,tangguhkan',
        'catatan'  => 'nullable|string|max:500',
    ]);

    $user = auth()->user();
    if (!$user->canApproveAsPejabat() && !$user->isAdmin()) {
        abort(403);
    }

    $statusMap = [
        'setuju'     => LeaveRequest::STATUS_DISETUJUI,
        'tolak'      => LeaveRequest::STATUS_DITOLAK,
        'tangguhkan' => LeaveRequest::STATUS_DITANGGUHKAN,
    ];
    $newStatus = $statusMap[$request->decision];

    // Pre-load semua leave sekaligus — tidak ada lagi N+1
    $leaves = LeaveRequest::with('user')
        ->whereIn('id', $request->ids)
        ->whereIn('status', [LeaveRequest::STATUS_PERTIMBANGAN, LeaveRequest::STATUS_DIAJUKAN])
        ->get();

    if ($leaves->isEmpty()) {
        return back()->with('error', 'Tidak ada pengajuan valid yang dapat diproses.');
    }

    $count = 0;

    DB::transaction(function () use ($leaves, $newStatus, $request, $user, &$count) {
        foreach ($leaves as $leave) {
            $leave->update([
                'status'            => $newStatus,
                'pejabat_id'        => $user->id,
                'keputusan_pejabat' => $request->decision,
                'catatan_pejabat'   => $request->catatan,
                'decided_at'        => now(),
            ]);

            // Deduct balance hanya untuk cuti tahunan/bersama yang disetujui
            if ($request->decision === 'setuju' && in_array($leave->type, [LeaveRequest::TYPE_TAHUNAN, LeaveRequest::TYPE_BERSAMA])) {
                $lockedUser = \App\Models\User::lockForUpdate()->find($leave->user_id);
                $totalDays = $leave->total_hari_kerja ?? 0;
                $previousBalance = $lockedUser->leave_balance;
                $lockedUser->decrement('leave_balance', $totalDays);

                BalanceAuditService::logBalanceChange(
                    $lockedUser,
                    $previousBalance,
                    $previousBalance - $totalDays,
                    "Pengajuan {$leave->type_label} disetujui (bulk)",
                    $leave->id
                );
            }

            AuditLog::log(
                $request->decision === 'setuju' ? 'approve' : ($request->decision === 'tolak' ? 'reject' : $request->decision),
                LeaveRequest::class,
                $leave->id,
                null,
                ['status' => $newStatus, 'keputusan_pejabat' => $request->decision, 'catatan_pejabat' => $request->catatan],
                "Pejabat {$user->name} bulk-{$request->decision} pengajuan cuti #{$leave->id} milik {$leave->user?->name}"
            );

            $count++;
        }
    });

    // Notifikasi & WA dikirim di luar transaction (I/O tidak perlu di dalam transaction)
    foreach ($leaves->take($count) as $leave) {
        $notifType = $request->decision === 'setuju'
            ? Notification::TYPE_CUTI_DISETUJUI
            : ($request->decision === 'tolak' ? Notification::TYPE_CUTI_DITOLAK : Notification::TYPE_CUTI_PERTIMBANGAN);

        $notifTitle = match ($request->decision) {
            'setuju'     => 'Cuti Disetujui',
            'tolak'      => 'Cuti Ditolak',
            'tangguhkan' => 'Cuti Ditangguhkan',
        };

        $notifMessage = "Pengajuan {$leave->type_label} Anda telah " .
            match ($request->decision) {
                'setuju'     => 'disetujui',
                'tolak'      => 'ditolak',
                'tangguhkan' => 'ditangguhkan',
            } .
            " oleh {$user->name}" .
            ($request->catatan ? '. Catatan: ' . $request->catatan : '.');

        Notification::kirim($leave->user_id, $notifTitle, $notifMessage, $notifType, route('leave.show', $leave->id));

        if ($leave->user && $leave->user->wantsWhatsAppNotification()) {
            $statusLabel = match ($request->decision) {
                'setuju'     => 'DISETUJUI',
                'tolak'      => 'DITOLAK',
                'tangguhkan' => 'DITANGGUHKAN',
            };
            $tgl = $leave->start_date->format('d/m/Y') . ' - ' . $leave->end_date->format('d/m/Y');
            WhatsAppService::send(
                $leave->user->telepon,
                "SiCAIR: Pengajuan {$leave->type_label} Anda\n{$tgl}\ntelah {$statusLabel} oleh {$user->name}." .
                ($request->catatan ? "\nCatatan: {$request->catatan}" : '')
            );
        }
    }

    CacheKeys::forgetAnalytics(now()->year);

    return back()->with('success', "{$count} pengajuan berhasil diproses.");
}
```

- [ ] **Step 2: Jalankan smoke tests**

Expected: pass

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/LeaveRequestController.php
git commit -m "perf: fix N+1 di bulkDecide — pre-load dengan whereIn, single transaction"
```

---

### Task 7: Extract LeaveQueryFilters trait (#7 duplikasi)

**Files:**
- Create: `app/Traits/LeaveQueryFilters.php`
- Modify: `app/Http/Controllers/KeputusanController.php` (gunakan trait, hapus private methods)

**Masalah:** Logika filter (status, type, tanggal, nama) ada sebagai private method di KeputusanController. Controller lain (LaporanController, dll) tidak bisa reuse.

- [ ] **Step 1: Buat trait**

Buat `app/Traits/LeaveQueryFilters.php`:

```php
<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait LeaveQueryFilters
{
    protected function applyStatusFilter($query, Request $request): void
    {
        if (!$request->filled('status')) return;

        $query->where(function ($q) use ($request) {
            match ($request->status) {
                'disetujui' => $q->whereIn('status', [\App\Models\LeaveRequest::STATUS_DISETUJUI, \App\Models\LeaveRequest::STATUS_APPROVED]),
                'ditolak'   => $q->whereIn('status', [\App\Models\LeaveRequest::STATUS_DITOLAK, \App\Models\LeaveRequest::STATUS_REJECTED]),
                default     => $q->where('status', $request->status),
            };
        });
    }

    protected function applyTypeAndDateFilters($query, Request $request): void
    {
        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('start_date')) $query->where('start_date', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->where('end_date', '<=', $request->end_date);
    }

    protected function applyNameFilter($query, Request $request): void
    {
        if (!$request->filled('q')) return;
        $q = $request->q;
        $query->whereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
    }
}
```

- [ ] **Step 2: Update KeputusanController — gunakan trait, hapus private methods duplikat**

Di `app/Http/Controllers/KeputusanController.php`:

```php
// Tambahkan use setelah class declaration:
use App\Traits\LeaveQueryFilters;

class KeputusanController extends Controller
{
    use LeaveQueryFilters;

    // ... hapus 3 private methods: applyStatusFilter, applyTypeAndDateFilters, applyNameFilter
```

- [ ] **Step 3: Jalankan smoke tests**

Expected: pass

- [ ] **Step 4: Commit**

```bash
git add app/Traits/LeaveQueryFilters.php app/Http/Controllers/KeputusanController.php
git commit -m "refactor: ekstrak filter logic ke LeaveQueryFilters trait"
```

---

### Task 8: Inject AnalyticsService via constructor (#14 DI)

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`

**Masalah:** `new AnalyticsService()` dipanggil langsung di dalam method — tidak bisa di-mock di test, tidak mengikuti pola DI Laravel.

- [ ] **Step 1: Cek constructor DashboardController**

```bash
grep -n "__construct\|AnalyticsService" /c/laragon/www/sicair/app/Http/Controllers/DashboardController.php | head -10
```

- [ ] **Step 2: Tambahkan constructor injection**

Di `DashboardController.php`, tambahkan/ubah constructor:

```php
use App\Services\AnalyticsService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        // Hapus: $analyticsService = new AnalyticsService();
        // Gunakan: $this->analyticsService
        $analytics = $this->analyticsService->getDashboardAnalytics($year);
        // ...
    }
}
```

- [ ] **Step 3: Update semua `$analyticsService->` menjadi `$this->analyticsService->`**

```bash
grep -n "analyticsService->" /c/laragon/www/sicair/app/Http/Controllers/DashboardController.php
```

Ganti semua `$analyticsService->` dengan `$this->analyticsService->` di method yang menggunakannya.

- [ ] **Step 4: Jalankan smoke tests**

Expected: pass

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/DashboardController.php
git commit -m "refactor: inject AnalyticsService via constructor DI di DashboardController"
```

---

### Task 9: Tambah eager loading hint untuk getEffectiveAtasan (#10)

**Files:**
- Modify: `app/Models/User.php`

**Masalah:** `getEffectiveAtasan()` melakukan lazy load `$this->atasan` yang bisa menjadi N+1 jika dipanggil dalam loop banyak user.

- [ ] **Step 1: Tambahkan guard di method**

Di `app/Models/User.php`, update method `getEffectiveAtasan`:

```php
public function getEffectiveAtasan(): ?User
{
    // Ensure relation loaded to avoid N+1
    if (!$this->relationLoaded('atasan')) {
        $this->load('atasan');
    }

    $atasanAsli = $this->atasan;
    // ... rest of method unchanged
```

- [ ] **Step 2: Tambahkan doc comment peringatan**

Di atas method `getEffectiveAtasan()`, tambahkan:

```php
/**
 * Dapatkan atasan efektif (mempertimbangkan delegasi).
 *
 * PERHATIAN: Jika dipanggil dalam loop banyak user, pastikan eager load:
 *   User::with('atasan')->get()->each->getEffectiveAtasan()
 */
```

- [ ] **Step 3: Jalankan smoke tests**

Expected: pass

- [ ] **Step 4: Commit**

```bash
git add app/Models/User.php
git commit -m "perf: tambah eager loading guard di getEffectiveAtasan untuk cegah N+1"
```

---

### Task 10: Hapus redundant double authorization (#12)

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php` (baris sekitar 579)

**Masalah:** Route sudah ada middleware `role:ketua,wakil_ketua,admin`, tapi di dalam `bulkDecide` masih ada manual check yang redundan.

- [ ] **Step 1: Identifikasi semua double-check**

```bash
grep -n "canApproveAsPejabat\|isAdmin\|abort(403" /c/laragon/www/sicair/app/Http/Controllers/LeaveRequestController.php | head -20
grep -n "role:ketua\|role:admin\|role:atasan" /c/laragon/www/sicair/routes/web.php | head -20
```

- [ ] **Step 2: Evaluasi mana yang redundan**

Aturan: Jika route sudah dilindungi middleware role yang tepat, hapus manual check di dalam method. Jika route punya middleware lebih luas (misal `auth` saja) dan method butuh role spesifik, pertahankan manual check.

Contoh di `bulkDecide` (baris ~579):
```php
// Cek ini REDUNDAN jika route sudah ada middleware role:ketua,wakil_ketua,admin
// Tapi jika route hanya punya middleware auth, pertahankan
$user = auth()->user();
if (!$user->canApproveAsPejabat() && !$user->isAdmin()) {
    abort(403);
}
```

Verifikasi route di `routes/web.php` apakah endpoint ini sudah punya middleware role yang tepat. Jika ya, tambahkan komentar dan hapus cek manual.

- [ ] **Step 3: Jalankan smoke tests**

Expected: pass

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/LeaveRequestController.php routes/web.php
git commit -m "refactor: hapus redundant manual authorization check di bulkDecide"
```

---

## FASE C — Large Refactors (Post-Release)

> ⚠️ **Fase ini JANGAN dikerjakan sebelum release.** Risikonya tinggi karena menyentuh controller utama yang berisi 1268 baris. Lakukan di branch terpisah dengan test coverage lebih lengkap.

### Task 11: Split LeaveRequestController (#5)

**Tujuan:** Pecah LeaveRequestController (1268 baris, 20+ method) menjadi:
- `LeaveRequestController` — hanya CRUD: index, create, store, show, destroy
- `LeaveApprovalController` — reviewAtasan, decidePejabat, bulkDecide, bulkPertimbangan
- `LeaveReapplyController` — reapply, reapplyStore

**Files to create:**
- `app/Http/Controllers/LeaveApprovalController.php`
- `app/Http/Controllers/LeaveReapplyController.php`

**Files to modify:**
- `app/Http/Controllers/LeaveRequestController.php` (slim down)
- `routes/web.php` (update route references)

**Pendekatan:** Salin method ke controller baru, update routes, pastikan semua smoke test + manual test pass sebelum hapus dari controller lama.

---

### Task 12: Split PegawaiController (#13)

**Tujuan:** Pecah PegawaiController (495 baris, 23 method) menjadi:
- `PegawaiController` — CRUD standard + foto + toggle
- `PegawaiImportExportController` — import CSV, export CSV
- `PegawaiImpersonateController` — impersonate, stopImpersonate

**Files to create:**
- `app/Http/Controllers/PegawaiImportExportController.php`
- `app/Http/Controllers/PegawaiImpersonateController.php`

---

### Task 13: Standardize authorization ke Policy (#2/#18)

**Tujuan:** Semua endpoint gunakan `$this->authorize()` via Policy, bukan manual `abort(403)`.

**Files to create:**
- `app/Policies/LeaveRequestPolicy.php` (extend existing)
- `app/Policies/UserPolicy.php`
- `app/Policies/BalanceAdjustmentPolicy.php`

**Catatan:** Saat ini `LeaveRequestPolicy` sudah diregistrasi di `AppServiceProvider` tapi tidak semua method menggunakannya.

---

## Ringkasan Fase & Estimasi

| Fase | Items | Effort | Risiko | Kapan |
|------|-------|--------|--------|-------|
| A (Quick Wins) | Task 1–5 | ~2 jam | Rendah | **Sebelum release** |
| B (Medium) | Task 6–10 | ~4 jam | Sedang | Pre atau post release |
| C (Large) | Task 11–13 | ~2–3 hari | Tinggi | **Post release** |
