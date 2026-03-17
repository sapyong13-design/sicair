# Sprint C — Admin UX: Design Spec

**Tanggal:** 2026-03-17
**Status:** Draft
**Scope:** Name search di `/keputusan` + Bulk "Teruskan ke Ketua" untuk atasan

---

## Tujuan

Dua peningkatan UX untuk role reviewer:

1. **Name search** — ketua dan atasan bisa filter pengajuan berdasarkan nama pegawai di halaman `/keputusan`
2. **Bulk pertimbangan** — atasan bisa meneruskan banyak pengajuan ke ketua sekaligus (satu klik), mengurangi repetisi aksi individual

---

## Pendekatan: Progressive Enhancement

Tidak ada perubahan DB schema, tidak ada route resource baru yang besar. Satu endpoint POST baru, satu method controller baru, dua file view yang dimodifikasi, satu file filter yang dimodifikasi.

---

## File yang Dimodifikasi / Dibuat

| File | Perubahan |
|---|---|
| `resources/views/keputusan/_filter.blade.php` | Tambah `<input name="q">` untuk name search |
| `resources/views/keputusan/index.blade.php` | Tambah bulk-pertimbangan form + checkboxes di atasan view |
| `app/Http/Controllers/KeputusanController.php` | Tambah `applyNameFilter()`, panggil di ketua view + atasan review tab |
| `app/Http/Controllers/LeaveRequestController.php` | Tambah method `bulkPertimbangan()` |
| `routes/web.php` | Modifikasi grup `role:atasan,...` (tambah `wakil_ketua`) + tambah route `leave.bulk-pertimbangan` |

---

## Fitur 1: Name Search di `/keputusan`

### Scope

Name search diapply ke:
- **Ketua view**: tab "Menunggu Keputusan" (`$menunggQuery`) dan tab "Riwayat" (`$riwayatQuery`)
- **Atasan view**: tab "Review Bawahan" (`$reviewQuery`) saja — tab "Pengajuan Saya" menampilkan pengajuan milik user sendiri, filter nama tidak relevan
- **Pegawai view**: tidak ada name search (pegawai hanya melihat pengajuan sendiri)

### UI

`_filter.blade.php` menggunakan pola `@if($showX ?? false)` untuk semua input yang ada (`$showStatus`, `$showType`, `$showDate`). Name search mengikuti pola yang sama dengan variable `$showNameSearch`.

