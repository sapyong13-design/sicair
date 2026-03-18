# Halaman Keputusan Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Buat halaman `/keputusan` tersendiri yang menampilkan konten keputusan/riwayat sesuai role, sederhanakan dashboard, dan perbarui navbar.

**Architecture:** Controller baru `KeputusanController` dengan satu method `index()` yang mem-branch berdasarkan role (admin/ketua → atasan → pegawai). Dashboard hanya menampilkan preview 5 item dengan link "Lihat semua". View tunggal `keputusan/index.blade.php` render konten berbeda per role menggunakan `@if`.

**Tech Stack:** Laravel 12, Blade, Bootstrap 5/Tabler CSS, SQLite, PHP 8.3

**Spec:** `docs/superpowers/specs/2026-03-17-halaman-keputusan-design.md`

---

## Chunk 1: Foundation — Controller, Route, Tests

### Task 1: Tambah smoke tests untuk `/keputusan`

**Files:**
- Modify: `tests/Feature/SmokeTest.php`

- [ ] **Step 1: Tambah test methods di SmokeTest.php**

Buka `tests/Feature/SmokeTest.php`. Tambahkan setelah method terakhir yang ada (sebelum penutup kelas `}`):

```php
public function test_keputusan_redirects_for_guest(): void
{
    $this->get('/keputusan')->assertRedirect('/login');
}

public function test_keputusan_loads_for_pegawai(): void
{
    $user = $this->makeUser();
    $this->actingAs($user)->get('/keputusan')->assertStatus(200);
}

public function test_keputusan_loads_for_ketua(): void
{
    $ketua = $this->makeKetua();
    $this->actingAs($ketua)->get('/keputusan')->assertStatus(200);
}

public function test_keputusan_loads_for_atasan(): void
{
    $atasan = $this->makeUser(['role' => 'sekretaris', 'nip' => '199001012020011002']);
    $this->actingAs($atasan)->get('/keputusan')->assertStatus(200);
}

public function test_keputusan_loads_for_admin(): void
{
    $admin = $this->makeAdmin();
    $this->actingAs($admin)->get('/keputusan')->assertStatus(200);
}

public function test_keputusan_tab_riwayat_loads_for_ketua(): void
{
    $ketua = $this->makeKetua();
    $this->actingAs($ketua)->get('/keputusan?tab=riwayat')->assertStatus(200);
}

public function test_keputusan_tab_pengajuan_loads_for_atasan(): void
{
    $atasan = $this->makeUser(['role' => 'sekretaris', 'nip' => '199001012020011003']);
    $this->actingAs($atasan)->get('/keputusan?tab=pengajuan')->assertStatus(200);
}

public function test_keputusan_filter_by_type(): void
{
    $user = $this->makeUser();
    $this->actingAs($user)
        ->get('/keputusan?type=cuti_tahunan')
        ->assertStatus(200);
}
```

- [ ] **Step 2: Jalankan tests — harus GAGAL dulu**

```bash
cd /c/laragon/www/sicair
/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe artisan test --filter=test_keputusan
```

Expected output: FAIL — `Route [keputusan.index] not defined` atau 404.

---

### Task 2: Buat KeputusanController

**Files:**
- Create: `app/Http/Controllers/KeputusanController.php`

- [ ] **Step 3: Buat controller baru**

