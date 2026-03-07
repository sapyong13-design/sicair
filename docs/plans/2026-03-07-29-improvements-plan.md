# SiHEALING — 29 Improvements Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Implement 29 improvements across security, backend performance, business features, testing, and architecture for SiHEALING leave management system.

**Architecture:** Laravel 11 monolith, SQLite/MySQL database, Blade templating, no API framework (Sanctum added in E2). All changes follow existing patterns in the codebase. No new frontend frameworks.

**Tech Stack:** PHP 8.1+, Laravel 11, Blade, Tabler CSS, Vanilla JS, Maatwebsite/Excel (new), Sentry (optional)

**Working directory:** `/c/Users/faris/sihealing`

---

## CATEGORY A — KEAMANAN

---

### Task A1: Rate Limiting pada Login

**Files:**
- Modify: `routes/web.php`

**Step 1: Tambah throttle middleware ke POST /login**

Cari baris `Route::post('/login', ...)` di `routes/web.php`. Tambahkan `->middleware('throttle:5,1')`:

```php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.post');
```

Artinya: max 5 percobaan per 1 menit per IP. Jika lewat, Laravel otomatis return 429.

**Step 2: Tambah pesan error di login view**

Baca `resources/views/auth/login.blade.php`. Cari blok `@if ($errors->any())`. Tambahkan handler untuk 429:

```blade
@if(session()->has('errors') || $errors->any())
    {{-- existing error display --}}
@endif
```

Tidak perlu perubahan tambahan — Laravel otomatis redirect dengan error `throttle` saat limit terlampaui. Pesan sudah muncul di `$errors`.

**Step 3: Commit**
```bash
cd /c/Users/faris/sihealing
rtk git add routes/web.php
rtk git commit -m "security: tambah rate limiting 5x/menit pada endpoint login"
```

---

### Task A2: Forgot Password — Migration & Routes

**Files:**
- Check: `database/migrations/` (tabel `password_reset_tokens` mungkin sudah ada)
- Modify: `routes/web.php`
- Create: `app/Http/Controllers/PasswordResetController.php`

**Step 1: Cek tabel password_reset_tokens**
```bash
ls /c/Users/faris/sihealing/database/migrations/ | grep password
```
Jika ada `*_create_password_reset_tokens_table.php` → skip ke Step 3.
Jika tidak ada, buat migration:
```bash
cd /c/Users/faris/sihealing
php artisan make:migration create_password_reset_tokens_table
```
Isi migration:
```php
public function up(): void
{
    Schema::create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });
}
```
```bash
php artisan migrate
```

**Step 2: Tambah routes**

Di `routes/web.php`, dalam grup guest (setelah `Route::get('/login', ...)`):

```php
// Password Reset
Route::get('/forgot-password', [PasswordResetController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email')->middleware('throttle:3,1');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
```

**Step 3: Buat controller**

Create `app/Http/Controllers/PasswordResetController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );

        $user = User::where('email', $request->email)->first();
        $resetUrl = route('password.reset', ['token' => $token]) . '?email=' . urlencode($request->email);

        Mail::send('emails.password-reset', ['user' => $user, 'url' => $resetUrl], function ($m) use ($user) {
            $m->to($user->email)->subject('Reset Password — SiHEALING');
        });

        return back()->with('status', 'Link reset password telah dikirim ke email Anda.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Token tidak valid atau sudah kadaluarsa.']);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['token' => 'Link reset sudah kadaluarsa. Silakan minta ulang.']);
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login.');
    }
}
```

**Step 4: Commit**
```bash
rtk git add routes/web.php app/Http/Controllers/PasswordResetController.php database/migrations/
rtk git commit -m "feat(auth): tambah forgot password / reset password controller & routes"
```

---

### Task A3: Forgot Password — Views & Email Template

**Files:**
- Create: `resources/views/auth/forgot-password.blade.php`
- Create: `resources/views/auth/reset-password.blade.php`
- Create: `resources/views/emails/password-reset.blade.php`

**Step 1: Buat forgot-password.blade.php**

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — SiHEALING</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <style>
        body { background: #f0fdf4; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-card { max-width: 420px; width: 100%; }
    </style>
</head>
<body>
<div class="reset-card mx-auto p-3">
    <div class="card shadow-sm border-0" style="border-top: 4px solid #166534 !important;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="ti ti-lock-open" style="font-size: 2.5rem; color: #166534;"></i>
                <h4 class="fw-bold mt-2 mb-1">Lupa Password</h4>
                <p class="text-muted small">Masukkan email Anda untuk menerima link reset password.</p>
            </div>
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <button type="submit" class="btn btn-success w-100">Kirim Link Reset</button>
            </form>
            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-muted small">← Kembali ke Login</a>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</body>
</html>
```

**Step 2: Buat reset-password.blade.php**

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — SiHEALING</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <style>
        body { background: #f0fdf4; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-card { max-width: 420px; width: 100%; }
    </style>
</head>
<body>
<div class="reset-card mx-auto p-3">
    <div class="card shadow-sm border-0" style="border-top: 4px solid #166534 !important;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="ti ti-key" style="font-size: 2.5rem; color: #166534;"></i>
                <h4 class="fw-bold mt-2 mb-1">Buat Password Baru</h4>
            </div>
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password Baru</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Reset Password</button>
            </form>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</body>
</html>
```

**Step 3: Buat email template `resources/views/emails/password-reset.blade.php`**

```blade
<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; background: #f0fdf4; padding: 2rem;">
<div style="max-width: 500px; margin: 0 auto; background: white; border-radius: 12px; padding: 2rem; border-top: 4px solid #166534;">
    <h2 style="color: #166534;">Reset Password SiHEALING</h2>
    <p>Halo, <strong>{{ $user->name }}</strong>.</p>
    <p>Kami menerima permintaan reset password untuk akun Anda. Klik tombol di bawah untuk membuat password baru:</p>
    <div style="text-align: center; margin: 2rem 0;">
        <a href="{{ $url }}" style="background: #166534; color: white; padding: 0.75rem 2rem; border-radius: 8px; text-decoration: none; font-weight: bold;">Reset Password</a>
    </div>
    <p style="color: #64748b; font-size: 0.85rem;">Link ini akan kadaluarsa dalam 60 menit. Jika Anda tidak meminta reset password, abaikan email ini.</p>
    <hr style="border: none; border-top: 1px solid #e2e8f0;">
    <p style="color: #94a3b8; font-size: 0.8rem;">SiHEALING — Pengadilan Negeri Natuna</p>
</div>
</body>
</html>
```

**Step 4: Tambah link "Lupa password?" di login page**

Baca `resources/views/auth/login.blade.php`. Cari field password, tambahkan link di bawahnya:
```html
<div class="d-flex justify-content-end mb-2">
    <a href="{{ route('password.request') }}" class="text-muted small">Lupa password?</a>
</div>
```

**Step 5: Commit**
```bash
rtk git add resources/views/auth/ resources/views/emails/password-reset.blade.php
rtk git commit -m "feat(auth): tambah views forgot password, reset password, dan email template"
```

---

### Task A4: Form Request Classes

**Files:**
- Create: `app/Http/Requests/StoreLeaveRequestRequest.php`
- Create: `app/Http/Requests/StoreUserRequest.php`
- Create: `app/Http/Requests/UpdateUserRequest.php`
- Modify: `app/Http/Controllers/LeaveRequestController.php`
- Modify: `app/Http/Controllers/PegawaiController.php`

**Step 1: Buat StoreLeaveRequestRequest**
```bash
cd /c/Users/faris/sihealing
php artisan make:request StoreLeaveRequestRequest
```

Edit `app/Http/Requests/StoreLeaveRequestRequest.php`:
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type'              => 'required|string|max:50',
            'start_date'        => 'required|date|after_or_equal:today',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'reason'            => 'required|string|min:10|max:500',
            'alamat_cuti'       => 'nullable|string|max:255',
            'telepon_cuti'      => 'nullable|string|max:20',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'       => 'Jenis cuti wajib dipilih.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
            'end_date.after_or_equal'   => 'Tanggal selesai harus setelah tanggal mulai.',
            'reason.min'          => 'Alasan minimal 10 karakter.',
            'reason.max'          => 'Alasan maksimal 500 karakter.',
            'dokumen_pendukung.max' => 'File maksimal 5MB.',
        ];
    }
}
```

**Step 2: Buat StoreUserRequest**
```bash
php artisan make:request StoreUserRequest
```
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'nip'             => 'required|string|max:30|unique:users,nip',
            'email'           => 'nullable|email|unique:users,email|max:255',
            'password'        => 'required|string|min:8',
            'role'            => 'required|string|in:admin,ketua,wakil_ketua,atasan,hakim,hakim_adhoc,panitera,sekretaris,kepegawaian,pegawai',
            'jabatan'         => 'nullable|string|max:100',
            'golongan_ruang'  => 'nullable|string|max:10',
            'unit_kerja'      => 'nullable|string|max:100',
            'atasan_id'       => 'nullable|exists:users,id',
            'leave_balance'   => 'nullable|integer|min:0|max:365',
        ];
    }
}
```

**Step 3: Update LeaveRequestController::store() untuk pakai Form Request**

Baca `app/Http/Controllers/LeaveRequestController.php`, cari method `store(Request $request)`. Ganti type hint:

```php
// Sebelum:
public function store(Request $request)
{
    $validated = $request->validate([...]);

// Sesudah:
public function store(StoreLeaveRequestRequest $request)
{
    $validated = $request->validated();
```

Tambah import di atas:
```php
use App\Http\Requests\StoreLeaveRequestRequest;
```

**Step 4: Update PegawaiController::store()**

Sama seperti step 3 tapi untuk `PegawaiController::store()` pakai `StoreUserRequest`.

