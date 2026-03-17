# Sprint A — Form & Flow Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tingkatkan UX form pengajuan cuti dan kalender dengan 7 fitur: auto-save draft, form collapsed/expanded, preview modal, template alasan, date picker cerdas, conflict warning real-time, dan kalender sidebar panel.

**Architecture:** Progressive enhancement di atas kode yang ada. Alpine.js (sudah tersedia) untuk state management form. Dua endpoint JSON baru ditambahkan ke `routes/web.php`. Satu tabel baru `leave_reason_templates` dengan controller CRUD tersendiri. Tidak ada perubahan pada approval workflow atau model `LeaveRequest`.

**Tech Stack:** Laravel 12, Blade, Alpine.js, Bootstrap 5 / Tabler CSS, SQLite, PHP 8.3

**Spec:** `docs/superpowers/specs/2026-03-17-sprint-a-form-flow-design.md`

---

## Chunk 1: Foundation — Migration, Model, Routes, dan JSON Endpoints

### Task 1: Buat migration tabel `leave_reason_templates`

**Files:**
- Create: `database/migrations/2026_03_17_000001_create_leave_reason_templates_table.php`

- [ ] **Step 1: Buat file migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_reason_templates', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_reason_templates');
    }
};
```

- [ ] **Step 2: Jalankan migration**

```bash
cd /c/laragon/www/sicair && php artisan migrate
```

Expected output: `Migrating: 2026_03_17_000001_create_leave_reason_templates_table` → `Migrated`

- [ ] **Step 3: Commit**

```bash
cd /c/laragon/www/sicair && git add database/migrations/2026_03_17_000001_create_leave_reason_templates_table.php && git commit -m "feat: add leave_reason_templates migration"
```

---

### Task 2: Buat Model `LeaveReasonTemplate`

**Files:**
- Create: `app/Models/LeaveReasonTemplate.php`

- [ ] **Step 1: Tulis model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveReasonTemplate extends Model
{
    protected $fillable = ['label', 'body', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
```

- [ ] **Step 2: Commit**

```bash
cd /c/laragon/www/sicair && git add app/Models/LeaveReasonTemplate.php && git commit -m "feat: add LeaveReasonTemplate model"
```

---

### Task 3: Tambah endpoint JSON `GET /hari-libur/api` di HariLiburController

**Files:**
- Modify: `app/Http/Controllers/HariLiburController.php`

- [ ] **Step 1: Tambah method `apiByYear()` setelah method `index()`**

Buka `app/Http/Controllers/HariLiburController.php`. Tambahkan method berikut setelah penutup `}` method `index()` (sebelum method `create()`).

Catatan: Model `HariLibur` sudah punya method `getHolidaysForYear(int $year)` yang mengembalikan Collection dengan field `tanggal` sebagai Carbon instance — gunakan itu:

```php
/**
 * Return hari libur sebagai JSON array tanggal untuk frontend date picker.
 * Accessible oleh semua role (auth middleware).
 */
public function apiByYear(Request $request)
{
    $year = (int) $request->query('year', date('Y'));
    $tanggal = HariLibur::getHolidaysForYear($year)
        ->pluck('tanggal')
        ->map(fn($t) => $t->format('Y-m-d'));

    return response()->json($tanggal);
}
```

- [ ] **Step 2: Tambah route di `routes/web.php`**

Buka `routes/web.php`. Di dalam blok `Route::middleware('auth')->group(function () {`, tambahkan setelah route `/dashboard/live-stats`:

```php
// === Hari Libur JSON API (semua role) ===
Route::get('/hari-libur/api', [HariLiburController::class, 'apiByYear'])->name('hari-libur.api');
```

Pastikan `HariLiburController` sudah ada di blok `use` atas file (sudah ada karena dipakai oleh route hari-libur admin).

- [ ] **Step 3: Test manual di browser**

Buka `http://sicair.test/hari-libur/api?year=2026` saat login → harus return JSON array.

- [ ] **Step 4: Commit**

```bash
cd /c/laragon/www/sicair && git add app/Http/Controllers/HariLiburController.php routes/web.php && git commit -m "feat: tambah endpoint GET /hari-libur/api?year=YYYY"
```

---

### Task 4: Tambah endpoint JSON `GET /leave-reason-templates/api`

**Files:**
- Create: `app/Http/Controllers/LeaveReasonTemplateController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Buat controller**

```php
<?php

namespace App\Http\Controllers;

use App\Models\LeaveReasonTemplate;
use Illuminate\Http\Request;

class LeaveReasonTemplateController extends Controller
{
    /** JSON endpoint untuk dropdown di form pengajuan (semua role) */
    public function api()
    {
        $templates = LeaveReasonTemplate::active()->get(['id', 'label', 'body']);
        return response()->json($templates);
    }

