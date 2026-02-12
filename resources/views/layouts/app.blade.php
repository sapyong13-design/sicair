<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>@yield('title', 'SiHEALING')</title>
    <!-- Tabler CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <style>
        /* ===== SiHEALING Custom Theme ===== */
        :root {
            --sh-primary: #1a56db;
            --sh-primary-dark: #1e3a8a;
            --sh-primary-light: #dbeafe;
            --sh-success: #059669;
            --sh-success-light: #d1fae5;
            --sh-warning: #d97706;
            --sh-warning-light: #fef3c7;
            --sh-danger: #dc2626;
            --sh-danger-light: #fee2e2;
            --sh-gray-50: #f8fafc;
            --sh-gray-100: #f1f5f9;
        }

        body {
            background-color: var(--sh-gray-50);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        /* --- Navbar --- */
        .sh-navbar {
            background: linear-gradient(135deg, var(--sh-primary) 0%, var(--sh-primary-dark) 100%);
            box-shadow: 0 4px 20px rgba(26, 86, 219, 0.3);
            border: none;
            padding: 0.6rem 0;
        }
        .sh-navbar .navbar-brand-text {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sh-navbar .navbar-brand-text .brand-icon {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .sh-navbar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            border-radius: 8px;
            padding: 0.5rem 1rem !important;
            transition: all 0.2s ease;
        }
        .sh-navbar .nav-link:hover,
        .sh-navbar .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,0.15);
        }
        .sh-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(255,255,255,0.2);
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255,255,255,0.3);
        }

        /* --- Status Badges --- */
        .sh-badge {
            padding: 0.35em 0.75em;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .sh-badge-pending {
            background: var(--sh-warning-light);
            color: var(--sh-warning);
        }
        .sh-badge-approved {
            background: var(--sh-success-light);
            color: var(--sh-success);
        }
        .sh-badge-rejected {
            background: var(--sh-danger-light);
            color: var(--sh-danger);
        }

        /* --- Cards --- */
        .sh-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .sh-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08), 0 8px 24px rgba(0,0,0,0.06);
            transform: translateY(-2px);
        }
        .sh-card .card-header {
            background: #fff;
            border-bottom: 2px solid var(--sh-gray-100);
            padding: 1rem 1.25rem;
        }
        .sh-card .card-header .card-title {
            font-weight: 700;
            color: #1e293b;
        }

        /* --- Stat Card --- */
        .sh-stat-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .sh-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        .sh-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .sh-stat-card.stat-warning::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .sh-stat-card.stat-success::before { background: linear-gradient(90deg, #059669, #34d399); }
        .sh-stat-card.stat-danger::before { background: linear-gradient(90deg, #dc2626, #f87171); }
        .sh-stat-card.stat-primary::before { background: linear-gradient(90deg, #1a56db, #60a5fa); }

        .sh-stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .sh-stat-icon.icon-warning { background: var(--sh-warning-light); color: var(--sh-warning); }
        .sh-stat-icon.icon-success { background: var(--sh-success-light); color: var(--sh-success); }
        .sh-stat-icon.icon-danger { background: var(--sh-danger-light); color: var(--sh-danger); }
        .sh-stat-icon.icon-primary { background: var(--sh-primary-light); color: var(--sh-primary); }

        .sh-stat-number {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -1px;
        }
        .sh-stat-label {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* --- Hero Balance Card (Pegawai) --- */
        .sh-hero-balance {
            background: linear-gradient(135deg, var(--sh-primary) 0%, #2563eb 50%, #3b82f6 100%);
            border-radius: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
            border: none;
        }
        .sh-hero-balance::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        .sh-hero-balance::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .sh-hero-balance .hero-content {
            position: relative;
            z-index: 1;
        }
        .sh-hero-number {
            font-size: 4.5rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -3px;
        }
        .sh-hero-progress {
            height: 8px;
            border-radius: 4px;
            background: rgba(255,255,255,0.2);
            overflow: hidden;
        }
        .sh-hero-progress-bar {
            height: 100%;
            border-radius: 4px;
            background: rgba(255,255,255,0.9);
            transition: width 1s ease;
        }

        /* --- Table --- */
        .sh-table thead th {
            background: var(--sh-gray-50);
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 2px solid var(--sh-gray-100);
            padding: 0.85rem 1rem;
        }
        .sh-table tbody tr {
            transition: background 0.15s ease;
        }
        .sh-table tbody tr:hover {
            background: var(--sh-primary-light) !important;
        }
        .sh-table tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
        }

        /* --- Buttons --- */
        .sh-btn-primary {
            background: linear-gradient(135deg, var(--sh-primary), #2563eb);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            box-shadow: 0 4px 14px rgba(26, 86, 219, 0.3);
            transition: all 0.2s ease;
        }
        .sh-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(26, 86, 219, 0.4);
            background: linear-gradient(135deg, #1e40af, var(--sh-primary));
        }
        .sh-btn-success {
            background: linear-gradient(135deg, #059669, #10b981);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
        }
        .sh-btn-success:hover { background: linear-gradient(135deg, #047857, #059669); color: #fff; }
        .sh-btn-danger {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
        }
        .sh-btn-danger:hover { background: linear-gradient(135deg, #b91c1c, #dc2626); color: #fff; }

        /* --- Mobile Card (History) --- */
        .sh-history-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            border-left: 4px solid var(--sh-gray-100);
            transition: all 0.2s ease;
        }
        .sh-history-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .sh-history-card.status-pending { border-left-color: var(--sh-warning); }
        .sh-history-card.status-approved { border-left-color: var(--sh-success); }
        .sh-history-card.status-rejected { border-left-color: var(--sh-danger); }

        /* --- Alerts --- */
        .sh-alert {
            border-radius: 12px;
            border: none;
            font-weight: 500;
        }

        /* --- Footer --- */
        .sh-footer {
            background: #fff;
            border-top: 1px solid var(--sh-gray-100);
            padding: 1rem 0;
        }

        /* --- Animations --- */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-in {
            animation: fadeInUp 0.5s ease forwards;
        }
        .animate-in:nth-child(1) { animation-delay: 0s; }
        .animate-in:nth-child(2) { animation-delay: 0.1s; }
        .animate-in:nth-child(3) { animation-delay: 0.2s; }

        /* --- Empty State --- */
        .sh-empty-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: var(--sh-gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: #94a3b8;
        }

        /* --- Page Header --- */
        .sh-page-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--sh-gray-100);
        }
        .sh-page-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        /* --- Mobile optimizations --- */
        @media (max-width: 768px) {
            .sh-hero-number { font-size: 3.5rem; }
            .sh-stat-number { font-size: 1.5rem; }
            .sh-page-title { font-size: 1.25rem; }
            .container-xl { padding-left: 1rem; padding-right: 1rem; }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="navbar navbar-expand-md d-print-none sh-navbar">
        <div class="container-xl">
            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                <i class="ti ti-menu-2" style="font-size: 1.4rem;"></i>
            </button>
            <a href="/dashboard" class="navbar-brand-text">
                <span class="brand-icon"><i class="ti ti-building-hospital"></i></span>
                SiHEALING
            </a>
            <div class="navbar-nav flex-row order-md-last">
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                        <div class="sh-user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="d-none d-xl-block ps-2">
                            <div class="text-white fw-semibold" style="font-size: 0.9rem;">{{ Auth::user()->name }}</div>
                            <div style="color: rgba(255,255,255,0.6); font-size: 0.75rem;">
                                {{ ucfirst(Auth::user()->role) }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
                        <div class="px-3 py-2 border-bottom">
                            <div class="fw-bold">{{ Auth::user()->name }}</div>
                            <div class="text-muted small">NIP: {{ Auth::user()->nip }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger py-2">
                                <i class="ti ti-logout me-2"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="collapse navbar-collapse" id="navbar-menu">
                <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-layout-dashboard"></i></span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>
                        @if(!Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('leave/create') ? 'active' : '' }}" href="{{ route('leave.create') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-file-plus"></i></span>
                                <span class="nav-link-title">Ajukan Cuti</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="page-wrapper flex-fill" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
        <div class="container-xl">
            {{-- Flash messages --}}
            @if(session('success'))
            <div class="alert alert-success sh-alert alert-dismissible fade show mb-4" role="alert" style="background: var(--sh-success-light); color: var(--sh-success);">
                <div class="d-flex align-items-center">
                    <div class="sh-stat-icon icon-success me-3" style="width: 40px; height: 40px; border-radius: 10px; font-size: 1.2rem;">
                        <i class="ti ti-circle-check"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Berhasil!</div>
                        <div>{{ session('success') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger sh-alert alert-dismissible fade show mb-4" role="alert" style="background: var(--sh-danger-light); color: var(--sh-danger);">
                <div class="d-flex align-items-center">
                    <div class="sh-stat-icon icon-danger me-3" style="width: 40px; height: 40px; border-radius: 10px; font-size: 1.2rem;">
                        <i class="ti ti-alert-triangle"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Gagal!</div>
                        <div>{{ session('error') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </div>
    </div>

    <footer class="sh-footer d-print-none mt-auto">
        <div class="container-xl">
            <div class="text-center">
                <span class="text-muted" style="font-size: 0.8rem;">
                    &copy; {{ date('Y') }} <strong>SiHEALING</strong> &mdash; Sistem Informasi Hak Cuti & Administrasi Libur Pegawai
                </span>
            </div>
        </div>
    </footer>

    <!-- Tabler JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
    @stack('scripts')
</body>
</html>