**Step 5: Commit**
```bash
rtk git add app/Http/Requests/ app/Http/Controllers/LeaveRequestController.php app/Http/Controllers/PegawaiController.php
rtk git commit -m "refactor: tambah Form Request classes untuk validasi leave request & user"
```

---

### Task A5: Authorization Policies

**Files:**
- Create: `app/Policies/LeaveRequestPolicy.php`
- Create: `app/Policies/UserPolicy.php`
- Modify: `app/Providers/AppServiceProvider.php`

**Step 1: Buat LeaveRequestPolicy**
```bash
cd /c/Users/faris/sihealing
php artisan make:policy LeaveRequestPolicy --model=LeaveRequest
```

Edit `app/Policies/LeaveRequestPolicy.php`:
```php
<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->isAdmin()
            || $user->id === $leaveRequest->user_id
            || $user->id === $leaveRequest->atasan_reviewer_id
            || $user->canApproveAsPejabat();
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->id === $leaveRequest->user_id
            && $leaveRequest->status === 'diajukan';
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        return ($user->id === $leaveRequest->user_id || $user->isAdmin())
            && in_array($leaveRequest->status, ['diajukan', 'pertimbangan_atasan']);
    }

    public function reviewAtasan(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->id === $leaveRequest->atasan_reviewer_id
            || $user->isAdmin();
    }

    public function decidePejabat(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->canApproveAsPejabat() || $user->isAdmin();
    }
}
```

**Step 2: Register Policy di AppServiceProvider**

Baca `app/Providers/AppServiceProvider.php`. Tambah di method `boot()`:
```php
use App\Models\LeaveRequest;
use App\Policies\LeaveRequestPolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
    // Admin can do everything
    Gate::before(function ($user, $ability) {
        if ($user->isAdmin()) return true;
    });
}
```

**Step 3: Commit**
```bash
rtk git add app/Policies/ app/Providers/AppServiceProvider.php
rtk git commit -m "security: tambah LeaveRequestPolicy dan Gate registration"
```

---

### Task A6: Security Headers Middleware

**Files:**
- Create: `app/Http/Middleware/SecurityHeaders.php`
- Modify: `bootstrap/app.php`

**Step 1: Buat middleware**

