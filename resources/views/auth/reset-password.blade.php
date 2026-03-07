<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — SiHEALING</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
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
                <p class="text-muted small">Masukkan password baru Anda di bawah ini.</p>
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
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           required minlength="8" placeholder="Minimal 8 karakter">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="ti ti-check me-1"></i> Reset Password
                </button>
            </form>
            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-muted small">← Kembali ke Login</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
