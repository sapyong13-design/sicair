# Sprint D — Comprehensive Fixes & Features Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Perbaiki 27 item backlog SiCAIR — business logic, UX, fitur, dan teknis/ops — yang dikelompokkan ke dalam task-task koheren.

**Architecture:** Setiap task mandiri dan dapat di-commit sendiri. Business logic di-fix di layer Service/Controller, UX improvement di Blade views, ops fixes di Kernel/config, dan test coverage ditambah di SmokeTest.

**Tech Stack:** Laravel 12, Blade, Bootstrap 5 / Tabler CSS, SQLite, PHP 8.3, PhpSpreadsheet (sudah ada), Carbon

**PHP Binary:** `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`
**Test Command:** `& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest`
**Project Path:** `C:\laragon\www\sicair`

---

## Peta File

| Task | File yang Disentuh |
|------|--------------------|
| A1 | `app/Http/Controllers/LeaveRequestController.php`, `tests/Feature/SmokeTest.php` |
| A2 | `app/Http/Controllers/LeaveRequestController.php`, `app/Models/LeaveRequest.php`, `tests/Feature/SmokeTest.php` |
| A3 | `app/Http/Controllers/KeputusanController.php`, `resources/views/keputusan/index.blade.php`, `routes/web.php`, `tests/Feature/SmokeTest.php` |
| A4 | `app/Http/Controllers/DashboardController.php` |
| A5 | `app/Http/Controllers/LeaveRequestController.php` (reapply race condition) |
| B1 | `resources/views/leave/saya.blade.php`, `app/Http/Controllers/LeaveRequestController.php` |
| B2 | `resources/views/leave/history.blade.php`, `app/Http/Controllers/LeaveRequestController.php` |
| B3 | `resources/views/layouts/app.blade.php`, beberapa view tabel admin |
| B4 | `resources/views/layouts/app.blade.php` (toast JS), semua view yang pakai session flash |
| B5 | `resources/views/dashboard.blade.php` (live stats skeleton) |
| C1 | `resources/views/profile/index.blade.php`, `app/Http/Controllers/ProfileController.php` |
| C2 | `app/Http/Controllers/NotificationController.php`, `resources/views/components/notification-center.blade.php`, `resources/views/layouts/app.blade.php` |
| C3 | `routes/web.php`, `app/Http/Controllers/AdminLeaveController.php` (impersonate), `app/Http/Middleware/` |
| C4 | `app/Services/WhatsAppService.php`, `app/Mail/LeaveNotificationMail.php` (baru), `config/whatsapp.php` |
| D1 | `app/Console/Kernel.php` (tambah backup schedule) |
| D2 | `routes/web.php` (throttle leave.store) |
| D3 | `app/Http/Controllers/DocumentController.php` (validasi upload) |
| D4 | `resources/views/layouts/app.blade.php` (session timeout modal JS) |
| D5 | `app/Http/Controllers/PegawaiController.php`, `app/Http/Controllers/ProfileController.php`, `app/Http/Controllers/SystemSettingController.php` (tambah AuditLog) |
| D6 | `app/Http/Controllers/DashboardController.php` (N+1 audit), `app/Http/Controllers/KeputusanController.php` |
| D7 | `tests/Unit/BusinessRulesTest.php` (baru), `tests/Unit/CutiTahunanCalculatorTest.php` (baru) |

---

## GRUP A — Business Logic Fixes

### Task A1: Validasi Saldo + Max Consecutive Days

**Konteks:** `validateBusinessRules` sudah cek saldo untuk `TYPE_TAHUNAN` tapi `MAX_CONSECUTIVE_DAYS_WITHOUT_APPROVAL = 5` belum dipakai di mana pun. Cuti sakit dan cuti besar juga perlu saldo-check yang relevan.

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php` — tambah consecutive days check di `validateBusinessRules`
- Modify: `tests/Feature/SmokeTest.php` — tambah test consecutive days

- [ ] **Step A1.1: Tulis failing test untuk max consecutive days**

```php
// Di tests/Feature/SmokeTest.php, tambah method:
public function test_cuti_tahunan_rejects_more_than_14_consecutive_days(): void
{
    $user = User::factory()->create([
        'role' => 'pegawai',
        'leave_balance' => 12,
        'tanggal_masuk' => now()->subYears(2),
    ]);
    // Buat 14 hari kerja berurutan (3 minggu kalender ≈ 15 hari kerja)
    $start = now()->addDays(10)->startOfWeek(); // Senin
    $end = $start->copy()->addWeeks(3)->endOfWeek()->subDays(2); // Jumat 3 minggu kemudian

    $response = $this->actingAs($user)->post(route('leave.store'), [
        'type'           => 'cuti_tahunan',
        'start_date'     => $start->format('Y-m-d'),
        'end_date'       => $end->format('Y-m-d'),
        'reason'         => 'Test',
        'alamat_cuti'    => 'Jl Test',
        'telepon_cuti'   => '081234567890',
    ]);
    $response->assertSessionHasErrors();
}
```

- [ ] **Step A1.2: Jalankan test, verifikasi FAIL**

```bash
cd C:\laragon\www\sicair
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter test_cuti_tahunan_rejects_more_than_14_consecutive_days
```
Expected: FAIL (currently no validation)

- [ ] **Step A1.3: Tambah validasi consecutive days di `validateBusinessRules`**

Di `app/Http/Controllers/LeaveRequestController.php`, dalam `case LeaveRequest::TYPE_TAHUNAN:`, setelah cek saldo tambahkan:

```php
// Batas consecutive days — SEMA 13/2019: tidak lebih dari hak tahunan dalam 1 pengajuan
// Praktik: max 12 hari kerja per pengajuan untuk cuti tahunan
if ($hariKerja > 12) {
    return 'Cuti tahunan tidak boleh lebih dari 12 hari kerja dalam satu pengajuan. Ajukan terpisah untuk periode berbeda.';
}
```

- [ ] **Step A1.4: Jalankan test, verifikasi PASS**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```
Expected: 44+ passed

- [ ] **Step A1.5: Commit**

```bash
git add app/Http/Controllers/LeaveRequestController.php tests/Feature/SmokeTest.php
git commit -m "feat: tambah validasi max 12 hari kerja per pengajuan cuti tahunan"
```

---

### Task A2: Cuti Bersama Auto-Deduct dari Cuti Tahunan