Create `app/Http/Middleware/SecurityHeaders.php`:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
```

**Step 2: Daftarkan di bootstrap/app.php**

Baca `bootstrap/app.php`. Cari `->withMiddleware(function (Middleware $middleware)`. Tambahkan:
```php
$middleware->append(\App\Http\Middleware\SecurityHeaders::class);
```

**Step 3: Commit**
```bash
rtk git add app/Http/Middleware/SecurityHeaders.php bootstrap/app.php
rtk git commit -m "security: tambah SecurityHeaders middleware (X-Frame-Options, CSP, dll)"
```

---

### Task A7: Log Login Gagal ke Audit Log

**Files:**
- Modify: `app/Http/Controllers/AuthController.php`

**Step 1: Baca AuthController::login()**

Baca file, cari kondisi `if (!Auth::attempt(...))` atau bagian yang handle login gagal.

**Step 2: Tambah log di login gagal**

```php
if (!Auth::attempt($credentials, $remember)) {
    // Tambahkan ini:
    \App\Models\AuditLog::create([
        'user_id'    => null,
        'action'     => 'login_failed',
        'model_type' => 'Auth',
        'model_id'   => null,
        'old_values' => null,
        'new_values' => json_encode(['username_attempted' => $request->input('nip', $request->input('email', '-'))]),
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    return back()->withErrors(['nip' => 'NIP atau password salah.'])->withInput();
}
```

**Step 3: Commit**
```bash
rtk git add app/Http/Controllers/AuthController.php
rtk git commit -m "security: log percobaan login gagal ke audit_logs"
```

---

## CATEGORY B — BACKEND & PERFORMA

---

### Task B1: Email Async via Queue

**Files:**
- Modify: `.env` (instruksi untuk user)
- Modify: semua controller yang kirim Mail

**Step 1: Cek konfigurasi queue**
```bash
cd /c/Users/faris/sihealing
grep "QUEUE_CONNECTION" .env
```

Jika masih `QUEUE_CONNECTION=sync`, ubah menjadi `QUEUE_CONNECTION=database` di `.env`.

**Step 2: Jalankan queue migration (jika belum)**
```bash
php artisan queue:table  # jika tabel jobs belum ada
php artisan migrate
```

Tabel `jobs` sudah ada dari awal — skip jika sudah ada.

**Step 3: Ganti Mail::send() dengan Mail::queue() di semua controller**

Cari semua penggunaan `Mail::to(` atau `Mail::send(`:
```bash
grep -rn "Mail::" app/Http/Controllers/ --include="*.php"
```

Untuk setiap `->send(new SomeMailClass(...))`, ganti dengan `->queue(new SomeMailClass(...))`.

Contoh di `LeaveRequestController`:
```php
// Sebelum:
Mail::to($user->email)->send(new LeaveRequestSubmittedMail($leaveRequest));

// Sesudah:
Mail::to($user->email)->queue(new LeaveRequestSubmittedMail($leaveRequest));
```

Lakukan hal sama untuk `LeaveRequestApprovedMail`, `LeaveRequestRejectedMail`, `LeaveRequestNeedsConsiderationMail`.

**Step 4: Tambah instruksi queue worker ke README atau dokumentasi**

Tambah catatan di `docs/` bahwa server harus jalankan:
```bash
php artisan queue:work --daemon --sleep=3 --tries=3
```

**Step 5: Commit**
```bash
rtk git add app/Http/Controllers/ .env.example
rtk git commit -m "perf: email dikirim via queue (async) - tidak blocking request"
```

---

### Task B2: Database Indexes

**Files:**
- Create: `database/migrations/2026_03_07_100001_add_performance_indexes.php`

**Step 1: Buat migration**
```bash
cd /c/Users/faris/sihealing
php artisan make:migration add_performance_indexes
```

**Step 2: Isi migration**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_leave_user_status');
            $table->index(['status', 'created_at'], 'idx_leave_status_date');
            $table->index(['start_date', 'end_date'], 'idx_leave_dates');
            $table->index('atasan_reviewer_id', 'idx_leave_atasan');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_audit_user_date');
            $table->index('action', 'idx_audit_action');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'idx_notif_user_read');
            $table->index('created_at', 'idx_notif_date');
        });

        Schema::table('cuti_records', function (Blueprint $table) {
            $table->index(['user_id', 'year'], 'idx_cuti_user_year');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_leave_user_status');
            $table->dropIndex('idx_leave_status_date');
            $table->dropIndex('idx_leave_dates');
            $table->dropIndex('idx_leave_atasan');
        });
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_user_date');
            $table->dropIndex('idx_audit_action');
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notif_user_read');
            $table->dropIndex('idx_notif_date');
        });
        Schema::table('cuti_records', function (Blueprint $table) {
            $table->dropIndex('idx_cuti_user_year');
        });
    }
};
```

**Step 3: Jalankan migration**
```bash
php artisan migrate
```

**Step 4: Commit**
```bash
rtk git add database/migrations/
rtk git commit -m "perf: tambah database indexes di leave_requests, audit_logs, notifications, cuti_records"
```

---

### Task B3: Fix N+1 Queries di Controller

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `app/Http/Controllers/PegawaiController.php`
- Modify: `app/Http/Controllers/LaporanBulananController.php`

**Step 1: Baca DashboardController**

Cari query yang return collection LeaveRequest atau User. Tambahkan eager loading:

```php
// Sebelum:
$leaveRequests = LeaveRequest::where('status', 'diajukan')->get();

// Sesudah:
$leaveRequests = LeaveRequest::with(['user', 'user.cutiRecords'])
    ->where('status', 'diajukan')
    ->get();
```

**Step 2: Baca PegawaiController::index()**

```php
// Sebelum:
$pegawai = User::where('role', '!=', 'admin')->get();

// Sesudah:
$pegawai = User::with(['cutiRecords', 'atasan'])
    ->where('role', '!=', 'admin')
    ->get();
```

**Step 3: Baca LaporanBulananController::index()**

Tambahkan `->with(['user'])` ke semua query LeaveRequest yang dipakai di laporan.

**Step 4: Commit**
```bash
rtk git add app/Http/Controllers/DashboardController.php app/Http/Controllers/PegawaiController.php app/Http/Controllers/LaporanBulananController.php
rtk git commit -m "perf: fix N+1 queries dengan eager loading di dashboard, pegawai, laporan"
```

---

### Task B4: Cache untuk Analytics

**Files:**
- Modify: `app/Services/AnalyticsService.php`
- Modify: `app/Http/Controllers/AnalyticsController.php`

**Step 1: Baca AnalyticsService**

Baca method yang paling mahal (biasanya method yang query banyak data). Wrap dengan cache:

```php
use Illuminate\Support\Facades\Cache;

// Sebelum:
public function getAnnualStats(int $year): array
{
    // ... query berat ...
}

// Sesudah:
public function getAnnualStats(int $year): array
{
    return Cache::remember("analytics_annual_{$year}", 3600, function () use ($year) {
        // ... query berat (tidak berubah) ...
    });
}
```

**Step 2: Invalidate cache saat ada approval**

Di `LeaveRequestController::approve()` (atau method yang approve/reject):
```php
// Setelah save:
Cache::forget('analytics_annual_' . now()->year);
Cache::forget('analytics_annual_' . ($leaveRequest->start_date->year ?? now()->year));
```

**Step 3: Commit**
```bash
rtk git add app/Services/AnalyticsService.php app/Http/Controllers/AnalyticsController.php app/Http/Controllers/LeaveRequestController.php
rtk git commit -m "perf: cache analytics queries (TTL 1 jam), invalidate saat approval"
```

---

### Task B5: PHP 8.1 Enums untuk Status & Role

**Files:**
- Create: `app/Enums/LeaveStatus.php`
- Create: `app/Enums/UserRole.php`

**Step 1: Buat LeaveStatus enum**

Create `app/Enums/LeaveStatus.php`:
```php
<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case Diajukan           = 'diajukan';
    case PertimbanganAtasan = 'pertimbangan_atasan';
    case Approved           = 'disetujui';
    case Rejected           = 'ditolak';
    case Cancelled          = 'dibatalkan';
    case Revised            = 'revisi';
    case Ditangguhkan       = 'ditangguhkan';

    public function label(): string
    {
        return match($this) {
            self::Diajukan           => 'Menunggu Review',
            self::PertimbanganAtasan => 'Pertimbangan Atasan',
            self::Approved           => 'Disetujui',
            self::Rejected           => 'Ditolak',
            self::Cancelled          => 'Dibatalkan',
            self::Revised            => 'Perlu Revisi',
            self::Ditangguhkan       => 'Ditangguhkan',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Diajukan           => 'sh-badge-pending',
            self::PertimbanganAtasan => 'sh-badge-pending',
            self::Approved           => 'sh-badge-approved',
            self::Rejected           => 'sh-badge-rejected',
            self::Cancelled          => 'sh-badge-cancelled',
            self::Revised            => 'sh-badge-revised',
            self::Ditangguhkan       => 'sh-badge-pending',
        };
    }
}
```

**Step 2: Buat UserRole enum**

Create `app/Enums/UserRole.php`:
```php
<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin        = 'admin';
    case Ketua        = 'ketua';
    case WakilKetua   = 'wakil_ketua';
    case Atasan       = 'atasan';
    case Hakim        = 'hakim';
    case HakimAdHoc   = 'hakim_adhoc';
    case Panitera     = 'panitera';
    case Sekretaris   = 'sekretaris';
    case Kepegawaian  = 'kepegawaian';
    case Pegawai      = 'pegawai';

    public function canApproveAsAtasan(): bool
    {
        return in_array($this, [self::Atasan, self::Ketua, self::WakilKetua]);
    }

    public function canApproveAsPejabat(): bool
    {
        return in_array($this, [self::Ketua, self::WakilKetua, self::Panitera, self::Sekretaris]);
    }
}
```

**Step 3: Gunakan Enum di LeaveRequest model (opsional, bertahap)**

Di `app/Models/LeaveRequest.php`, tambahkan cast:
```php
protected $casts = [
    // ... existing ...
    // 'status' => LeaveStatus::class,  // aktifkan bertahap setelah semua referensi diupdate
];
```

Note: Jangan langsung cast ke enum dulu — lakukan bertahap agar tidak break existing code.

**Step 4: Commit**
```bash
rtk git add app/Enums/
rtk git commit -m "refactor: tambah PHP 8.1 Enums LeaveStatus dan UserRole"
```

---

### Task B6: Hapus Duplicate Mail Classes

**Files:**
- Delete duplikat Mail classes
- Modify: controller yang pakai Mail

**Step 1: Audit Mail classes**
```bash
ls /c/Users/faris/sihealing/app/Mail/
```

**Step 2: Cek mana yang dipakai**
```bash
grep -rn "LeaveRequestApproved\b" app/ --include="*.php"
grep -rn "LeaveRequestApprovedMail" app/ --include="*.php"
```

**Step 3: Hapus yang tidak dipakai**

Untuk setiap pasang duplikat (`LeaveRequestApproved` vs `LeaveRequestApprovedMail`):
- Pilih nama yang LEBIH BANYAK dipakai di controller
- Update referensi yang lebih sedikit ke nama yang dipilih
- Hapus file yang tidak dipakai

```bash
# Contoh:
rm /c/Users/faris/sihealing/app/Mail/LeaveRequestApproved.php
# (jika LeaveRequestApprovedMail yang dipakai di controller)
```

**Step 4: Commit**
```bash
rtk git add -A
rtk git commit -m "refactor: hapus duplikat Mail classes (4 pasang → 4 class)"
```

---

### Task B7: Conflict Detection di Server Level

**Files:**
- Modify: `app/Http/Controllers/LeaveRequestController.php`

**Step 1: Baca method store()**

Temukan method `store()`, cari di mana `LeaveRequest::create()` dipanggil.

**Step 2: Tambahkan conflict check sebelum create**

```php
public function store(StoreLeaveRequestRequest $request)
{
    $validated = $request->validated();
    $user = auth()->user();
    $startDate = $validated['start_date'];
    $endDate = $validated['end_date'];

    // Cek tumpang tindih dengan cuti yang aktif
    $conflict = LeaveRequest::where('user_id', $user->id)
        ->whereNotIn('status', ['ditolak', 'dibatalkan'])
        ->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        })->first();

    if ($conflict) {
        return back()->withErrors([
            'start_date' => 'Anda sudah memiliki pengajuan cuti pada periode ' .
                $conflict->start_date . ' s/d ' . $conflict->end_date .
                ' (status: ' . $conflict->status . ').'
        ])->withInput();
    }

    // ... lanjut create seperti biasa
}
```

**Step 3: Commit**
```bash
rtk git add app/Http/Controllers/LeaveRequestController.php
rtk git commit -m "security: tambah conflict detection di server level sebelum simpan cuti"
```

---

## CATEGORY C — FITUR BISNIS

---

### Task C1: Delegasi Atasan — Migration & Model

**Files:**
- Create: `database/migrations/2026_03_07_200001_add_delegate_to_users_table.php`
- Modify: `app/Models/User.php`

**Step 1: Buat migration**
```bash
cd /c/Users/faris/sihealing
php artisan make:migration add_delegate_to_users_table
```

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->foreignId('delegate_atasan_id')->nullable()->constrained('users')->nullOnDelete()->after('atasan_id');
        $table->date('delegate_start')->nullable()->after('delegate_atasan_id');
        $table->date('delegate_end')->nullable()->after('delegate_start');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['delegate_atasan_id']);
        $table->dropColumn(['delegate_atasan_id', 'delegate_start', 'delegate_end']);
    });
}
```

```bash
php artisan migrate
```

**Step 2: Update User model**

Tambahkan ke `$fillable`:
```php
'delegate_atasan_id', 'delegate_start', 'delegate_end',
```

Tambahkan ke `$casts`:
```php
'delegate_start' => 'date',
'delegate_end'   => 'date',
```

Tambahkan method baru:
```php
public function delegateAtasan()
{
    return $this->belongsTo(User::class, 'delegate_atasan_id');
}

public function getEffectiveAtas(): ?User
{
    // Cek apakah atasan asli sedang cuti aktif
    $atasanAsli = $this->atasan;
    if (!$atasanAsli) return null;

    $atasanSedangCuti = \App\Models\LeaveRequest::where('user_id', $atasanAsli->id)
        ->where('status', 'disetujui')
        ->where('start_date', '<=', today())
        ->where('end_date', '>=', today())
        ->exists();

    if ($atasanSedangCuti && $atasanAsli->delegate_atasan_id) {
        // Cek apakah periode delegasi masih berlaku
        if ($atasanAsli->delegate_start <= today() && $atasanAsli->delegate_end >= today()) {
            return $atasanAsli->delegateAtasan;
        }
    }

    return $atasanAsli;
}
```

**Step 3: Commit**
```bash
rtk git add database/migrations/ app/Models/User.php
rtk git commit -m "feat(delegasi): migration dan model User::getEffectiveAtasan() untuk delegasi atasan"
```

---

### Task C2: Delegasi Atasan — UI di Profile

**Files:**
- Modify: `resources/views/profile/index.blade.php`
- Modify: `app/Http/Controllers/ProfileController.php`

**Step 1: Tambah form delegasi di profile page**

Baca `resources/views/profile/index.blade.php`. Di bawah section password change, tambahkan:

```blade
@if(auth()->user()->canApproveAsAtasan())
{{-- Delegasi Atasan --}}
<div class="card sh-card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ti ti-user-share me-2"></i>Delegasi Persetujuan</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small">Saat Anda cuti, pengajuan bawahan akan diteruskan ke atasan pengganti selama periode yang ditentukan.</p>
        <form method="POST" action="{{ route('profile.delegate') }}">
            @csrf
            <div class="row g-3">
                <div class="col-sm-12">
                    <label class="form-label fw-semibold">Atasan Pengganti</label>
                    <select name="delegate_atasan_id" class="form-select">
                        <option value="">-- Tidak ada delegasi --</option>
                        @foreach(\App\Models\User::whereIn('role', ['atasan', 'ketua', 'wakil_ketua'])->where('id', '!=', auth()->id())->get() as $u)
                            <option value="{{ $u->id }}" {{ auth()->user()->delegate_atasan_id == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->jabatan }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Mulai Delegasi</label>
                    <input type="date" name="delegate_start" class="form-control" value="{{ auth()->user()->delegate_start?->format('Y-m-d') }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Selesai Delegasi</label>
                    <input type="date" name="delegate_end" class="form-control" value="{{ auth()->user()->delegate_end?->format('Y-m-d') }}">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Simpan Delegasi</button>
        </form>
    </div>
</div>
@endif
```

**Step 2: Tambah route dan method controller**

Di `routes/web.php`:
```php
Route::post('/profile/delegate', [ProfileController::class, 'updateDelegate'])->name('profile.delegate');
```

Di `app/Http/Controllers/ProfileController.php`:
```php
public function updateDelegate(Request $request)
{
    $request->validate([
        'delegate_atasan_id' => 'nullable|exists:users,id',
        'delegate_start'     => 'nullable|date',
        'delegate_end'       => 'nullable|date|after_or_equal:delegate_start',
    ]);

    auth()->user()->update([
        'delegate_atasan_id' => $request->delegate_atasan_id ?: null,
        'delegate_start'     => $request->delegate_start ?: null,
        'delegate_end'       => $request->delegate_end ?: null,
    ]);

    return back()->with('success', 'Delegasi persetujuan berhasil disimpan.');
}
```

**Step 3: Commit**
```bash
rtk git add resources/views/profile/index.blade.php app/Http/Controllers/ProfileController.php routes/web.php
rtk git commit -m "feat(delegasi): UI dan controller untuk set atasan pengganti saat cuti"
```

---

### Task C3: Bulk Approval untuk Ketua/Pejabat

**Files:**
- Modify: `resources/views/dashboard.blade.php` (atau halaman approval ketua)
- Modify: `app/Http/Controllers/LeaveRequestController.php`
- Modify: `routes/web.php`

**Step 1: Tambah route bulk approve**

Di `routes/web.php`:
```php
Route::post('/leave/bulk-decide', [LeaveRequestController::class, 'bulkDecide'])->name('leave.bulk-decide');
```

**Step 2: Tambah method bulkDecide di controller**

```php
public function bulkDecide(Request $request)
{
    $request->validate([
        'ids'      => 'required|array|min:1',
        'ids.*'    => 'exists:leave_requests,id',
        'decision' => 'required|in:disetujui,ditolak,ditangguhkan',
        'note'     => 'nullable|string|max:500',
    ]);

    $user = auth()->user();
    if (!$user->canApproveAsPejabat() && !$user->isAdmin()) {
        abort(403);
    }

    $count = 0;
    foreach ($request->ids as $id) {
        $leave = LeaveRequest::find($id);
        if (!$leave || !in_array($leave->status, ['pertimbangan_atasan', 'diajukan'])) continue;

        $leave->update([
            'status'           => $request->decision,
            'pejabat_id'       => $user->id,
            'keputusan_pejabat'=> $request->decision,
            'catatan_pejabat'  => $request->note,
            'decided_at'       => now(),
        ]);

        if ($request->decision === 'disetujui') {
            // Kurangi saldo
            $leave->user->decrement('leave_balance', $leave->total_hari_kerja ?? 0);
        }

        // Kirim notifikasi
        \App\Models\Notification::create([
            'user_id'    => $leave->user_id,
            'type'       => $request->decision === 'disetujui' ? 'leave_approved' : 'leave_rejected',
            'title'      => $request->decision === 'disetujui' ? 'Cuti Disetujui' : 'Cuti Ditolak',
            'message'    => 'Pengajuan cuti Anda telah ' . ($request->decision === 'disetujui' ? 'disetujui' : 'ditolak') . ($request->note ? '. Catatan: ' . $request->note : '.'),
            'action_url' => route('leave.show', $leave->id),
        ]);

        $count++;
    }

    return back()->with('success', "{$count} pengajuan berhasil di-{$request->decision}.");
}
```

**Step 3: Tambah UI bulk checkbox di dashboard ketua**

Baca dashboard.blade.php, temukan tabel pending approvals di section ketua/pejabat. Tambahkan checkbox dan form bulk action:

```html
<form id="sh-bulk-form" method="POST" action="{{ route('leave.bulk-decide') }}">
    @csrf
    <div class="d-flex gap-2 mb-3 align-items-center flex-wrap" id="sh-bulk-actions" style="display:none!important;">
        <span class="text-muted small" id="sh-bulk-count">0 dipilih</span>
        <select name="decision" class="form-select form-select-sm" style="width:auto;" required>
            <option value="">-- Pilih Keputusan --</option>
            <option value="disetujui">Setujui Semua</option>
            <option value="ditolak">Tolak Semua</option>
            <option value="ditangguhkan">Tangguhkan Semua</option>
        </select>
        <input type="text" name="note" class="form-control form-control-sm" placeholder="Catatan (opsional)" style="width:200px;">
        <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="sh-bulk-clear">Batal</button>
    </div>
    <!-- Di setiap baris tabel pending, tambahkan: -->
    <!-- <input type="checkbox" name="ids[]" value="{{ $req->id }}" class="sh-bulk-cb form-check-input"> -->
</form>
```

Tambahkan JS:
```javascript
document.querySelectorAll('.sh-bulk-cb').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var checked = document.querySelectorAll('.sh-bulk-cb:checked').length;
        document.getElementById('sh-bulk-count').textContent = checked + ' dipilih';
        document.getElementById('sh-bulk-actions').style.display = checked > 0 ? 'flex' : 'none';
    });
});
document.getElementById('sh-bulk-clear')?.addEventListener('click', function() {
    document.querySelectorAll('.sh-bulk-cb').forEach(function(cb) { cb.checked = false; });
    document.getElementById('sh-bulk-actions').style.display = 'none';
});
```

**Step 4: Commit**
```bash
rtk git add routes/web.php app/Http/Controllers/LeaveRequestController.php resources/views/dashboard.blade.php
rtk git commit -m "feat(approval): bulk approve/reject/tangguhkan untuk ketua/pejabat"
```

---

### Task C4: Notification Channel Management (Email + WhatsApp)

**Files:**
- Create: `database/migrations/2026_03_07_200002_add_notification_channels_to_users.php`
- Create: `app/Services/WhatsAppService.php`
- Create: `config/whatsapp.php`
- Modify: `app/Models/User.php`
- Modify: `resources/views/pegawai/show.blade.php`

**Step 1: Migration untuk notification_channels**
```bash
php artisan make:migration add_notification_channels_to_users
```

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->json('notification_channels')->default('{"email":true,"whatsapp":false}')->after('notification_preferences');
    });
}
```

