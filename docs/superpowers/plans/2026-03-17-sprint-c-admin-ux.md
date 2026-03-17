# Sprint C — Admin UX Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tambahkan name search di `/keputusan` dan bulk "Teruskan ke Ketua" untuk atasan, sekaligus fix pre-existing gap `wakil_ketua` di route middleware.

**Architecture:** Tiga kelompok perubahan server-side: (1) fix route middleware group + endpoint POST baru di `LeaveRequestController`, (2) name filter di `KeputusanController`, (3) UI di dua view file. Tidak ada JS baru, tidak ada DB migration, tidak ada Alpine.js.

**Tech Stack:** Laravel 12, Blade, Bootstrap 5 / Tabler CSS, PHP 8.3, SQLite (test), PHPUnit.

---

> **Catatan untuk implementer:** Semua perintah `php artisan` dijalankan dari root project (`/path/to/sicair`). Jika menjalankan dari shell baru, pastikan `cd` ke direktori project terlebih dahulu.

## Chunk 1: Route Fix + Bulk Pertimbangan Endpoint

### Task 1: Fix route middleware group + tambah route `leave.bulk-pertimbangan`

**Files:**
- Modify: `routes/web.php:182-184`

**Context penting:**
- Grup `role:atasan,panitera,sekretaris,ketua,admin` (lines ~182-184) tidak mencakup `wakil_ketua`, padahal `User::isAtasan()` mencakupnya (lihat `app/Models/User.php:81-84`). Ini menyebabkan `wakil_ketua` dapat 403 saat submit review — pre-existing bug yang diperbaiki di sini.
- Route baru `leave.bulk-pertimbangan` harus ada di grup yang sama.
- **Jangan** letakkan di grup `role:ketua,admin` (lines ~187-190, berisi `bulk-decide`) — itu akan menghalangi atasan.

- [ ] **Step 1: Jalankan smoke test baseline**

```bash
cd /path/to/project && php artisan test --filter SmokeTest
```

Expected: semua test PASS. Catat jumlahnya (sekitar 40 test).

- [ ] **Step 2: Modifikasi `routes/web.php`**

Cari dan ganti blok berikut (lines ~182-184):

```php
// SEBELUM:
Route::middleware('role:atasan,panitera,sekretaris,ketua,admin')->group(function () {
    Route::post('/leave/{leaveRequest}/review', [LeaveRequestController::class, 'reviewAtasan'])->name('leave.review');
});
```

Dengan:

```php
// SESUDAH — tambah wakil_ketua + route baru:
Route::middleware('role:atasan,panitera,sekretaris,wakil_ketua,ketua,admin')->group(function () {
    Route::post('/leave/{leaveRequest}/review', [LeaveRequestController::class, 'reviewAtasan'])->name('leave.review');
    Route::post('/leave/bulk-pertimbangan', [LeaveRequestController::class, 'bulkPertimbangan'])->name('leave.bulk-pertimbangan');
});
```

- [ ] **Step 3: Jalankan smoke test**

```bash
php artisan test --filter SmokeTest
```

Expected: semua PASS. Route baru akan return 500 jika dipanggil (method belum ada di controller), tapi smoke test tidak POST ke sana — jadi test tetap PASS.

- [ ] **Step 4: Commit**

```bash
git add routes/web.php
git commit -m "fix: tambah wakil_ketua ke middleware review + route bulk-pertimbangan"
```

---

### Task 2: Implementasi `bulkPertimbangan()` di `LeaveRequestController`

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php`
- Test: `tests/Feature/SmokeTest.php`

**Context penting:**
- `LeaveRequestController` ada di `app/Http/Controllers/LeaveRequestController.php`. Method `bulkDecide()` sudah ada di sana — lihat sebagai referensi pola.
- Constants yang digunakan: `LeaveRequest::STATUS_DIAJUKAN` (`'diajukan'`), `LeaveRequest::STATUS_PENDING` (`'pending'`), `LeaveRequest::STATUS_PERTIMBANGAN` (`'pertimbangan_atasan'`).
- Test helper: `SmokeTest` punya private method `makeUser(array $overrides)` yang menggunakan `User::create()`. Gunakan ini untuk buat user di test — lihat `tests/Feature/SmokeTest.php:23-40`.
- `LeaveRequestFactory` ada di `database/factories/LeaveRequestFactory.php`. Gunakan `LeaveRequest::factory()->create([...])` untuk buat leave record di test.
- **Setup fixture penting:** test `test_bulk_pertimbangan_updates_status` HARUS membuat `LeaveRequest` dengan `atasan_reviewer_id` = id atasan test DAN status `diajukan`/`pending`. Tanpa ini, loop di controller akan skip semua ID dan `$count === 0`, membuat assertion status change gagal secara silent.
- Guard akses: route middleware sudah handle (tidak perlu `abort(403)` di controller). Query `->where('atasan_reviewer_id', $user->id)` adalah data guard.

- [ ] **Step 1: Tulis 2 failing test di `tests/Feature/SmokeTest.php`**

Tambah di akhir class `SmokeTest` (sebelum penutup `}`):

```php
public function test_bulk_pertimbangan_forbidden_for_pegawai(): void
{
    $pegawai = $this->makeUser(['nip' => '999999999999999991']);
    $this->actingAs($pegawai)
         ->post('/leave/bulk-pertimbangan', ['ids' => [999]])
         ->assertStatus(403);
}