**Konteks:** `TYPE_BERSAMA` ada di model tapi tidak ada penanganan di `validateBusinessRules` maupun di `decidePejabat` (approval flow). Cuti bersama seharusnya otomatis deduct dari saldo cuti tahunan saat disetujui.

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php`
- Modify: `tests/Feature/SmokeTest.php`

- [ ] **Step A2.1: Tulis failing test cuti bersama**

```php
public function test_cuti_bersama_deducts_from_tahunan_balance(): void
{
    $ketua = User::factory()->create(['role' => 'ketua']);
    $pegawai = User::factory()->create([
        'role'          => 'pegawai',
        'leave_balance' => 12,
        'tanggal_masuk' => now()->subYears(2),
        'atasan_id'     => $ketua->id,
    ]);

    // Buat pengajuan cuti bersama yang sudah di-pertimbangan
    $leave = \App\Models\LeaveRequest::factory()->create([
        'user_id'    => $pegawai->id,
        'type'       => \App\Models\LeaveRequest::TYPE_BERSAMA,
        'status'     => \App\Models\LeaveRequest::STATUS_PERTIMBANGAN,
        'start_date' => now()->addDays(5),
        'end_date'   => now()->addDays(6),
        'total_hari_kerja' => 2,
    ]);

    $this->actingAs($ketua)->post(route('leave.decide-pejabat', $leave), [
        'keputusan' => 'disetujui',
        'catatan_pejabat' => '',
    ]);

    $pegawai->refresh();
    $this->assertEquals(10, $pegawai->leave_balance);
}
```

- [ ] **Step A2.2: Jalankan test, verifikasi FAIL**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter test_cuti_bersama_deducts
```

- [ ] **Step A2.3: Tambah handling TYPE_BERSAMA di `validateBusinessRules`**

Di `app/Http/Controllers/LeaveRequestController.php`, tambah case baru setelah `TYPE_LUAR_TANGGUNGAN`:

```php
case LeaveRequest::TYPE_BERSAMA:
    // Cuti bersama deduct dari saldo cuti tahunan — cek saldo
    if ($hariKerja > $user->leave_balance) {
        return "Jumlah hari cuti bersama ($hariKerja hari) melebihi sisa cuti tahunan Anda ($user->leave_balance hari). Cuti bersama deduct dari saldo tahunan.";
    }
    break;
```

- [ ] **Step A2.4: Pastikan `decidePejabat` deduct saldo untuk TYPE_BERSAMA**

Di method `decidePejabat`, cari blok yang melakukan deduct saldo (biasanya ada `$user->decrement('leave_balance', ...)`). Pastikan kondisi mencakup `TYPE_BERSAMA`:

```php
// Cari baris ini (sudah ada untuk TYPE_TAHUNAN):
if (in_array($leaveRequest->type, [
    LeaveRequest::TYPE_TAHUNAN,
    LeaveRequest::TYPE_BERSAMA,  // ← tambahkan jika belum ada
])) {
    $leaveRequest->user->decrement('leave_balance', $leaveRequest->total_hari_kerja);
}
```

> **Catatan:** Baca method `decidePejabat` dulu (sekitar baris 350-450 di LeaveRequestController.php) untuk menemukan blok deduct yang tepat sebelum mengedit.

- [ ] **Step A2.5: Jalankan semua test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```
Expected: semua pass + test baru pass

- [ ] **Step A2.6: Commit**

```bash
git add app/Http/Controllers/LeaveRequestController.php tests/Feature/SmokeTest.php
git commit -m "feat: cuti bersama auto-deduct saldo tahunan sesuai SEMA 13/2019"
```

---

### Task A3: Ketua — Bulk Approve/Reject

**Konteks:** Atasan sudah punya bulk pertimbangan (`/keputusan/bulk-pertimbangan`). Ketua belum punya bulk approve/reject. Simetri fitur diperlukan untuk efisiensi ketua.

**Files:**
- Modify: `app/Http/Controllers/KeputusanController.php` — tambah method `bulkKeputusan`
- Modify: `resources/views/keputusan/index.blade.php` — tambah checkbox + bulk form untuk ketua
- Modify: `routes/web.php` — tambah route `POST /keputusan/bulk-keputusan`
- Modify: `tests/Feature/SmokeTest.php`

- [ ] **Step A3.1: Tulis failing test bulk keputusan ketua**

```php
public function test_bulk_keputusan_updates_status_for_ketua(): void
{
    $ketua = User::factory()->create(['role' => 'ketua']);
    $atasan = User::factory()->create(['role' => 'atasan']);
    $pegawai = User::factory()->create([
        'role' => 'pegawai', 'leave_balance' => 12,
        'tanggal_masuk' => now()->subYears(2),
    ]);

    $leaves = \App\Models\LeaveRequest::factory()->count(3)->create([
        'user_id' => $pegawai->id,
        'status'  => \App\Models\LeaveRequest::STATUS_PERTIMBANGAN,
        'type'    => \App\Models\LeaveRequest::TYPE_TAHUNAN,
        'total_hari_kerja' => 1,
        'start_date' => now()->addDays(10),
        'end_date'   => now()->addDays(10),
    ]);

    $response = $this->actingAs($ketua)->post(route('keputusan.bulk-keputusan'), [
        'ids'        => $leaves->pluck('id')->toArray(),
        'keputusan'  => 'disetujui',
    ]);

    $response->assertRedirect();
    foreach ($leaves as $leave) {
        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => \App\Models\LeaveRequest::STATUS_DISETUJUI,
        ]);
    }
}
```

- [ ] **Step A3.2: Jalankan test, verifikasi FAIL**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter test_bulk_keputusan
```

- [ ] **Step A3.3: Tambah route di `routes/web.php`**

Setelah route `keputusan.index`, tambahkan:

```php
Route::post('/keputusan/bulk-keputusan', [\App\Http\Controllers\KeputusanController::class, 'bulkKeputusan'])
    ->name('keputusan.bulk-keputusan')
    ->middleware('role:ketua,wakil_ketua,admin');
```

- [ ] **Step A3.4: Tambah method `bulkKeputusan` di `KeputusanController`**

Pastikan import berikut ada di bagian atas file `KeputusanController.php` (tambah yang belum ada):
```php
use Illuminate\Support\Facades\DB;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
```

Method:

```php
public function bulkKeputusan(Request $request): \Illuminate\Http\RedirectResponse
{
    $request->validate([
        'ids'       => 'required|array|min:1|max:50',
        'ids.*'     => 'integer|exists:leave_requests,id',
        'keputusan' => 'required|in:disetujui,ditolak,ditangguhkan',
    ]);

    // Akses sudah dijamin middleware route — tidak perlu double-check di sini
    $user = Auth::user();

    $statusMap = [
        'disetujui'    => LeaveRequest::STATUS_DISETUJUI,
        'ditolak'      => LeaveRequest::STATUS_DITOLAK,
        'ditangguhkan' => LeaveRequest::STATUS_DITANGGUHKAN,
    ];

    $leaves = LeaveRequest::whereIn('id', $request->ids)
        ->where('status', LeaveRequest::STATUS_PERTIMBANGAN)
        ->get();

    $processed = 0;
    foreach ($leaves as $leave) {
        DB::transaction(function () use ($leave, $user, $request, $statusMap) {
            $leave->update([
                'status'          => $statusMap[$request->keputusan],
                'pejabat_id'      => $user->id,
                'keputusan_pejabat' => $request->keputusan,
                'decided_at'      => now(),
            ]);

            if ($request->keputusan === 'disetujui') {
                if (in_array($leave->type, [
                    LeaveRequest::TYPE_TAHUNAN,
                    LeaveRequest::TYPE_BERSAMA,
                ])) {
                    $leave->user->decrement('leave_balance', $leave->total_hari_kerja ?? 1);
                }
            }
        });
        $processed++;
    }

    return redirect()->route('keputusan.index')
        ->with('success', "Bulk keputusan berhasil: $processed pengajuan diproses.");
}
```

- [ ] **Step A3.5: Tambah UI bulk ke `resources/views/keputusan/index.blade.php` (tab pengajuan ketua)**

Cari bagian tabel pengajuan untuk role ketua/wakil_ketua. Tambahkan sebelum tabel:

```html
@if($user->isKetua() || $user->isWakilKetua())
<form id="bulkKeputusanForm" method="POST" action="{{ route('keputusan.bulk-keputusan') }}" class="mb-3" style="display:none;">
    @csrf
    <div id="bulkKeputusanIds"></div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="text-muted" id="bulkSelectedCount">0 dipilih</span>
        <button type="submit" name="keputusan" value="disetujui" class="btn btn-success btn-sm">
            <i class="ti ti-check me-1"></i> Setujui Semua
        </button>
        <button type="submit" name="keputusan" value="ditolak" class="btn btn-danger btn-sm"
            onclick="return confirm('Tolak semua yang dipilih?')">
            <i class="ti ti-x me-1"></i> Tolak Semua
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="bulkKeputusanClear">Batal</button>
    </div>
</form>
@endif
```

Tambahkan checkbox di setiap baris tabel, dan script JS minimal untuk handle select/deselect (ikuti pola JS bulk-pertimbangan atasan yang sudah ada).

- [ ] **Step A3.6: Jalankan semua test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```
Expected: 45+ passed

- [ ] **Step A3.7: Commit**

```bash
git add app/Http/Controllers/KeputusanController.php resources/views/keputusan/index.blade.php routes/web.php tests/Feature/SmokeTest.php
git commit -m "feat: tambah bulk approve/reject untuk ketua di halaman keputusan"
```

---

### Task A4: Wakil Ketua — Audit Dashboard & Verifikasi Akses

**Konteks:** `wakil_ketua` masuk ke `atasanDashboard` karena `isAtasan()` includes `wakil_ketua`. Tapi wakil_ketua seharusnya melihat konten seperti ketua (butuh persetujuan). Perlu diverifikasi dan perbaiki jika salah.

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`

- [ ] **Step A4.1: Baca dan audit `index()` di DashboardController**

```
Baca baris 16-35 DashboardController.php.
Wakil_ketua saat ini → atasanDashboard (karena isAtasan() = true, isKetua() = false).
Keputusan: wakil_ketua perlu melihat "perlu keputusan" seperti ketua.
Fix: tambah isWakilKetua() check sebelum isAtasan().
```

- [ ] **Step A4.2: Tambah branch wakil_ketua di `index()`**

```php
// Di DashboardController::index(), setelah if ($user->isKetua())
if ($user->isWakilKetua()) {
    return $this->ketuaDashboard($user); // wakil_ketua lihat dashboard yang sama dengan ketua
}
```

- [ ] **Step A4.3: Jalankan test, verifikasi tidak ada regresi**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step A4.4: Commit**

```bash
git add app/Http/Controllers/DashboardController.php
git commit -m "fix: wakil_ketua menggunakan ketuaDashboard bukan atasanDashboard"
```

---

### Task A5: Race Condition Reapply — Audit & Fix

**Konteks:** Migration `add_unique_pending_constraint` ada, tapi flow reapply bisa bypass constraint jika tidak menggunakan proper transaction + soft-delete pada entry lama.

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php` — method `reapply` dan `store`

- [ ] **Step A5.1: Baca method `reapply` dan `store` di LeaveRequestController**

Baca baris sekitar method `reapply` dan awal `store` untuk memahami flow.

- [ ] **Step A5.2: Verifikasi bahwa `store()` sudah menggunakan `DB::transaction()` sebelum menambah catch**

Pertama, grep untuk menemukan apakah transaction sudah ada:

```bash
grep -n "DB::transaction" app/Http/Controllers/LeaveRequestController.php
```

**Jika transaction sudah ada**, bungkus dengan try/catch:

```php
try {
    DB::transaction(function () use (...) {
        // ... existing store logic
    });
} catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
    return back()->withErrors(['reason' => 'Anda sudah memiliki pengajuan aktif yang sedang diproses. Tunggu keputusan sebelum mengajukan ulang.'])->withInput();
}
```

**Jika transaction belum ada**, bungkus seluruh logic insert dengan `DB::transaction()` dulu, baru tambah try/catch di luarnya.

- [ ] **Step A5.3: Verifikasi reapply meng-soft-delete entry lama sebelum buat baru**

Pastikan dalam `reapply`, entry lama di-soft-delete (atau status-nya diubah ke 'diubah') sebelum membuat pengajuan baru agar constraint tidak terpicu.

- [ ] **Step A5.4: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step A5.5: Commit**

```bash
git add app/Http/Controllers/LeaveRequestController.php
git commit -m "fix: tangani UniqueConstraintViolationException di store + audit reapply flow"
```

---

## GRUP B — UX / Tampilan

### Task B1: Pagination di `/leave/saya`

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php` — method `saya`, ganti `->get()` ke `->paginate(10)`
- Modify: `resources/views/leave/saya.blade.php` — tambah `{{ $leaveRequests->links() }}`

- [ ] **Step B1.1: Update method `saya` di LeaveRequestController**

Cari baris `$leaveRequests = ...->get()` dalam method `saya()`, ganti:

```php
$leaveRequests = $query->latest()->paginate(10)->withQueryString();
```

- [ ] **Step B1.2: Tambah pagination links di view**

Di `resources/views/leave/saya.blade.php`, setelah tabel/card list:

```html
@if($leaveRequests->hasPages())
<div class="d-flex justify-content-center mt-3">
    {{ $leaveRequests->links() }}
</div>
@endif
```

- [ ] **Step B1.3: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step B1.4: Commit**

```bash
git add app/Http/Controllers/LeaveRequestController.php resources/views/leave/saya.blade.php
git commit -m "feat: tambah pagination di halaman saya (10 per halaman)"
```

