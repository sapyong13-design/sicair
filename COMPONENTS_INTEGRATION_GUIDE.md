# 📚 SiHEALING UI/UX Components Integration Guide

Panduan lengkap untuk mengintegrasikan 10 komponen UI/UX baru ke dalam aplikasi SiHEALING.

---

## 🎯 Daftar Komponen

| # | Komponen | File | Tujuan |
|---|----------|------|--------|
| 1 | Timeline Status Tracking | `leave-status-timeline.blade.php` | Tampilkan progress pengajuan cuti |
| 2 | Leave Balance Card | `leave-balance-card.blade.php` | Tampilkan sisa cuti dengan visual progress |
| 3 | Notification Center | `notification-center.blade.php` | Kelola notifikasi dengan filtering |
| 4 | Smart Calendar | `leave-calendar.blade.php` | Lihat cuti dalam kalender interaktif |
| 5 | Quick Actions | `dashboard-quick-actions.blade.php` | Shortcut ke fitur utama |
| 6 | Analytics Cards | `analytics-cards.blade.php` | Tampilkan statistik dan perbandingan |
| 7 | Approval Notes | `approval-notes.blade.php` | Lihat catatan persetujuan |
| 8 | Empty States | `empty-states.blade.php` | Screen untuk data kosong |
| 9 | Export Actions | `export-actions.blade.php` | Export/Print/Share dokumen |
| 10 | Theme Switcher | `theme-switcher.blade.php` | Dark mode dan kustomisasi tema |

---

## 🚀 Cara Menggunakan Setiap Komponen

### 1️⃣ Timeline Status Tracking

**File:** `resources/views/components/leave-status-timeline.blade.php`

**Tempat Integrasi:** `resources/views/leave/show.blade.php`

```blade
{{-- Di leave/show.blade.php setelah status banner --}}
<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card sh-card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-timeline me-2"></i>
                    Timeline Proses Pengajuan
                </h3>
            </div>
            <div class="card-body">
                @include('components.leave-status-timeline', ['leaveRequest' => $leaveRequest])
            </div>
        </div>
    </div>
</div>
```

**Data yang Dibutuhkan:**
```php
// Di controller
$leaveRequest = LeaveRequest::find($id);
// Pastikan relasi atasanReviewer dan pejabatReviewer sudah di-load
```

---

### 2️⃣ Leave Balance Card

**File:** `resources/views/components/leave-balance-card.blade.php`

**Tempat Integrasi:** `resources/views/dashboard.blade.php` atau `resources/views/leave/create.blade.php`

```blade
{{-- Di dashboard atau leave create page --}}
@include('components.leave-balance-card', [
    'sisaCuti' => $user->leave_balance,
    'totalHak' => 12,
    'cutiTahunan' => $cutiTahunan,
    'sisaCutiTahunan' => $sisaCutiTahunan,
    'cutiSakit' => 0,
    'penggunaanCutiSakit' => 0,
    'carryOver' => $carryOver ?? 0
])
```

**Data yang Dibutuhkan:**
```php
// Di controller
$user = Auth::user();
$sisaCuti = $user->leave_balance;
$totalHak = 12;
$cutiTahunan = 12;
$sisaCutiTahunan = $sisaCuti;
$carryOver = $user->carry_over ?? 0;
```

---

### 3️⃣ Notification Center

**File:** `resources/views/components/notification-center.blade.php`

**Tempat Integrasi:** Modal di header atau sidebar

```blade
{{-- Di layouts/app.blade.php atau dalam modal --}}
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px;">
            @include('components.notification-center', [
                'notifications' => Auth::user()->notifications()->latest()->take(20)->get(),
                'unreadCount' => Auth::user()->unreadNotifications()->count()
            ])
        </div>
    </div>
</div>

{{-- Trigger button di header --}}
<button class="btn btn-outline-secondary position-relative" data-bs-toggle="modal" data-bs-target="#notificationModal">
    <i class="ti ti-bell"></i>
    @if(Auth::user()->unreadNotifications()->count() > 0)
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        {{ Auth::user()->unreadNotifications()->count() }}
    </span>
    @endif
</button>
```

**Endpoints yang Diperlukan:**
```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::patch('/notifications/{id}/mark-read', 'NotificationController@markRead');
    Route::delete('/notifications/{id}', 'NotificationController@delete');
    Route::patch('/notifications/mark-all-read', 'NotificationController@markAllRead');
});
```

---

### 4️⃣ Smart Calendar

**File:** `resources/views/components/leave-calendar.blade.php`