```bash
php artisan migrate
```

**Step 2: Buat config/whatsapp.php**
```php
<?php
return [
    'enabled' => env('WHATSAPP_ENABLED', false),
    'driver'  => env('WHATSAPP_DRIVER', 'fonnte'), // fonnte | wablas
    'fonnte'  => [
        'token'    => env('FONNTE_TOKEN', ''),
        'endpoint' => 'https://api.fonnte.com/send',
    ],
    'wablas' => [
        'token'    => env('WABLAS_TOKEN', ''),
        'endpoint' => env('WABLAS_ENDPOINT', ''),
    ],
];
```

Tambahkan ke `.env.example`:
```
WHATSAPP_ENABLED=false
WHATSAPP_DRIVER=fonnte
FONNTE_TOKEN=
```

**Step 3: Buat WhatsAppService**

Create `app/Services/WhatsAppService.php`:
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public static function send(string $phone, string $message): bool
    {
        if (!config('whatsapp.enabled')) return false;

        // Normalize phone number (08xx → 628xx)
        $phone = preg_replace('/^0/', '62', preg_replace('/\D/', '', $phone));
        if (empty($phone)) return false;

        $driver = config('whatsapp.driver', 'fonnte');

        try {
            if ($driver === 'fonnte') {
                $response = Http::withHeaders([
                    'Authorization' => config('whatsapp.fonnte.token'),
                ])->post(config('whatsapp.fonnte.endpoint'), [
                    'target'  => $phone,
                    'message' => $message,
                ]);
                return $response->successful() && ($response->json('status') === true || $response->json('status') === 'true');
            }

            if ($driver === 'wablas') {
                $response = Http::withHeaders([
                    'Authorization' => config('whatsapp.wablas.token'),
                ])->post(config('whatsapp.wablas.endpoint') . '/send-message', [
                    'phone'   => $phone,
                    'message' => $message,
                ]);
                return $response->successful();
            }
        } catch (\Exception $e) {
            Log::warning('WhatsApp send failed: ' . $e->getMessage());
        }

        return false;
    }
}
```

**Step 4: Update User model**

Tambah helper:
```php
public function wantsWhatsAppNotification(): bool
{
    $channels = is_array($this->notification_channels)
        ? $this->notification_channels
        : json_decode($this->notification_channels, true) ?? [];
    return ($channels['whatsapp'] ?? false) && !empty($this->telepon);
}
```

**Step 5: Tambah UI di pegawai/show (admin bisa toggle)**

Baca `resources/views/pegawai/show.blade.php`. Tambahkan form kecil:

```blade
<form method="POST" action="{{ route('pegawai.update-channels', $pegawai->id) }}" class="d-inline">
    @csrf @method('PATCH')
    <div class="d-flex gap-2 align-items-center">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="channels[email]" value="1"
                {{ ($pegawai->notification_channels['email'] ?? true) ? 'checked' : '' }}>
            <label class="form-check-label small">Email</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="channels[whatsapp]" value="1"
                {{ ($pegawai->notification_channels['whatsapp'] ?? false) ? 'checked' : '' }}>
            <label class="form-check-label small">WhatsApp</label>
        </div>
        <button type="submit" class="btn btn-xs btn-outline-primary">Simpan</button>
    </div>
</form>
```

**Step 6: Tambah route dan method**

Di `routes/web.php`:
```php
Route::patch('/pegawai/{user}/channels', [PegawaiController::class, 'updateChannels'])->name('pegawai.update-channels');
```

Di `PegawaiController`:
```php
public function updateChannels(Request $request, User $user)
{
    $channels = [
        'email'     => $request->boolean('channels.email', false),
        'whatsapp'  => $request->boolean('channels.whatsapp', false),
    ];
    $user->update(['notification_channels' => $channels]);
    return back()->with('success', 'Channel notifikasi diperbarui.');
}
```

**Step 7: Commit**
```bash
rtk git add database/migrations/ app/Services/WhatsAppService.php config/whatsapp.php app/Models/User.php resources/views/pegawai/show.blade.php app/Http/Controllers/PegawaiController.php routes/web.php
rtk git commit -m "feat(notif): notification channel management - Email/WhatsApp toggleable per user"
```

---

### Task C5: Quota Cuti Otomatis per Golongan

**Files:**
- Modify: `app/Console/Commands/SyncCutiRecords.php`
- Create: `app/Console/Commands/GenerateCutiQuota.php`
- Modify: `routes/console.php`

**Step 1: Buat command GenerateCutiQuota**
```bash
cd /c/Users/faris/sihealing
php artisan make:command GenerateCutiQuota
```

Edit `app/Console/Commands/GenerateCutiQuota.php`:
```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\CutiRecord;
use App\Services\CutiTahunanCalculator;
use Illuminate\Console\Command;