public function test_bulk_pertimbangan_updates_status(): void
{
    $atasan  = $this->makeUser(['role' => 'atasan', 'nip' => '999999999999999992']);
    $pegawai = $this->makeUser(['nip' => '999999999999999993']);

    $leave = \App\Models\LeaveRequest::factory()->create([
        'user_id'            => $pegawai->id,
        'atasan_reviewer_id' => $atasan->id,
        'status'             => \App\Models\LeaveRequest::STATUS_DIAJUKAN,
    ]);

    $this->actingAs($atasan)
         ->post('/leave/bulk-pertimbangan', ['ids' => [$leave->id]])
         ->assertRedirect(route('keputusan.index', ['tab' => 'review']));

    $this->assertDatabaseHas('leave_requests', [
        'id'     => $leave->id,
        'status' => \App\Models\LeaveRequest::STATUS_PERTIMBANGAN,
    ]);
}
```

- [ ] **Step 2: Jalankan test untuk konfirmasi FAIL**

```bash
php artisan test --filter "test_bulk_pertimbangan"
```

Expected: 2 tests FAIL. (`test_bulk_pertimbangan_updates_status` mungkin 500 karena method belum ada.)

- [ ] **Step 3: Tambah method `bulkPertimbangan()` ke `LeaveRequestController`**

Buka `app/Http/Controllers/LeaveRequestController.php`. Tambahkan method berikut setelah method `bulkDecide()`:

```php
/**
 * Bulk teruskan ke ketua (pertimbangan_atasan) oleh Atasan
 */
public function bulkPertimbangan(Request $request)
{
    $request->validate([
        'ids'   => 'required|array|min:1',
        'ids.*' => 'exists:leave_requests,id',
    ]);

    $user  = auth()->user();
    $count = 0;

    foreach ($request->ids as $id) {
        $leave = LeaveRequest::where('id', $id)
            ->where('atasan_reviewer_id', $user->id)
            ->whereIn('status', [
                LeaveRequest::STATUS_DIAJUKAN,
                LeaveRequest::STATUS_PENDING,
            ])
            ->first();

        if (!$leave) continue;

        $leave->status = LeaveRequest::STATUS_PERTIMBANGAN;
        $leave->save();
        $count++;
    }

    return redirect()->route('keputusan.index', ['tab' => 'review'])
        ->with('success', "{$count} pengajuan berhasil diteruskan ke ketua.");
}
```

- [ ] **Step 4: Jalankan test**

```bash
php artisan test --filter "test_bulk_pertimbangan"
```

Expected: 2 tests PASS.

- [ ] **Step 5: Jalankan full smoke test**

```bash
php artisan test --filter SmokeTest
```

Expected: N+2 tests PASS (jumlah baseline + 2 test baru).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/LeaveRequestController.php tests/Feature/SmokeTest.php
git commit -m "feat: tambah endpoint bulk-pertimbangan untuk atasan"
```

---

## Chunk 2: Name Search + UI

### Task 3: Tambah `applyNameFilter()` ke `KeputusanController`

**Files:**
- Modify: `app/Http/Controllers/KeputusanController.php`
- Test: `tests/Feature/SmokeTest.php`

**Context penting:**
- `KeputusanController` ada di `app/Http/Controllers/KeputusanController.php`. Sudah ada method `applyStatusFilter()` dan `applyTypeAndDateFilters()` — ikuti pola void return type + early return yang sama.
- Method baru `applyNameFilter()` dipanggil di:
  - `ketuaView()`: pada `$menunggQuery` dan `$riwayatQuery`
  - `atasanView()`: pada `$reviewQuery` saja — **tidak** pada `$pengajuanQuery`
  - `pegawaiView()`: **tidak dipanggil** sama sekali
