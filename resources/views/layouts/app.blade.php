<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>@yield('title', 'SiHEALING - PN Natuna')</title>
    <!-- Tabler CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <style>
        /* ===== SiHEALING - Tema Pengadilan Negeri Natuna ===== */
        :root {
            --sh-primary: #166534;
            --sh-primary-dark: #14532d;
            --sh-primary-light: #dcfce7;
            --sh-accent: #b8860b;
            --sh-accent-light: #fef9c3;
            --sh-success: #059669;
            --sh-success-light: #d1fae5;
            --sh-warning: #d97706;
            --sh-warning-light: #fef3c7;
            --sh-danger: #dc2626;
            --sh-danger-light: #fee2e2;
            --sh-gray-50: #f8faf8;
            --sh-gray-100: #f0f4f0;
            --sh-body-bg: var(--sh-gray-50);
            --sh-card-bg: #fff;
            --sh-text: #1e293b;
            --sh-text-muted: #64748b;
            --sh-border: #e2e8f0;
        }

        /* ===== Dark Mode ===== */
        [data-bs-theme="dark"] {
            --sh-body-bg: #0f172a;
            --sh-card-bg: #1e293b;
            --sh-text: #e2e8f0;
            --sh-text-muted: #94a3b8;
            --sh-gray-50: #1e293b;
            --sh-gray-100: #334155;
            --sh-border: #334155;
            --sh-primary-light: #064e3b;
            --sh-success-light: #064e3b;
            --sh-warning-light: #451a03;
            --sh-danger-light: #450a0a;
            --sh-accent-light: #422006;
        }
        [data-bs-theme="dark"] body {
            background-color: var(--sh-body-bg) !important;
            color: var(--sh-text);
        }
        [data-bs-theme="dark"] .sh-card,
        [data-bs-theme="dark"] .sh-stat-card,
        [data-bs-theme="dark"] .card {
            background: var(--sh-card-bg) !important;
            border-color: var(--sh-border) !important;
        }
        [data-bs-theme="dark"] .sh-card .card-header {
            background: var(--sh-card-bg) !important;
            border-bottom-color: var(--sh-border) !important;
        }
        [data-bs-theme="dark"] .sh-table thead th {
            background: var(--sh-gray-50) !important;
            color: var(--sh-text-muted) !important;
            border-bottom-color: var(--sh-border) !important;
        }
        [data-bs-theme="dark"] .sh-table tbody tr:hover {
            background: var(--sh-gray-100) !important;
        }
        [data-bs-theme="dark"] .text-dark { color: var(--sh-text) !important; }
        [data-bs-theme="dark"] .text-muted { color: var(--sh-text-muted) !important; }
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background: var(--sh-gray-100) !important;
            border-color: var(--sh-border) !important;
            color: var(--sh-text) !important;
        }
        [data-bs-theme="dark"] .dropdown-menu {
            background: var(--sh-card-bg) !important;
            border-color: var(--sh-border) !important;
        }
        [data-bs-theme="dark"] .dropdown-item { color: var(--sh-text) !important; }
        [data-bs-theme="dark"] .dropdown-item:hover { background: var(--sh-gray-100) !important; }
        [data-bs-theme="dark"] .sh-footer {
            background: linear-gradient(to right, #0f172a, #1e293b) !important;
        }
        [data-bs-theme="dark"] .modal-content { background: var(--sh-card-bg) !important; }
        [data-bs-theme="dark"] .list-group-item { background: var(--sh-card-bg) !important; border-color: var(--sh-border) !important; }
        [data-bs-theme="dark"] .sh-page-title { color: #4ade80 !important; }
        [data-bs-theme="dark"] .sh-history-card { background: var(--sh-card-bg) !important; }
        [data-bs-theme="dark"] .table { color: var(--sh-text) !important; }
        [data-bs-theme="dark"] .table-bordered td,
        [data-bs-theme="dark"] .table-bordered th { border-color: var(--sh-border) !important; }

        body {
            background-color: var(--sh-body-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }

        /* Prevent modal from removing scrollbar space and causing layout shift */
        body.modal-open {
            padding-right: 0 !important;
            overflow-y: scroll !important;
        }

        /* --- Navbar --- */
        .sh-navbar {
            background: linear-gradient(135deg, #14532d 0%, #166534 40%, #15803d 100%);
            box-shadow: 0 4px 20px rgba(20, 83, 45, 0.35);
            border: none;
            padding: 0.6rem 0;
            border-bottom: 3px solid var(--sh-accent);
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
        .sh-navbar .navbar-brand-text .brand-logo {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            object-fit: contain;
            background: rgba(255,255,255,0.15);
            padding: 2px;
        }
        .sh-navbar .navbar-brand-text .brand-icon {
            background: rgba(255,255,255,0.15);
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
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--sh-accent);
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
            background: var(--sh-card-bg);
        }
        .sh-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08), 0 8px 24px rgba(0,0,0,0.06);
        }
        .sh-card .card-header {
            background: var(--sh-card-bg);
            border-bottom: 2px solid var(--sh-gray-100);
            padding: 1rem 1.25rem;
        }
        .sh-card .card-header .card-title {
            font-weight: 700;
            color: var(--sh-text);
        }

        /* --- Stat Card --- */
        .sh-stat-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            background: var(--sh-card-bg);
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
        .sh-stat-card.stat-primary::before { background: linear-gradient(90deg, #166534, #22c55e); }

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
            color: var(--sh-text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* --- Hero Balance Card (Pegawai) --- */
        .sh-hero-balance {
            background: linear-gradient(135deg, #14532d 0%, #166534 50%, #15803d 100%);
            border-radius: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
            border: none;
            border-bottom: 4px solid var(--sh-accent);
        }
        .sh-hero-balance::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(184,134,11,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .sh-hero-balance::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
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
            background: linear-gradient(90deg, var(--sh-accent), #fbbf24);
            transition: width 1s ease;
        }

        /* --- Table --- */
        .sh-table thead th {
            background: var(--sh-gray-50);
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--sh-text-muted);
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
            background: linear-gradient(135deg, #166534, #15803d);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            box-shadow: 0 4px 14px rgba(22, 101, 52, 0.3);
            transition: all 0.2s ease;
            color: #fff;
        }
        .sh-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(22, 101, 52, 0.4);
            background: linear-gradient(135deg, #14532d, #166534);
            color: #fff;
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

        .btn-primary {
            background: linear-gradient(135deg, #166534, #15803d) !important;
            border: none !important;
            box-shadow: 0 4px 14px rgba(22, 101, 52, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #14532d, #166534) !important;
            box-shadow: 0 6px 20px rgba(22, 101, 52, 0.4);
        }

        /* --- Mobile Card (History) --- */
        .sh-history-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            border-left: 4px solid var(--sh-gray-100);
            transition: all 0.2s ease;
            background: var(--sh-card-bg);
        }
        .sh-history-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .sh-history-card.status-pending,
        .sh-history-card.status-diajukan,
        .sh-history-card.status-pertimbangan_atasan { border-left-color: var(--sh-warning); }
        .sh-history-card.status-approved,
        .sh-history-card.status-disetujui { border-left-color: var(--sh-success); }
        .sh-history-card.status-rejected,
        .sh-history-card.status-ditolak { border-left-color: var(--sh-danger); }
        .sh-history-card.status-ditangguhkan { border-left-color: var(--sh-accent); }
        .sh-history-card.status-diubah { border-left-color: var(--sh-primary); }

        /* --- Alerts --- */
        .sh-alert {
            border-radius: 12px;
            border: none;
            font-weight: 500;
        }

        /* --- Footer --- */
        .sh-footer {
            background: linear-gradient(to right, #14532d, #166534);
            border-top: 3px solid var(--sh-accent);
            padding: 1rem 0;
        }
        .sh-footer .text-muted {
            color: rgba(255,255,255,0.7) !important;
        }
        .sh-footer strong {
            color: var(--sh-accent) !important;
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
            background: var(--sh-primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: var(--sh-primary);
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
            color: #14532d;
            letter-spacing: -0.5px;
        }

        /* --- Gold accent links --- */
        a { color: var(--sh-primary); }
        a:hover { color: var(--sh-primary-dark); }

        /* --- Form inputs focus --- */
        .form-control:focus, .form-select:focus {
            border-color: var(--sh-primary) !important;
            box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.1) !important;
        }

        /* ===== Fitur 3: Notification Badge ===== */
        .sh-notif-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            border-radius: 50px;
            background: var(--sh-danger);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid #14532d;
            animation: notifPulse 2s ease infinite;
        }
        @keyframes notifPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .sh-notif-unread {
            background: var(--sh-primary-light) !important;
        }

        /* ===== Fitur 8: Loading States ===== */
        .sh-btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.75;
        }
        .sh-btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-top: -8px;
            margin-left: -8px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-right-color: currentColor;
            border-radius: 50%;
            animation: sh-spin 0.6s linear infinite;
        }
        .sh-btn-loading .sh-btn-text {
            visibility: hidden;
        }
        @keyframes sh-spin {
            to { transform: rotate(360deg); }
        }

        /* ===== Fitur 9: Dark Mode Toggle ===== */
        .sh-dark-toggle {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .sh-dark-toggle:hover {
            background: rgba(255,255,255,0.2);
        }

        /* ===== Fitur 10: Calendar Styles ===== */
        .sh-cal-today {
            background: var(--sh-primary);
            color: #fff !important;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .sh-cal-event {
            font-size: 0.68rem;
            padding: 1px 4px;
            border-radius: 4px;
            margin-bottom: 2px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .sh-cal-leave {
            background: var(--sh-success-light);
            color: var(--sh-success);
            font-weight: 600;
        }
        .sh-cal-holiday {
            background: var(--sh-danger-light);
            color: var(--sh-danger);
            font-weight: 600;
        }

        /* ===== Fitur 7: Chart container ===== */
        .sh-chart-container {
            position: relative;
            height: 280px;
        }

        /* --- Mobile optimizations --- */
        @media (max-width: 768px) {
            .sh-hero-number { font-size: 3.5rem; }
            .sh-stat-number { font-size: 1.5rem; }
            .sh-page-title { font-size: 1.25rem; }
            .container-xl { padding-left: 1rem; padding-right: 1rem; }
            .sh-chart-container { height: 200px; }
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
                @if(file_exists(public_path('images/logo-pn-natuna.png')))
                    <img src="{{ asset('images/logo-pn-natuna.png') }}" alt="Logo PN Natuna" class="brand-logo">
                @else
                    <span class="brand-icon"><i class="ti ti-scale"></i></span>
                @endif
                <span>
                    <span style="color: var(--sh-accent);">Si</span>HEALING
                </span>
            </a>
            <div class="navbar-nav flex-row order-md-last">
                {{-- Dark Mode Toggle --}}
                <button class="sh-dark-toggle me-2" id="darkModeToggle" title="Toggle Dark Mode">
                    <i class="ti ti-moon" id="darkModeIcon"></i>
                </button>

                {{-- Notification Bell --}}
                @auth
                <div class="nav-item me-2" style="position: relative;">
                    <a href="{{ route('notifications') }}" class="sh-dark-toggle" title="Notifikasi" style="text-decoration: none;">
                        <i class="ti ti-bell"></i>
                        @php
                            $unreadCount = \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                        <span class="sh-notif-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                        @endif
                    </a>
                </div>
                @endauth

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                        <div class="sh-user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="d-none d-xl-block ps-2">
                            <div class="text-white fw-semibold" style="font-size: 0.9rem;">{{ Auth::user()->name }}</div>
                            <div style="color: var(--sh-accent); font-size: 0.75rem; font-weight: 600;">
                                {{ ucfirst(Auth::user()->role) }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
                        <div class="px-3 py-2 border-bottom">
                            <div class="fw-bold">{{ Auth::user()->name }}</div>
                            <div class="text-muted small">NIP: {{ Auth::user()->nip }}</div>
                        </div>
                        <a href="{{ route('profile') }}" class="dropdown-item py-2">
                            <i class="ti ti-user-circle me-2"></i> Profil Saya
                        </a>
                        <a href="{{ route('notifications') }}" class="dropdown-item py-2">
                            <i class="ti ti-bell me-2"></i> Notifikasi
                            @if($unreadCount > 0)
                            <span class="badge ms-1" style="background: var(--sh-danger); border-radius: 50px; font-size: 0.65rem;">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('kalender') }}" class="dropdown-item py-2">
                            <i class="ti ti-calendar me-2"></i> Kalender Cuti
                        </a>
                        <div class="dropdown-divider"></div>
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
                        {{-- Dashboard: all roles --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-layout-dashboard"></i></span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>

                        {{-- Kalender Cuti: all roles --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('kalender*') ? 'active' : '' }}" href="{{ route('kalender') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-calendar"></i></span>
                                <span class="nav-link-title">Kalender</span>
                            </a>
                        </li>

                        {{-- Ajukan Cuti: pegawai, atasan, ketua --}}
                        @if(!Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('leave/create') || request()->is('leave/select-type') ? 'active' : '' }}" href="{{ route('leave.create') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-file-plus"></i></span>
                                <span class="nav-link-title">Ajukan Cuti</span>
                            </a>
                        </li>
                        @endif

                        {{-- Atasan: badge count for pending reviews --}}
                        @if(Auth::user()->isAtasan())
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard#pending-review">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-checklist"></i></span>
                                <span class="nav-link-title">
                                    Pertimbangan
                                    @php
                                        $navPendingReview = \App\Models\LeaveRequest::where('status', \App\Models\LeaveRequest::STATUS_DIAJUKAN)
                                            ->whereHas('user', fn($q) => $q->where('atasan_id', Auth::id()))
                                            ->count();
                                    @endphp
                                    @if($navPendingReview > 0)
                                    <span class="badge ms-1" style="font-size: 0.7rem; border-radius: 50px; min-width: 20px; background: var(--sh-accent); color: #fff;">{{ $navPendingReview }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                        @endif

                        {{-- Ketua: badge count for pending decisions --}}
                        @if(Auth::user()->isKetua())
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard#needs-decision">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-gavel"></i></span>
                                <span class="nav-link-title">
                                    Keputusan
                                    @php
                                        $navNeedsDecision = \App\Models\LeaveRequest::where('status', \App\Models\LeaveRequest::STATUS_PERTIMBANGAN)->count();
                                    @endphp
                                    @if($navNeedsDecision > 0)
                                    <span class="badge ms-1" style="font-size: 0.7rem; border-radius: 50px; min-width: 20px; background: var(--sh-accent); color: #fff;">{{ $navNeedsDecision }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                        @endif

                        {{-- Admin: Kelola Pegawai --}}
                        @if(Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('pegawai*') ? 'active' : '' }}" href="{{ route('pegawai.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-users"></i></span>
                                <span class="nav-link-title">Kelola Pegawai</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('hari-libur*') ? 'active' : '' }}" href="{{ route('hari-libur.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-calendar-off"></i></span>
                                <span class="nav-link-title">Hari Libur</span>
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
                    &copy; {{ date('Y') }} <strong>SiHEALING</strong> &mdash; Pengadilan Negeri Natuna, Kepulauan Riau
                </span>
            </div>
        </div>
    </footer>

    <!-- Tabler JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>

    {{-- Fitur 9: Dark Mode Toggle JS --}}
    <script>
    (function() {
        const toggle = document.getElementById('darkModeToggle');
        const icon = document.getElementById('darkModeIcon');
        const html = document.documentElement;

        // Load saved preference
        const saved = localStorage.getItem('sh-theme');
        if (saved === 'dark') {
            html.setAttribute('data-bs-theme', 'dark');
            icon.className = 'ti ti-sun';
        }

        toggle.addEventListener('click', function() {
            const isDark = html.getAttribute('data-bs-theme') === 'dark';
            if (isDark) {
                html.setAttribute('data-bs-theme', 'light');
                icon.className = 'ti ti-moon';
                localStorage.setItem('sh-theme', 'light');
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                icon.className = 'ti ti-sun';
                localStorage.setItem('sh-theme', 'dark');
            }
        });
    })();
    </script>

    {{-- Fitur 8: Loading States JS --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var btn = form.querySelector('button[type="submit"]');
                if (btn && !btn.classList.contains('sh-btn-loading')) {
                    // Wrap existing content
                    var inner = btn.innerHTML;
                    btn.innerHTML = '<span class="sh-btn-text">' + inner + '</span>';
                    btn.classList.add('sh-btn-loading');
                    btn.disabled = true;

                    // Auto-reset after 10s in case of error
                    setTimeout(function() {
                        btn.classList.remove('sh-btn-loading');
                        btn.disabled = false;
                        btn.innerHTML = inner;
                    }, 10000);
                }
            });
        });
    });
    </script>

    @stack('scripts')
</body>
</html>