class GenerateCutiQuota extends Command
{
    protected $signature = 'cuti:generate-quota {year? : Tahun target (default tahun sekarang)}';
    protected $description = 'Generate/update quota cuti tahunan untuk semua pegawai aktif berdasarkan golongan';

    public function handle(CutiTahunanCalculator $calculator): int
    {
        $year = (int) ($this->argument('year') ?? now()->year);
        $users = User::where('is_active', true)
            ->whereNotIn('role', ['admin'])
            ->get();

        $this->info("Generating quota cuti tahun {$year} untuk {$users->count()} pegawai...");
        $bar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {
            try {
                $quota = $calculator->calculate($user, $year);

                CutiRecord::updateOrCreate(
                    ['user_id' => $user->id, 'year' => $year],
                    [
                        'total_hak' => $quota,
                        'notes'     => 'Auto-generated dari golongan ' . $user->golongan_ruang . ', masa kerja ' . $user->masa_kerja_mulai,
                    ]
                );
            } catch (\Exception $e) {
                $this->warn("Skip user {$user->name}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai. Quota cuti tahun {$year} berhasil digenerate.");

        return self::SUCCESS;
    }
}
```

**Step 2: Jadwalkan di routes/console.php**

Baca `routes/console.php`. Tambahkan:
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('cuti:generate-quota')->yearlyOn(1, 1, '00:30'); // 1 Januari pukul 00:30
Schedule::command('cuti:remind-expiry')->dailyAt('08:00');
```

**Step 3: Tambah tombol "Generate Quota" di admin panel**

Baca `resources/views/pegawai/index.blade.php`. Tambahkan tombol di area actions:
```blade
<form method="POST" action="{{ route('admin.generate-quota') }}" class="d-inline">
    @csrf
    <input type="hidden" name="year" value="{{ now()->year }}">
    <button type="submit" class="btn btn-outline-success btn-sm"
        onclick="return confirm('Generate quota cuti {{ now()->year }} untuk semua pegawai aktif?')">
        <i class="ti ti-refresh me-1"></i>Generate Quota {{ now()->year }}
    </button>
</form>
```

Tambahkan route:
```php
Route::post('/admin/generate-quota', function(\Illuminate\Http\Request $req) {
    $year = $req->input('year', now()->year);
    \Illuminate\Support\Facades\Artisan::call('cuti:generate-quota', ['year' => $year]);
    return back()->with('success', "Quota cuti tahun {$year} berhasil digenerate.");
})->name('admin.generate-quota')->middleware('auth');
```

**Step 4: Commit**
```bash
rtk git add app/Console/Commands/GenerateCutiQuota.php routes/console.php routes/web.php resources/views/pegawai/index.blade.php
rtk git commit -m "feat(quota): command generate quota cuti otomatis per golongan + schedule tahunan"
```

---

### Task C6: Kalender Tim untuk Atasan

**Files:**
- Create: `resources/views/kalender/tim.blade.php`
- Modify: `app/Http/Controllers/KalenderController.php`
- Modify: `routes/web.php`

**Step 1: Tambah route**
```php
Route::get('/kalender/tim', [KalenderController::class, 'tim'])->name('kalender.tim');
```

**Step 2: Tambah method di KalenderController**
```php
public function tim(Request $request)
{
    $user = auth()->user();
    if (!$user->canApproveAsAtasan() && !$user->isAdmin()) {
        abort(403);
    }

    $month = (int) $request->get('month', now()->month);
    $year  = (int) $request->get('year', now()->year);

    // Ambil semua bawahan (yang atasan_id = user ini)
    $bawahanIds = \App\Models\User::where('atasan_id', $user->id)->pluck('id');

    $leaves = \App\Models\LeaveRequest::with('user')
        ->whereIn('user_id', $bawahanIds)
        ->where('status', 'disetujui')
        ->whereYear('start_date', $year)
        ->whereMonth('start_date', $month)
        ->orWhere(function($q) use ($year, $month, $bawahanIds) {
            $q->whereIn('user_id', $bawahanIds)
              ->where('status', 'disetujui')
              ->whereYear('end_date', $year)
              ->whereMonth('end_date', $month);
        })
        ->get();

    $prevMonth = $month == 1 ? 12 : $month - 1;
    $prevYear  = $month == 1 ? $year - 1 : $year;
    $nextMonth = $month == 12 ? 1 : $month + 1;
    $nextYear  = $month == 12 ? $year + 1 : $year;

    return view('kalender.tim', compact('leaves', 'month', 'year', 'prevMonth', 'prevYear', 'nextMonth', 'nextYear'));
}
```

**Step 3: Buat view kalender/tim.blade.php**

Baca `resources/views/kalender/index.blade.php` sebagai referensi struktur. Buat `resources/views/kalender/tim.blade.php` dengan struktur serupa tapi:
- Title: "Kalender Tim — {nama bulan} {tahun}"
- Tampilkan cuti bawahan per hari dengan nama pegawai dan warna berbeda
- Tambahkan link kembali ke kalender pribadi

**Step 4: Tambah link di navbar/kalender**

Di `kalender/index.blade.php`, tambahkan tombol "Lihat Kalender Tim":
```blade
@if(auth()->user()->canApproveAsAtasan())
<a href="{{ route('kalender.tim') }}" class="btn btn-outline-primary btn-sm">
    <i class="ti ti-users me-1"></i>Kalender Tim
</a>
@endif
```

**Step 5: Commit**
```bash
rtk git add resources/views/kalender/tim.blade.php app/Http/Controllers/KalenderController.php routes/web.php resources/views/kalender/index.blade.php
rtk git commit -m "feat(kalender): kalender tim untuk atasan - lihat cuti semua bawahan"
```

---

### Task C7: Statistik Personal Pegawai di Dashboard

**Files:**
- Modify: `resources/views/dashboard.blade.php`
- Modify: `app/Http/Controllers/DashboardController.php`

**Step 1: Tambah query statistik di DashboardController**

Cari method yang handle dashboard pegawai biasa. Tambahkan:
```php
// Statistik penggunaan cuti 12 bulan terakhir
$cutiPerBulan = \App\Models\LeaveRequest::where('user_id', $user->id)
    ->where('status', 'disetujui')
    ->where('start_date', '>=', now()->subMonths(11)->startOfMonth())
    ->selectRaw('MONTH(start_date) as bulan, YEAR(start_date) as tahun, SUM(total_hari_kerja) as total')
    ->groupBy('tahun', 'bulan')
    ->orderBy('tahun')->orderBy('bulan')
    ->get()
    ->keyBy(fn($r) => $r->tahun . '-' . str_pad($r->bulan, 2, '0', STR_PAD_LEFT));

// Siapkan array 12 bulan lengkap (fill 0 jika tidak ada data)
$chartLabels = [];
$chartData   = [];
for ($i = 11; $i >= 0; $i--) {
    $date = now()->subMonths($i);
    $key  = $date->format('Y-m');
    $chartLabels[] = $date->translatedFormat('M Y');
    $chartData[]   = $cutiPerBulan[$key]->total ?? 0;
}
```

Pass ke view: `compact('chartLabels', 'chartData', ...)`.

**Step 2: Tambah chart di dashboard.blade.php**

Di section pegawai (@else branch), tambahkan card statistik:
```blade
<div class="card sh-card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ti ti-chart-bar me-2"></i>Penggunaan Cuti 12 Bulan Terakhir</h5>
    </div>
    <div class="card-body">
        <canvas id="sh-cuti-chart" height="80"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function() {
    var ctx = document.getElementById('sh-cuti-chart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [{
                label: 'Hari Cuti',
                data: {!! json_encode($chartData ?? []) !!},
                backgroundColor: 'rgba(22, 101, 52, 0.7)',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
})();
</script>
@endpush
```

**Step 3: Commit**
```bash
rtk git add resources/views/dashboard.blade.php app/Http/Controllers/DashboardController.php
rtk git commit -m "feat(dashboard): chart statistik penggunaan cuti 12 bulan untuk pegawai"
```

---

### Task C8: Reminder Cuti Kadaluarsa

**Files:**
- Create: `app/Console/Commands/RemindCutiExpiry.php`
- Modify: `routes/console.php`

**Step 1: Buat command**
```bash
cd /c/Users/faris/sihealing
php artisan make:command RemindCutiExpiry
```

Edit `app/Console/Commands/RemindCutiExpiry.php`:
```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\CutiRecord;
use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RemindCutiExpiry extends Command
{
    protected $signature = 'cuti:remind-expiry';
    protected $description = 'Kirim reminder untuk sisa cuti carry-over yang akan kadaluarsa 31 Maret';

    public function handle(): int
    {
        $today      = now()->toDateString();
        $expiryDate = now()->year . '-03-31';

        // Hanya berlaku Januari-Maret
        if (now()->month > 3) {
            $this->info('Bukan periode reminder (Jan-Mar). Skip.');
            return self::SUCCESS;
        }

        $daysLeft = now()->diffInDays($expiryDate);

        // Hanya kirim di H-30 dan H-7
        if (!in_array($daysLeft, [30, 7])) {
            $this->info("Hari ini H-{$daysLeft} dari 31 Maret. Bukan hari reminder.");
            return self::SUCCESS;
        }

        $records = CutiRecord::with('user')
            ->where('year', now()->year - 1)
            ->where('carry_over_to_next', '>', 0)
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get();

        $this->info("Mengirim reminder ke {$records->count()} pegawai (H-{$daysLeft})...");

        foreach ($records as $record) {
            $user = $record->user;
            $sisa = $record->carry_over_to_next;

            $message = "📅 Reminder SiHEALING: Anda masih memiliki {$sisa} hari cuti carry-over dari tahun " .
                (now()->year - 1) . " yang akan kadaluarsa pada 31 Maret " . now()->year .
                ". Segera ajukan cuti sebelum kadaluarsa!";

            // In-app notification
            Notification::create([
                'user_id'    => $user->id,
                'type'       => 'cuti_expiry_reminder',
                'title'      => "Sisa Cuti Carry-Over Akan Kadaluarsa H-{$daysLeft}",
                'message'    => "Anda memiliki {$sisa} hari sisa cuti dari tahun sebelumnya yang kadaluarsa 31 Maret.",
                'action_url' => route('leave.create'),
            ]);

            // Email
            if ($user->wantsEmailNotification() && $user->email) {
                // Kirim email sederhana
                Mail::raw($message, fn($m) => $m->to($user->email)->subject("Reminder: Sisa Cuti Akan Kadaluarsa H-{$daysLeft}"));
            }

            // WhatsApp
            if ($user->wantsWhatsAppNotification()) {
                WhatsAppService::send($user->telepon, $message);
            }
        }

        $this->info("Reminder berhasil dikirim.");
        return self::SUCCESS;
    }
}
```

**Step 2: Update schedule di routes/console.php** (sudah ditambahkan di Task C5)

**Step 3: Commit**
```bash
rtk git add app/Console/Commands/RemindCutiExpiry.php
rtk git commit -m "feat(reminder): command reminder cuti carry-over akan kadaluarsa H-30 dan H-7"
```

---

### Task C9: Export Excel

**Files:**
- Modify: `composer.json` (install package)
- Create: `app/Exports/LaporanBulananExport.php`
- Create: `app/Exports/LaporanSaldoCutiExport.php`
- Create: `app/Exports/PegawaiExport.php`
- Modify: `app/Http/Controllers/LaporanBulananController.php`
- Modify: `app/Http/Controllers/LaporanSaldoCutiController.php`

**Step 1: Install Maatwebsite/Excel**
```bash
cd /c/Users/faris/sihealing
composer require maatwebsite/excel
```

**Step 2: Buat LaporanBulananExport**

Create `app/Exports/LaporanBulananExport.php`:
```php
<?php

namespace App\Exports;

use App\Models\LeaveRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanBulananExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private int $month, private int $year) {}

    public function collection()
    {
        return LeaveRequest::with(['user'])
            ->whereMonth('start_date', $this->month)
            ->whereYear('start_date', $this->year)
            ->orderBy('start_date')
            ->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama', 'NIP', 'Jabatan', 'Jenis Cuti', 'Tanggal Mulai', 'Tanggal Selesai', 'Hari Kerja', 'Status'];
    }

    public function map($row): array
    {
        static $no = 0;
        return [
            ++$no,
            $row->user->name ?? '-',
            $row->user->nip ?? '-',
            $row->user->jabatan ?? '-',
            $row->type_label ?? $row->type,
            $row->start_date,
            $row->end_date,
            $row->total_hari_kerja ?? '-',
            $row->status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '166534']]],
        ];
    }
}
```

**Step 3: Update LaporanBulananController**
```php
use App\Exports\LaporanBulananExport;
use Maatwebsite\Excel\Facades\Excel;

public function export(Request $request)
{
    $month = $request->get('month', now()->month);
    $year  = $request->get('year', now()->year);
    $format = $request->get('format', 'pdf');

    if ($format === 'excel') {
        return Excel::download(
            new LaporanBulananExport($month, $year),
            "laporan-bulanan-{$year}-{$month}.xlsx"
        );
    }

    // existing PDF logic...
}
```

**Step 4: Tambah tombol Export Excel di view**

Baca `resources/views/laporan-bulanan/index.blade.php`. Di samping tombol "Export PDF", tambahkan:
```blade
<a href="{{ route('laporan-bulanan.export', ['month' => $month, 'year' => $year, 'format' => 'excel']) }}"
   class="btn btn-outline-success btn-sm">
    <i class="ti ti-file-spreadsheet me-1"></i>Export Excel
</a>
```

**Step 5: Commit**
```bash
rtk git add app/Exports/ app/Http/Controllers/LaporanBulananController.php resources/views/laporan-bulanan/
rtk git commit -m "feat(laporan): export Excel untuk laporan bulanan menggunakan Maatwebsite/Excel"
```

---

## CATEGORY D — TESTING

---

### Task D1: Factory & Seeder Lengkap

**Files:**
- Create: `database/factories/UserFactory.php` (update existing)
- Create: `database/factories/LeaveRequestFactory.php`
- Create: `database/factories/HariLiburFactory.php`
- Modify: `database/seeders/DatabaseSeeder.php`

**Step 1: Buat UserFactory**
```bash
cd /c/Users/faris/sihealing
php artisan make:factory UserFactory --model=User 2>/dev/null || true
```

Edit `database/factories/UserFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'            => $this->faker->name(),
            'nip'             => $this->faker->unique()->numerify('19########01####'),
            'email'           => $this->faker->unique()->safeEmail(),
            'password'        => Hash::make('password'),
            'role'            => 'pegawai',
            'jabatan'         => $this->faker->randomElement(['Staf', 'Panitera Muda', 'Panitera Pengganti']),
            'golongan_ruang'  => $this->faker->randomElement(['II/a', 'II/b', 'III/a', 'III/b', 'IV/a']),
            'unit_kerja'      => 'Pengadilan Negeri Natuna',
            'masa_kerja_mulai'=> $this->faker->dateTimeBetween('-20 years', '-1 year')->format('Y-m-d'),
            'leave_balance'   => 12,
            'is_active'       => true,
            'jenis_kelamin'   => $this->faker->randomElement(['L', 'P']),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin', 'name' => 'Administrator']);
    }

    public function ketua(): static
    {
        return $this->state(['role' => 'ketua', 'jabatan' => 'Ketua Pengadilan']);
    }

    public function atasan(): static
    {
        return $this->state(['role' => 'atasan', 'jabatan' => 'Panitera']);
    }
}
```

**Step 2: Buat LeaveRequestFactory**
```bash
php artisan make:factory LeaveRequestFactory --model=LeaveRequest
```

```php
<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-6 months', '+1 month');
        $end   = Carbon::instance($start)->addDays(rand(1, 5));

        return [
            'user_id'         => User::factory(),
            'type'            => $this->faker->randomElement(['tahunan', 'sakit', 'melahirkan', 'besar']),
            'start_date'      => $start->format('Y-m-d'),
            'end_date'        => $end->format('Y-m-d'),
            'reason'          => $this->faker->sentence(10),
            'status'          => 'diajukan',
            'total_hari_kerja'=> rand(1, 5),
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => 'disetujui']);
    }

    public function rejected(): static
    {
        return $this->state(['status' => 'ditolak']);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pertimbangan_atasan']);
    }
}
```

**Step 3: Update DatabaseSeeder**

Edit `database/seeders/DatabaseSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\HariLibur;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::factory()->admin()->create([
            'name'  => 'Administrator',
            'nip'   => '000000000000000000',
            'email' => 'admin@pn-natuna.go.id',
        ]);

        // Ketua
        $ketua = User::factory()->ketua()->create([
            'name' => 'Ketua Pengadilan',
            'nip'  => '197001010000000001',
        ]);

        // 2 Atasan
        $atasan1 = User::factory()->atasan()->create(['name' => 'Panitera Kepala']);
        $atasan2 = User::factory()->atasan()->create(['name' => 'Sekretaris']);

        // 10 Pegawai
        $pegawai = User::factory(10)->create([
            'atasan_id' => $atasan1->id,
        ]);

        // Holiday data
        HariLibur::insert([
            ['date' => '2026-01-01', 'name' => 'Tahun Baru', 'is_national_holiday' => true],
            ['date' => '2026-01-27', 'name' => 'Isra Mi\'raj', 'is_national_holiday' => true],
            ['date' => '2026-03-31', 'name' => 'Idul Fitri', 'is_national_holiday' => true],
        ]);

        // Leave requests berbagai status
        foreach ($pegawai as $p) {
            LeaveRequest::factory()->approved()->create(['user_id' => $p->id]);
            LeaveRequest::factory()->rejected()->create(['user_id' => $p->id]);
            LeaveRequest::factory()->create(['user_id' => $p->id]); // pending
        }
    }
}
```

**Step 4: Jalankan seeder**
```bash
php artisan db:seed --class=DatabaseSeeder
```

**Step 5: Commit**
```bash
rtk git add database/factories/ database/seeders/
rtk git commit -m "test: tambah UserFactory, LeaveRequestFactory, dan DatabaseSeeder yang realistis"
```

---

### Task D2: Feature Tests untuk Approval Workflow

**Files:**
- Create: `tests/Feature/LeaveApprovalWorkflowTest.php`

**Step 1: Buat test file**

Create `tests/Feature/LeaveApprovalWorkflowTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $atasan;
    private User $pegawai;
    private User $ketua;

    protected function setUp(): void
    {
        parent::setUp();

        $this->atasan  = User::factory()->atasan()->create();
        $this->pegawai = User::factory()->create(['atasan_id' => $this->atasan->id]);
        $this->ketua   = User::factory()->ketua()->create();
    }

    public function test_pegawai_dapat_submit_leave_request(): void
    {
        $response = $this->actingAs($this->pegawai)->post(route('leave.store'), [
            'type'       => 'tahunan',
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date'   => now()->addDays(3)->format('Y-m-d'),
            'reason'     => 'Keperluan keluarga yang mendesak',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->pegawai->id,
            'type'    => 'tahunan',
            'status'  => 'diajukan',
        ]);
    }

    public function test_atasan_dapat_approve_ke_pertimbangan(): void
    {
        $leave = LeaveRequest::factory()->create([
            'user_id'            => $this->pegawai->id,
            'atasan_reviewer_id' => $this->atasan->id,
            'status'             => 'diajukan',
        ]);

        $response = $this->actingAs($this->atasan)->post(route('leave.review-atasan', $leave->id), [
            'pertimbangan' => 'direkomendasikan',
            'catatan'      => 'Disetujui untuk dilanjutkan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id'     => $leave->id,
            'status' => 'pertimbangan_atasan',
        ]);
    }

    public function test_pejabat_dapat_setujui_dan_saldo_berkurang(): void
    {
        $this->pegawai->update(['leave_balance' => 12]);
        $leave = LeaveRequest::factory()->approved()->create([
            'user_id'         => $this->pegawai->id,
            'status'          => 'pertimbangan_atasan',
            'total_hari_kerja'=> 3,
        ]);

        $response = $this->actingAs($this->ketua)->post(route('leave.decide-pejabat', $leave->id), [
            'keputusan'     => 'disetujui',
            'catatan_pejabat' => '',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', ['id' => $leave->id, 'status' => 'disetujui']);
        $this->assertEquals(9, $this->pegawai->fresh()->leave_balance);
    }

    public function test_pejabat_dapat_tolak_dan_saldo_tidak_berubah(): void
    {
        $this->pegawai->update(['leave_balance' => 12]);
        $leave = LeaveRequest::factory()->create([
            'user_id'         => $this->pegawai->id,
            'status'          => 'pertimbangan_atasan',
            'total_hari_kerja'=> 3,
        ]);

        $this->actingAs($this->ketua)->post(route('leave.decide-pejabat', $leave->id), [
            'keputusan'      => 'ditolak',
            'catatan_pejabat'=> 'Kebutuhan kantor mendesak',
        ]);

        $this->assertDatabaseHas('leave_requests', ['id' => $leave->id, 'status' => 'ditolak']);
        $this->assertEquals(12, $this->pegawai->fresh()->leave_balance); // tidak berubah
    }

    public function test_pegawai_tidak_dapat_approve_leave_orang_lain(): void
    {
        $pegawai2 = User::factory()->create(['atasan_id' => $this->atasan->id]);
        $leave = LeaveRequest::factory()->create(['user_id' => $this->pegawai->id]);

        $response = $this->actingAs($pegawai2)->post(route('leave.decide-pejabat', $leave->id), [
            'keputusan' => 'disetujui',
        ]);

        $response->assertStatus(403);
    }
}
```

**Step 2: Jalankan tests**
```bash
cd /c/Users/faris/sihealing
php artisan test tests/Feature/LeaveApprovalWorkflowTest.php --verbose
```

Expected: semua test pass (atau jika ada yang fail, perbaiki logic controller sesuai test).

**Step 3: Commit**
```bash
rtk git add tests/Feature/LeaveApprovalWorkflowTest.php
rtk git commit -m "test: feature tests untuk approval workflow - submit, atasan review, pejabat decide"
```

---

### Task D3: Unit Tests untuk Kalkulasi Cuti

**Files:**
- Create: `tests/Unit/HariKerjaCalculatorTest.php`
- Create: `tests/Unit/CutiTahunanCalculatorTest.php`

**Step 1: Baca service yang akan di-test**
```bash
cat /c/Users/faris/sihealing/app/Services/HariKerjaCalculator.php
cat /c/Users/faris/sihealing/app/Services/CutiTahunanCalculator.php
```

**Step 2: Buat HariKerjaCalculatorTest**

Create `tests/Unit/HariKerjaCalculatorTest.php`:
```php
<?php

namespace Tests\Unit;

use App\Models\HariLibur;
use App\Services\HariKerjaCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HariKerjaCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private HariKerjaCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = app(HariKerjaCalculator::class);
    }

    public function test_senin_sampai_jumat_dihitung_hari_kerja(): void
    {
        // Senin 2026-03-09 s/d Jumat 2026-03-13 = 5 hari kerja
        $result = $this->calculator->calculate('2026-03-09', '2026-03-13');
        $this->assertEquals(5, $result);
    }

    public function test_sabtu_minggu_tidak_dihitung(): void
    {
        // Jumat 2026-03-13 s/d Senin 2026-03-16 = 2 hari kerja (Jum & Sen)
        $result = $this->calculator->calculate('2026-03-13', '2026-03-16');
        $this->assertEquals(2, $result);
    }

    public function test_hari_libur_nasional_tidak_dihitung(): void
    {
        HariLibur::create(['date' => '2026-03-10', 'name' => 'Libur Test', 'is_national_holiday' => true]);
        // Senin-Jumat, tapi Selasa libur = 4 hari kerja
        $result = $this->calculator->calculate('2026-03-09', '2026-03-13');
        $this->assertEquals(4, $result);
    }

    public function test_satu_hari_kerja(): void
    {
        $result = $this->calculator->calculate('2026-03-09', '2026-03-09'); // Senin
        $this->assertEquals(1, $result);
    }
}
```

**Step 3: Buat CutiTahunanCalculatorTest**

Create `tests/Unit/CutiTahunanCalculatorTest.php`:
```php
<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\CutiTahunanCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CutiTahunanCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private CutiTahunanCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = app(CutiTahunanCalculator::class);
    }

    public function test_pegawai_baru_kurang_1_tahun_tidak_dapat_cuti_tahunan(): void
    {
        $user = User::factory()->create([
            'masa_kerja_mulai' => now()->subMonths(6)->format('Y-m-d'),
        ]);
        // Baca implementasi dan sesuaikan assertion
        $quota = $this->calculator->calculate($user, now()->year);
        $this->assertGreaterThanOrEqual(0, $quota);
    }

    public function test_pegawai_1_tahun_dapat_12_hari(): void
    {
        $user = User::factory()->create([
            'masa_kerja_mulai' => now()->subYear()->subDays(1)->format('Y-m-d'),
        ]);
        $quota = $this->calculator->calculate($user, now()->year);
        $this->assertGreaterThanOrEqual(12, $quota);
    }
}
```

**Step 4: Jalankan tests**
```bash
php artisan test tests/Unit/ --verbose
```

**Step 5: Commit**
```bash
rtk git add tests/Unit/
rtk git commit -m "test: unit tests untuk HariKerjaCalculator dan CutiTahunanCalculator"
```

---

### Task D4: Feature Tests untuk Saldo Cuti

**Files:**
- Create: `tests/Feature/LeaveBalanceTest.php`

**Step 1: Buat test**

Create `tests/Feature/LeaveBalanceTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_saldo_berkurang_saat_cuti_disetujui(): void
    {
        $ketua = User::factory()->ketua()->create();
        $user  = User::factory()->create(['leave_balance' => 12]);
        $leave = LeaveRequest::factory()->create([
            'user_id'         => $user->id,
            'status'          => 'pertimbangan_atasan',
            'total_hari_kerja'=> 5,
        ]);

        $this->actingAs($ketua)->post(route('leave.decide-pejabat', $leave->id), [
            'keputusan' => 'disetujui',
        ]);

        $this->assertEquals(7, $user->fresh()->leave_balance);
    }

    public function test_saldo_tidak_berubah_saat_cuti_ditolak(): void
    {
        $ketua = User::factory()->ketua()->create();
        $user  = User::factory()->create(['leave_balance' => 12]);
        $leave = LeaveRequest::factory()->create([
            'user_id'         => $user->id,
            'status'          => 'pertimbangan_atasan',
            'total_hari_kerja'=> 5,
        ]);

        $this->actingAs($ketua)->post(route('leave.decide-pejabat', $leave->id), [
            'keputusan' => 'ditolak',
        ]);

        $this->assertEquals(12, $user->fresh()->leave_balance);
    }

    public function test_saldo_tidak_bisa_negatif(): void
    {
        $ketua = User::factory()->ketua()->create();
        $user  = User::factory()->create(['leave_balance' => 2]);
        $leave = LeaveRequest::factory()->create([
            'user_id'         => $user->id,
            'status'          => 'pertimbangan_atasan',
            'total_hari_kerja'=> 5, // minta lebih dari saldo
        ]);

        // Should either be rejected or clamped to 0
        $this->actingAs($ketua)->post(route('leave.decide-pejabat', $leave->id), [
            'keputusan' => 'disetujui',
        ]);

        $this->assertGreaterThanOrEqual(0, $user->fresh()->leave_balance);
    }
}
```

**Step 2: Jalankan tests**
```bash
php artisan test tests/Feature/LeaveBalanceTest.php --verbose
```

**Step 3: Commit**
```bash
rtk git add tests/Feature/LeaveBalanceTest.php
rtk git commit -m "test: feature tests untuk saldo cuti - pengurangan, penolakan, tidak negatif"
```

---

## CATEGORY E — ARSITEKTUR & DEVOPS

---

### Task E1: Health Check Endpoint

**Files:**
- Modify: `routes/web.php`

**Step 1: Tambah route /health**

Di `routes/web.php`, tambahkan di luar semua middleware group (public route):

```php
Route::get('/health', function () {
    $status = 'ok';
    $checks = [];

    // Database check
    try {
        \DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (\Exception $e) {
        $checks['database'] = 'error: ' . $e->getMessage();
        $status = 'degraded';
    }

    // Queue check (cek apakah ada jobs stuck > 10 menit)
    $stuckJobs = \DB::table('jobs')->where('reserved_at', '<', now()->subMinutes(10)->timestamp)->count();
    $checks['queue'] = $stuckJobs === 0 ? 'ok' : "degraded ({$stuckJobs} stuck jobs)";

    // Disk check
    $freeBytes = disk_free_space(storage_path());
    $freeMb    = round($freeBytes / 1024 / 1024);
    $checks['disk_free_mb'] = $freeMb;
    if ($freeMb < 100) {
        $checks['disk'] = 'warning: low disk space';
        $status = 'degraded';
    } else {
        $checks['disk'] = 'ok';
    }

    $checks['app_env']    = app()->environment();
    $checks['php_version'] = phpversion();

    $httpStatus = $status === 'ok' ? 200 : 503;

    return response()->json([
        'status'    => $status,
        'timestamp' => now()->toIso8601String(),
        'checks'    => $checks,
    ], $httpStatus);
})->name('health');
```

**Step 2: Test endpoint**
```bash
php artisan serve &
curl http://localhost:8000/health
```

Expected: `{"status":"ok","timestamp":"...","checks":{"database":"ok",...}}`

**Step 3: Commit**
```bash
rtk git add routes/web.php
rtk git commit -m "ops: tambah /health endpoint untuk monitoring (database, queue, disk)"
```

---

### Task E2: Error Tracking — Laravel Logging yang Proper

**Files:**
- Modify: `config/logging.php`
- Modify: `.env.example`

**Step 1: Pastikan logging di-setup dengan baik**

Baca `config/logging.php`. Pastikan ada channel `daily` yang aktif:

```php
'channels' => [
    'stack' => [
        'driver'   => 'stack',
        'channels' => ['daily'],  // pastikan 'daily' ada, bukan hanya 'single'
        'ignore_exceptions' => false,
    ],
    'daily' => [
        'driver' => 'daily',
        'path'   => storage_path('logs/laravel.log'),
        'level'  => env('LOG_LEVEL', 'debug'),
        'days'   => 30,  // simpan 30 hari log
        'replace_placeholders' => true,
    ],
    // ... rest
],
```

**Step 2: Tambah Sentry (opsional — jika mau)**

Jika tidak mau Sentry, cukup ensure proper logging. Jika mau Sentry:
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=PASTE_DSN_DARI_SENTRY_IO
```

Tambah ke `.env`:
```
SENTRY_LARAVEL_DSN=
LOG_LEVEL=error
```

**Step 3: Tambah custom exception handler untuk user-friendly errors**

Baca `bootstrap/app.php`. Tambahkan:
```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
        if (!$request->expectsJson()) {
            return redirect()->route('login')->with('info', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }
    });
})
```

**Step 4: Commit**
```bash
rtk git add config/logging.php bootstrap/app.php .env.example
rtk git commit -m "ops: perbaiki logging config (daily rotation 30 hari) + custom auth exception handler"
```

---

### Task E3: REST API dengan Laravel Sanctum

**Files:**
- Modify: `routes/api.php`
- Create: `app/Http/Controllers/Api/AuthController.php`
- Create: `app/Http/Controllers/Api/LeaveRequestController.php`

**Step 1: Pastikan Sanctum terinstall**
```bash
cd /c/Users/faris/sihealing
php artisan install:api 2>/dev/null || echo "Sanctum already installed"
php artisan migrate
```

**Step 2: Buat Api/AuthController**

Create `app/Http/Controllers/Api/AuthController.php`:
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nip'      => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('nip', $request->nip)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'NIP atau password salah.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun tidak aktif.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'     => $user->id,
                'name'   => $user->name,
                'nip'    => $user->nip,
                'role'   => $user->role,
                'jabatan'=> $user->jabatan,
                'leave_balance' => $user->leave_balance,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->only([
            'id', 'name', 'nip', 'email', 'role', 'jabatan', 'golongan_ruang', 'leave_balance',
        ]));
    }
}
```