Buat file `app/Http/Controllers/KeputusanController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeputusanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isKetua()) {
            return $this->ketuaView($user, $request);
        }

        if ($user->isAtasan()) {
            return $this->atasanView($user, $request);
        }

        return $this->pegawaiView($user, $request);
    }

    private function ketuaView($user, Request $request)
    {
        $tab = $request->get('tab', 'menunggu');

        $menunggQuery = LeaveRequest::with(['user', 'atasanReviewer'])
            ->where('status', LeaveRequest::STATUS_PERTIMBANGAN)
            ->latest();

        $riwayatQuery = LeaveRequest::with(['user'])
            ->where('pejabat_id', $user->id)
            ->latest();

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

        return view('keputusan.index', compact('user', 'tab', 'menunggu', 'riwayat'));
    }

    private function atasanView($user, Request $request)
    {
        $tab = $request->get('tab', 'review');

        $reviewQuery   = LeaveRequest::with(['user'])
            ->where('atasan_reviewer_id', $user->id)
            ->latest();

        $pengajuanQuery = $user->leaveRequests()->with(['user'])->latest();

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

        return view('keputusan.index', compact('user', 'tab', 'review', 'pengajuan'));
    }

    private function pegawaiView($user, Request $request)
    {
        $pengajuanQuery = $user->leaveRequests()->with(['user'])->latest();

        $this->applyStatusFilter($pengajuanQuery, $request);
        $this->applyTypeAndDateFilters($pengajuanQuery, $request);

        $pengajuan = $pengajuanQuery->paginate(15)->withQueryString();

        return view('keputusan.index', compact('user', 'pengajuan'));
    }

    private function applyStatusFilter($query, Request $request): void
    {
        if (!$request->filled('status')) return;

        match ($request->status) {
            'disetujui'  => $query->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED]),
            'ditolak'    => $query->whereIn('status', [LeaveRequest::STATUS_DITOLAK, LeaveRequest::STATUS_REJECTED]),
            default      => $query->where('status', $request->status),
        };
    }

    private function applyTypeAndDateFilters($query, Request $request): void
    {
        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('start_date')) $query->where('start_date', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->where('end_date', '<=', $request->end_date);
    }
}
```

---

### Task 3: Daftarkan route dan buat view placeholder

**Files:**
- Modify: `routes/web.php`
- Create: `resources/views/keputusan/index.blade.php` (placeholder)

- [ ] **Step 4: Tambah route di web.php**

Di `routes/web.php`, temukan grup `Route::middleware(['auth'])->group(function () {` dan tambahkan route keputusan setelah route dashboard (sekitar line 92):

```php
Route::get('/keputusan', [\App\Http\Controllers\KeputusanController::class, 'index'])->name('keputusan.index');
```

- [ ] **Step 5: Buat folder dan view placeholder**

Buat folder `resources/views/keputusan/` lalu buat `resources/views/keputusan/index.blade.php` dengan konten minimal agar test tidak error:

```blade
@extends('layouts.app')

@section('title', 'Keputusan — SiCAIR')

@section('content')
<div class="container-xl py-4">
    <h2>Keputusan</h2>
    <p>Halaman keputusan — dalam pengembangan.</p>
</div>
@endsection
```

- [ ] **Step 6: Jalankan smoke tests — harus LULUS**

```bash
cd /c/laragon/www/sicair
/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe artisan test --filter=test_keputusan
```

Expected output: 8 tests PASS.

- [ ] **Step 7: Jalankan seluruh test suite — pastikan tidak ada regresi**

```bash
/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe artisan test
```

Expected: semua test sebelumnya tetap PASS.

- [ ] **Step 8: Commit foundation**

```bash
git add app/Http/Controllers/KeputusanController.php \
        resources/views/keputusan/index.blade.php \
        routes/web.php \
        tests/Feature/SmokeTest.php
git commit -m "feat: tambah KeputusanController, route /keputusan, dan smoke tests"
```

---

## Chunk 2: View Lengkap — keputusan/index.blade.php

### Task 4: Ganti placeholder dengan view lengkap

**Files:**
- Modify: `resources/views/keputusan/index.blade.php`

View ini menangani 3 branch role dalam satu file. Gunakan class CSS yang sama dengan dashboard (`sc-card`, `sc-badge`, `sc-history-card`, dll.) agar tampilan konsisten.

- [ ] **Step 9: Ganti isi view dengan implementasi lengkap**

Ganti seluruh isi `resources/views/keputusan/index.blade.php` dengan:

```blade
@extends('layouts.app')

@section('title', 'Keputusan — SiCAIR')

@section('content')
<div class="container-xl py-4">

    {{-- ================================================================
         HEADER
    ================================================================ --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-0" style="font-size: 1.4rem;">
                <i class="ti ti-gavel me-2" style="color: var(--sc-warning);"></i>Keputusan
            </h2>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                @if(Auth::user()->isAdmin() || Auth::user()->isKetua())
                    Pengajuan menunggu keputusan &amp; riwayat keputusan Anda
                @elseif(Auth::user()->isAtasan())
                    Riwayat review Anda &amp; pengajuan cuti Anda sendiri
                @else
                    Riwayat pengajuan cuti dan keputusan atasannya
                @endif
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
            <i class="ti ti-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    {{-- ================================================================
         ADMIN / KETUA VIEW — dua tab
    ================================================================ --}}
    @if(Auth::user()->isAdmin() || Auth::user()->isKetua())

    {{-- Tab Nav --}}
    <ul class="nav nav-tabs mb-3" style="border-bottom: 2px solid var(--sc-gray-200);">
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? 'menunggu') === 'menunggu' ? 'active fw-semibold' : '' }}"
               href="{{ route('keputusan.index', ['tab' => 'menunggu']) }}"
               style="{{ ($tab ?? 'menunggu') === 'menunggu' ? 'color: var(--sc-primary); border-bottom: 2px solid var(--sc-primary);' : '' }}">
                <i class="ti ti-clock me-1"></i> Menunggu Keputusan
                @if(($menunggu ?? collect())->total() > 0)
                <span class="badge ms-1" style="background: var(--sc-warning); font-size: 0.7rem; border-radius: 50px;">
                    {{ $menunggu->total() }}
                </span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'riwayat' ? 'active fw-semibold' : '' }}"
               href="{{ route('keputusan.index', ['tab' => 'riwayat']) }}"
               style="{{ ($tab ?? '') === 'riwayat' ? 'color: var(--sc-primary); border-bottom: 2px solid var(--sc-primary);' : '' }}">
                <i class="ti ti-history me-1"></i> Riwayat Keputusan
            </a>
        </li>
    </ul>

    {{-- Filter --}}
    @include('keputusan._filter', [
        'showStatus' => ($tab ?? 'menunggu') === 'riwayat',
        'showType'   => true,
        'showDate'   => true,
    ])

    {{-- Tab: Menunggu Keputusan --}}
    @if(($tab ?? 'menunggu') === 'menunggu')
        @if($menunggu->isEmpty())
        <div class="card sc-card"><div class="card-body py-5 text-center">
            <div class="sc-empty-icon"><i class="ti ti-mood-happy"></i></div>
            <h4 class="fw-bold">Tidak Ada Antrian</h4>
            <p class="text-muted mb-0">Tidak ada pengajuan yang menunggu keputusan Anda.</p>
        </div></div>
        @else
        <form method="POST" action="{{ route('leave.bulk-decide') }}">
            @csrf
            {{-- Bulk action bar (muncul saat ada yang dicentang) --}}
            <div id="sc-bulk-actions" style="display:none;" class="mb-3">
                <div class="p-3" style="background:var(--sc-primary-light);border-radius:10px;">
                    <div class="d-flex gap-2 align-items-center flex-wrap mb-2">
                        <span class="text-muted small" id="sc-bulk-count">0 dipilih</span>
                        <select name="decision" class="form-select form-select-sm" style="width:auto;" required>
                            <option value="">-- Pilih Keputusan --</option>
                            <option value="setuju">Setujui Semua</option>
                            <option value="tolak">Tolak Semua</option>
                            <option value="tangguhkan">Tangguhkan Semua</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ti ti-check me-1"></i> Terapkan
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="sc-bulk-clear">Batal</button>
                    </div>
                    <div class="form-text mb-2" style="color: var(--sc-warning); font-size:0.8rem;">
                        <i class="ti ti-info-circle me-1"></i>
                        Bulk action hanya berlaku untuk item yang ditampilkan di halaman ini ({{ $menunggu->count() }} dari {{ $menunggu->total() }}).
                    </div>
                    <textarea name="catatan" class="form-control form-control-sm" rows="2"
                        placeholder="Catatan untuk semua pengajuan yang dipilih (opsional)..."></textarea>
                </div>
            </div>

            @foreach($menunggu as $req)
            <div class="card sc-history-card status-{{ $req->status }} mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" name="ids[]" value="{{ $req->id }}"
                                class="sc-bulk-cb form-check-input" style="width:18px;height:18px;flex-shrink:0;margin-top:0;">
                            <div class="sc-user-avatar" style="width:40px;height:40px;font-size:0.8rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:10px;">
                                {{ strtoupper(substr($req->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:0.95rem;">{{ $req->user->name }}</div>
                                <div class="text-muted" style="font-size:0.78rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                            </div>
                        </div>
                        <span class="sc-badge sc-badge-pending">{{ $req->type_label }}</span>
                    </div>
                    <div class="row g-2 mb-2" style="font-size:0.82rem;">
                        <div class="col-sm-6">
                            <i class="ti ti-calendar me-1 text-muted"></i>
                            {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                        </div>
                        <div class="col-sm-6">
                            <i class="ti ti-clock me-1 text-muted"></i>
                            {{ $req->total_days }} hari
                            @if($req->total_hari_kerja) ({{ $req->total_hari_kerja }} hari kerja) @endif
                        </div>
                    </div>
                    @if($req->atasanReviewer)
                    <div class="mb-2" style="background:var(--sc-primary-light);border-radius:8px;padding:0.5rem 0.75rem;font-size:0.82rem;">
                        <i class="ti ti-user-check me-1" style="color:var(--sc-primary);"></i>
                        <strong>Pertimbangan {{ $req->atasanReviewer->name }}:</strong>
                        <span style="color:{{ $req->pertimbangan_atasan === 'setuju' ? 'var(--sc-success)' : 'var(--sc-warning)' }}">
                            {{ ucfirst($req->pertimbangan_atasan) }}
                        </span>
                        @if($req->catatan_atasan) &mdash; {{ Str::limit($req->catatan_atasan, 80) }} @endif
                    </div>
                    @else
                    <div class="mb-2" style="background:#ecfdf5;border-radius:8px;padding:0.5rem 0.75rem;font-size:0.82rem;">
                        <i class="ti ti-arrow-forward me-1" style="color:var(--sc-success);"></i>
                        <strong>Pengajuan Langsung</strong> &mdash; tanpa pertimbangan atasan
                    </div>
                    @endif
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm sc-btn-primary flex-fill"
                            data-bs-toggle="modal" data-bs-target="#decisionModal{{ $req->id }}">
                            <i class="ti ti-gavel me-1"></i> Beri Keputusan
                        </button>
                        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @include('partials.pejabat-decision-modal', ['req' => $req])
            @endforeach
        </form>
        {{ $menunggu->links() }}
        @endif
    @endif

    {{-- Tab: Riwayat Keputusan --}}
    @if(($tab ?? '') === 'riwayat')
        @if($riwayat->isEmpty())
        <div class="card sc-card"><div class="card-body py-5 text-center">
            <div class="sc-empty-icon"><i class="ti ti-history"></i></div>
            <h4 class="fw-bold">Belum Ada Riwayat</h4>
            <p class="text-muted mb-0">Belum ada keputusan yang Anda buat, atau semua keputusan dibuat via admin.</p>
        </div></div>
        @else
        @foreach($riwayat as $req)
        @include('keputusan._riwayat-card', ['req' => $req])
        @endforeach
        {{ $riwayat->links() }}
        @endif
    @endif

    {{-- ================================================================
         ATASAN VIEW — dua tab
    ================================================================ --}}
    @elseif(Auth::user()->isAtasan())

    <ul class="nav nav-tabs mb-3" style="border-bottom: 2px solid var(--sc-gray-200);">
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? 'review') === 'review' ? 'active fw-semibold' : '' }}"
               href="{{ route('keputusan.index', ['tab' => 'review']) }}"
               style="{{ ($tab ?? 'review') === 'review' ? 'color: var(--sc-primary); border-bottom: 2px solid var(--sc-primary);' : '' }}">
                <i class="ti ti-checklist me-1"></i> Review Saya
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ ($tab ?? '') === 'pengajuan' ? 'active fw-semibold' : '' }}"
               href="{{ route('keputusan.index', ['tab' => 'pengajuan']) }}"
               style="{{ ($tab ?? '') === 'pengajuan' ? 'color: var(--sc-primary); border-bottom: 2px solid var(--sc-primary);' : '' }}">
                <i class="ti ti-file-text me-1"></i> Pengajuan Saya
            </a>
        </li>
    </ul>

    @include('keputusan._filter', ['showStatus' => true, 'showType' => true, 'showDate' => true])

    @if(($tab ?? 'review') === 'review')
        @if($review->isEmpty())
        <div class="card sc-card"><div class="card-body py-5 text-center">
            <div class="sc-empty-icon"><i class="ti ti-checklist"></i></div>
            <h4 class="fw-bold">Belum Ada Riwayat Review</h4>
            <p class="text-muted mb-0">Anda belum pernah mereview pengajuan bawahan, atau antrian review ada di Dashboard.</p>
        </div></div>
        @else
        @foreach($review as $req)
        <div class="card sc-history-card status-{{ $req->status }} mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="sc-user-avatar" style="width:40px;height:40px;font-size:0.8rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:10px;">
                            {{ strtoupper(substr($req->user->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:0.95rem;">{{ $req->user->name }}</div>
                            <div class="text-muted" style="font-size:0.78rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="sc-badge sc-badge-{{ in_array($req->status, ['disetujui','approved']) ? 'approved' : (in_array($req->status, ['ditolak','rejected']) ? 'rejected' : 'pending') }}">
                            {{ $req->status_label ?? ucfirst($req->status) }}
                        </span>
                    </div>
                </div>
                <div class="row g-2 mb-2" style="font-size:0.82rem;">
                    <div class="col-sm-4"><i class="ti ti-tag me-1 text-muted"></i>{{ $req->type_label }}</div>
                    <div class="col-sm-4"><i class="ti ti-calendar me-1 text-muted"></i>{{ $req->start_date->format('d M Y') }}</div>
                    <div class="col-sm-4"><i class="ti ti-clock me-1 text-muted"></i>{{ $req->total_days }} hari</div>
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
                    Keputusan {{ $req->pejabat->name }}: <strong>{{ ucfirst($req->keputusan_pejabat ?? '-') }}</strong>
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
    @endif

    @if(($tab ?? '') === 'pengajuan')
        @include('keputusan._pengajuan-list', ['items' => $pengajuan])
    @endif

    {{-- ================================================================
         PEGAWAI / HAKIM VIEW — tanpa tab
    ================================================================ --}}
    @else

    @include('keputusan._filter', ['showStatus' => true, 'showType' => true, 'showDate' => true])
    @include('keputusan._pengajuan-list', ['items' => $pengajuan])

    @endif

</div>

@push('scripts')
<script>
// Bulk action checkbox handler (sama dengan dashboard)
document.querySelectorAll('.sc-bulk-cb').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var checked = document.querySelectorAll('.sc-bulk-cb:checked').length;
        var bar = document.getElementById('sc-bulk-actions');
        var countEl = document.getElementById('sc-bulk-count');
        if (bar) bar.style.display = checked > 0 ? 'block' : 'none';
        if (countEl) countEl.textContent = checked + ' dipilih';
    });
});
var clearBtn = document.getElementById('sc-bulk-clear');
if (clearBtn) {
    clearBtn.addEventListener('click', function() {
        document.querySelectorAll('.sc-bulk-cb:checked').forEach(function(cb) { cb.checked = false; });
        var bar = document.getElementById('sc-bulk-actions');
        if (bar) bar.style.display = 'none';
    });
}
</script>
@endpush

@endsection
```

