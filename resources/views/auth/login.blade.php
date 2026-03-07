<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <title>Login - SiHEALING PN Natuna</title>
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-pn.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        /* Left panel - decorative */
        .login-banner {
            display: none;
            width: 50%;
            background-image: url('/gedung.webp');
            background-size: cover;
            background-position: center center;
            position: relative;
            overflow: hidden;
            padding: 3rem;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        @media (min-width: 992px) {
            .login-banner { display: flex; }
        }

        /* Dark overlay di atas foto gedung */
        .login-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(10, 40, 24, 0.55);
            z-index: 0;
        }
        .login-banner::after {
            content: '';
            position: absolute;
            bottom: -15%;
            left: -10%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 65%);
            border-radius: 50%;
        }

        /* Gold decoration line */
        .login-banner .gold-line {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, #b8860b, #fbbf24, #d4a017, #b8860b, transparent);
        }
        /* Gold vertical accent on right edge */
        .login-banner .gold-line-right {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, transparent, #b8860b 20%, #fbbf24 50%, #b8860b 80%, transparent);
        }

        .banner-content {
            position: relative;
            z-index: 1;
            max-width: 440px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Logo */
        .banner-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            margin-bottom: 2rem;
        }
        .banner-logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }
        .banner-logo-icon img {
            width: 180px;
            height: 180px;
            object-fit: cover;
            object-position: center;
            filter: drop-shadow(0 8px 24px rgba(0,0,0,0.4));
        }
        .banner-logo-icon i {
            font-size: 5rem;
            color: #fbbf24;
        }

        /* Institution name — primary identity */
        .banner-institution-primary {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.5px;
            line-height: 1.3;
            margin-bottom: 0.2rem;
        }
        .banner-institution-sub {
            font-size: 0.82rem;
            font-weight: 500;
            color: rgba(255,255,255,0.55);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1.75rem;
        }

        /* Divider */
        .banner-divider {
            width: 48px;
            height: 3px;
            background: linear-gradient(90deg, #b8860b, #fbbf24, #b8860b);
            border-radius: 2px;
            margin: 0 auto 1.75rem;
        }

        /* System name */
        .banner-system-name {
            font-size: 1.6rem;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.5px;
            margin-bottom: 0.5rem;
        }
        .banner-system-name span { color: #fbbf24; }

        .banner-system-desc {
            color: rgba(255,255,255,0.6);
            font-size: 0.88rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* Info items — replace feature pills with cleaner list */
        .banner-info-list {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            width: 100%;
            text-align: left;
        }
        .banner-info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.9rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 10px;
            color: rgba(255,255,255,0.85);
            font-size: 0.85rem;
        }
        .banner-info-item i {
            color: #fbbf24;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .banner-info-item strong {
            color: #fff;
            font-weight: 600;
        }

        /* Right panel - form */
        .login-form-panel {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            background: #ffffff;
        }
        @media (min-width: 992px) {
            .login-form-panel { width: 50%; padding: 3rem; }
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 420px;
        }

        /* Mobile header */
        .mobile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        @media (min-width: 992px) {
            .mobile-header { display: none; }
        }
        .mobile-logo-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            object-position: center;
            margin-bottom: 0.75rem;
            filter: drop-shadow(0 4px 12px rgba(20,83,45,0.25));
        }
        .mobile-logo-fallback {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #14532d, #166534);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            border: 2px solid #b8860b;
        }
        .mobile-logo-fallback i {
            font-size: 2rem;
            color: #fbbf24;
        }
        .mobile-institution {
            font-size: 1rem;
            font-weight: 800;
            color: #14532d;
        }
        .mobile-subtitle {
            color: #64748b;
            font-size: 0.78rem;
            margin-top: 0.15rem;
        }

        /* Form card */
        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05), 0 12px 40px rgba(0,0,0,0.08);
            padding: 2rem;
            border: 1px solid #d1e7d8;
            border-top: 4px solid #166534;
        }
        .login-card h2 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #14532d;
            margin-bottom: 0.25rem;
        }
        .login-card .login-hint {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 1.75rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #334155;
            margin-bottom: 0.4rem;
        }

        .sh-input-group {
            position: relative;
        }
        .sh-input-group .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #166534;
            font-size: 1.1rem;
            z-index: 2;
        }
        .sh-input-group input {
            padding-left: 44px;
            height: 48px;
            border-radius: 12px;
            border: 2px solid #d1e7d8;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: #f6fbf7;
        }
        .sh-input-group input:focus {
            border-color: #166534;
            box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.08);
            background: #fff;
        }

        .sh-login-btn {
            height: 48px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            background: linear-gradient(135deg, #14532d, #166534, #15803d);
            border: none;
            box-shadow: 0 4px 14px rgba(20, 83, 45, 0.35);
            transition: all 0.2s ease;
        }
        .sh-login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(20, 83, 45, 0.45);
            background: linear-gradient(135deg, #0d3320, #14532d, #166534);
        }
        .sh-login-btn:active {
            transform: translateY(0) scale(0.98);
            box-shadow: 0 2px 8px rgba(20, 83, 45, 0.3);
        }

        /* Password toggle */
        .sh-pw-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            font-size: 1.1rem;
            z-index: 3;
            transition: color 0.2s;
        }
        .sh-pw-toggle:hover { color: #166534; }

        .sh-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .footer-text {
            text-align: center;
            color: #94a3b8;
            font-size: 0.78rem;
            margin-top: 2rem;
        }
        .footer-text strong { color: #166534; }

        /* Mobile: full-screen background + glass card */
        @media (max-width: 991px) {
            body {
                background-image: url('/gedung.webp');
                background-size: cover;
                background-position: center center;
                background-attachment: fixed;
                position: relative;
            }
            body::before {
                content: '';
                position: fixed;
                inset: 0;
                background: rgba(10, 40, 24, 0.45);
                z-index: 0;
            }
            .login-form-panel {
                background: transparent !important;
                position: relative;
                z-index: 1;
                min-height: 100vh;
                padding: 1.5rem 1rem;
            }
            .login-card {
                background: rgba(255, 255, 255, 0.88) !important;
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.5) !important;
                border-top: 4px solid #166534 !important;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
            }
            .mobile-header {
                position: relative;
                z-index: 1;
            }
            .mobile-institution {
                color: #ffffff !important;
                text-shadow: 0 1px 4px rgba(0,0,0,0.5);
            }
            .mobile-subtitle {
                color: rgba(255,255,255,0.85) !important;
                text-shadow: 0 1px 3px rgba(0,0,0,0.4);
            }
            /* Dark mode override for glass card */
            [data-bs-theme="dark"] .login-card {
                background: rgba(15, 23, 42, 0.88) !important;
                border-color: rgba(255, 255, 255, 0.15) !important;
            }
        }
    </style>
</head>
<body>
    {{-- Left Panel (Desktop only) --}}
    <div class="login-banner">
        <div class="gold-line"></div>
        <div class="gold-line-right"></div>
        <div class="banner-content">
            <div class="banner-logo">
                <div class="banner-logo-icon">
                    @if(file_exists(public_path('images/favicon-pn.png')))
                        <img src="{{ asset('images/favicon-pn.png') }}" alt="Logo PN Natuna">
                    @else
                        <i class="ti ti-scale"></i>
                    @endif
                </div>
                <div class="banner-institution-primary">Pengadilan Negeri Natuna</div>
                <div class="banner-institution-sub">Kepulauan Riau</div>
            </div>

            <div class="banner-divider"></div>

            <div class="banner-system-name"><span>Si</span>HEALING</div>
            <div class="banner-system-desc">
                Sistem Informasi Hak Elektronik Cuti<br>
                Pengelolaan cuti pegawai sesuai SE MA No. 13/2019
            </div>

            <div class="banner-info-list">
                <div class="banner-info-item">
                    <i class="ti ti-file-check"></i>
                    <span><strong>Pengajuan</strong> cuti online tanpa tatap muka</span>
                </div>
                <div class="banner-info-item">
                    <i class="ti ti-users-group"></i>
                    <span><strong>Alur persetujuan</strong> atasan langsung & pejabat berwenang</span>
                </div>
                <div class="banner-info-item">
                    <i class="ti ti-chart-bar"></i>
                    <span><strong>Rekap & laporan</strong> sisa cuti real-time</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Panel (Form) --}}
    <div class="login-form-panel">
        <div class="login-form-wrapper">
            {{-- Mobile Logo --}}
            <div class="mobile-header">
                @if(file_exists(public_path('images/favicon-pn.png')))
                    <div><img src="{{ asset('images/favicon-pn.png') }}" alt="Logo PN Natuna" class="mobile-logo-img"></div>
                @else
                    <div class="mobile-logo-fallback"><i class="ti ti-scale"></i></div>
                @endif
                <div class="mobile-institution">Pengadilan Negeri Natuna</div>
                <div class="mobile-subtitle">Sistem Informasi Hak Elektronik Cuti</div>
            </div>

            <div class="login-card">
                <h2>Selamat Datang</h2>
                <p class="login-hint">Masuk menggunakan NIP dan password Anda</p>

                @if($errors->any())
                <div class="sh-alert-error mb-3">
                    <i class="ti ti-alert-circle me-1"></i>
                    {{ $errors->first() }}
                </div>
                @endif

                <form action="{{ route('login') }}" method="POST" id="loginForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="nip_input">NIP (Nomor Induk Pegawai)</label>
                        <div class="sh-input-group">
                            <i class="ti ti-id-badge input-icon" aria-hidden="true"></i>
                            <input type="text"
                                   id="nip_input"
                                   name="nip"
                                   class="form-control"
                                   placeholder="Masukkan NIP Anda"
                                   value="{{ old('nip') }}"
                                   autocomplete="username"
                                   autofocus
                                   required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password_input">Password</label>
                        <div class="sh-input-group">
                            <i class="ti ti-lock input-icon" aria-hidden="true"></i>
                            <input type="password"
                                   id="password_input"
                                   name="password"
                                   class="form-control"
                                   placeholder="Masukkan password"
                                   autocomplete="current-password"
                                   style="padding-right: 44px;"
                                   required>
                            <button type="button" class="sh-pw-toggle" id="pwToggle" aria-label="Tampilkan password" title="Tampilkan/sembunyikan password">
                                <i class="ti ti-eye" id="pwToggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ route('password.request') }}" class="text-muted small" style="font-size: 0.82rem;">Lupa password?</a>
                    </div>
                    <div class="mb-4">
                        <label class="form-check" style="cursor: pointer;">
                            <input type="checkbox" name="remember" class="form-check-input" style="border-radius: 6px; border-color: #166534;"/>
                            <span class="form-check-label" style="font-size: 0.85rem; color: #475569;">Ingat saya di perangkat ini</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary sh-login-btn w-100" id="loginBtn">
                        <span id="loginBtnText"><i class="ti ti-login me-2"></i> Masuk</span>
                        <span id="loginBtnLoading" style="display:none;"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...</span>
                    </button>
                </form>
            </div>

            <div class="footer-text">
                &copy; {{ date('Y') }} <strong>SiHEALING</strong> &mdash; Pengadilan Negeri Natuna
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
    <script>
        // Password show/hide toggle
        var pwToggle = document.getElementById('pwToggle');
        var pwInput  = document.getElementById('password_input');
        var pwIcon   = document.getElementById('pwToggleIcon');
        if (pwToggle) {
            pwToggle.addEventListener('click', function() {
                var isHidden = pwInput.type === 'password';
                pwInput.type = isHidden ? 'text' : 'password';
                pwIcon.className = isHidden ? 'ti ti-eye-off' : 'ti ti-eye';
                pwToggle.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
            });
        }
        // Submit loading state
        var loginForm = document.getElementById('loginForm');
        var loginBtn  = document.getElementById('loginBtn');
        if (loginForm) {
            loginForm.addEventListener('submit', function() {
                loginBtn.disabled = true;
                document.getElementById('loginBtnText').style.display    = 'none';
                document.getElementById('loginBtnLoading').style.display = 'inline-flex';
            });
        }
    </script>
</body>
</html>