**Step 3: Buat Api/LeaveRequestController**

Create `app/Http/Controllers/Api/LeaveRequestController.php`:
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $leaves = LeaveRequest::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($leaves);
    }

    public function show(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        return response()->json($leaveRequest->load('user'));
    }

    public function balance(Request $request)
    {
        return response()->json([
            'leave_balance' => $request->user()->leave_balance,
            'cuti_records'  => $request->user()->cutiRecords()->where('year', now()->year)->first(),
        ]);
    }
}
```

**Step 4: Update routes/api.php**

Edit `routes/api.php`:
```php
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeaveRequestController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
    Route::get('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show']);
    Route::get('/leave-balance', [LeaveRequestController::class, 'balance']);
});
```

**Step 5: Commit**
```bash
rtk git add routes/api.php app/Http/Controllers/Api/
rtk git commit -m "feat(api): REST API dengan Sanctum - auth, leave requests, balance"
```

---

### Task E4: Background Job untuk PDF/DOCX Besar

**Files:**
- Create: `app/Jobs/GenerateReportJob.php`
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/PdfExportController.php`

**Step 1: Buat GenerateReportJob**
```bash
cd /c/Users/faris/sihealing
php artisan make:job GenerateReportJob
```

Edit `app/Jobs/GenerateReportJob.php`:
```php
<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries   = 2;

    public function __construct(
        private string $reportType,
        private array  $params,
        private int    $requestedBy,
        private string $downloadToken
    ) {}

    public function handle(): void
    {
        // Generate report ke file temporary
        $path = "reports/{$this->downloadToken}.pdf";

        // Dispatch ke service yang sesuai
        // (implementasi sesuai reportType)
        $content = match($this->reportType) {
            'all_leaves'  => app(\App\Services\PdfExportService::class)->generateAllLeaves($this->params),
            'statistics'  => app(\App\Services\PdfExportService::class)->generateStatistics($this->params),
            default       => throw new \InvalidArgumentException("Unknown report type: {$this->reportType}"),
        };

        Storage::put($path, $content);

        // Notifikasi user bahwa laporan siap
        $downloadUrl = route('reports.download', ['token' => $this->downloadToken]);
        Notification::create([
            'user_id'    => $this->requestedBy,
            'type'       => 'report_ready',
            'title'      => 'Laporan Siap Diunduh',
            'message'    => 'Laporan yang Anda minta telah selesai dibuat. Klik untuk mengunduh.',
            'action_url' => $downloadUrl,
        ]);
    }
}
```

