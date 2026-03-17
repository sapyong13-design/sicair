# Sprint B — Status Visual Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tambahkan mini stepper horizontal 3-step di halaman `/leave/saya` dan perbaiki badge warna di `dashboard.blade.php`.

**Architecture:** Satu Blade component baru (`leave-status-stepper`) yang menerima `LeaveRequest` dan merender 3 dot (Diajukan → Atasan → Ketua) dengan warna sesuai status. Dua modifikasi kecil pada file view yang ada.

**Tech Stack:** Laravel 12, Blade, Bootstrap 5 / Tabler CSS, PHP 8.3. Tidak ada JS, Alpine.js, DB, controller, atau route baru.

---

## Chunk 1: Component + Integrasi + Badge Fix

### Task 1: Buat Blade component `leave-status-stepper`

**Files:**
- Create: `resources/views/components/leave-status-stepper.blade.php`

**Spec reference:** `docs/superpowers/specs/2026-03-17-sprint-b-status-visual.md` — bagian "Detail: Komponen"

**Context penting:**
- Component adalah file Blade biasa (bukan PHP class) — letakkan di `resources/views/components/`
- Laravel otomatis resolve `<x-leave-status-stepper>` dari path tersebut
- `LeaveRequest` constants yang relevan: `STATUS_DIAJUKAN='diajukan'`, `STATUS_PERTIMBANGAN='pertimbangan_atasan'`, `STATUS_DISETUJUI='disetujui'`, `STATUS_DIUBAH='diubah'`, `STATUS_DITANGGUHKAN='ditangguhkan'`, `STATUS_DITOLAK='ditolak'`, `STATUS_PENDING='pending'`, `STATUS_APPROVED='approved'`, `STATUS_REJECTED='rejected'`
- CSS variables yang tersedia: `var(--sc-success)`, `var(--sc-danger)`, `var(--sc-primary)`, `var(--sc-warning)`, `var(--sc-gray-100)`, `var(--sc-text-muted)`
- `--sc-primary-rgb` **tidak ada** secara global — gunakan `rgba(37, 99, 235, ...)` hardcoded untuk pulse animation
- Tabler Icons sudah di-load global — gunakan class `ti ti-check`, `ti ti-x`, `ti ti-clock-pause`, `ti ti-edit`

- [ ] **Step 1: Buat file component**

Isi file `resources/views/components/leave-status-stepper.blade.php`:

```blade
@props(['leave'])
{{-- $leave: App\Models\LeaveRequest --}}
@isset($leave)
@php
use App\Models\LeaveRequest;

$status = $leave->status;

$step2 = match(true) {
    in_array($status, [
        LeaveRequest::STATUS_PERTIMBANGAN,
        LeaveRequest::STATUS_DISETUJUI,
        LeaveRequest::STATUS_APPROVED,
        LeaveRequest::STATUS_DITOLAK,
        LeaveRequest::STATUS_REJECTED,
        LeaveRequest::STATUS_DITANGGUHKAN,
    ]) => 'done',
    $status === LeaveRequest::STATUS_DIUBAH => 'warn',
    default => 'active',
};

$step3 = match(true) {
    in_array($status, [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED]) => 'done',
    in_array($status, [LeaveRequest::STATUS_DITOLAK, LeaveRequest::STATUS_REJECTED])   => 'rejected',
    $status === LeaveRequest::STATUS_DITANGGUHKAN => 'warn',
    $status === LeaveRequest::STATUS_PERTIMBANGAN => 'active',
    default => 'pending',
};

// Warn icon: computed once — hanya satu dot yang pernah 'warn' untuk status manapun
$warnIcon = $status === LeaveRequest::STATUS_DITANGGUHKAN ? 'ti-clock-pause' : 'ti-edit';

$steps = [
    ['state' => 'done',  'label' => 'Diajukan'],
    ['state' => $step2,  'label' => 'Atasan'],
    ['state' => $step3,  'label' => 'Ketua'],
];
@endphp

{{--
  Catatan CSS naming: Plan ini menggunakan compound class pattern (.sc-step-dot.done)
  alih-alih single class (sc-step-done) seperti di spec — hasilnya identik secara visual.
  @once memastikan <style> hanya di-render sekali meskipun component di-loop berkali-kali.
--}}
@once
<style>
@keyframes sc-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
    50%       { box-shadow: 0 0 0 5px rgba(37, 99, 235, 0); }
}
.sc-step-dot.done     { background: var(--sc-success);    color: #fff; }
.sc-step-dot.active   { background: var(--sc-primary);    color: #fff; animation: sc-pulse 2s infinite; }
.sc-step-dot.rejected { background: var(--sc-danger);     color: #fff; }
.sc-step-dot.warn     { background: var(--sc-warning);    color: #fff; }
.sc-step-dot.pending  { background: var(--sc-gray-100);   color: var(--sc-text-muted); }
</style>
@endonce

<div class="d-flex align-items-start" style="min-width: 140px;">
    @foreach($steps as $i => $step)
        <div class="d-flex flex-column align-items-center">
            <div class="sc-step-dot {{ $step['state'] }} d-flex align-items-center justify-content-center"
                 style="width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;">
                @if($step['state'] === 'done')
                    <i class="ti ti-check" style="font-size: 0.6rem;"></i>
                @elseif($step['state'] === 'rejected')
                    <i class="ti ti-x" style="font-size: 0.6rem;"></i>
                @elseif($step['state'] === 'warn')
                    <i class="ti {{ $warnIcon }}" style="font-size: 0.6rem;"></i>
                @endif
            </div>
            <div style="font-size: 0.6rem; color: var(--sc-text-muted); margin-top: 3px; white-space: nowrap;">
                {{ $step['label'] }}
            </div>
        </div>
        @if(!$loop->last)
            @php $nextState = $steps[$i + 1]['state']; @endphp
            <div style="height: 2px; flex: 1; min-width: 12px;
                        background: {{ $nextState === 'done' ? 'var(--sc-success)' : 'var(--sc-gray-100)' }};
                        margin-top: 9px;"></div>
        @endif
    @endforeach
</div>
@endisset
```