**Tempat Integrasi:** Halaman kalender atau dashboard

```blade
{{-- Di leave/calendar.blade.php atau dalam tab di dashboard --}}
<div class="row">
    <div class="col-lg-12">
        @include('components.leave-calendar', [
            'leavesByDate' => $leavesByDate,
            'holidays' => $holidays
        ])
    </div>
</div>
```

**Data yang Dibutuhkan:**
```php
// Di controller
$leavesByDate = LeaveRequest::approved()
    ->whereBetween('start_date', [now()->startOfYear(), now()->endOfYear()])
    ->get()
    ->reduce(function($carry, $leave) {
        for($date = $leave->start_date; $date <= $leave->end_date; $date->addDay()) {
            $carry[$date->format('Y-m-d')] = [
                'type' => $leave->type,
                'type_label' => $leave->type_label,
                'status' => $leave->status
            ];
        }
        return $carry;
    }, []);

$holidays = Holiday::where('year', now()->year)
    ->get()
    ->mapWithKeys(fn($h) => [$h->date->format('Y-m-d') => $h->name]);
```

---

### 5️⃣ Dashboard Quick Actions

**File:** `resources/views/components/dashboard-quick-actions.blade.php`

**Tempat Integrasi:** `resources/views/dashboard.blade.php`

```blade
{{-- Setelah page header --}}
@include('components.dashboard-quick-actions', [
    'sisaCuti' => $user->leave_balance,
    'totalPengajuan' => $user->leaveRequests()->count(),
    'unreadNotifications' => $user->unreadNotifications()->count()
])

{{-- Konten dashboard lainnya --}}
```

---

### 6️⃣ Analytics Cards

**File:** `resources/views/components/analytics-cards.blade.php`

**Tempat Integrasi:** Dashboard atau halaman laporan

```blade
{{-- Di dashboard untuk section analytics --}}
<h3 class="mb-3">Analitik Cuti Anda</h3>
@include('components.analytics-cards', [
    'averageUsage' => $averageUsage,
    'approvalRate' => $approvalRate,
    'mostUsedMonth' => $mostUsedMonth,
    'mostUsedCount' => $mostUsedCount,
    'positionInTeam' => $positionInTeam,
    'teamMemberCount' => $teamMemberCount
])
```

**Data yang Dibutuhkan:**
```php
// Di controller
$leaveRequests = Auth::user()->leaveRequests()->approved();
$averageUsage = round($leaveRequests->sum('total_days') / 12, 1); // per bulan
$approvalRate = round(($leaveRequests->count() / Auth::user()->leaveRequests()->count()) * 100);
$mostUsedMonth = $leaveRequests->groupBy('start_date.month')->map->count()->keys()->first();
$mostUsedCount = $leaveRequests->groupBy('start_date.month')->map->count()->values()->max();
$teamMemberCount = Auth::user()->team()->count() ?? 0;
```

---

### 7️⃣ Approval Notes

**File:** `resources/views/components/approval-notes.blade.php`

**Tempat Integrasi:** `resources/views/leave/show.blade.php`

```blade
{{-- Di leave/show.blade.php setelah detail pengajuan --}}
<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card sh-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-notes me-2"></i>
                    Catatan & Keputusan
                </h3>
            </div>
            <div class="card-body">
                @include('components.approval-notes', ['leaveRequest' => $leaveRequest])
            </div>
        </div>
    </div>
</div>
```

---

### 8️⃣ Empty States

**File:** `resources/views/components/empty-states.blade.php`

**Tempat Integrasi:** Ketika tidak ada data

```blade
{{-- Jika tidak ada pengajuan cuti --}}
@forelse($leaveRequests as $request)
    {{-- tampilkan pengajuan --}}
@empty
    @include('components.empty-states', [
        'icon' => 'ti-inbox',
        'iconColor' => 'var(--sh-primary)',
        'backgroundColor' => 'var(--sh-primary-light)',
        'title' => 'Belum Ada Pengajuan Cuti',
        'description' => 'Anda belum membuat pengajuan cuti apapun. Mulai dengan membuat pengajuan baru untuk memanfaatkan hak cuti Anda.',
        'tips' => [
            'Siapkan dokumen pendukung jika diperlukan',
            'Ajukan minimal 5 hari kerja sebelum pelaksanaan',
            'Koordinasikan dengan atasan langsung Anda'
        ],
        'actionUrl' => route('leave.select-type'),
        'actionText' => 'Ajukan Cuti Sekarang',
        'actionIcon' => 'ti-plus'
    ])
@endforelse
```

---

### 9️⃣ Export Actions