- [ ] **Step 10: Buat partial filter `keputusan/_filter.blade.php`**

Buat `resources/views/keputusan/_filter.blade.php`:

```blade
<div class="card sc-card mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('keputusan.index') }}" class="row g-2 align-items-end">
            @if(request('tab'))
            <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            @if($showStatus ?? false)
            <div class="col-sm-auto">
                <label class="form-label mb-1" style="font-size:0.8rem;">Status</label>
                <select name="status" class="form-select form-select-sm" style="min-width:140px;">
                    <option value="">Semua Status</option>
                    <option value="diajukan" {{ request('status') === 'diajukan' ? 'selected' : '' }}>Menunggu Review</option>
                    <option value="pertimbangan_atasan" {{ request('status') === 'pertimbangan_atasan' ? 'selected' : '' }}>Pertimbangan Ketua</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="diubah" {{ request('status') === 'diubah' ? 'selected' : '' }}>Diubah</option>
                    <option value="ditangguhkan" {{ request('status') === 'ditangguhkan' ? 'selected' : '' }}>Ditangguhkan</option>
                </select>
            </div>
            @endif

            @if($showType ?? false)
            <div class="col-sm-auto">
                <label class="form-label mb-1" style="font-size:0.8rem;">Jenis Cuti</label>
                <select name="type" class="form-select form-select-sm" style="min-width:160px;">
                    <option value="">Semua Jenis</option>
                    @foreach(\App\Models\LeaveRequest::typeLabels() as $value => $label)
                    <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($showDate ?? false)
            <div class="col-sm-auto">
                <label class="form-label mb-1" style="font-size:0.8rem;">Dari</label>
                <input type="date" name="start_date" class="form-control form-control-sm"
                    value="{{ request('start_date') }}" style="min-width:130px;">
            </div>
            <div class="col-sm-auto">
                <label class="form-label mb-1" style="font-size:0.8rem;">Sampai</label>
                <input type="date" name="end_date" class="form-control form-control-sm"
                    value="{{ request('end_date') }}" style="min-width:130px;">
            </div>
            @endif

            <div class="col-sm-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm sc-btn-primary">
                    <i class="ti ti-search me-1"></i> Filter
                </button>
                <a href="{{ route('keputusan.index', request('tab') ? ['tab' => request('tab')] : []) }}"
                   class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 11: Buat partial riwayat card `keputusan/_riwayat-card.blade.php`**

Buat `resources/views/keputusan/_riwayat-card.blade.php`:

```blade
<div class="card sc-history-card status-{{ $req->status }} mb-3">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="d-flex align-items-center gap-2">
                <div class="sc-user-avatar" style="width:40px;height:40px;font-size:0.8rem;background:var(--sc-primary-light);color:var(--sc-primary);border:none;border-radius:10px;">
                    {{ strtoupper(substr($req->user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="fw-bold" style="font-size:0.95rem;">{{ $req->user->name }}</div>
                    <div class="text-muted" style="font-size:0.78rem;">{{ $req->user->jabatan ?? $req->user->nip }}</div>
                </div>
            </div>
            <span class="sc-badge sc-badge-{{ in_array($req->status, ['disetujui','approved']) ? 'approved' : (in_array($req->status, ['ditolak','rejected']) ? 'rejected' : 'pending') }}">
                {{ $req->status_label ?? ucfirst($req->status) }}
            </span>
        </div>
        <div class="row g-2 mb-2" style="font-size:0.82rem;">
            <div class="col-sm-4"><i class="ti ti-tag me-1 text-muted"></i>{{ $req->type_label }}</div>
            <div class="col-sm-4"><i class="ti ti-calendar me-1 text-muted"></i>{{ $req->start_date->format('d M Y') }}</div>
            <div class="col-sm-4"><i class="ti ti-clock me-1 text-muted"></i>{{ $req->total_days }} hari</div>
        </div>
        @if($req->catatan_pejabat)
        <div style="font-size:0.82rem;color:var(--sc-muted);">
            <i class="ti ti-message me-1"></i> {{ Str::limit($req->catatan_pejabat, 100) }}
        </div>
        @endif
        @if($req->decided_at)
        <div class="mt-1" style="font-size:0.78rem;color:var(--sc-muted);">
            <i class="ti ti-calendar-check me-1"></i> Diputuskan {{ $req->decided_at->diffForHumans() }}
        </div>
        @endif
        <div class="mt-2">
            <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:0.8rem;">
                <i class="ti ti-eye me-1"></i> Detail
            </a>
        </div>
    </div>
</div>
```

- [ ] **Step 12: Buat partial pengajuan list `keputusan/_pengajuan-list.blade.php`**

Buat `resources/views/keputusan/_pengajuan-list.blade.php`:

```blade
@if($items->isEmpty())
<div class="card sc-card"><div class="card-body py-5 text-center">
    <div class="sc-empty-icon"><i class="ti ti-file-off"></i></div>
    <h4 class="fw-bold">Belum Ada Pengajuan</h4>
    <p class="text-muted mb-0">Anda belum pernah mengajukan cuti.</p>
</div></div>
@else
@foreach($items as $req)
<div class="card sc-history-card status-{{ $req->status }} mb-3">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="fw-bold" style="font-size:0.95rem;">{{ $req->type_label }}</div>
                <div class="text-muted" style="font-size:0.78rem;">
                    {{ $req->start_date->format('d M Y') }} &mdash; {{ $req->end_date->format('d M Y') }}
                    &nbsp;·&nbsp; {{ $req->total_days }} hari
                </div>
            </div>
            <span class="sc-badge sc-badge-{{ in_array($req->status, ['disetujui','approved']) ? 'approved' : (in_array($req->status, ['ditolak','rejected']) ? 'rejected' : 'pending') }}">
                {{ $req->status_label ?? ucfirst($req->status) }}
            </span>
        </div>
        @if($req->pejabat)
        <div style="font-size:0.82rem;background:var(--sc-gray-50);border-radius:8px;padding:0.45rem 0.75rem;" class="mb-2">
            <i class="ti ti-gavel me-1" style="color:var(--sc-warning);"></i>
            <strong>{{ $req->pejabat->name }}</strong>:
            {{ ucfirst($req->keputusan_pejabat ?? 'Belum diputuskan') }}
            @if($req->catatan_pejabat) &mdash; {{ Str::limit($req->catatan_pejabat, 80) }} @endif
        </div>
        @endif
        <a href="{{ route('leave.show', $req) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:0.8rem;">
            <i class="ti ti-eye me-1"></i> Detail
        </a>
    </div>
</div>
@endforeach
{{ $items->links() }}
@endif
```

- [ ] **Step 13: Cek relasi `pejabat` di LeaveRequest model**

```bash
grep -n "pejabat\|atasanReviewer" /c/laragon/www/sicair/app/Models/LeaveRequest.php
```

Jika relasi `pejabat()` belum ada, tambahkan di `app/Models/LeaveRequest.php` setelah relasi yang sudah ada:

```php
public function pejabat(): BelongsTo
{
    return $this->belongsTo(User::class, 'pejabat_id');
}
```

Jika sudah ada, skip langkah ini.

- [ ] **Step 14: Jalankan smoke tests untuk memastikan semua pass**

```bash
/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe artisan test --filter=test_keputusan
```

Expected: 8 tests PASS.

- [ ] **Step 15: Jalankan seluruh test suite**

```bash
/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe artisan test
```

Expected: semua PASS.

- [ ] **Step 16: Commit view lengkap**

```bash
git add resources/views/keputusan/
git commit -m "feat: implementasi view halaman Keputusan (semua role)"
```

---

## Chunk 3: Dashboard Changes + Navbar

### Task 5: Update DashboardController — pisah count dan preview

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php` (sekitar line 124-152)

- [ ] **Step 17: Update method ketuaDashboard()**

Di `app/Http/Controllers/DashboardController.php`, ganti bagian query `$needsDecision` di method `ketuaDashboard()`:

**Sebelum:**
```php
$needsDecision = LeaveRequest::with(['user', 'atasanReviewer'])
    ->where('status', LeaveRequest::STATUS_PERTIMBANGAN)
    ->distinct()
    ->latest()
    ->get();
```

**Sesudah:**
```php
$needsDecisionCount = LeaveRequest::where('status', LeaveRequest::STATUS_PERTIMBANGAN)
    ->distinct()
    ->count();

$needsDecision = LeaveRequest::with(['user', 'atasanReviewer'])
    ->where('status', LeaveRequest::STATUS_PERTIMBANGAN)
    ->distinct()
    ->latest()
    ->take(5)
    ->get();
```

Juga tambahkan `$needsDecisionCount` ke array `compact()` di return:

**Sebelum:**
```php
return view('dashboard', compact('user', 'needsDecision', 'recentDecisions', 'leaveRequests', 'todayOnLeave'));
```

**Sesudah:**
```php
return view('dashboard', compact('user', 'needsDecision', 'needsDecisionCount', 'recentDecisions', 'leaveRequests', 'todayOnLeave'));
```

---

### Task 6: Update dashboard.blade.php — pakai $needsDecisionCount + tombol "Lihat semua"

**Files:**
- Modify: `resources/views/dashboard.blade.php`

- [ ] **Step 18: Ganti `$needsDecision->count()` dengan `$needsDecisionCount` di stat card**

Cari di `dashboard.blade.php`:
```blade
<div class="sc-stat-number" style="color: var(--sc-warning);">{{ $needsDecision->count() }}</div>
```

Ganti dengan:
```blade
<div class="sc-stat-number" style="color: var(--sc-warning);">{{ $needsDecisionCount }}</div>
```

- [ ] **Step 19: Ganti badge count "antrian" dengan $needsDecisionCount**

Cari:
```blade
<span class="sc-badge sc-badge-pending">{{ $needsDecision->count() }} antrian</span>
```

Ganti dengan:
```blade
<span class="sc-badge sc-badge-pending">{{ $needsDecisionCount }} antrian</span>
```

- [ ] **Step 20: Tambah tombol "Lihat semua" di card header Menunggu Keputusan**

Cari header card dengan teks "Menunggu Keputusan Anda":
```blade
<h3 class="card-title mb-0">
    <i class="ti ti-gavel me-2" style="color: var(--sc-warning);"></i>
    Menunggu Keputusan Anda
</h3>
```

Ganti dengan:
```blade
<h3 class="card-title mb-0">
    <i class="ti ti-gavel me-2" style="color: var(--sc-warning);"></i>
    Menunggu Keputusan Anda
</h3>
```
Dan di div `d-flex` yang membungkus header, tambahkan tombol "Lihat semua" setelah badge count:

Tambahkan setelah `@endif` badge:
```blade
<a href="{{ route('keputusan.index') }}" class="btn btn-sm btn-outline-secondary ms-auto" style="border-radius:8px;font-size:0.8rem;">
    Lihat semua <i class="ti ti-arrow-right ms-1"></i>
</a>
```

---

### Task 7: Update navbar — link, visibilitas, badge guard

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

- [ ] **Step 21: Update blok @php navbar — perluas badge ke admin**

Di bagian atas `app.blade.php` (sekitar line 1-20), di dalam blok `@auth @php`, ganti:

```php
$navNeedsDecision = \Illuminate\Support\Facades\Auth::user()->isKetua()
    ? \Illuminate\Support\Facades\Cache::remember(
        'nav_needs_decision', 60,
        fn() => \App\Models\LeaveRequest::where('status', \App\Models\LeaveRequest::STATUS_PERTIMBANGAN)->count()
    ) : 0;
```

Ganti dengan:

```php
$navNeedsDecision = (\Illuminate\Support\Facades\Auth::user()->isKetua() || \Illuminate\Support\Facades\Auth::user()->isAdmin())
    ? \Illuminate\Support\Facades\Cache::remember(
        'nav_needs_decision', 60,
        fn() => \App\Models\LeaveRequest::where('status', \App\Models\LeaveRequest::STATUS_PERTIMBANGAN)->count()
    ) : 0;
```

- [ ] **Step 22: Update link dan visibilitas menu Keputusan di navbar**

Cari blok navbar Keputusan (sekitar line 2143-2155):

```blade
{{-- Ketua: badge count for pending decisions --}}
@if(Auth::user()->isKetua())
<li class="nav-item">
    <a class="nav-link" href="/dashboard#needs-decision">
```

Ganti seluruh blok tersebut dengan:

```blade
{{-- Keputusan: tampil untuk semua role --}}
<li class="nav-item">
    <a class="nav-link {{ request()->is('keputusan*') ? 'active' : '' }}"
       href="{{ route('keputusan.index') }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-gavel"></i></span>
        <span class="nav-link-title">
            Keputusan
            @if($navNeedsDecision > 0)
            <span class="badge ms-1" style="font-size: 0.7rem; border-radius: 50px; min-width: 20px; background: var(--sc-accent); color: #fff;">{{ $navNeedsDecision }}</span>
            @endif
        </span>
    </a>
</li>
```

- [ ] **Step 23: Clear view cache dan sync ke Laragon**

```bash
cd /c/laragon/www/sicair
/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe artisan view:clear
```

- [ ] **Step 24: Jalankan seluruh test suite — final check**

```bash
/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe artisan test
```

Expected: semua PASS.

- [ ] **Step 25: Commit semua perubahan dashboard + navbar**

```bash
git add app/Http/Controllers/DashboardController.php \
        resources/views/dashboard.blade.php \
        resources/views/layouts/app.blade.php \
        app/Models/LeaveRequest.php
git commit -m "feat: update dashboard preview (limit 5 + Lihat semua) dan navbar Keputusan"
```

---

## Verifikasi Manual di Browser

Setelah semua task selesai, verifikasi di browser Laragon (`http://localhost/sicair` atau sesuai URL):

| Skenario | Yang Diperiksa |
|---|---|
| Login sebagai **ketua** → klik Keputusan | Tab "Menunggu Keputusan" muncul, badge di navbar ada |
| Tab "Riwayat Keputusan" | Daftar keputusan ketua muncul, filter berfungsi |
| Login sebagai **sekretaris** (atasan) → klik Keputusan | Tab "Review Saya" + "Pengajuan Saya" muncul |
| Login sebagai **pegawai** → klik Keputusan | Daftar pengajuan sendiri, tanpa tab |
| Dashboard ketua | Badge "N antrian" akurat, maks 5 item, tombol "Lihat semua" ada |
| Navbar semua role | Menu "Keputusan" muncul untuk semua role |
| Filter status/jenis/tanggal | Hasil berubah sesuai filter, pagination bekerja |