- Query: `->whereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"))` — mencari substring di kolom `name` user pemilik leave request.

- [ ] **Step 1: Tulis failing test di `tests/Feature/SmokeTest.php`**

Tambah setelah test-test yang ada:

```php
public function test_keputusan_filter_by_name_for_ketua(): void
{
    $ketua = $this->makeKetua();

    // Buat dua pegawai dengan nama berbeda, keduanya punya leave pertimbangan
    $pegawai1 = $this->makeUser(['name' => 'Zulkifli Harahap', 'nip' => '999999999999999994']);
    $pegawai2 = $this->makeUser(['name' => 'Maria Ningsih',    'nip' => '999999999999999995']);
    \App\Models\LeaveRequest::factory()->create([
        'user_id' => $pegawai1->id,
        'status'  => \App\Models\LeaveRequest::STATUS_PERTIMBANGAN,
    ]);
    \App\Models\LeaveRequest::factory()->create([
        'user_id' => $pegawai2->id,
        'status'  => \App\Models\LeaveRequest::STATUS_PERTIMBANGAN,
    ]);

    // Cari hanya "Zulkifli" — Maria Ningsih seharusnya tidak muncul
    $this->actingAs($ketua)
         ->get('/keputusan?q=Zulkifli')
         ->assertStatus(200)
         ->assertSee('Zulkifli Harahap')
         ->assertDontSee('Maria Ningsih');
}
```

- [ ] **Step 2: Jalankan test untuk konfirmasi FAIL**

```bash
php artisan test --filter "test_keputusan_filter_by_name"
```

Expected: FAIL — karena tanpa `applyNameFilter()`, query mengembalikan semua record (termasuk Maria Ningsih), sehingga `assertDontSee('Maria Ningsih')` gagal.

- [ ] **Step 3: Tambah method `applyNameFilter()` ke `KeputusanController`**

Tambah private method berikut di `KeputusanController`, setelah `applyTypeAndDateFilters()`:

```php
private function applyNameFilter($query, Request $request): void
{
    if (!$request->filled('q')) return;
    $q = $request->q;
    $query->whereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
}
```

- [ ] **Step 4: Panggil `applyNameFilter()` di `ketuaView()`**

Cari blok ini di `ketuaView()` (lines ~38-47):

```php
if ($tab === 'menunggu') {
    $this->applyTypeAndDateFilters($menunggQuery, $request);
    $menunggu = $menunggQuery->paginate(15)->withQueryString();
    $riwayat  = collect();
} else {
    $this->applyStatusFilter($riwayatQuery, $request);
    $this->applyTypeAndDateFilters($riwayatQuery, $request);
    $riwayat  = $riwayatQuery->paginate(15)->withQueryString();
    $menunggu = collect();
}
```

Ganti dengan:

```php
if ($tab === 'menunggu') {
    $this->applyTypeAndDateFilters($menunggQuery, $request);
    $this->applyNameFilter($menunggQuery, $request);
    $menunggu = $menunggQuery->paginate(15)->withQueryString();
    $riwayat  = collect();
} else {
    $this->applyStatusFilter($riwayatQuery, $request);
    $this->applyTypeAndDateFilters($riwayatQuery, $request);
    $this->applyNameFilter($riwayatQuery, $request);
    $riwayat  = $riwayatQuery->paginate(15)->withQueryString();
    $menunggu = collect();
}
```

- [ ] **Step 5: Panggil `applyNameFilter()` di `atasanView()` — hanya untuk `$reviewQuery`**

Cari blok ini di `atasanView()` (lines ~62-72):

```php
if ($tab === 'review') {
    $this->applyStatusFilter($reviewQuery, $request);
    $this->applyTypeAndDateFilters($reviewQuery, $request);
    $review    = $reviewQuery->paginate(15)->withQueryString();
    $pengajuan = collect();
} else {
    $this->applyStatusFilter($pengajuanQuery, $request);
    $this->applyTypeAndDateFilters($pengajuanQuery, $request);
    $pengajuan = $pengajuanQuery->paginate(15)->withQueryString();
    $review    = collect();
}
```

Ganti dengan:

```php
if ($tab === 'review') {
    $this->applyStatusFilter($reviewQuery, $request);
    $this->applyTypeAndDateFilters($reviewQuery, $request);
    $this->applyNameFilter($reviewQuery, $request);
    $review    = $reviewQuery->paginate(15)->withQueryString();
    $pengajuan = collect();
} else {
    $this->applyStatusFilter($pengajuanQuery, $request);
    $this->applyTypeAndDateFilters($pengajuanQuery, $request);
    $pengajuan = $pengajuanQuery->paginate(15)->withQueryString();
    $review    = collect();
}
```

**Penting:** Tidak ada perubahan di `pegawaiView()`.

- [ ] **Step 6: Jalankan full smoke test**

```bash
php artisan test --filter SmokeTest
```

Expected: N+3 tests PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/KeputusanController.php tests/Feature/SmokeTest.php
git commit -m "feat: tambah name filter di KeputusanController"
```

---

### Task 4: Update `_filter.blade.php` dengan name search input

**Files:**
- Modify: `resources/views/keputusan/_filter.blade.php`

**Context penting:**
- File ini menggunakan pola `@if($showX ?? false)` — semua input bersifat conditional.
- Input baru `q` mengikuti pola yang sama dengan variable `$showNameSearch`.
- Tambah input **sebelum** div tombol submit (yang sudah ada).

- [ ] **Step 1: Modifikasi `_filter.blade.php`**

Cari dan ganti div tombol submit berikut (lines ~48-54):

```blade
            <div class="col-sm-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm sc-btn-primary">
                    <i class="ti ti-search me-1"></i> Filter
                </button>
                <a href="{{ route('keputusan.index', request('tab') ? ['tab' => request('tab')] : []) }}"
                   class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
```

Dengan (tambahkan blok `@if` di atas div tombol, lalu pertahankan div tombol):

```blade
            @if($showNameSearch ?? false)
            <div class="col-sm-auto">
                <label class="form-label mb-1" style="font-size:0.8rem;">Nama Pegawai</label>
                <input type="text"
                       name="q"
                       class="form-control form-control-sm"
                       placeholder="Cari nama..."
                       value="{{ request('q') }}"
                       style="min-width:160px;">
            </div>
            @endif
            <div class="col-sm-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm sc-btn-primary">
                    <i class="ti ti-search me-1"></i> Filter
                </button>
                <a href="{{ route('keputusan.index', request('tab') ? ['tab' => request('tab')] : []) }}"
                   class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
```

- [ ] **Step 2: Jalankan smoke test**

```bash
php artisan test --filter SmokeTest
```

Expected: semua PASS (input tidak muncul karena belum ada call site yang pass `showNameSearch`).

- [ ] **Step 3: Commit**

```bash
git add resources/views/keputusan/_filter.blade.php
git commit -m "feat: tambah name search input di filter keputusan"
```

---

### Task 5: Update `index.blade.php` — pass `showNameSearch` + tambah bulk form atasan

**Files:**
- Modify: `resources/views/keputusan/index.blade.php`

**Context penting tentang struktur file ini:**
- Ada 3 `@include('keputusan._filter', [...])` di file ini:
  - **Line ~54**: ketua view — shared `@include` sebelum tab conditionals. `showStatus` sudah conditional berdasarkan tab.
  - **Line ~192**: atasan view — shared `@include` sebelum tab conditionals. Berlaku untuk kedua tab.
  - **Line ~257**: pegawai view — tidak perlu `showNameSearch`.
- Atasan review tab (lines ~194-248): layout berupa **card-based** (`@foreach($review as $req)` dengan div cards), **bukan table**. Variabel loop: `$req` (bukan `$leave`). Struktur `@else` (mulai line ~201) berisi `@foreach` dan diakhiri `{{ $review->links() }}` + `@endif`.
- **Catatan:** Spec mendeskripsikan UI bulk dengan elemen `<thead>/<th>` (table), tapi DOM aktual adalah card-based. Plan ini mengimplementasikan checkboxes di dalam card (bukan di table cell) sesuai DOM nyata.
- JS di `@push('scripts')` (line ~264) sudah punya null guard: `if (bar)` — aman menggunakan class `sc-bulk-cb` di atasan view meskipun tidak ada `#sc-bulk-actions` div.
- Checkbox harus pakai class `sc-bulk-cb form-check-input` (sama dengan ketua) agar JS existing tetap berfungsi.
- Select-all: tambahkan di atas `@foreach`, berdampingan dengan tombol submit.

**Perubahan 1: Update `@include` ketua (line ~54) — tambah `showNameSearch`**