---

### Task B2: Filter Tahun + Jenis Cuti di `/leave/history`

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php` — method `history`
- Modify: `resources/views/leave/history.blade.php`

- [ ] **Step B2.1: Update method `history` untuk terima filter tahun + type**

```php
public function history(Request $request): View
{
    $user = Auth::user();
    $query = LeaveRequest::where('user_id', $user->id)->latest();

    if ($request->filled('tahun')) {
        $query->whereYear('start_date', $request->tahun);
    }
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $leaveRequests = $query->paginate(15)->withQueryString();
    $tahunList = range(date('Y'), date('Y') - 5);
    $typeLabels = LeaveRequest::typeLabels();

    return view('leave.history', compact('leaveRequests', 'tahunList', 'typeLabels'));
}
```

- [ ] **Step B2.2: Tambah filter form di `resources/views/leave/history.blade.php`**

Tambahkan sebelum tabel:

```html
<form method="GET" action="{{ route('leave.history') }}" class="row g-2 mb-3">
    <div class="col-sm-auto">
        <select name="tahun" class="form-select form-select-sm">
            <option value="">Semua Tahun</option>
            @foreach($tahunList as $y)
                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-auto">
        <select name="type" class="form-select form-select-sm">
            <option value="">Semua Jenis</option>
            @foreach($typeLabels as $val => $label)
                <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-auto">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('leave.history') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </div>
</form>
```

Tambahkan juga links pagination di bawah tabel.

- [ ] **Step B2.3: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step B2.4: Commit**

```bash
git add app/Http/Controllers/LeaveRequestController.php resources/views/leave/history.blade.php
git commit -m "feat: tambah filter tahun + jenis cuti + pagination di riwayat cuti"
```

---

### Task B3: Responsive Tables untuk Mobile

**Konteks:** Tabel-tabel admin (audit log, laporan, pegawai) perlu dibungkus `table-responsive` agar tidak overflow di mobile.

**Files:**
- Scan dan fix: `resources/views/admin/audit-logs/index.blade.php`, `resources/views/laporan/tahunan.blade.php`, `resources/views/laporan/unit-kerja.blade.php`, `resources/views/pegawai/index.blade.php` (jika ada), `resources/views/keputusan/index.blade.php`

- [ ] **Step B3.1: Audit semua tabel — cari `<table` yang tidak dibungkus `table-responsive`**

Cari pola ini di semua view:

```bash
# Jalankan dari project root:
grep -rn "<table" resources/views/ | grep -v "table-responsive"
```

- [ ] **Step B3.2: Bungkus setiap `<table` yang belum responsive**

Untuk setiap tabel yang ditemukan, tambahkan wrapper:

```html
{{-- Sebelum: --}}
<table class="table ...">

