<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <title>Login - SiHEALING</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
</head>
<body class="d-flex flex-column" style="background: linear-gradient(135deg, #1a56db 0%, #1e40af 50%, #1e3a8a 100%); min-height: 100vh;">
    <div class="page page-center flex-fill">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <h1 class="text-white fw-bold" style="font-size: 2rem;">
                    <i class="ti ti-calendar-event"></i> SiHEALING
                </h1>
                <p class="text-white" style="opacity: 0.8; font-size: 0.85rem;">
                    Sistem Informasi Hak Cuti & Administrasi Libur Pegawai
                </p>
            </div>
            <div class="card card-md shadow-lg">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Masuk ke Akun Anda</h2>

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="ti ti-alert-circle me-2"></i>
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST" autocomplete="off">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">NIP (Nomor Induk Pegawai)</label>
                            <div class="input-group input-group-flat">
                                <span class="input-group-text">
                                    <i class="ti ti-id-badge"></i>
                                </span>
                                <input type="text"
                                       name="nip"
                                       class="form-control"
                                       placeholder="Masukkan NIP Anda"
                                       value="{{ old('nip') }}"
                                       autofocus
                                       required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group input-group-flat">
                                <span class="input-group-text">
                                    <i class="ti ti-lock"></i>
                                </span>
                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       placeholder="Masukkan password"
                                       required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input"/>
                                <span class="form-check-label">Ingat saya</span>
                            </label>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-login me-2"></i> Masuk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center text-white mt-3" style="opacity: 0.6; font-size: 0.8rem;">
                &copy; {{ date('Y') }} SiHEALING
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
</body>
</html>
