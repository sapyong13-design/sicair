<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Terlalu Banyak Percobaan — SiCAIR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <style>
        body { background: #fef2f2; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', -apple-system, sans-serif; }
        .error-card { max-width: 440px; width: 100%; padding: 1rem; }
        .card { border-radius: 20px; border: 1px solid #fecaca; border-top: 4px solid #dc2626; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
<div class="error-card mx-auto">
    <div class="card">
        <div class="card-body p-4 text-center">
            <div style="font-size: 4rem; color: #dc2626; opacity: 0.2; font-weight: 900; line-height: 1;">429</div>
            <div style="margin-top: -0.5rem; margin-bottom: 1rem;">
                <i class="ti ti-shield-lock" style="font-size: 3rem; color: #dc2626; opacity: 0.6;"></i>
            </div>
            <h4 class="fw-bold mb-2" style="color: #dc2626;">Terlalu Banyak Percobaan</h4>
            <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.6;">
                Anda telah melakukan terlalu banyak percobaan login dalam waktu singkat.<br>
                Silakan tunggu beberapa menit sebelum mencoba kembali.
            </p>
            <a href="{{ url('/login') }}" class="btn btn-danger w-100" style="border-radius: 12px; height: 44px; font-weight: 600;">
                <i class="ti ti-arrow-left me-1"></i> Kembali ke Login
            </a>
        </div>
    </div>
    <div class="text-center mt-3" style="color: #94a3b8; font-size: 0.78rem;">
        &copy; {{ date('Y') }} <strong style="color: #166534;">SiCAIR</strong> &mdash; Pengadilan Negeri Natuna
    </div>
</div>
</body>
</html>