- [ ] **Step 2: Verifikasi file ada**

```bash
ls resources/views/components/leave-status-stepper.blade.php
```

Expected: file ditemukan.

- [ ] **Step 3: Commit**

```bash
git add resources/views/components/leave-status-stepper.blade.php
git commit -m "feat: tambah Blade component leave-status-stepper"
```

---

### Task 2: Integrasikan stepper ke `saya.blade.php`

**Files:**
- Modify: `resources/views/leave/saya.blade.php:133-141`

**Context:** Di dalam `@forelse($recentLeaves as $leave)`, ada blok `<div>` yang berisi dynamic badge. Ganti seluruh blok `<div>` tersebut (lines ~133-142, termasuk `@php $badgeClass ...@endphp` dan `<span class="sc-badge...">`).

- [ ] **Step 1: Jalankan test yang sudah ada untuk konfirmasi baseline**

```bash
php artisan test --filter test_leave_saya_page_loads
```

Expected: PASS (1 test, ~0.03s)

- [ ] **Step 2: Modifikasi `saya.blade.php`**

Cari dan ganti blok berikut di `resources/views/leave/saya.blade.php`:

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
                    <x-leave-status-stepper :leave="$leave" />
```

- [ ] **Step 3: Jalankan test**

```bash
php artisan test --filter test_leave_saya_page_loads
```

Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add resources/views/leave/saya.blade.php
git commit -m "feat: integrasikan leave-status-stepper ke halaman saya"
```

---

### Task 3: Fix badge warna di `dashboard.blade.php`

**Files:**
- Modify: `resources/views/dashboard.blade.php:541` (desktop table)
- Modify: `resources/views/dashboard.blade.php:569` (mobile card)

**Context:** Dua baris di dalam blok `@foreach($pendingRequests as $req)` (admin section) menggunakan `sc-badge-pending` hardcoded untuk semua status. Hanya dua baris ini yang disentuh — jangan ubah baris `sc-badge-pending` lain (~16 total di file ini) yang berada di luar `$pendingRequests` foreach atau yang sudah merupakan `@else` branch.

**Cara menemukan lokasi yang benar:** cari string `sc-badge-pending">{{ $req->status_label }}`  di dalam blok `@foreach($pendingRequests as $req)` — ada 2 match (desktop ~line 541, mobile ~line 569).

- [ ] **Step 1: Jalankan test baseline**

```bash
php artisan test --filter "test_dashboard_loads_for_admin|test_dashboard_loads_for_pegawai"
```

Expected: 2 tests PASS

- [ ] **Step 2: Fix baris ~541 (desktop table)**

Cari dan ganti **hanya** blok `<td>` berikut di dalam `@foreach($pendingRequests as $req)`:

```blade
{{-- SEBELUM: --}}
                        <td>
                            <span class="sc-badge sc-badge-pending">{{ $req->status_label }}</span>
                        </td>
```

Dengan:

```blade
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

- [ ] **Step 3: Fix baris ~569 (mobile card)**

Cari dan ganti **hanya** `<span>` berikut di dalam `@foreach($pendingRequests as $req)` (mobile card view, setelah blok avatar):

```blade
{{-- SEBELUM: --}}
                        <span class="sc-badge sc-badge-pending">{{ $req->status_label }}</span>
```

Dengan:

```blade
                        @php
                            $bc2 = match(true) {
                                $req->isApproved() => 'sc-badge-approved',
                                $req->isRejected() => 'sc-badge-rejected',
                                default            => 'sc-badge-pending',
                            };
                        @endphp
                        <span class="sc-badge {{ $bc2 }}">{{ $req->status_label }}</span>
```

Catatan: variabel `$bc2` (bukan `$bc`) digunakan untuk mobile agar tidak konflik dengan `$bc` jika kedua blok berada di scope yang sama.

- [ ] **Step 4: Jalankan test**

```bash
php artisan test --filter "test_dashboard_loads_for_admin|test_dashboard_loads_for_pegawai"
```

Expected: 2 tests PASS

- [ ] **Step 5: Jalankan full test suite**

```bash
php artisan test --filter SmokeTest
```

Expected: 40 tests PASS, 0 failures

- [ ] **Step 6: Commit**

```bash
git add resources/views/dashboard.blade.php
git commit -m "fix: badge warna konsisten di admin pending requests section"
```