**File:** `resources/views/components/export-actions.blade.php`

**Tempat Integrasi:** Header detail pengajuan atau laporan

```blade
{{-- Di leave/show.blade.php di page header --}}
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="sh-page-title mb-0">Detail Pengajuan Cuti</h2>
        </div>
        @include('components.export-actions', ['leaveRequest' => $leaveRequest])
    </div>
</div>
```

**Endpoints yang Diperlukan:**
```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/export/leave/{id}/pdf', 'ExportController@leavePdf')->name('export.leave-pdf');
    Route::get('/export/leave/{id}/excel', 'ExportController@leaveExcel')->name('export.leave-excel');
    Route::get('/reports/summary', 'ReportController@summaryPdf')->name('reports.summary-pdf');
    Route::get('/reports/monthly', 'ReportController@monthlyReport')->name('reports.monthly-report');
    Route::get('/reports/annual', 'ReportController@annualReport')->name('reports.annual-report');
});
```

---

### 🔟 Theme Switcher

**File:** `resources/views/components/theme-switcher.blade.php`

**Tempat Integrasi:** Navbar atau sidebar

```blade
{{-- Di layouts/app.blade.php di navbar --}}
<div class="d-flex align-items-center gap-2">
    {{-- komponen lainnya --}}
    @include('components.theme-switcher')
</div>
```

**Tidak memerlukan data backend** - semuanya disimpan di localStorage browser.

---

## 📋 Checklist Integrasi

Gunakan checklist ini untuk memastikan semua komponen sudah terintegrasi dengan benar:

- [ ] **Timeline Status Tracking** - Ditampilkan di `leave/show.blade.php`
- [ ] **Leave Balance Card** - Ditampilkan di dashboard atau create page
- [ ] **Notification Center** - Modal/dropdown di header dengan endpoints
- [ ] **Smart Calendar** - Halaman/tab kalender dengan data leave
- [ ] **Quick Actions** - 6 shortcut di dashboard utama
- [ ] **Analytics Cards** - Section analytics di dashboard
- [ ] **Approval Notes** - Detail persetujuan di leave/show
- [ ] **Empty States** - Ditampilkan ketika tidak ada data
- [ ] **Export Actions** - Dropdown di detail pengajuan
- [ ] **Theme Switcher** - Toggle di navbar untuk dark mode

---

## 🔧 Setup Database/Migration

Beberapa komponen memerlukan tabel tambahan:

```php
// migrations/xxxx_xx_xx_create_notifications_table.php
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('type');
    $table->string('title');
    $table->text('message');
    $table->json('data')->nullable();
    $table->timestamp('read_at')->nullable();
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});

// migrations/xxxx_xx_xx_create_holidays_table.php
Schema::create('holidays', function (Blueprint $table) {
    $table->id();
    $table->date('date');
    $table->string('name');
    $table->year('year');
    $table->timestamps();
});
```

---

## 🎨 Customization Tips

### Mengubah Warna Utama (Primary Color)

Edit di `resources/css/variables.css` atau `app.css`:

```css
:root {
    --sh-primary: #2563eb;      /* Ubah warna utama */
    --sh-primary-light: #dbeafe;
    --sh-primary-dark: #1e40af;
}
```

### Mengubah Theme Switcher Default

Edit di `theme-switcher.blade.php`:

```javascript
// Ganti default theme
const savedTheme = localStorage.getItem('sh-theme') || 'light'; // Ubah ke 'dark' atau 'auto'
```

---

## 📱 Responsive Design

Semua komponen sudah responsif untuk:
- Desktop (1200px+)
- Tablet (768px - 1200px)
- Mobile (< 768px)

Pastikan viewport meta tag di layout:
```html
<meta name="viewport" content="width=device-width, initial-scale=1.0">
```

---

## 🚨 Troubleshooting

| Issue | Solusi |
|-------|--------|
| Komponen tidak muncul | Pastikan path `@include` benar |
| Style tidak diterapkan | Pastikan CSS variables sudah didefinisikan |
| Dark mode tidak bekerja | Cek localStorage, clear browser cache |
| Calendar data tidak muncul | Verify data format array dengan keys Y-m-d |
| Export tidak berfungsi | Pastikan routes sudah didefinisikan |

---

## 📞 Support & Questions

Untuk pertanyaan atau perbaikan, silakan:
1. Check dokumentasi component
2. Review example di integration guide ini
3. Debug dengan browser DevTools
4. Konsultasi dengan tim development

---

**Last Updated:** {{ date('d M Y') }}
**Version:** 1.0.0