Tambah di `_filter.blade.php` (sebelum baris tombol submit):

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
```

**Call sites** — setiap `@include('keputusan._filter', [...])` di `index.blade.php` harus pass `'showNameSearch'`.

Penting: struktur existing di `index.blade.php` menggunakan **satu shared `@include`** per view branch, bukan satu include per tab. Karena itu:

- **Ketua view** (line ~54): satu `@include` sebelum tab conditionals. Tambah `'showNameSearch' => true` — berlaku untuk kedua tab (menunggu dan riwayat). Controller memanggil `applyNameFilter` di keduanya.

- **Atasan view** (line ~192): satu `@include` sebelum tab conditionals. Tambah `'showNameSearch' => true` unconditionally — input akan muncul di kedua tab, tapi controller hanya memanggil `applyNameFilter` di `$reviewQuery` (bukan `$pengajuanQuery`). UX: q input tampil di tab "Pengajuan Saya" tapi tidak mempengaruhi hasil — acceptable, tidak perlu split include.

- **Pegawai view** (line ~257): `@include` yang berbeda. Tidak perlu pass `showNameSearch` (default `false`).

Tidak perlu memecah (split) include yang sudah ada menjadi per-tab.

Nilai persist via `value="{{ request('q') }}"`. Tidak ada JS, tidak ada autocomplete.

### Controller

Tambah private method di `KeputusanController`:

```php
private function applyNameFilter($query, Request $request): void
{
    if (!$request->filled('q')) return;
    $q = $request->q;
    $query->whereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
}
```

Panggil `$this->applyNameFilter($query, $request)` setelah filter lain di:
- `ketuaView()`: `$menunggQuery` dan `$riwayatQuery`
- `atasanView()`: `$reviewQuery` saja (bukan `$pengajuanQuery`)

---

## Fitur 2: Bulk "Teruskan ke Ketua" untuk Atasan

### Endpoint Baru

```
POST /leave/bulk-pertimbangan
Name: leave.bulk-pertimbangan
Controller: LeaveRequestController@bulkPertimbangan
Middleware: auth
```

### Guard

Route middleware `role:atasan,panitera,sekretaris,ketua,admin` (inherited dari grup route) sudah menangani akses control — tidak perlu in-controller `abort(403)`. Cukup andalkan `atasan_reviewer_id === $user->id` check di dalam query loop sebagai data guard.

Catatan: ketua dan admin secara teknis bisa POST ke endpoint ini via middleware, tetapi query `->where('atasan_reviewer_id', $user->id)` akan mengembalikan nol hasil karena mereka tidak terdaftar sebagai `atasan_reviewer` di pengajuan bawahan. Hasilnya: "0 pengajuan berhasil diteruskan" — graceful, tidak berbahaya.

UI form bulk-pertimbangan hanya dirender di `atasanView` branch (`isAtasan()` = atasan, panitera, sekretaris, wakil_ketua) — ketua dan admin tidak akan melihat form ini di UI normal.

### Validasi Request

```php
$request->validate([
    'ids'   => 'required|array|min:1',
    'ids.*' => 'exists:leave_requests,id',
]);
```

### Logic

Untuk setiap ID:
1. Load `LeaveRequest` dengan guard: `atasan_reviewer_id === auth()->id()`
2. Jika status bukan `diajukan` atau `pending` → skip (tidak abort)
3. Set status ke `pertimbangan_atasan` dan save

IDs yang tidak lolos guard atau kondisi status di-skip silently. Hanya proses yang berhasil yang dihitung.

### Flash Message

```php
session()->flash('success', "{$count} pengajuan berhasil diteruskan ke ketua.");
```

### Redirect

```php
return redirect()->route('keputusan.index', ['tab' => 'review']);
```

Route name `keputusan.index` sudah dikonfirmasi ada di `routes/web.php` line 111:
`Route::get('/keputusan', [KeputusanController::class, 'index'])->name('keputusan.index');`

### Controller Method Lengkap

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

    $user = auth()->user();
    // Tidak perlu abort(403) di sini — route middleware 'role:atasan,...' sudah menangani.
    // Guard data di bawah (atasan_reviewer_id === $user->id) mencegah cross-user manipulation.

    $count = 0;
    foreach ($request->ids as $id) {
        $leave = LeaveRequest::where('id', $id)
            ->where('atasan_reviewer_id', $user->id)
            ->whereIn('status', [LeaveRequest::STATUS_DIAJUKAN, LeaveRequest::STATUS_PENDING])
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

### UI di `keputusan/index.blade.php`

Tambah di atasan view, tab 'review' saja. Pola identik dengan bulk form ketua yang sudah ada:

1. Wrap tabel `$review` dalam `<form method="POST" action="{{ route('leave.bulk-pertimbangan') }}">` + `@csrf`
2. Tambah kolom checkbox di thead (select-all) dan di setiap row (hanya untuk item `status === diajukan || pending`)
3. Tombol submit "Teruskan ke Ketua" di bawah tabel

**Checkbox select-all** (thead):
```html
<th><input type="checkbox" onclick="document.querySelectorAll('.sc-bulk-cb').forEach(cb => cb.checked = this.checked)"></th>
```

**Checkbox per row** (hanya jika actionable):
```blade
<td>
    @if(in_array($leave->status, [\App\Models\LeaveRequest::STATUS_DIAJUKAN, \App\Models\LeaveRequest::STATUS_PENDING]))
        <input type="checkbox" name="ids[]" value="{{ $leave->id }}" class="sc-bulk-cb form-check-input">
    @else
        <span></span>
    @endif
</td>
```

**Catatan JS:** Gunakan class `sc-bulk-cb` (bukan `bulk-cb`) agar sesuai dengan JS yang sudah ada di `@push('scripts')` di `index.blade.php` (line ~266 yang mendengarkan `.sc-bulk-cb`). Tidak perlu JS baru — kedua branch (ketua dan atasan) tidak pernah ada di DOM bersamaan, jadi tidak ada konflik ID.

**Tombol submit**:
```html
<button type="submit" class="btn btn-primary btn-sm">
    <i class="ti ti-send"></i> Teruskan ke Ketua