- [ ] **Step 1: Tambah `showNameSearch` ke @include ketua**

Cari (lines ~54-58):

```blade
    @include('keputusan._filter', [
        'showStatus' => ($tab ?? 'menunggu') === 'riwayat',
        'showType'   => true,
        'showDate'   => true,
    ])
```

Ganti dengan:

```blade
    @include('keputusan._filter', [
        'showStatus'     => ($tab ?? 'menunggu') === 'riwayat',
        'showType'       => true,
        'showDate'       => true,
        'showNameSearch' => true,
    ])
```

**Perubahan 2: Update `@include` atasan (line ~192) — tambah `showNameSearch`**

- [ ] **Step 2: Tambah `showNameSearch` ke @include atasan**

Cari (line ~192):

```blade
    @include('keputusan._filter', ['showStatus' => true, 'showType' => true, 'showDate' => true])
```

Ganti dengan:

```blade
    @include('keputusan._filter', ['showStatus' => true, 'showType' => true, 'showDate' => true, 'showNameSearch' => true])
```

- [ ] **Step 3: Jalankan smoke test (sanity check)**

```bash
php artisan test --filter SmokeTest
```

Expected: semua PASS.

- [ ] **Step 4: Commit @include changes**

```bash
git add resources/views/keputusan/index.blade.php
git commit -m "feat: pass showNameSearch ke filter includes di keputusan"
```

**Perubahan 3: Tambah bulk form di atasan review tab**

- [ ] **Step 5: Wrap `@else` block atasan review dengan `<form>` + tambah checkbox + submit button**

Cari dan ganti seluruh blok `@else` hingga `@endif` **pertama** di atasan review tab (lines ~201-247). Hati-hati: ada dua `@endif` berturut-turut di lines ~247-248 — yang perlu diganti adalah `@endif` pertama (line ~247) yang menutup `@if(!isset($review)...)`. `@endif` kedua (line ~248) menutup outer tab `@if` dan harus tetap ada.

Blok **SEBELUM** yang harus dicari (lines 201-247, verbatim):

```blade
        @else
        @foreach($review as $req)
        <div class="card sc-history-card status-{{ $req->status }} mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="sc-user-avatar" style="width:40px;height:40px;font-size:0.8rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:10px;">
                            {{ strtoupper(substr(optional($req->user)->name ?? 'N/A', 0, 2)) }}
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:0.95rem;">{{ optional($req->user)->name ?? 'N/A' }}</div>
                            <div class="text-muted" style="font-size:0.78rem;">{{ optional($req->user)->jabatan ?? optional($req->user)->nip ?? '-' }}</div>
                        </div>
                    </div>
                    <span class="sc-badge sc-badge-{{ in_array($req->status, ['disetujui','approved']) ? 'approved' : (in_array($req->status, ['ditolak','rejected']) ? 'rejected' : 'pending') }}">
                        {{ $req->status_label ?? ucfirst($req->status) }}
                    </span>
                </div>
                <div class="row g-2 mb-2" style="font-size:0.82rem;">
                    <div class="col-sm-4"><i class="ti ti-tag me-1 text-muted"></i>{{ $req->type_label ?? ucfirst($req->type) }}</div>
                    <div class="col-sm-4"><i class="ti ti-calendar me-1 text-muted"></i>{{ $req->start_date->format('d M Y') }}</div>
                    <div class="col-sm-4"><i class="ti ti-clock me-1 text-muted"></i>{{ $req->total_days ?? $req->total_hari_kerja ?? '-' }} hari</div>
                </div>
                <div style="background:var(--sc-primary-light);border-radius:8px;padding:0.45rem 0.75rem;font-size:0.82rem;">
                    <i class="ti ti-user-check me-1" style="color:var(--sc-primary);"></i>
                    <strong>Pertimbangan Anda:</strong>
                    {{ ucfirst($req->pertimbangan_atasan ?? '-') }}
                    @if($req->catatan_atasan) &mdash; {{ Str::limit($req->catatan_atasan, 80) }} @endif
                </div>
                @if($req->pejabat)
                <div class="mt-2" style="font-size:0.8rem;color:var(--sc-muted);">
                    <i class="ti ti-gavel me-1"></i>
                    Keputusan {{ $req->pejabat->name }}:
                    <strong>{{ ucfirst($req->keputusan_pejabat ?? '-') }}</strong>
                    @if($req->catatan_pejabat) &mdash; {{ Str::limit($req->catatan_pejabat, 60) }} @endif
                </div>
                @endif
                <div class="mt-2">
                    <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:0.8rem;">
                        <i class="ti ti-eye me-1"></i> Detail
                    </a>
                </div>
            </div>
        </div>
        @endforeach
        {{ $review->links() }}
        @endif
```