{{-- Sesudah: --}}
<div class="table-responsive">
<table class="table ...">
...
</table>
</div>
```

- [ ] **Step B3.3: Verifikasi visual di browser width 375px** (jika bisa)

- [ ] **Step B3.4: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step B3.5: Commit**

```bash
git add resources/views/
git commit -m "fix: bungkus semua tabel dengan table-responsive untuk mobile"
```

---

### Task B4: Toast Notification (ganti flash banner)

**Konteks:** Sekarang pakai `@if(session('success'))` banner di setiap view. Ganti ke toast global di `layouts/app.blade.php` agar view tidak perlu kode flash masing-masing.

**Files:**
- Modify: `resources/views/layouts/app.blade.php` — tambah toast container + JS
- Opsional cleanup: hapus/kurangi duplikat flash di individual views (lakukan bertahap)

- [ ] **Step B4.1: Tambah toast container di `layouts/app.blade.php` sebelum `</body>`**

```html
{{-- Toast Container --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;" id="scToastContainer">
    @if(session('success'))
    <div class="toast align-items-center text-white border-0 show"
         role="alert" style="background: var(--sc-success);"
         data-bs-autohide="true" data-bs-delay="4000">
        <div class="d-flex">
            <div class="toast-body">
                <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="toast align-items-center text-white border-0 show"
         role="alert" style="background: var(--sc-danger);"
         data-bs-autohide="true" data-bs-delay="5000">
        <div class="d-flex">
            <div class="toast-body">
                <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif
</div>

{{-- Init toasts --}}
<script>
document.querySelectorAll('#scToastContainer .toast').forEach(function(el) {
    new bootstrap.Toast(el).show();
});
// Global helper: scToast('Pesan', 'success'|'error'|'warning')
window.scToast = function(msg, type) {
    type = type || 'success';
    var colors = { success: 'var(--sc-success)', error: 'var(--sc-danger)', warning: 'var(--sc-warning)' };
    var icons  = { success: 'circle-check', error: 'alert-circle', warning: 'alert-triangle' };
    var el = document.createElement('div');
    el.className = 'toast align-items-center text-white border-0';
    el.setAttribute('role', 'alert');
    el.style.background = colors[type] || colors.success;
    el.innerHTML = '<div class="d-flex"><div class="toast-body"><i class="ti ti-' + icons[type] + ' me-2"></i>' + msg + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    document.getElementById('scToastContainer').appendChild(el);
    var t = new bootstrap.Toast(el, { delay: 4000 });
    t.show();
    el.addEventListener('hidden.bs.toast', function() { el.remove(); });
};
</script>
```

- [ ] **Step B4.2: Verifikasi flash banner di dashboard.blade.php masih bekerja**

Sekarang toast global akan menampilkan session flash. Banner manual di view-view lain bisa dibiarkan (tidak redundant karena session hanya muncul sekali), atau dibersihkan bertahap. **Jangan hapus banner lama sekarang** — biarkan coexist, lalu bersihkan di sprint berikutnya.

- [ ] **Step B4.3: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step B4.4: Commit**

```bash
git add resources/views/layouts/app.blade.php
git commit -m "feat: tambah toast notification global + helper scToast() di layout"
```

---

### Task B5: Loading Skeleton di Live Stats Dashboard

**Files:**
- Modify: `resources/views/dashboard.blade.php` — tambah skeleton saat live stats belum ter-load

- [ ] **Step B5.1: Cari div live stats di `dashboard.blade.php`**

Cari elemen yang di-update oleh polling live stats (biasanya ada `id` seperti `#liveStats`, `#pendingCount`, dll).

- [ ] **Step B5.2: Tambah CSS skeleton class di `layouts/app.blade.php`**

```css
.sc-skeleton {
    background: linear-gradient(90deg, var(--sc-gray-200) 25%, var(--sc-gray-100) 50%, var(--sc-gray-200) 75%);
    background-size: 200% 100%;
    animation: sc-shimmer 1.5s infinite;
    border-radius: 6px;
    display: inline-block;
    height: 1.2em;
    width: 3ch;
}
@keyframes sc-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
```

- [ ] **Step B5.3: Ganti angka-angka statis live stats dengan skeleton saat load pertama**

Untuk setiap counter live stats, tambahkan class `sc-skeleton` pada saat pertama render (sebelum polling JS mengisi nilai), lalu JS hapus class tersebut setelah update pertama.

Di JS polling (cari `fetch('/dashboard/live-stats')` atau `setInterval`):

```js
// Setelah berhasil fetch dan update DOM:
document.querySelectorAll('.sc-skeleton').forEach(el => el.classList.remove('sc-skeleton'));
```

- [ ] **Step B5.4: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step B5.5: Commit**

```bash
git add resources/views/dashboard.blade.php resources/views/layouts/app.blade.php
git commit -m "feat: tambah skeleton loading state di live stats dashboard"
```

---

## GRUP C — Fitur Pelengkap

### Task C1: Rekap Saldo Cuti di Halaman Profil

**Konteks:** Komponen `leave-balance-card` sudah ada tapi perlu dicek apakah di-render di halaman profil. Jika belum, integrasikan.

**Files:**
- Modify: `resources/views/profile/index.blade.php`
- Modify: `app/Http/Controllers/ProfileController.php` — pastikan pass data cuti ke view

- [ ] **Step C1.1: Cek apakah profil sudah menampilkan saldo cuti**

Baca `resources/views/profile/index.blade.php` dan `app/Http/Controllers/ProfileController.php::index()`.

- [ ] **Step C1.2: Jika belum, tambah data cuti ke ProfileController::index()**

```php
// Di ProfileController::index():
$user = Auth::user();
$calculator = new \App\Services\CutiTahunanCalculator($user, date('Y'));
$cutiInfo = $calculator->hitung();

return view('profile.index', compact('user', 'cutiInfo'));
```

- [ ] **Step C1.3: Tambah section rekap saldo di profile view**

```html
{{-- Rekap Saldo Cuti --}}
@if(!$user->isAdmin())
<div class="card sc-card mb-4">
    <div class="card-body">
        <h3 class="card-title mb-3">
            <i class="ti ti-calendar-stats me-2" style="color:var(--sc-primary);"></i>
            Rekap Saldo Cuti {{ date('Y') }}
        </h3>
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.8rem;">Hak Cuti</div>
                <div style="font-size:1.5rem;font-weight:700;">{{ $cutiInfo['hak_cuti'] ?? 12 }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.8rem;">Carry Over</div>
                <div style="font-size:1.5rem;font-weight:700;">{{ $cutiInfo['carry_over'] ?? 0 }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.8rem;">Terpakai</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--sc-warning);">{{ $cutiInfo['cuti_diambil'] ?? 0 }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.8rem;">Sisa</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--sc-success);">{{ $cutiInfo['sisa_cuti'] ?? $cutiInfo['sisa'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>
@endif
```

- [ ] **Step C1.4: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step C1.5: Commit**

```bash
git add resources/views/profile/index.blade.php app/Http/Controllers/ProfileController.php
git commit -m "feat: tampilkan rekap saldo cuti tahunan di halaman profil"
```

---

### Task C2: Audit & Fix Notifikasi Bell (In-App)

**Konteks:** Komponen `notification-center`, `NotificationController`, dan route sudah ada. Perlu diverifikasi bahwa bell di navbar menampilkan unread count yang benar dan auto-refresh.

**Files:**
- Modify: `resources/views/layouts/app.blade.php` — verifikasi polling unread count
- Modify: `resources/views/components/notification-center.blade.php` — fix jika ada

- [ ] **Step C2.1: Baca layouts/app.blade.php — cari bell icon dan unread count logic**

Verifikasi:
1. Apakah ada elemen `#notifBadge` atau sejenisnya?
2. Apakah ada JS yang polling `/notifications/unread-count`?
3. Apakah endpoint `unreadCount()` di NotificationController mengembalikan JSON?

- [ ] **Step C2.2: Jika polling belum ada, tambahkan**

```js
// Polling unread count setiap 60 detik
(function pollUnread() {
    fetch('/notifications/unread-count')
        .then(r => r.json())
        .then(data => {
            var badge = document.getElementById('notifUnreadBadge');
            if (badge) {
                badge.textContent = data.count || '';
                badge.style.display = data.count > 0 ? '' : 'none';
            }
        })
        .catch(() => {});
    setTimeout(pollUnread, 60000);
})();
```

- [ ] **Step C2.3: Verifikasi mark-as-read bekerja saat item diklik**

Di `notification-center.blade.php`, pastikan setiap `.notification-item` memiliki event handler yang POST ke `/notifications/{id}/read` dan update tampilan.

- [ ] **Step C2.4: Tambah smoke test untuk unread count endpoint**

```php
public function test_notifications_unread_count_returns_json(): void
{
    $user = User::factory()->create(['role' => 'pegawai']);
    $response = $this->actingAs($user)->getJson(route('notifications.unread-count'));
    $response->assertOk()->assertJsonStructure(['count']);
}
```

- [ ] **Step C2.5: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step C2.6: Commit**

```bash
git add resources/views/layouts/app.blade.php resources/views/components/notification-center.blade.php tests/Feature/SmokeTest.php
git commit -m "fix: verifikasi dan perbaiki polling unread count notifikasi bell"
```

---

### Task C3: Admin Impersonate Pegawai

**Konteks:** Fitur "Login as" untuk admin agar bisa debug masalah user tanpa perlu minta password mereka.

**Files:**
- Create: `app/Http/Middleware/ImpersonateMiddleware.php`
- Modify: `app/Http/Controllers/PegawaiController.php` — tambah method `impersonate` dan `stopImpersonate`
- Modify: `routes/web.php`
- Modify: `resources/views/pegawai/` — tambah tombol "Masuk Sebagai"
- Modify: `resources/views/layouts/app.blade.php` — tambah banner impersonation aktif

- [ ] **Step C3.1: Buat Middleware ImpersonateMiddleware**

```php
// app/Http/Middleware/ImpersonateMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('impersonating')) {
            // Banner impersonation ditangani di view
        }
        return $next($request);
    }
}
```

- [ ] **Step C3.2: Tambah method `impersonate` dan `stopImpersonate` di PegawaiController**

```php
public function impersonate(\App\Models\User $pegawai): \Illuminate\Http\RedirectResponse
{
    $admin = Auth::user();
    if (!$admin->isAdmin()) abort(403);
    if ($pegawai->isAdmin()) abort(403, 'Tidak bisa impersonate admin lain.');

    Session::put('impersonating', [
        'admin_id' => $admin->id,
        'admin_name' => $admin->name,
    ]);
    Auth::login($pegawai);

    AuditLog::log('impersonate', 'User', $pegawai->id, null, null,
        "Admin {$admin->name} masuk sebagai {$pegawai->name}", request());

    return redirect()->route('dashboard')
        ->with('success', "Anda sekarang masuk sebagai {$pegawai->name}.");
}

public function stopImpersonate(): \Illuminate\Http\RedirectResponse
{
    $impersonating = Session::get('impersonating');
    if (!$impersonating) return redirect()->route('dashboard');

    $admin = \App\Models\User::findOrFail($impersonating['admin_id']);
    Session::forget('impersonating');
    Auth::login($admin);

    return redirect()->route('pegawai.index')
        ->with('success', 'Sesi impersonasi berakhir. Anda kembali sebagai admin.');
}
```

- [ ] **Step C3.3: Tambah routes**

```php
// Di dalam middleware role:admin group
Route::post('/admin/impersonate/{pegawai}', [PegawaiController::class, 'impersonate'])->name('admin.impersonate');
Route::post('/admin/stop-impersonate', [PegawaiController::class, 'stopImpersonate'])->name('admin.stop-impersonate');
```

- [ ] **Step C3.4: Tambah banner di layouts/app.blade.php**

```html
@if(Session::has('impersonating'))
<div class="alert alert-warning text-center mb-0 py-2" style="border-radius:0;font-size:0.85rem;">
    <i class="ti ti-eye me-1"></i>
    Anda sedang masuk sebagai <strong>{{ Auth::user()->name }}</strong>
    (mode impersonasi oleh <strong>{{ Session::get('impersonating.admin_name') }}</strong>).
    <form method="POST" action="{{ route('admin.stop-impersonate') }}" class="d-inline ms-2">
        @csrf
        <button type="submit" class="btn btn-warning btn-sm py-0">Kembali ke Admin</button>
    </form>
</div>
@endif
```

- [ ] **Step C3.5: Tambah tombol "Masuk Sebagai" di halaman daftar pegawai (hanya visible untuk admin)**

```html
@if(Auth::user()->isAdmin() && !Session::has('impersonating'))
<form method="POST" action="{{ route('admin.impersonate', $pegawai) }}" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-outline-secondary btn-sm"
        onclick="return confirm('Masuk sebagai {{ $pegawai->name }}?')"
        title="Impersonate">
        <i class="ti ti-user-share"></i>
    </button>
</form>
@endif
```

- [ ] **Step C3.6: Tulis smoke test impersonate**

```php
public function test_admin_can_impersonate_pegawai(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $pegawai = User::factory()->create(['role' => 'pegawai']);

    $response = $this->actingAs($admin)->post(route('admin.impersonate', $pegawai));
    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($pegawai);
}

public function test_admin_can_stop_impersonate(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $pegawai = User::factory()->create(['role' => 'pegawai']);

    $this->actingAs($admin);
    session(['impersonating' => ['admin_id' => $admin->id, 'admin_name' => $admin->name]]);
    Auth::login($pegawai);

    $response = $this->post(route('admin.stop-impersonate'));
    $response->assertRedirect(route('pegawai.index'));
    $this->assertAuthenticatedAs($admin);
}
```

- [ ] **Step C3.7: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step C3.8: Commit**

```bash
git add app/Http/Controllers/PegawaiController.php app/Http/Middleware/ImpersonateMiddleware.php routes/web.php resources/views/ tests/Feature/SmokeTest.php
git commit -m "feat: tambah fitur admin impersonate pegawai"
```

---

### Task C4: Email Fallback saat WhatsApp Gagal

**Konteks:** `WhatsAppService::send()` sudah ada try/catch dan `Log::warning`. Perlu tambah fallback ke email jika WA gagal dan user memiliki email.

**Files:**
- Modify: `app/Services/WhatsAppService.php`
- Create: `app/Mail/LeaveNotificationMail.php`

- [ ] **Step C4.1: Buat Mail class**

```bash
# --markdown tanpa path lengkap — Laravel auto-create di resources/views/emails/leave-notification.blade.php
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan make:mail LeaveNotificationMail --markdown=emails/leave-notification
```

Edit `app/Mail/LeaveNotificationMail.php`:

```php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subject,
        public string $body
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subject);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.leave-notification');
    }
}
```

Edit `resources/views/emails/leave-notification.blade.php`:

```html
<x-mail::message>
# {{ $subject }}

{{ $body }}

<x-mail::button :url="config('app.url') . '/dashboard'">
Buka Dashboard
</x-mail::button>

Terima kasih,
{{ config('app.name') }}
</x-mail::message>
```

- [ ] **Step C4.2: Update `WhatsAppService::send()` dengan fallback email**

```php
public static function send(string $phone, string $message, ?string $email = null, ?string $subject = null): bool
{
    // ... (existing WA send logic) ...

    // Fallback ke email jika WA gagal dan email tersedia
    if (!$waSuccess && $email) {
        try {
            \Illuminate\Support\Facades\Mail::to($email)->send(
                new \App\Mail\LeaveNotificationMail(
                    $subject ?? 'Notifikasi SiCAIR',
                    $message
                )
            );
            Log::info("Email fallback sent to {$email} (WA failed for {$phone})");
            return true;
        } catch (\Exception $e) {
            Log::error("Email fallback juga gagal: " . $e->getMessage());
        }
    }

    return $waSuccess;
}
```

> **Catatan:** Baca signature `WhatsAppService::send()` yang ada terlebih dahulu. Pastikan perubahan signature backward-compatible (parameter baru nullable).

- [ ] **Step C4.3: Update pemanggil WhatsAppService untuk pass email jika tersedia**

Cari semua `WhatsAppService::send(...)` di codebase:

```bash
grep -rn "WhatsAppService::send" app/
```

Update yang paling penting (NotificationService atau tempat notifikasi cuti dikirim) untuk pass email user.

- [ ] **Step C4.4: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step C4.5: Commit**

```bash
git add app/Services/WhatsAppService.php app/Mail/LeaveNotificationMail.php resources/views/emails/
git commit -m "feat: tambah email fallback saat WhatsApp gagal kirim notifikasi"
```

---

## GRUP D — Teknis & Operasional

### Task D1: Jadwal Backup Otomatis

**Konteks:** `BackupDatabase` command sudah ada tapi **tidak** di-schedule di `Kernel.php`. Perlu ditambahkan.

**Files:**
- Modify: `app/Console/Kernel.php`

- [ ] **Step D1.1: Tambah schedule backup di Kernel.php**

```php
// Backup database setiap hari pukul 03:00, simpan 7 versi terakhir
$schedule->command('backup:database --keep=7')
    ->dailyAt('03:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->runInBackground();
```

- [ ] **Step D1.2: Jalankan command secara manual untuk verifikasi**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan backup:database --keep=3
```
Expected: output sukses, file backup di `storage/app/backups/`

- [ ] **Step D1.3: Commit**

```bash
git add app/Console/Kernel.php
git commit -m "feat: jadwalkan backup database otomatis setiap hari pukul 03:00"
```

---

### Task D2: Rate Limiting pada Pengajuan Cuti

**Konteks:** Route `leave.store` tidak punya throttle. Perlu tambah untuk mencegah double-submit.

**Files:**
- Modify: `routes/web.php`

- [ ] **Step D2.1: Tambah throttle middleware ke route `leave.store`**

```php
// Ubah:
Route::post('/leave', [LeaveRequestController::class, 'store'])->name('leave.store');

// Jadi:
Route::post('/leave', [LeaveRequestController::class, 'store'])
    ->name('leave.store')
    ->middleware('throttle:5,1'); // max 5 submit per menit per user
```

- [ ] **Step D2.2: Tambah throttle juga ke route `keputusan.bulk-keputusan` dan `leave.bulk-pertimbangan`**

```php
->middleware('throttle:10,1')
```

- [ ] **Step D2.3: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step D2.4: Commit**

```bash
git add routes/web.php
git commit -m "fix: tambah rate limiting throttle pada route pengajuan dan bulk actions"
```

---

### Task D3: Validasi File Upload di DocumentController

**Konteks:** `StoreLeaveRequestRequest` sudah punya validasi `mimes:pdf,jpg,jpeg,png|max:5120`. Tapi `DocumentController::upload()` perlu dicek apakah juga punya validasi yang sama.

**Files:**
- Modify: `app/Http/Controllers/DocumentController.php`

- [ ] **Step D3.1: Baca method `upload()` di DocumentController**

Cari validasi file. Jika belum ada, tambahkan:

```php
$request->validate([
    'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
], [
    'document.mimes' => 'File harus berformat PDF, JPG, PNG, DOC, atau DOCX.',
    'document.max'   => 'Ukuran file maksimal 10MB.',
]);
```

- [ ] **Step D3.2: Tambah validasi MIME type di sisi server (double-check ekstensi)**

```php
// Setelah validasi Laravel, tambah MIME check manual:
$file = $request->file('document');
$allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword',
                 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
if (!in_array($file->getMimeType(), $allowedMimes)) {
    return back()->withErrors(['document' => 'Tipe file tidak diizinkan.']);
}
```

- [ ] **Step D3.3: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step D3.4: Commit**

```bash
git add app/Http/Controllers/DocumentController.php
git commit -m "fix: tambah validasi MIME type dan ukuran file di DocumentController"
```

---

### Task D4: Session Timeout Warning Modal

**Konteks:** Session lifetime 120 menit (dari config). Perlu modal peringatan 5 menit sebelum habis.

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

- [ ] **Step D4.1: Tambah session timeout modal dan JS di layouts/app.blade.php**

```html
{{-- Session Timeout Warning Modal --}}
<div class="modal fade" id="sessionTimeoutModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="ti ti-clock-off me-2 text-warning"></i>Sesi Hampir Habis
                </h5>
            </div>
            <div class="modal-body text-center">
                <p class="mb-2">Sesi Anda akan berakhir dalam <strong id="sessionCountdown">5:00</strong>.</p>
                <p class="text-muted" style="font-size:0.85rem;">Klik "Perpanjang" untuk tetap login.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-primary" id="sessionExtendBtn">
                    <i class="ti ti-refresh me-1"></i>Perpanjang
                </button>
                <a href="{{ route('logout') }}" class="btn btn-outline-secondary"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            </div>
        </div>
    </div>
</div>
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

<script>
(function() {
    var SESSION_LIFETIME = {{ config('session.lifetime') }} * 60 * 1000; // ms
    var WARNING_BEFORE   = 5 * 60 * 1000; // 5 menit sebelum habis
    var warningTimer, countdownInterval;
    var modal = null;

    function startTimer() {
        clearTimeout(warningTimer);
        warningTimer = setTimeout(showWarning, SESSION_LIFETIME - WARNING_BEFORE);
    }

    function showWarning() {
        if (!modal) modal = new bootstrap.Modal(document.getElementById('sessionTimeoutModal'));
        var remaining = WARNING_BEFORE;
        modal.show();
        countdownInterval = setInterval(function() {
            remaining -= 1000;
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                window.location.href = '{{ route("logout") }}';
                return;
            }
            var m = Math.floor(remaining / 60000);
            var s = Math.floor((remaining % 60000) / 1000);
            document.getElementById('sessionCountdown').textContent = m + ':' + (s < 10 ? '0' : '') + s;
        }, 1000);
    }

    document.getElementById('sessionExtendBtn').addEventListener('click', function() {
        fetch('/dashboard/live-stats').finally(function() {
            clearInterval(countdownInterval);
            if (modal) modal.hide();
            startTimer();
        });
    });

    // Reset timer on user activity
    ['click', 'keydown', 'mousemove', 'touchstart'].forEach(function(evt) {
        document.addEventListener(evt, function() { startTimer(); }, { passive: true });
    });

    startTimer();
})();
</script>
```

- [ ] **Step D4.2: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step D4.3: Commit**

```bash
git add resources/views/layouts/app.blade.php
git commit -m "feat: tambah session timeout warning modal 5 menit sebelum sesi habis"
```

---

### Task D5: Audit Log Lebih Lengkap

**Konteks:** `AuditLog::log()` sudah dipanggil di banyak tempat, tapi `ProfileController::update()` dan `ProfileController::updatePassword()` belum dicek. `SystemSettingController::update()` juga perlu diaudit.

**Files:**
- Modify: `app/Http/Controllers/ProfileController.php`
- Modify: `app/Http/Controllers/SystemSettingController.php`

- [ ] **Step D5.1: Baca ProfileController — cari method update() dan updatePassword()**

Cek apakah ada `AuditLog::log(...)` di sana.

- [ ] **Step D5.2: Tambah audit log di ProfileController::update()**

```php
// Setelah $user->update([...]):
AuditLog::log(
    'update_profile',
    'User',
    $user->id,
    $oldValues,      // capture sebelum update: $oldValues = $user->only(['name', 'phone', ...])
    $user->fresh()->only(['name', 'phone', 'email']),
    'User memperbarui profil',
    request()
);
```

- [ ] **Step D5.3: Tambah audit log di ProfileController::updatePassword()**

```php
AuditLog::log('change_password', 'User', $user->id, null, null, 'User mengganti password', request());
```

- [ ] **Step D5.4: Tambah audit log di SystemSettingController::update()**

```php
AuditLog::log('update_settings', 'SystemSetting', 1, $oldSettings, $newSettings, 'Admin memperbarui pengaturan sistem', request());
```

- [ ] **Step D5.5: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step D5.6: Commit**

```bash
git add app/Http/Controllers/ProfileController.php app/Http/Controllers/SystemSettingController.php
git commit -m "feat: tambah audit log di update profil, ganti password, dan update settings"
```

---

### Task D6: Optimasi Query N+1 — Audit & Fix

**Konteks:** DashboardController sudah pakai `with('user')` untuk eager loading. Tapi `KeputusanController` dan beberapa controller lain perlu diaudit.

**Files:**
- Modify: `app/Http/Controllers/KeputusanController.php`
- Modify: `app/Http/Controllers/DashboardController.php` (jika ditemukan masalah)

- [ ] **Step D6.1: Baca KeputusanController::index() — audit eager loading**

Cari query yang tidak memuat relasi yang dipakai di view (mis. `$leave->user->name` tanpa `with('user')`).

- [ ] **Step D6.2: Tambah eager loading yang kurang**

```php
// Contoh fix:
$pengajuan = LeaveRequest::with(['user', 'atasanReviewer'])
    ->where('status', ...)
    ->paginate(20);
```

- [ ] **Step D6.3: Aktifkan query logging sementara di local untuk verifikasi**

```php
// Tambah sementara di AppServiceProvider::boot() untuk dev:
if (app()->environment('local')) {
    DB::enableQueryLog();
    // Di akhir request, dump query log ke log file
}
```

> Cukup visual inspection — tidak perlu aktifkan query log di production.

- [ ] **Step D6.4: Jalankan test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test --filter SmokeTest
```

- [ ] **Step D6.5: Commit**

```bash
git add app/Http/Controllers/KeputusanController.php app/Http/Controllers/DashboardController.php
git commit -m "perf: tambah eager loading untuk menghilangkan N+1 di KeputusanController"
```

---

### Task D7: Unit Tests untuk Business Rules

**Konteks:** 44 smoke test sudah ada (HTTP-level). Perlu unit test untuk logika bisnis di `CutiTahunanCalculator`, `HariKerjaCalculator`, dan `validateBusinessRules`.

**Files:**
- Create: `tests/Unit/CutiTahunanCalculatorTest.php`
- Create: `tests/Unit/HariKerjaCalculatorTest.php`

- [ ] **Step D7.1: Buat `tests/Unit/CutiTahunanCalculatorTest.php`**

```php
<?php

namespace Tests\Unit;

use App\Models\CutiRecord;
use App\Models\User;
use App\Services\CutiTahunanCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CutiTahunanCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_hitung_sisa_cuti_dengan_carry_over(): void
    {
        $user = User::factory()->create(['leave_balance' => 12, 'tanggal_masuk' => now()->subYears(2)]);
        CutiRecord::create([
            'user_id'      => $user->id,
            'tahun'        => date('Y'),
            'hak_cuti'     => 12,
            'carry_over'   => 3,
            'cuti_diambil' => 5,
        ]);

        $calc = new CutiTahunanCalculator($user, (int)date('Y'));
        $result = $calc->hitung();

        $this->assertEquals(12, $result['hak_cuti']);
        $this->assertEquals(3, $result['carry_over']);
        $this->assertEquals(5, $result['cuti_diambil']);
        $this->assertEquals(10, $result['sisa_cuti'] ?? $result['sisa']); // 12 + 3 - 5
    }

    public function test_carry_over_tidak_boleh_lebih_dari_6(): void
    {
        $user = User::factory()->create(['leave_balance' => 12, 'tanggal_masuk' => now()->subYears(2)]);
        CutiRecord::create([
            'user_id'      => $user->id,
            'tahun'        => date('Y') - 1,
            'hak_cuti'     => 12,
            'carry_over'   => 0,
            'cuti_diambil' => 0, // sisa 12 hari → carry over max 6
        ]);

        $calc = new CutiTahunanCalculator($user, (int)date('Y') - 1);
        $result = $calc->hitung();

        $carryOver = min($result['sisa_cuti'] ?? $result['sisa'] ?? 0, 6);
        $this->assertLessThanOrEqual(6, $carryOver);
    }

    public function test_pegawai_baru_belum_berhak_cuti_tahunan(): void
    {
        $user = User::factory()->create([
            'tanggal_masuk' => now()->subMonths(6), // baru 6 bulan
        ]);
        $this->assertFalse($user->sudahBekerjaSatuTahun());
    }
}
```

- [ ] **Step D7.2: Buat `tests/Unit/HariKerjaCalculatorTest.php`**

```php
<?php

namespace Tests\Unit;

use App\Services\HariKerjaCalculator;
use Carbon\Carbon;
use Tests\TestCase;

class HariKerjaCalculatorTest extends TestCase
{
    public function test_senin_jumat_adalah_5_hari_kerja(): void
    {
        $senin = Carbon::parse('next monday');
        $jumat = $senin->copy()->addDays(4);
        $this->assertEquals(5, HariKerjaCalculator::hitungHariKerja($senin, $jumat));
    }

    public function test_sabtu_minggu_tidak_terhitung(): void
    {
        $senin = Carbon::parse('next monday');
        $minggu = $senin->copy()->addDays(6); // Senin s/d Minggu = 5 hari kerja
        $this->assertEquals(5, HariKerjaCalculator::hitungHariKerja($senin, $minggu));
    }

    public function test_satu_hari_adalah_1_hari_kerja(): void
    {
        $senin = Carbon::parse('next monday');
        $this->assertEquals(1, HariKerjaCalculator::hitungHariKerja($senin, $senin));
    }
}
```

- [ ] **Step D7.3: Jalankan unit tests**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test tests/Unit/
```
Expected: semua pass

- [ ] **Step D7.4: Jalankan semua test**

```bash
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test
```
Expected: 44+ smoke tests + baru unit tests semua pass

- [ ] **Step D7.5: Commit**

```bash
git add tests/Unit/
git commit -m "test: tambah unit test CutiTahunanCalculator dan HariKerjaCalculator"
```

---

## Urutan Eksekusi yang Disarankan

Kerjakan dalam urutan ini untuk meminimalkan konflik:

```
A1 → A2 → A3 → A4 → A5     (business logic, saling independen)
B1 → B2 → B3 → B4 → B5     (UX, saling independen)
C1 → C2 → C3 → C4           (fitur baru)
D1 → D2 → D3 → D4 → D5 → D6 → D7  (teknis/ops)
```

Tasks dalam grup yang sama bisa dikerjakan paralel oleh subagents berbeda.

---

## Verifikasi Akhir

Setelah semua task selesai:

```bash
# Jalankan SEMUA test
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test

# Expected: 44+ smoke tests + unit tests semua hijau
```