    /** Halaman CRUD (admin only) */
    public function index()
    {
        $templates = LeaveReasonTemplate::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.leave-reason-templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:100',
            'body'       => 'required|string|max:1000',
            'sort_order' => 'integer|min:0',
        ]);
        LeaveReasonTemplate::create($validated);
        return back()->with('success', 'Template berhasil ditambahkan.');
    }

    public function update(Request $request, LeaveReasonTemplate $leaveReasonTemplate)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:100',
            'body'       => 'required|string|max:1000',
            'sort_order' => 'integer|min:0',
        ]);
        // Checkbox tidak dikirim browser saat unchecked — gunakan boolean() helper
        $validated['is_active'] = $request->boolean('is_active');
        $leaveReasonTemplate->update($validated);
        return back()->with('success', 'Template berhasil diupdate.');
    }

    public function destroy(LeaveReasonTemplate $leaveReasonTemplate)
    {
        $leaveReasonTemplate->delete();
        return back()->with('success', 'Template berhasil dihapus.');
    }

    public function toggleActive(LeaveReasonTemplate $leaveReasonTemplate)
    {
        $leaveReasonTemplate->update(['is_active' => !$leaveReasonTemplate->is_active]);
        return back()->with('success', 'Status template diperbarui.');
    }
}
```

- [ ] **Step 2: Tambah routes di `routes/web.php`**

Di dalam blok `Route::middleware('auth')->group(function () {`, tambahkan setelah route `/hari-libur/api`:

```php
// === Template Alasan Cuti JSON API (semua role) ===
Route::get('/leave-reason-templates/api', [LeaveReasonTemplateController::class, 'api'])->name('leave-reason-templates.api');

// === Template Alasan Cuti CRUD (admin only) ===
Route::middleware('role:admin')->prefix('admin/leave-reason-templates')->name('admin.leave-reason-templates.')->group(function () {
    Route::get('/', [LeaveReasonTemplateController::class, 'index'])->name('index');
    Route::post('/', [LeaveReasonTemplateController::class, 'store'])->name('store');
    Route::put('/{leaveReasonTemplate}', [LeaveReasonTemplateController::class, 'update'])->name('update');
    Route::delete('/{leaveReasonTemplate}', [LeaveReasonTemplateController::class, 'destroy'])->name('destroy');
    Route::post('/{leaveReasonTemplate}/toggle', [LeaveReasonTemplateController::class, 'toggleActive'])->name('toggle');
});
```

Tambah `use` statement di blok `use` atas file (antara `LaporanSaldoCutiController` dan `LeaveRequestController` secara alfabetis):
```php
use App\Http\Controllers\LeaveReasonTemplateController;
```

- [ ] **Step 3: Commit**

```bash
cd /c/laragon/www/sicair && git add app/Http/Controllers/LeaveReasonTemplateController.php routes/web.php && git commit -m "feat: tambah LeaveReasonTemplateController + routes"
```

---

## Chunk 2: Admin CRUD Template Alasan Cuti

### Task 5: Buat view admin CRUD template alasan

**Files:**
- Create: `resources/views/admin/leave-reason-templates/index.blade.php`

- [ ] **Step 1: Buat direktori dan view**

```bash
mkdir -p /c/laragon/www/sicair/resources/views/admin/leave-reason-templates
```

Buat file `resources/views/admin/leave-reason-templates/index.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Template Alasan Cuti - SiCAIR')

@section('content')
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep"><i class="ti ti-chevron-right" style="font-size:0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Template Alasan Cuti</span>
</nav>

<div class="sc-page-header">
    <h2 class="sc-page-title mb-0">Template Alasan Cuti</h2>
    <p class="text-muted" style="font-size:0.85rem;">Kelola template alasan yang tersedia di form pengajuan cuti pegawai.</p>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Form tambah --}}
    <div class="col-lg-4">
        <div class="card sc-card">
            <div class="card-body">
                <h5 class="card-title mb-3">Tambah Template</h5>
                <form method="POST" action="{{ route('admin.leave-reason-templates.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Label <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
                               placeholder="cth: Keperluan Keluarga" value="{{ old('label') }}" maxlength="100" required>
                        @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Isi Template <span class="text-danger">*</span></label>
                        <textarea name="body" rows="4" class="form-control @error('body') is-invalid @enderror"
                                  placeholder="Teks yang akan mengisi kolom alasan..." maxlength="1000" required>{{ old('body') }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Urutan</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                        <div class="form-text">Angka kecil muncul lebih atas.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-plus me-1"></i> Tambah Template
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Daftar template --}}
    <div class="col-lg-8">
        <div class="card sc-card">
            <div class="card-body p-0">
                @if($templates->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="ti ti-file-text" style="font-size:2rem;"></i>
                        <p class="mt-2">Belum ada template. Tambahkan di sebelah kiri.</p>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Urutan</th>
                                <th>Label</th>
                                <th>Isi (preview)</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $tpl)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $tpl->sort_order }}</span></td>
                                <td class="fw-semibold">{{ $tpl->label }}</td>
                                <td class="text-muted" style="font-size:0.82rem;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $tpl->body }}
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.leave-reason-templates.toggle', $tpl) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="badge {{ $tpl->is_active ? 'bg-success' : 'bg-secondary' }} border-0 btn p-1">
                                            {{ $tpl->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    {{-- Edit inline modal --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal" data-bs-target="#editModal{{ $tpl->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    {{-- Hapus --}}
                                    <form method="POST" action="{{ route('admin.leave-reason-templates.destroy', $tpl) }}" class="d-inline"
                                          onsubmit="return confirm('Hapus template ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Edit Modal --}}
                            <div class="modal fade" id="editModal{{ $tpl->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Template</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.leave-reason-templates.update', $tpl) }}">
                                            @csrf @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Label</label>
                                                    <input type="text" name="label" class="form-control" value="{{ $tpl->label }}" maxlength="100" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Isi Template</label>
                                                    <textarea name="body" rows="4" class="form-control" maxlength="1000" required>{{ $tpl->body }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Urutan</label>
                                                    <input type="number" name="sort_order" class="form-control" value="{{ $tpl->sort_order }}" min="0">
                                                </div>
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active_{{ $tpl->id }}" value="1" {{ $tpl->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="is_active_{{ $tpl->id }}">Aktif</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Commit**

```bash
cd /c/laragon/www/sicair && git add resources/views/admin/leave-reason-templates/ && git commit -m "feat: tambah halaman admin CRUD template alasan cuti"
```

---

### Task 6: Tambah link Template Alasan di sidebar admin

**Files:**
- Modify: `resources/views/layouts/app.blade.php` (atau file sidebar yang dipakai)

- [ ] **Step 1: Cari file sidebar**

```bash
cd /c/laragon/www/sicair && grep -r "admin/settings\|hari-libur" resources/views/layouts/ --include="*.blade.php" -l
```

- [ ] **Step 2: Tambah link di sebelah link "Hari Libur" (atau di bawah Settings)**

Cari baris yang berisi link `hari-libur` atau `admin/settings` di sidebar. Tambahkan link baru:

```blade
@if(Auth::user()->isAdmin())
<a href="{{ route('admin.leave-reason-templates.index') }}" class="nav-link {{ request()->routeIs('admin.leave-reason-templates.*') ? 'active' : '' }}">
    <i class="ti ti-file-text nav-link-icon"></i>
    <span>Template Alasan</span>
</a>
@endif
```

- [ ] **Step 3: Commit**

```bash
cd /c/laragon/www/sicair && git add resources/views/layouts/ && git commit -m "feat: tambah link Template Alasan di sidebar admin"
```

---

## Chunk 3: Form Enhancement — Auto-save, Collapsed Sections, Preview Modal

### Task 7: Update `LeaveRequestController` untuk dukung `?start=` dan teruskan ke `selectType`

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php`

- [ ] **Step 1: Update method `selectType()` untuk teruskan `?start=`**

Buka `app/Http/Controllers/LeaveRequestController.php`. Cari method `selectType()` (sekitar baris 32). Signature saat ini adalah `public function selectType()` — TANPA `Request $request`. Ubah menjadi:

```php
public function selectType(Request $request)
{
    $user = Auth::user();
    if (!$user->bolehCuti() && $user->status_pegawai !== 'cpns') {
        $pesan = 'PPPK yang baru dilantik (masa kerja < 1 tahun) belum berhak mengajukan cuti.';
        return redirect('/dashboard')->with('error', $pesan);
    }

    $prefillStart = $request->query('start');
    return view('leave.select-type', compact('prefillStart'));
}
```

Perubahan: tambah `Request $request` di parameter dan kirim `$prefillStart` ke view.

- [ ] **Step 2: Update method `create()` untuk baca `?start=`**

Cari method `create()` (sekitar baris 48). Setelah baris `$type = $request->query('type', LeaveRequest::TYPE_TAHUNAN);`, tambahkan:

```php
$prefillStart = $request->query('start');
```

Cari bagian `return view('leave.create', ...)` di method ini. Tambahkan `$prefillStart` ke array compact atau array data yang dikirim ke view. Contoh jika sudah ada `compact(...)`:

```php
// Ubah dari:
return view('leave.create', compact('type', 'cutiInfo', /* ... */));

// Menjadi (tambah prefillStart):
return view('leave.create', compact('type', 'cutiInfo', /* ... */, 'prefillStart'));
```

Pastikan semua return `view('leave.create', ...)` di method ini menyertakan `$prefillStart`.

- [ ] **Step 3: Commit**

```bash
cd /c/laragon/www/sicair && git add app/Http/Controllers/LeaveRequestController.php && git commit -m "feat: teruskan ?start= di selectType dan create"
```

---

### Task 8: Update `leave/select-type.blade.php` teruskan `?start=` ke link jenis cuti

**Files:**
- Modify: `resources/views/leave/select-type.blade.php`

- [ ] **Step 1: Cek struktur view**

```bash
cd /c/laragon/www/sicair && grep -n "leave.create\|route(" resources/views/leave/select-type.blade.php | head -20
```

- [ ] **Step 2: Update setiap link jenis cuti**

Cari semua link yang mengarah ke `route('leave.create', ...)`. Ubah menjadi:

```blade
{{-- Dari: --}}
href="{{ route('leave.create', ['type' => 'cuti_tahunan']) }}"

{{-- Menjadi: --}}
href="{{ route('leave.create', array_filter(['type' => 'cuti_tahunan', 'start' => $prefillStart ?? null])) }}"
```

Lakukan untuk semua jenis cuti yang ada di view ini. `array_filter` memastikan `start` tidak muncul di URL jika null.

- [ ] **Step 3: Commit**

```bash
cd /c/laragon/www/sicair && git add resources/views/leave/select-type.blade.php && git commit -m "feat: teruskan ?start= dari select-type ke create"
```

---

### Task 9: Pre-fill tanggal dari `?start=` dan cleanup duplikat draft di `create.blade.php`

**Files:**
- Modify: `resources/views/leave/create.blade.php`

**Konteks penting:** `create.blade.php` sudah memiliki TWO draft implementations:
- Lines ~705–778: key `cuti_draft_{{ $type }}` — dengan banner "Lanjutkan draft?" ✅ KEEP
- Lines ~896–918: key `sicair_cuti_draft` — silent restore tanpa banner ❌ REMOVE (duplikat)

Jangan buat implementasi draft baru. Cukup hapus duplikat dan tambah pre-fill `?start=`.

- [ ] **Step 1: Hapus duplikat draft (lines ~896–918)**

Buka `create.blade.php`. Cari blok komentar `// --- Feature 5: Draft localStorage ---` (sekitar baris 896). Hapus seluruh blok berikut:

```js
// --- Feature 5: Draft localStorage ---
var DRAFT_KEY = 'sicair_cuti_draft';
var form = document.querySelector('form');
if (form) {
    var fields = ['start_date','end_date','reason','alasan','address_during_leave','phone_during_leave'];
    var saved = {};
    try { saved = JSON.parse(localStorage.getItem(DRAFT_KEY) || '{}'); } catch(e) {}
    fields.forEach(function(name) {
        var el = form.querySelector('[name="' + name + '"]');
        if (el && saved[name]) el.value = saved[name];
    });
    form.addEventListener('input', function() {
        var data = {};
        fields.forEach(function(name) {
            var el = form.querySelector('[name="' + name + '"]');
            if (el) data[name] = el.value;
        });
        try { localStorage.setItem(DRAFT_KEY, JSON.stringify(data)); } catch(e) {}
    });
    form.addEventListener('submit', function() {
        try { localStorage.removeItem(DRAFT_KEY); } catch(e) {}
    });
}
```

- [ ] **Step 2: Tambah pre-fill tanggal mulai dari `?start=`**

Cari input `name="start_date"` di form. Ubah atau tambah atribut `value`:

```blade
{{-- Cari: --}}
<input type="date" name="start_date" ...>

{{-- Tambahkan value (jaga old() sebagai fallback): --}}
<input type="date" name="start_date" value="{{ $prefillStart ?? old('start_date') }}" ...>
```

- [ ] **Step 3: Commit**

```bash
cd /c/laragon/www/sicair && git add resources/views/leave/create.blade.php && git commit -m "feat: hapus duplikat draft dan tambah pre-fill ?start= di form cuti"
```

---

### Task 10: Enhance preview/konfirmasi modal yang sudah ada di `create.blade.php`

**Files:**
- Modify: `resources/views/leave/create.blade.php`

**Konteks penting:** Modal konfirmasi sudah ada sebagai `#sc-confirm-modal` dengan button `#sc-confirm-btn` (hidden). Tidak perlu buat modal baru — cukup enhance agar modal menampilkan ringkasan detail pengajuan sebelum kirim.

Catatan: `$typeLabel` dicompute via `@php` block di atas view (baris ~6-8). Jangan hapus atau pindahkan `@php` block itu.

- [ ] **Step 1: Cari JS yang mengisi `sc-confirm-body`**

```bash
cd /c/laragon/www/sicair && grep -n "sc-confirm-body\|confirmBtn\|confirmSubmit" resources/views/leave/create.blade.php
```

- [ ] **Step 2: Update isi modal `sc-confirm-body` agar tampilkan ringkasan**

Cari di JS section bagian yang mengisi `sc-confirm-body` (sekitar baris 924-940). Update agar menampilkan tabel ringkasan:

Catatan: `workingDaysBadge` baru dibuat di Task 12. Sebelum Task 12 selesai, field hari kerja di modal tampil sebagai '-' — ini expected.

```js
// Ganti atau update baris yang mengisi sc-confirm-body:
var body = document.getElementById('sc-confirm-body');
if (body) {
    var g = function(name) { var el = document.querySelector('[name="' + name + '"]'); return el ? (el.value || '-') : '-'; };
    var daysBadge = document.getElementById('workingDaysBadge');
    var daysText = daysBadge ? daysBadge.textContent.trim() : '-';
    body.innerHTML =
        '<div class="p-3" style="background:var(--sc-gray-50);border-radius:12px;">' +
        '<table class="table table-borderless mb-0" style="font-size:0.9rem;">' +
        '<tr><td class="text-muted" style="width:40%">Jenis Cuti</td><td class="fw-semibold">{{ $typeLabel }}</td></tr>' +
        '<tr><td class="text-muted">Tanggal Mulai</td><td class="fw-semibold">' + g('start_date') + '</td></tr>' +
        '<tr><td class="text-muted">Tanggal Selesai</td><td class="fw-semibold">' + g('end_date') + '</td></tr>' +
        '<tr><td class="text-muted">Hari Kerja</td><td class="fw-semibold">' + daysText + '</td></tr>' +
        '<tr><td class="text-muted">Alasan</td><td class="fw-semibold" style="white-space:pre-line;">' + (g('reason').substring(0,100) + (g('reason').length > 100 ? '…' : '')) + '</td></tr>' +
        '</table></div>';
}
```

- [ ] **Step 3: Commit**

```bash
cd /c/laragon/www/sicair && git add resources/views/leave/create.blade.php && git commit -m "feat: enhance modal konfirmasi dengan ringkasan pengajuan"
```

---

### Task 11: Ganti opsi hardcoded template alasan dengan data dari API

**Files:**
- Modify: `resources/views/leave/create.blade.php`

**Konteks penting:** Dropdown template alasan sudah ada dengan ID `sc-reason-template` (baris ~251) dengan opsi hardcoded. Juga sudah ada JS handler di baris ~885-894 yang handle `change` event. Tugasnya adalah: hapus opsi hardcoded dan load dari API `/leave-reason-templates/api`.

- [ ] **Step 1: Hapus opsi hardcoded di dropdown `sc-reason-template`**

Cari elemen (sekitar baris 251-258):

```blade
<select id="sc-reason-template" class="form-select form-select-sm" style="border-radius:8px; font-size:0.85rem;">
    <option value="">-- Pilih template alasan (opsional) --</option>
    <option value="Keperluan keluarga yang mendesak...">Keperluan keluarga mendesak</option>
    {{-- ... semua opsi hardcoded ... --}}
</select>
```

Ubah menjadi (hanya satu opsi placeholder, sisanya load via JS):

```blade
<select id="sc-reason-template" class="form-select form-select-sm" style="border-radius:8px; font-size:0.85rem; display:none;">
    <option value="">-- Gunakan Template Alasan --</option>
</select>
```

Tambah `display:none` sementara — akan ditampilkan oleh JS setelah API berhasil load.

- [ ] **Step 2: Tambah JS untuk load opsi dari API**

Di dalam blok `@push('scripts')`, tambahkan setelah JS yang sudah ada:

```js
// Load template alasan dari API (menggantikan opsi hardcoded)
(async function() {
    try {
        var res = await fetch('/leave-reason-templates/api', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error();
        var templates = await res.json();
        var sel = document.getElementById('sc-reason-template');
        if (!sel || !templates.length) return;
        templates.forEach(function(t) {
            var opt = document.createElement('option');
            opt.value = t.body;
            opt.textContent = t.label;
            sel.appendChild(opt);
        });
        sel.style.display = ''; // tampilkan sekarang ada data
    } catch(e) {
        // Gagal — dropdown tetap tersembunyi, user isi manual
    }
})();
```

Catatan: JS handler `change` untuk `sc-reason-template` sudah ada di baris ~885-894, tidak perlu ditambah.

- [ ] **Step 3: Commit**

```bash
cd /c/laragon/www/sicair && git add resources/views/leave/create.blade.php && git commit -m "feat: ganti opsi hardcoded template alasan dengan data dari API"
```

---

## Chunk 4: Date Picker Cerdas dan Conflict Warning

### Task 12: Date picker cerdas — hitung hari kerja dan warning libur

**Files:**
- Modify: `resources/views/leave/create.blade.php`

- [ ] **Step 1: Cari input tanggal mulai dan selesai**

```bash
cd /c/laragon/www/sicair && grep -n "start_date\|end_date" resources/views/leave/create.blade.php | head -20
```

- [ ] **Step 2: Tambah badge hari kerja dan sembunyikan elemen durasi lama**

Tepat setelah `<input type="date" name="end_date" ...>`, tambahkan:

```blade
<div id="workingDaysBadge" class="mt-1" style="font-size:0.82rem; display:none;"></div>
<div id="holidayWarning" class="alert alert-warning py-1 px-2 mt-1" style="font-size:0.82rem; display:none; border-radius:8px;">
    <i class="ti ti-alert-triangle me-1"></i>
    <span id="holidayWarningText"></span>
</div>
```

Elemen `#sc-duration-info` (baris ~361) dan `#duration-preview` (baris ~375) menampilkan hitungan hari lama (kalender biasa, bukan hari kerja). Sembunyikan keduanya dengan menambah `style="display:none!important;"` pada atribut style masing-masing, atau hapus seluruh elemen tersebut dari HTML karena sudah digantikan oleh `#workingDaysBadge`.

- [ ] **Step 3: Tambah JS date picker di `@push('scripts')`**

```js
// Date picker cerdas — hitung hari kerja (skip weekend + libur nasional)
(async () => {
    let holidays = [];
    const year = new Date().getFullYear();
    try {
        const res = await fetch('/hari-libur/api?year=' + year, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (res.ok) holidays = await res.json();
    } catch(e) { /* fallback: hanya skip weekend */ }

    // Gunakan 'T00:00:00' untuk hindari timezone UTC offset (WIB = UTC+7)
    function toDateStr(d) {
        return d.getFullYear() + '-' +
               String(d.getMonth() + 1).padStart(2, '0') + '-' +
               String(d.getDate()).padStart(2, '0');
    }
    function isHoliday(dateStr) { return holidays.includes(dateStr); }
    function isWeekend(dateStr) { const d = new Date(dateStr + 'T00:00:00'); return d.getDay() === 0 || d.getDay() === 6; }
    function countWorkingDays(start, end) {
        let count = 0;
        let cur = new Date(start + 'T00:00:00');
        const endDate = new Date(end + 'T00:00:00');
        while (cur <= endDate) {
            const s = toDateStr(cur);
            if (!isWeekend(s) && !isHoliday(s)) count++;
            cur.setDate(cur.getDate() + 1);
        }
        return count;
    }

    function updateDateInfo() {
        const startEl = document.querySelector('[name="start_date"]');
        const endEl   = document.querySelector('[name="end_date"]');
        const badge   = document.getElementById('workingDaysBadge');
        const warn    = document.getElementById('holidayWarning');
        const warnTxt = document.getElementById('holidayWarningText');
        if (!startEl || !endEl || !badge) return;
        const s = startEl.value, e = endEl.value;

        // Warning hari libur
        if (s && isHoliday(s)) {
            warn.style.display = ''; warnTxt.textContent = 'Tanggal mulai adalah hari libur.';
        } else if (e && isHoliday(e)) {
            warn.style.display = ''; warnTxt.textContent = 'Tanggal selesai adalah hari libur.';
        } else {
            if (warn) warn.style.display = 'none';
        }

        // Badge hari kerja
        if (s && e && s <= e) {
            const days = countWorkingDays(s, e);
            badge.style.display = '';
            badge.innerHTML = '<span class="badge bg-primary-subtle text-primary">' +
                '<i class="ti ti-calendar-check me-1"></i>' + days + ' hari kerja</span>';
        } else {
            badge.style.display = 'none';
        }
    }

    document.querySelector('[name="start_date"]')?.addEventListener('change', updateDateInfo);
    document.querySelector('[name="end_date"]')?.addEventListener('change', updateDateInfo);
    updateDateInfo(); // run on load jika ada prefill
})();
```

- [ ] **Step 4: Commit**

```bash
cd /c/laragon/www/sicair && git add resources/views/leave/create.blade.php && git commit -m "feat: date picker cerdas - hitung hari kerja dan warning libur"
```

---

### Task 13: Verifikasi conflict warning sudah ada (NO-OP)

**Files:** Tidak ada yang diubah.

**Konteks:** Conflict warning sudah fully implemented dengan ID `#conflict-banner` dan `#conflict-message` (sekitar baris 367-370 di `create.blade.php`). Fungsi `checkConflict()` sudah ada (sekitar baris 537-585) dan sudah terhubung ke event `change` pada `start_date` dan `end_date`. Fitur ini sudah selesai.

- [ ] **Step 1: Verifikasi conflict warning berjalan**

```bash
cd /c/laragon/www/sicair && grep -n "conflict-banner\|checkConflict\|check-conflict" resources/views/leave/create.blade.php | head -10
```

Expected: menemukan `#conflict-banner` element dan fungsi `checkConflict`. Tidak ada perubahan kode diperlukan.

- [ ] **Step 2: Commit (tidak ada perubahan)**

Task ini adalah verifikasi saja. Tidak ada commit.

---

## Chunk 5: Kalender Sidebar Panel

### Task 14: Buat Blade component `leave-sidebar-panel`

**Files:**
- Create: `resources/views/components/leave-sidebar-panel.blade.php`

- [ ] **Step 1: Buat file component**

```bash
mkdir -p /c/laragon/www/sicair/resources/views/components
```

```blade
{{-- resources/views/components/leave-sidebar-panel.blade.php --}}
{{-- Slide-in panel kalender. Dikontrol via JS: showSidePanel(dateStr, leaves, holidays) --}}
<div id="calSidePanel"
     style="position:fixed; top:0; right:0; height:100vh; width:320px; background:#fff;
            box-shadow:-4px 0 24px rgba(0,0,0,0.12); transform:translateX(100%);
            transition:transform 0.25s ease; z-index:1055; overflow-y:auto;"
     aria-label="Detail tanggal">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom" style="background:var(--sc-primary); color:#fff;">
        <div>
            <div id="panelDateLabel" class="fw-bold" style="font-size:1rem;"></div>
            <div id="panelHolidayLabel" class="mt-1" style="font-size:0.8rem; opacity:0.85;"></div>
        </div>
        <button type="button" onclick="closeSidePanel()" class="btn btn-sm" style="color:#fff; background:rgba(255,255,255,0.2); border-radius:8px;" aria-label="Tutup panel">
            <i class="ti ti-x"></i>
        </button>
    </div>

    {{-- Pegawai yang cuti --}}
    <div class="p-3">
        <div class="fw-semibold mb-2" style="font-size:0.85rem; color:var(--sc-text-muted);">YANG CUTI HARI INI</div>
        <div id="panelLeaveList">
            <div class="text-muted" style="font-size:0.85rem;">Tidak ada yang cuti.</div>
        </div>
    </div>

    {{-- Tombol ajukan cuti --}}
    <div class="p-3 border-top">
        <a id="panelAjukanBtn" href="#" class="btn btn-primary w-100" style="border-radius:10px;">
            <i class="ti ti-calendar-plus me-2"></i>Ajukan Cuti Tanggal Ini
        </a>
    </div>
</div>

{{-- Overlay --}}
<div id="calSidePanelOverlay"
     onclick="closeSidePanel()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.3); z-index:1054;"></div>
```

- [ ] **Step 2: Commit**

```bash
cd /c/laragon/www/sicair && git add resources/views/components/leave-sidebar-panel.blade.php && git commit -m "feat: buat Blade component leave-sidebar-panel"
```

---

### Task 15: Integrasikan sidebar panel ke `kalender/index.blade.php`

**Files:**
- Modify: `resources/views/kalender/index.blade.php`

- [ ] **Step 1: Tambah component di akhir `@section('content')` sebelum `@endsection`**

```bash
cd /c/laragon/www/sicair && grep -n "endsection\|@endsection" resources/views/kalender/index.blade.php | tail -5
```

Tambahkan sebelum `@endsection`:

```blade
{{-- Sidebar Panel --}}
<x-leave-sidebar-panel />
```

- [ ] **Step 2: Cari event handler klik tanggal di kalender**

```bash
cd /c/laragon/www/sicair && grep -n "click\|dayClick\|dateClick\|calCell" resources/views/kalender/index.blade.php | head -20
```

- [ ] **Step 3: Tambah JS sidebar panel di `@push('scripts')`**

```js
// Sidebar panel functions
function showSidePanel(dateStr, leavesOnDay, holidayLabel) {
    const panel   = document.getElementById('calSidePanel');
    const overlay = document.getElementById('calSidePanelOverlay');
    if (!panel) return;

    // Format tanggal: "Senin, 17 Maret 2026"
    const d = new Date(dateStr + 'T00:00:00');
    const opts = { weekday:'long', day:'numeric', month:'long', year:'numeric' };
    document.getElementById('panelDateLabel').textContent = d.toLocaleDateString('id-ID', opts);

    // Hari libur label
    const hlEl = document.getElementById('panelHolidayLabel');
    hlEl.textContent = holidayLabel || '';
    hlEl.style.display = holidayLabel ? '' : 'none';

    // Daftar pegawai yang cuti
    const listEl = document.getElementById('panelLeaveList');
    if (leavesOnDay && leavesOnDay.length) {
        listEl.innerHTML = leavesOnDay.map(l => `
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                     style="width:32px;height:32px;flex-shrink:0;color:#fff;font-size:0.75rem;">
                    ${l.name?.charAt(0) ?? '?'}
                </div>
                <div>
                    <div style="font-size:0.85rem;font-weight:600;">${l.name ?? '-'}</div>
                    <div style="font-size:0.75rem;color:#64748b;">${l.type_label ?? ''}</div>
                </div>
            </div>`).join('');
    } else {
        listEl.innerHTML = '<div class="text-muted" style="font-size:0.85rem;">Tidak ada yang cuti.</div>';
    }

    // Tombol ajukan cuti
    document.getElementById('panelAjukanBtn').href = `/leave/select-type?start=${dateStr}`;

    // Tampilkan
    panel.style.transform = 'translateX(0)';
    overlay.style.display = '';

    // Mobile: full width
    if (window.innerWidth < 576) panel.style.width = '100vw';
    else panel.style.width = '320px';
}

function closeSidePanel() {
    const panel   = document.getElementById('calSidePanel');
    const overlay = document.getElementById('calSidePanelOverlay');
    if (panel)   panel.style.transform = 'translateX(100%)';
    if (overlay) overlay.style.display = 'none';
}
```

- [ ] **Step 4: Hook sidebar panel ke `openDayModal()` dan `renderDayModal()`**

Kalender sudah punya fungsi `openDayModal(dateStr)` yang klik pada tanggal memanggil `fetch('/kalender/leaves-for-day?date=...')` lalu `renderDayModal(data)`.

Strategi: modifikasi fungsi `renderDayModal(data)` yang sudah ada untuk JUGA memanggil `showSidePanel()` setelah data tersedia. Kalender modal (`#dayDetailModal`) tetap berjalan seperti biasa — sidebar adalah tambahan di samping.

Cari fungsi `renderDayModal(data)` (sekitar baris 739). Tambahkan di akhir fungsi (sebelum penutup `}`):

```js
// Hook sidebar panel — tampilkan bersamaan dengan modal
(function() {
    var dateStr = document.getElementById('modalDateTitle')?.dataset?.dateStr;
    // ambil dateStr dari closured variable di openDayModal
    // atau dari parameter jika sudah dipass

    // Bangun array leaves dari data yang sudah ada
    var leavesOnDay = [];
    if (data.leaves && data.leaves.length) {
        leavesOnDay = data.leaves.map(function(l) {
            return { name: l.user_name || l.user?.name || '-', type_label: l.type_label || '' };
        });
    }
    var holidayLabel = data.holiday ? data.holiday.keterangan : null;
    showSidePanel(window._calCurrentDate || '', leavesOnDay, holidayLabel);
})();
```

Dan di fungsi `openDayModal(dateStr)`, tambahkan satu baris setelah deklarasi `dateStr` parameter:

```js
window._calCurrentDate = dateStr; // simpan untuk sidebar panel
```

Dengan begitu `renderDayModal` bisa mengakses `dateStr` melalui `window._calCurrentDate`.

- [ ] **Step 5: Commit**

```bash
cd /c/laragon/www/sicair && git add resources/views/kalender/index.blade.php && git commit -m "feat: integrasikan kalender sidebar panel"
```

---

## Chunk 6: Tests

### Task 16: Tambah smoke tests di `SmokeTest.php`

**Files:**
- Modify: `tests/Feature/SmokeTest.php`

- [ ] **Step 1: Tulis failing tests**

Buka `tests/Feature/SmokeTest.php`. Tambahkan method-method berikut sebelum penutup kelas `}`:

```php
// -------------------------------------------------------------------------
// Sprint A: Form & Flow Tests
// -------------------------------------------------------------------------

public function test_hari_libur_api_returns_json_by_year(): void
{
    $user = $this->makeUser(); // role pegawai
    $response = $this->actingAs($user)->get('/hari-libur/api?year=2026');
    $response->assertStatus(200);
    $response->assertJsonIsArray();
}

public function test_leave_reason_templates_api_returns_json(): void
{
    $user = $this->makeUser();
    $response = $this->actingAs($user)->get('/leave-reason-templates/api');
    $response->assertStatus(200);
    $response->assertJsonIsArray();
}

public function test_leave_reason_templates_admin_can_create(): void
{
    $admin = $this->makeAdmin();
    $response = $this->actingAs($admin)->post('/admin/leave-reason-templates', [
        'label'      => 'Keperluan Keluarga',
        'body'       => 'Saya perlu menghadiri acara keluarga yang tidak dapat ditunda.',
        'sort_order' => 1,
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('leave_reason_templates', ['label' => 'Keperluan Keluarga']);
}

public function test_leave_reason_templates_admin_can_delete(): void
{
    $admin = $this->makeAdmin();
    $tpl = \App\Models\LeaveReasonTemplate::create([
        'label'      => 'Template Hapus',
        'body'       => 'Isi template.',
        'is_active'  => true,
        'sort_order' => 0,
    ]);
    $response = $this->actingAs($admin)->delete("/admin/leave-reason-templates/{$tpl->id}");
    $response->assertRedirect();
    $this->assertDatabaseMissing('leave_reason_templates', ['id' => $tpl->id]);
}

public function test_leave_create_accepts_start_query_param(): void
{
    $user = $this->makeUser();
    $response = $this->actingAs($user)->get('/leave/create?type=cuti_tahunan&start=2026-03-17');
    $response->assertStatus(200);
}

public function test_kalender_loads_with_panel_data(): void
{
    $user = $this->makeUser();
    $this->actingAs($user)->get('/kalender')->assertStatus(200);
}

public function test_leave_select_type_accepts_start_query_param(): void
{
    $user = $this->makeUser();
    $response = $this->actingAs($user)->get('/leave/select-type?start=2026-03-17');
    $response->assertStatus(200);
}
```

- [ ] **Step 2: Jalankan tests untuk pastikan fail (sebelum implementasi selesai) atau pass (jika sudah)**

```bash
cd /c/laragon/www/sicair && php artisan test tests/Feature/SmokeTest.php --filter="test_hari_libur_api\|test_leave_reason\|test_leave_create_accepts\|test_kalender_loads" 2>&1 | tail -20
```

- [ ] **Step 3: Jalankan semua tests untuk pastikan tidak ada regresi**

```bash
cd /c/laragon/www/sicair && php artisan test 2>&1 | tail -10
```

Expected: semua tests pass (atau hanya Sprint A tests yang fail jika belum diimplementasi).

- [ ] **Step 4: Commit**

```bash
cd /c/laragon/www/sicair && git add tests/Feature/SmokeTest.php && git commit -m "test: tambah smoke tests Sprint A - form & flow"
```

---

## Final: Push ke Remote

- [ ] **Push semua commit**

```bash
cd /c/laragon/www/sicair && git push
```

---

## Catatan Implementasi

- **Urutan yang benar:** Kerjakan Chunk 1 → 2 → 3 → 4 → 5 → 6. Chunk 1 harus selesai sebelum lainnya karena migration dan routes dibutuhkan.
- **Task 15 (kalender sidebar):** Perlu membaca JS kalender yang sudah ada sebelum hook. Sesuaikan panggilan `showSidePanel()` dengan event yang ada.
- **Task 6 (sidebar link):** Cek nama file sidebar yang benar dengan command di Task 6 Step 1.
- **Chunk 3-4:** Semua perubahan di `create.blade.php` bisa dikerjakan secara incremental — tiap task adalah commit tersendiri.