Ganti dengan (**SESUDAH**):

```blade
        @else
        <form method="POST" action="{{ route('leave.bulk-pertimbangan') }}">
            @csrf
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <label class="d-flex align-items-center gap-2" style="font-size:0.85rem;cursor:pointer;">
                    <input type="checkbox"
                           class="form-check-input"
                           style="width:18px;height:18px;"
                           onclick="document.querySelectorAll('.sc-bulk-cb').forEach(cb => cb.checked = this.checked)">
                    Pilih Semua
                </label>
                <button type="submit" class="btn btn-sm sc-btn-primary">
                    <i class="ti ti-send me-1"></i> Teruskan ke Ketua
                </button>
            </div>
        @foreach($review as $req)
        <div class="card sc-history-card status-{{ $req->status }} mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        @if(in_array($req->status, [\App\Models\LeaveRequest::STATUS_DIAJUKAN, \App\Models\LeaveRequest::STATUS_PENDING]))
                        <input type="checkbox" name="ids[]" value="{{ $req->id }}"
                            class="sc-bulk-cb form-check-input" style="width:18px;height:18px;flex-shrink:0;margin-top:0;">
                        @else
                        <div style="width:18px;flex-shrink:0;"></div>
                        @endif
                        <div class="sc-user-avatar" style="width:40px;height:40px;font-size:0.8rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:10px;">
                            {{ strtoupper(substr(optional($req->user)->name ?? 'N/A', 0, 2)) }}
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:0.95rem;">{{ optional($req->user)->name ?? 'N/A' }}</div>
                            <div class="text-muted" style="font-size:0.78rem;">{{ optional($req->user)->jabatan ?? optional($req->user)->nip ?? '-' }}</div>
                        </div>
                    </div>
                    <span class="sc-badge sc-badge-{{ in_array($req->status, ['disetujui','approved']) ? 'approved' : (in_array($req->status, ['ditolak','rejected']) ? 'rejected' : 'pending') }}">
                        {{ $req->status_label ?? ucfirst($req->status) }}
                    </span>
                </div>
                <div class="row g-2 mb-2" style="font-size:0.82rem;">
                    <div class="col-sm-4"><i class="ti ti-tag me-1 text-muted"></i>{{ $req->type_label ?? ucfirst($req->type) }}</div>
                    <div class="col-sm-4"><i class="ti ti-calendar me-1 text-muted"></i>{{ $req->start_date->format('d M Y') }}</div>
                    <div class="col-sm-4"><i class="ti ti-clock me-1 text-muted"></i>{{ $req->total_days ?? $req->total_hari_kerja ?? '-' }} hari</div>
                </div>
                <div style="background:var(--sc-primary-light);border-radius:8px;padding:0.45rem 0.75rem;font-size:0.82rem;">
                    <i class="ti ti-user-check me-1" style="color:var(--sc-primary);"></i>
                    <strong>Pertimbangan Anda:</strong>
                    {{ ucfirst($req->pertimbangan_atasan ?? '-') }}
                    @if($req->catatan_atasan) &mdash; {{ Str::limit($req->catatan_atasan, 80) }} @endif
                </div>
                @if($req->pejabat)
                <div class="mt-2" style="font-size:0.8rem;color:var(--sc-muted);">
                    <i class="ti ti-gavel me-1"></i>
                    Keputusan {{ $req->pejabat->name }}:
                    <strong>{{ ucfirst($req->keputusan_pejabat ?? '-') }}</strong>
                    @if($req->catatan_pejabat) &mdash; {{ Str::limit($req->catatan_pejabat, 60) }} @endif
                </div>
                @endif
                <div class="mt-2">
                    <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:0.8rem;">
                        <i class="ti ti-eye me-1"></i> Detail
                    </a>
                </div>
            </div>
        </div>
        @endforeach
        </form>
        {{ $review->links() }}
        @endif
```

- [ ] **Step 6: Jalankan SmokeTest**

```bash
php artisan test --filter SmokeTest
```

Expected: N+3 tests PASS, 0 failures.

- [ ] **Step 7: Commit**

```bash
git add resources/views/keputusan/index.blade.php
git commit -m "feat: tambah bulk pertimbangan form di atasan review tab"
```