</button>
```

---

## Route

Tambah di `routes/web.php`. Grup `role:atasan,...` yang ada di lines ~182–184 **tidak mencakup `wakil_ketua`**, padahal `User::isAtasan()` mencakupnya — ini adalah pre-existing gap yang menyebabkan wakil_ketua mendapat 403 saat submit review. Sprint C memperbaiki ini sekaligus.

**Ganti** grup yang ada:

```php
// SEBELUM (lines ~182-184):
Route::middleware('role:atasan,panitera,sekretaris,ketua,admin')->group(function () {
    Route::post('/leave/{leaveRequest}/review', [LeaveRequestController::class, 'reviewAtasan'])->name('leave.review');
});

// SESUDAH — tambah wakil_ketua + route baru:
Route::middleware('role:atasan,panitera,sekretaris,wakil_ketua,ketua,admin')->group(function () {
    Route::post('/leave/{leaveRequest}/review', [LeaveRequestController::class, 'reviewAtasan'])->name('leave.review');
    Route::post('/leave/bulk-pertimbangan', [LeaveRequestController::class, 'bulkPertimbangan'])->name('leave.bulk-pertimbangan');
});
```

**Jangan** letakkan di dalam grup `role:ketua,admin` (yang berisi `bulk-decide`) — grup tersebut akan menghalangi atasan dari mengakses endpoint ini.

---

## Error Handling

| Skenario | Handling |
|---|---|
| ID bukan milik bawahan atasan ini | Skip (tidak abort, tidak flash error) |
| Status sudah bukan diajukan/pending | Skip |
| Semua ID di-skip, `$count === 0` | Flash "0 pengajuan berhasil diteruskan ke ketua." |
| User bukan role yang diizinkan (misal pegawai) | 403 dari route middleware — tidak ada kode controller yang perlu ditambah |
| User adalah ketua/admin (lolos middleware, bukan `atasan_reviewer`) | Query mengembalikan 0 hasil → flash "0 pengajuan berhasil diteruskan ke ketua." |
| `ids` array kosong atau tidak dikirim | Validation error (redirect back) |

---

## Testing

Smoke test existing yang mencakup:

| Test | Assertion |
|---|---|
| `test_keputusan_loads_for_atasan` | GET `/keputusan` (auth atasan) → 200 |
| `test_keputusan_loads_for_ketua` | GET `/keputusan` (auth ketua) → 200 |
| `test_keputusan_loads_for_pegawai` | GET `/keputusan` (auth pegawai) → 200 |
| `test_keputusan_tab_riwayat_loads_for_ketua` | GET `/keputusan?tab=riwayat` → 200 |
| `test_keputusan_tab_pengajuan_loads_for_atasan` | GET `/keputusan?tab=pengajuan` → 200 |
| `test_keputusan_filter_by_type` | GET `/keputusan?type=tahunan` → 200 |

Test baru yang perlu ditambah (endpoint POST baru tidak tercakup smoke test):

| Test | Assertion |
|---|---|
| `test_bulk_pertimbangan_updates_status` | POST `/leave/bulk-pertimbangan` (auth atasan, IDs valid) → redirect + status berubah ke `pertimbangan_atasan`. **Setup fixture:** buat `LeaveRequest` dengan `atasan_reviewer_id = $atasan->id` dan status `diajukan` atau `pending` — tanpa ini loop skip semua dan `$count === 0`. |
| `test_bulk_pertimbangan_forbidden_for_pegawai` | POST `/leave/bulk-pertimbangan` (auth pegawai) → 403 |

---

## Out-of-Scope Sprint C

- Name search di `/pegawai` (sudah ada)
- Bulk reject / tangguhkan untuk atasan
- Notifikasi email/push setelah bulk pertimbangan
- Real-time update setelah bulk action
- Name search di `/leave/saya` atau dashboard
- Stepper di halaman lain
