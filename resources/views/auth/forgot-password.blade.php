<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — SiHEALING</title>
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
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus placeholder="email@pn-natuna.go.id">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="ti ti-send me-1"></i> Kirim Link Reset
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