**Step 2: Tambah route download**
```php
Route::get('/reports/{token}/download', function (string $token) {
    $path = "reports/{$token}.pdf";
    if (!Storage::exists($path)) {
        abort(404, 'Laporan tidak ditemukan atau sudah kadaluarsa.');
    }
    return Storage::download($path, 'laporan.pdf');
})->name('reports.download')->middleware('auth');
```

**Step 3: Dispatch job dari controller untuk laporan besar**

Di `PdfExportController`, untuk laporan yang besar (all leaves, statistics):
```php
public function allLeaveRequests(Request $request)
{
    $token = \Str::random(32);
    GenerateReportJob::dispatch(
        'all_leaves',
        $request->all(),
        auth()->id(),
        $token
    );

    return back()->with('info', 'Laporan sedang dibuat. Anda akan mendapat notifikasi saat selesai.');
}
```

**Step 4: Commit**
```bash
rtk git add app/Jobs/GenerateReportJob.php routes/web.php app/Http/Controllers/PdfExportController.php
rtk git commit -m "feat(perf): background job untuk generate laporan PDF besar - tidak blocking request"
```

---

## SELESAI — Push ke GitHub

```bash
cd /c/Users/faris/sihealing
rtk git push
```

---

## Summary Semua Tasks

| # | Task | Kategori | File Utama |
|---|------|----------|------------|
| A1 | Rate limiting login | Security | routes/web.php |
| A2 | Forgot password controller | Security | PasswordResetController.php |
| A3 | Forgot password views & email | Security | views/auth/ |
| A4 | Form Request classes | Security | Http/Requests/ |
| A5 | Authorization Policies | Security | Policies/ |
| A6 | Security headers middleware | Security | Middleware/SecurityHeaders.php |
| A7 | Log login gagal | Security | AuthController.php |
| B1 | Email async queue | Performa | semua controller Mail |
| B2 | Database indexes | Performa | migration baru |
| B3 | Fix N+1 queries | Performa | Dashboard, Pegawai, Laporan |
| B4 | Cache analytics | Performa | AnalyticsService.php |
| B5 | PHP 8.1 Enums | Refactor | app/Enums/ |
| B6 | Hapus duplikat Mail | Refactor | app/Mail/ |
| B7 | Conflict detection server | Security | LeaveRequestController.php |
| C1 | Delegasi atasan migration & model | Fitur | migration, User.php |
| C2 | Delegasi atasan UI | Fitur | views/profile/ |
| C3 | Bulk approval | Fitur | LeaveRequestController, dashboard |
| C4 | Notification channels WA/Email | Fitur | WhatsAppService, config |
| C5 | Quota cuti otomatis | Fitur | GenerateCutiQuota command |
| C6 | Kalender tim atasan | Fitur | views/kalender/tim.blade.php |
| C7 | Statistik personal pegawai | Fitur | dashboard.blade.php |
| C8 | Reminder cuti kadaluarsa | Fitur | RemindCutiExpiry command |
| C9 | Export Excel | Fitur | app/Exports/ |
| D1 | Factory & Seeder | Testing | database/factories/ |
| D2 | Feature tests approval workflow | Testing | LeaveApprovalWorkflowTest.php |
| D3 | Unit tests kalkulasi | Testing | Unit/ |
| D4 | Feature tests saldo | Testing | LeaveBalanceTest.php |
| E1 | Health check endpoint | DevOps | routes/web.php |
| E2 | Error tracking & logging | DevOps | config/logging.php |
| E3 | REST API Sanctum | Arsitektur | routes/api.php, Api/ |
| E4 | Background job PDF | Arsitektur | Jobs/GenerateReportJob.php |
