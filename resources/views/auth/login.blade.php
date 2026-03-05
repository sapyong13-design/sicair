<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <title>Login - SiHEALING PN Natuna</title>
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
            background-image: url('/gedung.png');
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
            bottom: -20%;
            left: -15%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* Gold decoration line */
        .login-banner .gold-line {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #b8860b, #d4a017, #fbbf24, #d4a017, #b8860b);
        }

        .banner-content {
            position: relative;
            z-index: 1;
            max-width: 480px;
        }
        .banner-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 3rem;
        }
        .banner-logo-icon {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(184,134,11,0.4);
            overflow: hidden;
        }
        .banner-logo-icon img {
            width: 54px;
            height: 54px;
            object-fit: contain;
        }
        .banner-logo-icon i {
            font-size: 2rem;
            color: #fbbf24;
        }
        .banner-logo-text {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .banner-logo-text span { color: #fbbf24; }
        .banner-title {
            font-size: 2.5rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.15;
            letter-spacing: -1px;
            margin-bottom: 1rem;
        }
        .banner-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }
        .banner-institution {
            color: #fbbf24;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Feature pills */
        .feature-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .feature-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 50px;
            padding: 0.5rem 1rem;
            color: rgba(255,255,255,0.9);
            font-size: 0.85rem;
            font-weight: 500;
        }
        .feature-pill i {
            color: #fbbf24;
        }

        /* Right panel - form */
        .login-form-panel {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            background: #f8faf8;
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
        .mobile-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #14532d, #166534);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 8px 25px rgba(20, 83, 45, 0.3);
            border: 3px solid #b8860b;
            overflow: hidden;
        }
        .mobile-logo img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .mobile-logo i {
            font-size: 2.2rem;
            color: #fbbf24;
        }
        .mobile-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #14532d;
            letter-spacing: -0.5px;
        }
        .mobile-title span { color: #b8860b; }
        .mobile-subtitle {
            color: #64748b;
            font-size: 0.85rem;
        }
        .mobile-institution {
            color: #166534;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.25rem;
        }

        /* Form card */
        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 30px rgba(0,0,0,0.06);
            padding: 2rem;
            border: 1px solid #e2e8f0;
            border-top: 4px solid #166534;
        }
        .login-card h2 {
            font-size: 1.3rem;
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
            border: 2px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: #f8faf8;
        }
        .sh-input-group input:focus {
            border-color: #166534;
            box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.1);
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
    </style>
</head>
<body>
    {{-- Left Panel (Desktop only) --}}
    <div class="login-banner">
        <div class="gold-line"></div>
        <div class="banner-content">
            <div class="banner-logo">
                <div class="banner-logo-icon">
                    @if(file_exists(public_path('images/logo-pn-natuna.png')))
                        <img src="{{ asset('images/logo-pn-natuna.png') }}" alt="Logo PN Natuna">
                    @else
                        <i class="ti ti-scale"></i>
                    @endif
                </div>
                <div class="banner-logo-text">
                    <span>Si</span>HEALING
                </div>
            </div>

            <div class="banner-institution">Pengadilan Negeri Natuna</div>
            <h1 class="banner-title">
                Kelola Cuti Pegawai<br>dengan Mudah
            </h1>
            <p class="banner-subtitle">
                Sistem Informasi Hak Elektronik Cuti &mdash; Pengadilan Negeri Natuna, Kepulauan Riau.
                Pantau sisa cuti, ajukan permohonan, dan kelola persetujuan sesuai SE MA No. 13/2019.
            </p>

            <div class="feature-pills">
                <div class="feature-pill">
                    <i class="ti ti-clock"></i> Proses Cepat
                </div>
                <div class="feature-pill">
                    <i class="ti ti-device-mobile"></i> Mobile Friendly
                </div>
                <div class="feature-pill">
                    <i class="ti ti-shield-check"></i> Aman
                </div>
                <div class="feature-pill">
                    <i class="ti ti-scale"></i> SE MA 13/2019
                </div>
            </div>
        </div>
    </div>

    {{-- Right Panel (Form) --}}
    <div class="login-form-panel">
        <div class="login-form-wrapper">
            {{-- Mobile Logo --}}
            <div class="mobile-header">
                <div class="mobile-logo">
                    @if(file_exists(public_path('images/logo-pn-natuna.png')))
                        <img src="{{ asset('images/logo-pn-natuna.png') }}" alt="Logo PN Natuna">
                    @else
                        <i class="ti ti-scale"></i>
                    @endif
                </div>
                <div class="mobile-title"><span>Si</span>HEALING</div>
                <div class="mobile-subtitle">Sistem Informasi Hak Elektronik Cuti</div>
                <div class="mobile-institution">Pengadilan Negeri Natuna</div>
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

                <form action="{{ route('login') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">NIP (Nomor Induk Pegawai)</label>
                        <div class="sh-input-group">
                            <i class="ti ti-id-badge input-icon"></i>
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
                        <div class="sh-input-group">
                            <i class="ti ti-lock input-icon"></i>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Masukkan password"
                                   required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-check" style="cursor: pointer;">
                            <input type="checkbox" name="remember" class="form-check-input" style="border-radius: 6px; border-color: #166534;"/>
                            <span class="form-check-label" style="font-size: 0.85rem; color: #475569;">Ingat saya di perangkat ini</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary sh-login-btn w-100">
                        <i class="ti ti-login me-2"></i> Masuk
                    </button>
                </form>
            </div>

            <div class="footer-text">
                &copy; {{ date('Y') }} <strong>SiHEALING</strong> &mdash; Pengadilan Negeri Natuna
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
</body>
</html>
