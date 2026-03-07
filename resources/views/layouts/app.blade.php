<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <!-- Mobile: theme color & safe area -->
    <meta name="theme-color" content="#166534" id="sh-theme-color-meta">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <!-- PWA manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>@yield('title', 'SiHEALING - PN Natuna')</title>
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-pn.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <!-- Tabler CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <!-- NProgress (#2) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css">
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
            /* Calendar palette */
            --sh-cal-holiday-bg: #fef2f2;
            --sh-cal-cb-bg: #eff6ff;
            --sh-cal-cb-border: #1d4ed8;
            --sh-cal-cb-text: #1d4ed8;
            --sh-cal-cb-event-bg: #dbeafe;
            --sh-cal-cb-badge-bg: #1d4ed8;
            --sh-cal-dinas-bg: #fff7ed;
            --sh-cal-legend-bg: #ffffff;
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
            /* Calendar palette — dark */
            --sh-cal-holiday-bg: rgba(239, 68, 68, 0.12);
            --sh-cal-cb-bg: rgba(59, 130, 246, 0.12);
            --sh-cal-cb-border: #60a5fa;
            --sh-cal-cb-text: #93c5fd;
            --sh-cal-cb-event-bg: rgba(59, 130, 246, 0.2);
            --sh-cal-cb-badge-bg: #1d4ed8;
            --sh-cal-dinas-bg: rgba(234, 88, 12, 0.12);
            --sh-cal-legend-bg: var(--sh-card-bg);
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
        /* Calendar dark mode table border */
        [data-bs-theme="dark"] .sh-card .table-bordered td,
        [data-bs-theme="dark"] .sh-card .table-bordered th { border-color: var(--sh-border) !important; }
        [data-bs-theme="dark"] .sh-cal-dinas-luar { color: #fb923c; }
        [data-bs-theme="dark"] .sh-cal-dinas-luar { border-left-color: #fb923c; }
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
            padding: 0.8rem 0;
            border-bottom: 3px solid var(--sh-accent);
            display: flex;
            align-items: center;
            min-height: 68px;
        }
        .sh-navbar .container-xl {
            display: flex;
            align-items: center;
            width: 100%;
            gap: 1rem;
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
            object-fit: cover;
            object-position: center;
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

        /* --- Nav Dropdown (Manajemen) --- */
        .sh-nav-dropdown .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.3rem;
            vertical-align: middle;
            border: none;
            content: "";
            font-family: 'tabler-icons';
            font-size: 0.75rem;
            opacity: 0.7;
        }
        .sh-nav-dropdown-menu {
            min-width: 220px;
            border: none;
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18), 0 2px 8px rgba(0,0,0,0.10);
            padding: 0.5rem;
            margin-top: 0.5rem !important;
            background: #fff;
            overflow: hidden;
        }
        [data-bs-theme="dark"] .sh-nav-dropdown-menu {
            background: #1e293b;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        .sh-nav-dropdown-section {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 0.5rem 0.75rem 0.25rem;
            margin-top: 0.2rem;
        }
        .sh-nav-dropdown-section:first-child { margin-top: 0; }
        .sh-nav-dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1e293b;
            transition: background 0.15s, color 0.15s;
        }
        [data-bs-theme="dark"] .sh-nav-dropdown-menu .dropdown-item { color: #e2e8f0; }
        .sh-nav-dropdown-menu .dropdown-item i {
            font-size: 1rem;
            color: #64748b;
            flex-shrink: 0;
            width: 18px;
            text-align: center;
        }
        .sh-nav-dropdown-menu .dropdown-item:hover,
        .sh-nav-dropdown-menu .dropdown-item:focus {
            background: #f0fdf4;
            color: #166534;
        }
        .sh-nav-dropdown-menu .dropdown-item:hover i,
        .sh-nav-dropdown-menu .dropdown-item:focus i { color: #166534; }
        [data-bs-theme="dark"] .sh-nav-dropdown-menu .dropdown-item:hover,
        [data-bs-theme="dark"] .sh-nav-dropdown-menu .dropdown-item:focus {
            background: #064e3b;
            color: #4ade80;
        }
        .sh-nav-dropdown-menu .dropdown-item.active {
            background: #dcfce7;
            color: #166534;
            font-weight: 700;
        }
        .sh-nav-dropdown-menu .dropdown-item.active i { color: #166534; }
        [data-bs-theme="dark"] .sh-nav-dropdown-menu .dropdown-item.active {
            background: #064e3b;
            color: #4ade80;
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
            transition: box-shadow 0.25s ease, transform 0.25s ease;
            overflow: hidden;
            background: var(--sh-card-bg);
        }
        .sh-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.09), 0 12px 32px rgba(0,0,0,0.07);
            transform: translateY(-1px);
        }
        [data-bs-theme="dark"] .sh-card {
            border: 1px solid rgba(255,255,255,0.06);
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
            transition: box-shadow 0.25s ease, transform 0.25s ease;
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
            transition: background 0.2s ease, box-shadow 0.2s ease;
            color: #fff;
        }
        .sh-btn-primary:hover {
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
            transition: background 0.2s ease, box-shadow 0.2s ease;
        }
        .sh-btn-success:hover { background: linear-gradient(135deg, #047857, #059669); color: #fff; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.4); }
        .sh-btn-danger {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
            transition: background 0.2s ease, box-shadow 0.2s ease;
        }
        .sh-btn-danger:hover { background: linear-gradient(135deg, #b91c1c, #dc2626); color: #fff; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.4); }

        .btn-primary {
            background: linear-gradient(135deg, #166534, #15803d) !important;
            border: none !important;
            box-shadow: 0 4px 14px rgba(22, 101, 52, 0.3);
            transition: background 0.2s ease, box-shadow 0.2s ease;
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
            transition: box-shadow 0.2s ease;
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
            background: radial-gradient(circle at 30% 30%, color-mix(in srgb, var(--sh-primary-light) 80%, white), var(--sh-primary-light));
            box-shadow: 0 4px 16px color-mix(in srgb, var(--sh-primary) 15%, transparent);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: var(--sh-primary);
        }

        /* --- Page Wrapper --- */
        .sh-page-wrapper {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
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
            color: var(--sh-primary);
            letter-spacing: -0.5px;
        }

        /* --- Modal overflow fix (mobile) --- */
        .modal-dialog {
            max-height: 90vh;
        }
        .modal-body {
            max-height: 65vh;
            overflow-y: auto;
        }

        /* --- Mobile table scroll hint --- */
        .table-responsive {
            position: relative;
        }
        @media (max-width: 767.98px) {
            .table-responsive::after {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                width: 32px;
                background: linear-gradient(to right, transparent, rgba(255,255,255,0.85));
                pointer-events: none;
                border-radius: 0 0 16px 0;
            }
            [data-bs-theme="dark"] .table-responsive::after {
                background: linear-gradient(to right, transparent, rgba(30,41,59,0.85));
            }
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
            transition: background 0.2s ease;
        }
        .sh-dark-toggle:hover {
            background: rgba(255,255,255,0.2);
        }
        /* Dark mode icon rotate animation */
        #darkModeIcon {
            display: inline-block;
            transition: transform 0.35s ease, opacity 0.2s ease;
        }
        #darkModeIcon.sh-icon-spin {
            transform: rotate(180deg);
            opacity: 0;
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
        .sh-cal-cuti-bersama {
            background: var(--sh-cal-cb-event-bg);
            color: var(--sh-cal-cb-text);
            font-weight: 600;
            border-left: 3px solid var(--sh-cal-cb-border);
        }
        .sh-cal-dinas-luar {
            background: var(--sh-cal-dinas-bg);
            color: #ea580c;
            border-left: 3px solid #ea580c;
            font-size: 0.65rem;
            font-weight: 600;
        }

        /* ===== Fitur 7: Chart container ===== */
        .sh-chart-container {
            position: relative;
            height: 280px;
        }

        /* --- Scrollbar dark mode --- */
        [data-bs-theme="dark"] {
            scrollbar-color: #334155 #0f172a;
        }
        [data-bs-theme="dark"] ::-webkit-scrollbar { width: 8px; height: 8px; }
        [data-bs-theme="dark"] ::-webkit-scrollbar-track { background: #0f172a; }
        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* --- Focus States (#11) --- */
        .btn:focus-visible,
        .form-control:focus-visible,
        .form-select:focus-visible,
        .form-check-input:focus-visible,
        .nav-link:focus-visible,
        a:focus-visible {
            outline: 3px solid var(--sh-accent) !important;
            outline-offset: 2px;
            box-shadow: 0 0 0 4px rgba(184, 134, 11, 0.2) !important;
        }
        .sh-leave-type-card:focus-within {
            border-color: var(--sh-accent) !important;
            box-shadow: 0 0 0 3px rgba(184, 134, 11, 0.2);
        }

        /* --- Disabled Button Styling (#27) --- */
        .btn:disabled,
        .btn.disabled {
            opacity: 0.55 !important;
            cursor: not-allowed !important;
            pointer-events: auto !important;
            filter: grayscale(30%);
        }

        /* --- Link Underline on Hover/Focus (#30) --- */
        .card-body a:not(.btn):not(.text-decoration-none):not(.nav-link):not(.navbar-brand-text):hover,
        .card-body a:not(.btn):not(.text-decoration-none):not(.nav-link):not(.navbar-brand-text):focus-visible {
            text-decoration: underline !important;
            text-decoration-color: var(--sh-primary) !important;
        }

        /* --- Loading Skeleton (#21) --- */
        .sh-skeleton {
            background: linear-gradient(90deg, var(--sh-gray-100) 25%, var(--sh-gray-50) 50%, var(--sh-gray-100) 75%);
            background-size: 200% 100%;
            animation: sh-shimmer 1.5s infinite;
            border-radius: 8px;
        }
        .sh-skeleton-text { height: 14px; margin-bottom: 8px; }
        .sh-skeleton-title { height: 24px; width: 60%; margin-bottom: 12px; }
        .sh-skeleton-circle { width: 48px; height: 48px; border-radius: 50%; }
        .sh-skeleton-card { height: 120px; border-radius: 16px; }
        @keyframes sh-shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* --- Toast Notifications (#13) --- */
        .sh-toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        }
        .sh-toast {
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            pointer-events: auto;
            animation: sh-toast-in 0.4s ease forwards;
            max-width: 380px;
        }
        .sh-toast-success { background: var(--sh-success); color: #fff; }
        .sh-toast-error { background: var(--sh-danger); color: #fff; }
        .sh-toast-warning { background: var(--sh-warning); color: #fff; }
        .sh-toast-info { background: #3b82f6; color: #fff; }
        .sh-toast-out { animation: sh-toast-out 0.3s ease forwards; }
        @keyframes sh-toast-in {
            from { opacity: 0; transform: translateX(40px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes sh-toast-out {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(40px); }
        }

        /* --- Character Counter (#14) --- */
        .sh-char-counter {
            font-size: 0.75rem;
            color: var(--sh-text-muted);
            text-align: right;
            margin-top: 4px;
            transition: color 0.2s;
        }
        .sh-char-counter.sh-char-warning { color: var(--sh-warning); }
        .sh-char-counter.sh-char-danger { color: var(--sh-danger); font-weight: 600; }

        /* --- Breadcrumb (#10) --- */
        .sh-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.82rem;
            color: var(--sh-text-muted);
            margin-bottom: 0.75rem;
        }
        .sh-breadcrumb a {
            color: var(--sh-primary);
            text-decoration: none;
            font-weight: 500;
        }
        .sh-breadcrumb a:hover { text-decoration: underline; }
        .sh-breadcrumb .sh-breadcrumb-sep { color: var(--sh-text-muted); opacity: 0.5; }
        .sh-breadcrumb .sh-breadcrumb-current { color: var(--sh-text); font-weight: 600; }

        /* --- Skip to Content (#9) --- */
        .sh-skip-link {
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--sh-primary);
            color: #fff;
            padding: 0.5rem 1.5rem;
            border-radius: 0 0 10px 10px;
            z-index: 10000;
            font-weight: 600;
            text-decoration: none;
            transition: top 0.2s;
        }
        .sh-skip-link:focus {
            top: 0;
            color: #fff;
        }

        /* --- Mobile optimizations --- */
        @media (max-width: 767.98px) {
            .sh-hero-number { font-size: 3rem; }
            .sh-stat-number { font-size: 1.4rem; }
            .sh-page-title { font-size: 1.15rem; }
            .container-xl { padding-left: 0.875rem; padding-right: 0.875rem; }
            .sh-chart-container { height: 200px; }
            .sh-history-card .card-body { padding: 0.75rem !important; }
            .sh-toast-container { right: 10px; left: 10px; }
            .sh-toast { max-width: 100%; }
            /* Hero card tighter on mobile */
            .sh-hero-balance .card-body { padding: 1rem !important; }
            /* Stat cards: 3 equal columns on mobile */
            .sh-pegawai-stats .col-6 { flex: 0 0 33.333%; max-width: 33.333%; }
            .sh-stat-card .sh-stat-icon { width: 36px; height: 36px; font-size: 1rem; }
            /* Greeting icon on mobile */
            .sh-greeting-icon { display: none; }

            /* Taller navbar on mobile with proper vertical spacing */
            .sh-navbar {
                padding: 1rem 0 !important;
                min-height: 72px !important;
            }

            /* Significant breathing room between navbar and page content */
            .sh-page-wrapper { padding-top: 3rem; }

            /* Page header: generous top padding matching Notification style */
            .sh-page-header {
                padding-top: 1.25rem;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
            }
            /* Consistent layout for page header content */
            .sh-page-header > div {
                gap: 1rem !important;
                row-gap: 1.25rem !important;
                align-items: flex-start !important;
            }
        }

        /* --- Tablet optimizations (#26) --- */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sh-hero-number { font-size: 3.8rem; }
            .sh-stat-number { font-size: 1.7rem; }
            .sh-page-title { font-size: 1.35rem; }
            .sh-chart-container { height: 240px; }
            .sh-stat-card .card-body { padding: 0.75rem !important; }
        }

        /* === Mobile Nav Panel ===
           Slides out as a clean white card below the navbar,
           replacing the hard-to-read white-on-green look.
        */
        @media (max-width: 767.98px) {
            .sh-navbar { position: relative; }

            /* The collapse panel itself */
            #navbar-menu {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                z-index: 1050;
                background: #ffffff;
                border-top: 3px solid var(--sh-accent);
                border-radius: 0 0 16px 16px;
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
                overflow: hidden;
                /* Animate open/close with max-height */
                max-height: 0;
                transition: max-height 0.32s cubic-bezier(0.4, 0, 0.2, 1);
            }
            #navbar-menu.show {
                max-height: 600px;
            }
            [data-bs-theme="dark"] #navbar-menu {
                background: #1e293b;
                border-top-color: var(--sh-accent);
            }

            /* User info strip at top of mobile menu */
            .sh-mobile-user-strip {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.85rem 1.25rem;
                border-bottom: 2px solid #f1f5f9;
                background: var(--sh-gray-100);
            }
            [data-bs-theme="dark"] .sh-mobile-user-strip {
                background: #0f172a;
                border-bottom-color: #334155;
            }
            .sh-mobile-user-strip .sh-user-avatar {
                background: var(--sh-primary);
                border-color: var(--sh-accent);
                flex-shrink: 0;
            }
            .sh-mobile-user-strip .mu-name {
                font-weight: 700;
                font-size: 0.9rem;
                color: #1e293b;
                line-height: 1.2;
            }
            [data-bs-theme="dark"] .sh-mobile-user-strip .mu-name {
                color: #e2e8f0;
            }
            .sh-mobile-user-strip .mu-role {
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--sh-primary);
            }

            /* Nav links */
            #navbar-menu .nav-link {
                color: #1e293b !important;
                padding: 0.8rem 1.25rem !important;
                font-size: 0.92rem;
                font-weight: 500;
                border-radius: 0 !important;
                border-bottom: 1px solid #f1f5f9;
                display: flex !important;
                align-items: center;
                gap: 0.75rem;
                background: transparent;
                transition: background 0.15s ease, color 0.15s ease;
                min-height: 52px;
            }
            [data-bs-theme="dark"] #navbar-menu .nav-link {
                color: #cbd5e1 !important;
                border-bottom-color: #334155;
            }
            #navbar-menu .nav-link:hover,
            #navbar-menu .nav-link:focus {
                background: var(--sh-primary-light) !important;
                color: var(--sh-primary) !important;
            }
            #navbar-menu .nav-link.active {
                background: var(--sh-primary-light) !important;
                color: var(--sh-primary) !important;
                font-weight: 700;
                border-left: 4px solid var(--sh-primary);
            }
            [data-bs-theme="dark"] #navbar-menu .nav-link:hover,
            [data-bs-theme="dark"] #navbar-menu .nav-link:focus,
            [data-bs-theme="dark"] #navbar-menu .nav-link.active {
                background: #064e3b !important;
                color: #4ade80 !important;
            }

            /* Icons inside mobile nav */
            #navbar-menu .nav-link-icon {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
                border-radius: 8px;
                background: var(--sh-primary-light);
                color: var(--sh-primary);
                font-size: 1rem;
                flex-shrink: 0;
            }
            [data-bs-theme="dark"] #navbar-menu .nav-link-icon {
                background: #064e3b;
                color: #4ade80;
            }
            #navbar-menu .nav-link.active .nav-link-icon {
                background: var(--sh-primary);
                color: #fff;
            }

            /* Mobile: Manajemen dropdown inline */
            #navbar-menu .sh-nav-dropdown-menu {
                position: static !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: #f8fafb !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100%;
            }
            [data-bs-theme="dark"] #navbar-menu .sh-nav-dropdown-menu {
                background: #0f172a !important;
            }
            #navbar-menu .sh-nav-dropdown-menu .dropdown-item {
                border-radius: 0 !important;
                padding: 0.75rem 1.25rem 0.75rem 2.5rem !important;
                border-bottom: 1px solid #f1f5f9;
                font-size: 0.88rem;
                color: #1e293b !important;
            }
            [data-bs-theme="dark"] #navbar-menu .sh-nav-dropdown-menu .dropdown-item { color: #cbd5e1 !important; border-bottom-color: #334155; }
            #navbar-menu .sh-nav-dropdown-menu .dropdown-item:hover,
            #navbar-menu .sh-nav-dropdown-menu .dropdown-item:focus {
                background: var(--sh-primary-light) !important;
                color: var(--sh-primary) !important;
            }
            #navbar-menu .sh-nav-dropdown-menu .dropdown-item.active {
                background: var(--sh-primary-light) !important;
                color: var(--sh-primary) !important;
                font-weight: 700;
                border-left: 4px solid var(--sh-primary);
            }
            #navbar-menu .sh-nav-dropdown-section {
                padding-left: 2.5rem !important;
                background: #f0fdf4;
                border-bottom: 1px solid #dcfce7;
            }
            [data-bs-theme="dark"] #navbar-menu .sh-nav-dropdown-section { background: #064e3b; border-bottom-color: #065f46; }

            /* Bottom action buttons (Profil, Keluar) */
            .sh-mobile-nav-footer {
                padding: 0.75rem 1.25rem;
                display: flex;
                gap: 0.5rem;
                border-top: 2px solid #f1f5f9;
                background: var(--sh-gray-100);
            }
            [data-bs-theme="dark"] .sh-mobile-nav-footer {
                background: #0f172a;
                border-top-color: #334155;
            }
            .sh-mobile-nav-footer .btn {
                flex: 1;
                border-radius: 10px;
                font-size: 0.82rem;
                font-weight: 600;
                padding: 0.5rem 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.4rem;
            }
        }
        /* Hide mobile-only elements on desktop */
        @media (min-width: 768px) {
            .sh-mobile-user-strip,
            .sh-mobile-nav-footer { display: none !important; }
        }

        /* ===== #2 NProgress Custom ===== */
        #nprogress .bar {
            background: var(--sh-accent) !important;
            height: 3px !important;
        }
        #nprogress .peg {
            box-shadow: 0 0 10px var(--sh-accent), 0 0 5px var(--sh-accent) !important;
        }
        #nprogress .spinner-icon {
            border-top-color: var(--sh-accent) !important;
            border-left-color: var(--sh-accent) !important;
        }

        /* ===== #6 Mobile Bottom Navigation ===== */
        .sh-bottom-nav {
            display: none;
        }
        @media (max-width: 767.98px) {
            .sh-bottom-nav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 1040;
                background: var(--sh-card-bg);
                border-top: 2px solid var(--sh-accent);
                box-shadow: 0 -4px 20px rgba(0,0,0,0.12);
                padding: 0.3rem 0 calc(0.3rem + env(safe-area-inset-bottom));
            }
            .sh-bottom-nav-item {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 0.3rem 0.5rem;
                color: var(--sh-text-muted);
                text-decoration: none;
                font-size: 0.6rem;
                font-weight: 600;
                gap: 2px;
                border-radius: 8px;
                transition: color 0.15s, background 0.15s;
                position: relative;
            }
            .sh-bottom-nav-item i {
                font-size: 1.25rem;
                line-height: 1;
            }
            .sh-bottom-nav-item.active {
                color: var(--sh-primary);
            }
            .sh-bottom-nav-item:hover {
                color: var(--sh-primary);
                background: var(--sh-primary-light);
            }
            [data-bs-theme="dark"] .sh-bottom-nav-item.active {
                color: #4ade80;
            }
            [data-bs-theme="dark"] .sh-bottom-nav-item:hover {
                background: #064e3b;
                color: #4ade80;
            }
            .sh-bottom-nav-badge {
                position: absolute;
                top: 2px;
                right: calc(50% - 18px);
                min-width: 16px;
                height: 16px;
                border-radius: 50px;
                background: var(--sh-danger);
                color: #fff;
                font-size: 0.6rem;
                font-weight: 700;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0 3px;
                border: 2px solid var(--sh-card-bg);
            }
            /* Add bottom padding to page for bottom nav */
            .sh-page-wrapper {
                padding-bottom: calc(4.5rem + env(safe-area-inset-bottom)) !important;
            }
        }

        /* ===== #14 Quick Actions Floating Panel ===== */
        .sh-quick-actions {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }
        @media (max-width: 767.98px) {
            .sh-quick-actions { display: none; }
        }
        .sh-qa-toggle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #166534, #15803d);
            color: #fff;
            border: none;
            box-shadow: 0 4px 16px rgba(22,101,52,0.4);
            font-size: 1.3rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .sh-qa-toggle:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 20px rgba(22,101,52,0.5);
        }
        .sh-qa-toggle.active { background: linear-gradient(135deg, #14532d, #166534); }
        .sh-qa-menu {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            opacity: 0;
            transform: translateY(10px) scale(0.95);
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .sh-qa-menu.open {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }
        .sh-qa-btn {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.5rem 1rem 0.5rem 0.6rem;
            border-radius: 50px;
            background: var(--sh-card-bg);
            color: var(--sh-text);
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid var(--sh-border);
            white-space: nowrap;
            transition: background 0.15s, transform 0.15s;
        }
        .sh-qa-btn:hover {
            background: var(--sh-primary-light);
            color: var(--sh-primary);
            transform: translateX(-3px);
        }
        .sh-qa-btn i {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--sh-primary-light);
            color: var(--sh-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        [data-bs-theme="dark"] .sh-qa-btn:hover {
            background: #064e3b;
            color: #4ade80;
        }
        [data-bs-theme="dark"] .sh-qa-btn i {
            background: #064e3b;
            color: #4ade80;
        }

        /* ===== #47 Saldo Warning Badge ===== */
        .sh-saldo-warn {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.6rem;
            border-radius: 50px;
            background: var(--sh-warning);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            animation: notifPulse 2s ease infinite;
        }

        /* ===== Sprint 2 #6: Loading Skeleton ===== */
        .sh-skeleton {
            background: linear-gradient(90deg, var(--sh-gray-100) 25%, var(--sh-gray-50) 50%, var(--sh-gray-100) 75%);
            background-size: 200% 100%;
            animation: sh-shimmer 1.4s infinite;
            border-radius: 6px;
            display: inline-block;
        }
        @keyframes sh-shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .sh-skeleton-row { height: 46px; width: 100%; margin-bottom: 0.5rem; border-radius: 8px; }
        .sh-skeleton-text { height: 14px; width: 80%; margin-bottom: 0.4rem; border-radius: 4px; }
        .sh-skeleton-avatar { width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0; }

        /* ===== Sprint 8 #43: High Contrast Mode ===== */
        [data-contrast="high"] {
            --sh-primary: #004d00 !important;
            --sh-text: #000 !important;
            --sh-text-muted: #333 !important;
            --sh-border: #000 !important;
            --sh-body-bg: #fff !important;
            --sh-card-bg: #fff !important;
            --sh-gray-50: #f0f0f0 !important;
            --sh-gray-100: #e0e0e0 !important;
        }
        [data-contrast="high"] .card, [data-contrast="high"] .sh-card { border: 2px solid #000 !important; }
        [data-contrast="high"] .btn-outline-secondary { border-color: #000 !important; color: #000 !important; }
        [data-contrast="high"] .text-muted { color: #333 !important; }

        /* High Contrast + Dark Mode combined */
        [data-bs-theme="dark"][data-contrast="high"],
        [data-contrast="high"][data-bs-theme="dark"] {
            --sh-body-bg: #000000 !important;
            --sh-card-bg: #0a0a0a !important;
            --sh-text: #ffffff !important;
            --sh-text-muted: #cccccc !important;
            --sh-border: #ffffff !important;
            --sh-primary: #4ade80 !important;
            --sh-primary-light: #052e16 !important;
            --sh-gray-50: #111111 !important;
            --sh-gray-100: #1a1a1a !important;
            --sh-success-light: #052e16 !important;
            --sh-warning-light: #1c1007 !important;
            --sh-danger-light: #1a0505 !important;
        }
        [data-bs-theme="dark"][data-contrast="high"] .card,
        [data-bs-theme="dark"][data-contrast="high"] .sh-card,
        [data-contrast="high"][data-bs-theme="dark"] .card,
        [data-contrast="high"][data-bs-theme="dark"] .sh-card {
            border: 2px solid #ffffff !important;
        }
        [data-bs-theme="dark"][data-contrast="high"] .text-muted,
        [data-contrast="high"][data-bs-theme="dark"] .text-muted {
            color: #cccccc !important;
        }
        [data-bs-theme="dark"][data-contrast="high"] .btn-outline-secondary,
        [data-contrast="high"][data-bs-theme="dark"] .btn-outline-secondary {
            border-color: #ffffff !important;
            color: #ffffff !important;
        }

        /* ===== Sprint 8 #45: Focus Visible ===== */
        :focus-visible {
            outline: 2px solid var(--sh-primary) !important;
            outline-offset: 2px !important;
            border-radius: 4px;
        }
        .btn:focus-visible, a:focus-visible { box-shadow: 0 0 0 3px rgba(22,101,52,0.25) !important; }

        /* --- Button active press feedback --- */
        .btn:not(:disabled):active {
            transform: scale(0.97) !important;
            transition: transform 0.08s ease !important;
        }
        .sh-card a.text-decoration-none:active .sh-leave-type-card,
        .sh-leave-type-card:active { transform: translateY(-1px) scale(0.99) !important; }

        /* --- Inline edit dark mode hover fix --- */
        [data-bs-theme="dark"] .sh-inline-edit:hover,
        [data-bs-theme="dark"] .sh-inline-edit:focus {
            background: rgba(34, 197, 94, 0.12) !important;
        }

        /* ===== Sprint 8 #42: Font Size Adjuster ===== */
        .sh-font-btn {
            background: rgba(255,255,255,0.15);
            border: none;
            color: #fff;
            border-radius: 6px;
            padding: 0.2rem 0.4rem;
            font-weight: 700;
            cursor: pointer;
            line-height: 1;
            transition: background 0.15s;
        }
        .sh-font-btn:hover { background: rgba(255,255,255,0.3); }
        .sh-font-size-group { display: flex; align-items: center; gap: 2px; }

        /* Accessibility popover dropdown */
        .sh-a11y-size-btn {
            background: var(--sh-bg, #f8fafc);
            color: var(--sh-text);
            border: 1.5px solid rgba(0,0,0,0.08);
            border-radius: 7px;
            padding: 0.25rem 0.4rem;
            font-weight: 700;
            cursor: pointer;
            line-height: 1.2;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
            text-align: center;
        }
        .sh-a11y-size-btn:hover { background: var(--sh-primary-light); color: var(--sh-primary); border-color: var(--sh-primary); }
        .sh-a11y-size-btn.sh-size-active { background: var(--sh-primary-light); color: var(--sh-primary); border-color: var(--sh-primary); }
        [data-bs-theme="dark"] .sh-a11y-size-btn { background: #1e293b; color: #94a3b8; border-color: #334155; }
        [data-bs-theme="dark"] .sh-a11y-size-btn:hover,
        [data-bs-theme="dark"] .sh-a11y-size-btn.sh-size-active { background: #064e3b; color: #4ade80; border-color: #166534; }
        .sh-a11y-dropdown { min-width: 170px; border-radius: 12px; padding: 0.75rem; }
        .sh-a11y-label { font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--sh-text-muted); margin-bottom: 0.35rem; }

        /* ===== Sprint 9 #48: Export Progress Overlay ===== */
        .sh-export-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.35); z-index: 9999;
            display: flex; align-items: center; justify-content: center;
        }
        .sh-export-overlay-inner {
            background: var(--sh-card-bg); border-radius: 16px; padding: 1.5rem 2rem;
            text-align: center; box-shadow: 0 16px 40px rgba(0,0,0,0.2);
            min-width: 240px;
        }

        /* ===== Sprint 6 #35: Notification date separator ===== */
        .sh-notif-date-sep {
            text-align: center; font-size: 0.75rem; font-weight: 600;
            color: var(--sh-text-muted); padding: 0.5rem 1rem;
            position: relative;
        }
        .sh-notif-date-sep::before, .sh-notif-date-sep::after {
            content: ''; position: absolute; top: 50%; width: 30%;
            height: 1px; background: var(--sh-border);
        }
        .sh-notif-date-sep::before { left: 1rem; }
        .sh-notif-date-sep::after { right: 1rem; }

        /* ============================================================
           UI/UX IMPROVEMENTS — 2026-03-07
           ============================================================ */

        /* T2: Prevent iOS auto-zoom on input focus */
        @media (max-width: 768px) {
            input[type="text"],
            input[type="email"],
            input[type="password"],
            input[type="number"],
            input[type="date"],
            input[type="search"],
            select,
            textarea {
                font-size: 16px !important;
            }
        }

        /* T3: Touch target minimum 44px (WCAG 2.5.5) */
        @media (max-width: 768px) {
            .sh-bottom-nav-item,
            .navbar-toggler,
            .page-link,
            .dropdown-item {
                min-height: 44px;
                min-width: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .btn-sm {
                padding: 0.5rem 0.85rem;
                min-height: 44px;
            }
        }

        /* T4: FAB — Floating Action Button */
        .sh-fab {
            position: fixed;
            bottom: 80px;
            right: 1.25rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #14532d, #166634);
            color: #fff;
            border: none;
            box-shadow: 0 4px 16px rgba(20,83,45,0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            z-index: 1040;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
        }
        .sh-fab:hover, .sh-fab:focus {
            transform: scale(1.08);
            box-shadow: 0 6px 24px rgba(20,83,45,0.55);
            color: #fff;
        }
        .sh-fab:active { transform: scale(0.96); }
        @media (min-width: 992px) { .sh-fab { display: none; } }
        [data-bs-theme="dark"] .sh-fab {
            background: linear-gradient(135deg, #064e3b, #065f46);
            box-shadow: 0 4px 16px rgba(0,0,0,0.4);
        }
        [data-bs-theme="dark"] .sh-fab:hover {
            background: linear-gradient(135deg, #065f46, #047857);
            box-shadow: 0 6px 24px rgba(0,0,0,0.5);
        }

        /* T6: Avatar inisial pegawai */
        .sh-avatar-initials {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            color: #fff;
            flex-shrink: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* T9: Print-friendly CSS */
        @media print {
            .navbar, .sh-bottom-nav, .sh-fab, .sh-sidebar,
            .navbar-toggler, .dropdown-menu,
            .sh-toast-container, #nprogress,
            .sh-page-header .btn, .btn:not(.btn-print) { display: none !important; }
            body { background: white !important; color: black !important; }
            .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
            .sh-stat-card, .card { break-inside: avoid; }
            .container-xl { max-width: 100% !important; padding: 0 !important; }
            a { color: inherit !important; text-decoration: none !important; }
            table { border-collapse: collapse !important; }
            th, td { border: 1px solid #dee2e6 !important; padding: 0.4rem !important; }
        }

        /* T22: Page fade-in transition */
        .sh-page-transition {
            animation: sh-fade-in 0.25s ease;
        }
        @keyframes sh-fade-in {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* T18: Responsive table → card stack on mobile */
        @media (max-width: 767px) {
            .sh-table-responsive-cards thead { display: none; }
            .sh-table-responsive-cards tbody tr {
                display: block;
                margin-bottom: 0.85rem;
                border-radius: 12px;
                border: 1px solid var(--sh-border, #d1e7d8);
                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                padding: 0.75rem;
                background: var(--sh-card-bg, #fff);
            }
            .sh-table-responsive-cards td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.35rem 0;
                border: none;
                border-bottom: 1px solid var(--sh-border-light, #e8f5e9);
                font-size: 0.88rem;
            }
            .sh-table-responsive-cards td:last-child { border-bottom: none; }
            .sh-table-responsive-cards td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--sh-muted, #64748b);
                font-size: 0.78rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-right: 0.5rem;
                flex-shrink: 0;
                min-width: 80px;
            }
        }

        /* T19: Sticky saldo balance bar */
        .sh-sticky-balance {
            position: sticky;
            top: 60px;
            z-index: 100;
            background: linear-gradient(135deg, #14532d, #166534);
            color: white;
            padding: 0.55rem 1rem;
            border-radius: 0 0 12px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
            box-shadow: 0 4px 12px rgba(20,83,45,0.2);
            margin-bottom: 1rem;
        }
        @media (min-width: 992px) { .sh-sticky-balance { display: none; } }
        [data-bs-theme="dark"] .sh-sticky-balance {
            background: linear-gradient(135deg, #064e3b, #065f46);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        /* Safe area insets (notch + home bar) */
        .sh-bottom-nav {
            padding-bottom: calc(0.5rem + env(safe-area-inset-bottom));
            padding-left: env(safe-area-inset-left);
            padding-right: env(safe-area-inset-right);
        }
        .navbar {
            padding-top: max(0.5rem, env(safe-area-inset-top));
        }
        body {
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* Landscape hint on mobile */
        @media (max-height: 500px) and (max-width: 900px) and (orientation: landscape) {
            .sh-landscape-hint {
                display: flex !important;
            }
        }
        .sh-landscape-hint {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 40, 24, 0.95);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            text-align: center;
            gap: 1rem;
        }

        /* Bottom Sheet (mobile modal replacement) */
        .sh-bottom-sheet-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .sh-bottom-sheet-backdrop.active {
            display: block;
            opacity: 1;
        }
        .sh-bottom-sheet {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1051;
            background: var(--sh-card-bg, #fff);
            border-radius: 20px 20px 0 0;
            padding: 0 1.25rem 1.5rem;
            padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 -8px 32px rgba(0,0,0,0.15);
        }
        .sh-bottom-sheet.active {
            transform: translateY(0);
        }
        .sh-bottom-sheet-handle {
            width: 40px;
            height: 4px;
            background: var(--sh-border, #d1e7d8);
            border-radius: 2px;
            margin: 0.75rem auto 1rem;
        }
        .sh-bottom-sheet-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--sh-text, #1e293b);
            margin-bottom: 1rem;
        }
        @media (min-width: 768px) {
            /* On desktop, bottom sheet behaves like normal modal */
            .sh-bottom-sheet {
                position: relative;
                transform: none;
                border-radius: 16px;
                max-height: none;
                box-shadow: none;
                padding: 1.25rem;
            }
        }

/* Quick filter chips */
.sh-filter-chips {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.sh-filter-chips::-webkit-scrollbar { display: none; }
.sh-filter-chip {
    flex-shrink: 0;
    font-size: 0.78rem;
    padding: 0.3rem 0.85rem;
    border-radius: 99px;
    border: 1.5px solid var(--sh-border, #d1e7d8);
    color: var(--sh-text-muted, #64748b);
    background: transparent;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
    font-weight: 500;
}
.sh-filter-chip.active,
.sh-filter-chip:hover {
    background: var(--sh-primary, #166534);
    border-color: var(--sh-primary, #166534);
    color: white;
}

/* Compact/Expanded toggle */
.sh-view-toggle { display: flex; gap: 0.25rem; }
.sh-view-btn {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: 1.5px solid var(--sh-border, #d1e7d8);
    background: transparent;
    color: var(--sh-text-muted, #64748b);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem;
    transition: all 0.15s;
}
.sh-view-btn.active {
    background: var(--sh-primary, #166534);
    border-color: var(--sh-primary, #166534);
    color: white;
}
.sh-compact-row td { padding: 0.4rem 0.75rem !important; font-size: 0.83rem !important; }

/* Long press preview tooltip */
.sh-longpress-preview {
    position: fixed;
    z-index: 2000;
    background: var(--sh-card-bg, #fff);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    border: 1px solid var(--sh-border, #d1e7d8);
    border-top: 3px solid var(--sh-primary, #166534);
    padding: 1rem;
    max-width: 280px;
    min-width: 220px;
    pointer-events: none;
    opacity: 0;
    transform: scale(0.95);
    transition: opacity 0.2s, transform 0.2s;
}
.sh-longpress-preview.visible {
    opacity: 1;
    transform: scale(1);
}
.sh-longpress-preview-title {
    font-weight: 700;
    font-size: 0.88rem;
    color: var(--sh-text);
    margin-bottom: 0.5rem;
}
.sh-longpress-preview-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    padding: 0.2rem 0;
    border-bottom: 1px solid var(--sh-border-light, #e8f5e9);
    color: var(--sh-text-muted);
}
.sh-longpress-preview-row:last-child { border-bottom: none; }

        /* Floating back button (mobile only) */
        .sh-float-back {
            display: none;
        }
        @media (max-width: 768px) {
            .sh-float-back {
                display: flex;
                position: fixed;
                bottom: 80px;
                left: 1.25rem;
                width: 44px;
                height: 44px;
                border-radius: 50%;
                background: var(--sh-card-bg, #fff);
                border: 1.5px solid var(--sh-border, #d1e7d8);
                box-shadow: 0 2px 12px rgba(0,0,0,0.12);
                align-items: center;
                justify-content: center;
                color: var(--sh-primary, #166534);
                font-size: 1.1rem;
                z-index: 1039;
                text-decoration: none;
                transition: transform 0.15s;
            }
            .sh-float-back:hover { transform: scale(1.08); color: var(--sh-primary); }
            .sh-float-back:active { transform: scale(0.95); }
        }
/* Lottie success overlay */
.sh-success-overlay {
    position: fixed;
    inset: 0;
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(4px);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
}
[data-bs-theme="dark"] .sh-success-overlay {
    background: rgba(15, 23, 42, 0.92);
}
.sh-success-overlay.visible {
    opacity: 1;
    pointer-events: all;
}
.sh-success-overlay p {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--sh-primary, #166534);
    margin-top: 0.5rem;
}
    </style>
<script src="https://cdn.jsdelivr.net/npm/@lottiefiles/lottie-player@2/dist/lottie-player.js" defer></script>
</head>
<body class="d-flex flex-column min-vh-100">
<!-- Landscape hint -->
<div class="sh-landscape-hint" id="sh-landscape-hint">
    <i class="ti ti-rotate" style="font-size: 3rem; animation: sh-rotate-hint 1.5s ease-in-out infinite alternate;"></i>
    <p style="font-size: 1rem; font-weight: 600; margin: 0;">Putar perangkat untuk tampilan optimal</p>
</div>
<style>
@keyframes sh-rotate-hint {
    from { transform: rotate(-30deg); }
    to   { transform: rotate(30deg); }
}
</style>
    {{-- Skip to Content (#9) --}}
    <a href="#main-content" class="sh-skip-link">Langsung ke Konten</a>

    <header class="navbar navbar-expand-md d-print-none sh-navbar" role="banner">
        <div class="container-xl">
            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-label="Toggle navigasi">
                <i class="ti ti-menu-2" style="font-size: 1.4rem;" aria-hidden="true"></i>
            </button>
            <a href="/dashboard" class="navbar-brand-text">
                @if(file_exists(public_path('images/favicon-pn.png')))
                    <img src="{{ asset('images/favicon-pn.png') }}" alt="Logo PN Natuna" class="brand-logo">
                @else
                    <span class="brand-icon"><i class="ti ti-scale"></i></span>
                @endif
                <span>
                    <span style="color: var(--sh-accent);">Si</span>HEALING
                </span>
            </a>
            <div class="navbar-nav flex-row order-md-last align-items-center">
                {{-- Sprint 8 #42 & #43: Accessibility Menu (Font Size + Contrast) --}}
                <div class="nav-item dropdown me-2 d-none d-md-flex align-items-center">
                    <button class="sh-dark-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            title="Aksesibilitas" aria-label="Menu aksesibilitas" aria-expanded="false">
                        <span style="font-size:0.78rem;font-weight:800;line-height:1;letter-spacing:-0.5px;font-family:inherit;">Aa</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end sh-a11y-dropdown">
                        <div class="sh-a11y-label">Ukuran Teks</div>
                        <div class="d-flex gap-1 mb-3">
                            <button class="sh-a11y-size-btn flex-fill" id="fontSmall" aria-label="Teks kecil" style="font-size:0.7rem;">A</button>
                            <button class="sh-a11y-size-btn flex-fill" id="fontNormal" aria-label="Teks normal" style="font-size:0.85rem;">A</button>
                            <button class="sh-a11y-size-btn flex-fill" id="fontLarge" aria-label="Teks besar" style="font-size:1rem;">A</button>
                        </div>
                        <div class="sh-a11y-label">Tampilan</div>
                        <button id="contrastToggle" class="sh-a11y-size-btn w-100 d-flex align-items-center gap-2"
                                style="font-size:0.78rem;padding:0.35rem 0.6rem;" aria-label="Toggle kontras tinggi">
                            <i class="ti ti-contrast" id="contrastIcon"></i>
                            <span id="contrastLabel">Kontras Tinggi</span>
                        </button>
                    </div>
                </div>

                {{-- Dark Mode Toggle (#6, #7) --}}
                <div class="nav-item me-2">
                    <button class="sh-dark-toggle" id="darkModeToggle" title="Toggle Dark Mode" aria-label="Toggle mode gelap/terang">
                        <i class="ti ti-moon" id="darkModeIcon" aria-hidden="true"></i>
                    </button>
                </div>

                {{-- #47 Saldo Hampir Habis Warning --}}
                @auth
                @if(!Auth::user()->isAdmin() && Auth::user()->bolehCuti())
                @php $navLeaveBalance = Auth::user()->leave_balance ?? 0; @endphp
                @if($navLeaveBalance <= 3 && $navLeaveBalance >= 0)
                <div class="nav-item me-2 d-none d-md-flex align-items-center">
                    <a href="{{ route('dashboard') }}" class="sh-saldo-warn" title="Saldo cuti Anda tinggal {{ $navLeaveBalance }} hari">
                        <i class="ti ti-alert-triangle"></i>
                        <span>Saldo: {{ $navLeaveBalance }}h</span>
                    </a>
                </div>
                @endif
                @endif
                @endauth

                {{-- Notification Bell (#8) --}}
                @auth
                <div class="nav-item me-2" style="position: relative;">
                    <a href="{{ route('notifications') }}" class="sh-dark-toggle" title="Notifikasi" style="text-decoration: none;" aria-label="Notifikasi{{ ($unreadCount = \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count()) > 0 ? ' - ' . $unreadCount . ' belum dibaca' : '' }}">
                        <i class="ti ti-bell" aria-hidden="true"></i>
                        @if($unreadCount > 0)
                        <span class="sh-notif-badge" role="status" aria-label="{{ $unreadCount }} notifikasi belum dibaca">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                        @endif
                    </a>
                </div>
                @endauth

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                        <div class="sh-user-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="d-none d-md-block ps-2">
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
                {{-- Mobile-only: user info strip --}}
                <div class="sh-mobile-user-strip">
                    <div class="sh-user-avatar" style="width:38px;height:38px;border-radius:10px;color:#fff;font-weight:700;font-size:0.85rem;display:flex;align-items:center;justify-content:center;border:2px solid var(--sh-accent);">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="mu-name">{{ Auth::user()->name }}</div>
                        <div class="mu-role">
                            @php
                                $roleLabel = match(Auth::user()->role) {
                                    'admin' => 'Admin Kepegawaian',
                                    'ketua' => 'Ketua Pengadilan',
                                    'panitera' => 'Panitera',
                                    'sekretaris' => 'Sekretaris',
                                    'atasan' => 'Atasan',
                                    'hakim' => 'Hakim',
                                    'hakim_ad_hoc' => 'Hakim Ad Hoc',
                                    default => 'Pegawai',
                                };
                            @endphp
                            {{ $roleLabel }} &bull; NIP {{ Auth::user()->nip }}
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                    <ul class="navbar-nav" role="navigation" aria-label="Navigasi utama">
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

                        {{-- Ajukan Cuti: pegawai, atasan, ketua (kecuali CPNS & PPPK baru dilantik) --}}
                        @if(!Auth::user()->isAdmin() && Auth::user()->bolehCuti())
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

                        {{-- Admin: Dropdown Manajemen --}}
                        @if(Auth::user()->isAdmin())
                        @php
                            $isManajemenActive = request()->is('pegawai*') || request()->is('hari-libur*') || request()->is('dinas-luar*') || request()->is('laporan-bulanan*') || request()->is('admin/*') || request()->is('balance-adjustments*') || request()->is('analytics*') || request()->is('laporan-saldo-cuti*');
                        @endphp
                        <li class="nav-item dropdown sh-nav-dropdown">
                            <a class="nav-link dropdown-toggle {{ $isManajemenActive ? 'active' : '' }}"
                               href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                               aria-expanded="false" aria-haspopup="true">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-settings"></i></span>
                                <span class="nav-link-title">Manajemen</span>
                            </a>
                            <div class="dropdown-menu sh-nav-dropdown-menu">
                                <div class="sh-nav-dropdown-section">Kepegawaian</div>
                                <a class="dropdown-item {{ request()->is('pegawai*') ? 'active' : '' }}" href="{{ route('pegawai.index') }}">
                                    <i class="ti ti-users"></i> Kelola Pegawai
                                </a>
                                <a class="dropdown-item {{ request()->is('balance-adjustments*') ? 'active' : '' }}" href="{{ route('balance-adjustment.index') }}">
                                    <i class="ti ti-adjustments-horizontal"></i> Penyesuaian Saldo
                                </a>
                                <div class="sh-nav-dropdown-section">Kalender & Kehadiran</div>
                                <a class="dropdown-item {{ request()->is('hari-libur*') ? 'active' : '' }}" href="{{ route('hari-libur.index') }}">
                                    <i class="ti ti-calendar-off"></i> Hari Libur
                                </a>
                                <a class="dropdown-item {{ request()->is('dinas-luar*') ? 'active' : '' }}" href="{{ route('dinas-luar.index') }}">
                                    <i class="ti ti-briefcase"></i> Dinas Luar
                                </a>
                                <div class="sh-nav-dropdown-section">Laporan & Analytics</div>
                                <a class="dropdown-item {{ request()->is('laporan-bulanan*') ? 'active' : '' }}" href="{{ route('laporan-bulanan.index') }}">
                                    <i class="ti ti-file-spreadsheet"></i> Laporan Bulanan
                                </a>
                                <a class="dropdown-item {{ request()->is('analytics*') ? 'active' : '' }}" href="{{ route('analytics.index') }}">
                                    <i class="ti ti-chart-bar"></i> Analytics Cuti
                                </a>
                                <a class="dropdown-item {{ request()->is('laporan-saldo-cuti*') ? 'active' : '' }}" href="{{ route('laporan-saldo-cuti.index') }}">
                                    <i class="ti ti-report"></i> Laporan Saldo Cuti
                                </a>
                                <a class="dropdown-item {{ request()->is('admin/audit-logs*') ? 'active' : '' }}" href="{{ route('admin.audit-logs.index') }}">
                                    <i class="ti ti-clipboard-list"></i> Audit Log
                                </a>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>

                {{-- Mobile-only: quick action footer --}}
                <div class="sh-mobile-nav-footer">
                    <a href="{{ route('profile') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-user-circle"></i> Profil
                    </a>
                    <a href="{{ route('notifications') }}" class="btn btn-outline-secondary" style="position:relative;">
                        <i class="ti ti-bell"></i> Notifikasi
                        @if(isset($unreadCount) && $unreadCount > 0)
                        <span style="position:absolute;top:4px;right:4px;width:8px;height:8px;border-radius:50%;background:var(--sh-danger);display:block;"></span>
                        @endif
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="flex:1;">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="ti ti-logout"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Toast Container (#13) --}}
    <div class="sh-toast-container" id="shToastContainer" aria-live="polite"></div>

    <div class="page-wrapper flex-fill sh-page-wrapper" id="main-content" role="main" tabindex="-1">
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

    {{-- #6 Mobile Bottom Navigation --}}
    @auth
    <nav class="sh-bottom-nav d-print-none" aria-label="Navigasi bawah mobile">
        <a href="{{ route('dashboard') }}" class="sh-bottom-nav-item {{ request()->is('dashboard') ? 'active' : '' }}" aria-label="Dashboard">
            <i class="ti ti-layout-dashboard"></i>
            <span>Dashboard</span>
        </a>
        @if(!Auth::user()->isAdmin() && Auth::user()->bolehCuti())
        <a href="{{ route('leave.create') }}" class="sh-bottom-nav-item {{ request()->is('leave/create') || request()->is('leave/select-type') ? 'active' : '' }}" aria-label="Ajukan Cuti">
            <i class="ti ti-file-plus"></i>
            <span>Ajukan</span>
        </a>
        @endif
        <a href="{{ route('kalender') }}" class="sh-bottom-nav-item {{ request()->is('kalender*') ? 'active' : '' }}" aria-label="Kalender">
            <i class="ti ti-calendar"></i>
            <span>Kalender</span>
        </a>
        <a href="{{ route('notifications') }}" class="sh-bottom-nav-item {{ request()->is('notifications*') ? 'active' : '' }}" aria-label="Notifikasi" style="position:relative;">
            <i class="ti ti-bell"></i>
            @php $bottomUnread = \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count(); @endphp
            @if($bottomUnread > 0)
            <span class="sh-bottom-nav-badge">{{ $bottomUnread > 9 ? '9+' : $bottomUnread }}</span>
            @endif
            <span>Notifikasi</span>
        </a>
        @if(Auth::user()->isAdmin())
        <a href="{{ route('pegawai.index') }}" class="sh-bottom-nav-item {{ request()->is('pegawai*') ? 'active' : '' }}" aria-label="Pegawai">
            <i class="ti ti-users"></i>
            <span>Pegawai</span>
        </a>
        @endif
    </nav>
    @endauth

    {{-- FAB: Ajukan Cuti (mobile only) --}}
    @auth
        @if(auth()->check() && method_exists(auth()->user(), 'bolehCuti') && auth()->user()->bolehCuti() && !auth()->user()->isAdmin())
        <a href="{{ route('leave.select-type') }}"
           class="sh-fab"
           aria-label="Ajukan Cuti"
           title="Ajukan Cuti">
            <i class="ti ti-file-plus"></i>
        </a>
        @endif
    @endauth

    {{-- #14 Quick Actions Floating Panel (Desktop) --}}
    @auth
    <div class="sh-quick-actions d-print-none">
        <div class="sh-qa-menu" id="shQaMenu">
            @if(!Auth::user()->isAdmin() && Auth::user()->bolehCuti())
            <a href="{{ route('leave.create') }}" class="sh-qa-btn">
                <i class="ti ti-file-plus"></i> Ajukan Cuti
            </a>
            @endif
            <a href="{{ route('kalender') }}" class="sh-qa-btn">
                <i class="ti ti-calendar"></i> Lihat Kalender
            </a>
            @if(Auth::user()->isAdmin())
            <a href="{{ route('laporan-bulanan.index') }}" class="sh-qa-btn">
                <i class="ti ti-file-spreadsheet"></i> Laporan Bulanan
            </a>
            <a href="{{ route('analytics.index') }}" class="sh-qa-btn">
                <i class="ti ti-chart-bar"></i> Analytics
            </a>
            @endif
        </div>
        <button class="sh-qa-toggle" id="shQaToggle" aria-label="Quick Actions" title="Aksi Cepat">
            <i class="ti ti-bolt" id="shQaIcon"></i>
        </button>
    </div>
    @endauth

    {{-- Sprint 8 #44: Keyboard Shortcuts Modal --}}
    <div class="modal fade" id="shKeyboardModal" tabindex="-1" aria-labelledby="shKeyboardModalLabel">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="shKeyboardModalLabel">
                        <i class="ti ti-keyboard me-2" style="color:var(--sh-primary);"></i>
                        Pintasan Keyboard
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><td><kbd>?</kbd></td><td>Tampilkan/sembunyikan daftar pintasan ini</td></tr>
                            <tr><td><kbd>n</kbd></td><td>Ajukan Cuti baru</td></tr>
                            <tr><td><kbd>k</kbd></td><td>Buka Kalender Cuti</td></tr>
                            <tr><td><kbd>d</kbd></td><td>Kembali ke Dashboard</td></tr>
                            <tr><td><kbd>Esc</kbd></td><td>Tutup modal/dialog yang sedang terbuka</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="sh-footer d-print-none mt-auto" role="contentinfo">
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
    <!-- NProgress (#2) -->
    <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.js"></script>

    {{-- System dark mode auto-follow --}}
    <script>
    // System dark mode auto-follow
    (function() {
        var html = document.documentElement;
        // Only auto-follow if user hasn't set a manual preference
        if (!localStorage.getItem('sh-theme')) {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                html.setAttribute('data-bs-theme', 'dark');
            }
            // Listen for OS changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('sh-theme')) {
                    html.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
                }
            });
        }
    })();
    </script>

    {{-- Dark Mode Toggle JS --}}
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
            // Spin out
            icon.classList.add('sh-icon-spin');
            setTimeout(function() {
                if (isDark) {
                    html.setAttribute('data-bs-theme', 'light');
                    icon.className = 'ti ti-moon';
                    localStorage.setItem('sh-theme', 'light');
                } else {
                    html.setAttribute('data-bs-theme', 'dark');
                    icon.className = 'ti ti-sun';
                    localStorage.setItem('sh-theme', 'dark');
                }
                // Spin in
                icon.style.transform = 'rotate(0deg)';
                icon.style.opacity = '1';
                setTimeout(function() {
                    icon.classList.remove('sh-icon-spin');
                }, 50);
            }, 200);
        });
    })();
    </script>

    {{-- Sync theme-color meta with dark/light mode --}}
    <script>
    // Sync theme-color meta with dark/light mode
    (function() {
        var meta = document.getElementById('sh-theme-color-meta');
        function syncMeta() {
            if (!meta) return;
            meta.content = document.documentElement.getAttribute('data-bs-theme') === 'dark'
                ? '#0f172a' : '#166534';
        }
        syncMeta();
        var toggle = document.getElementById('themeToggle');
        if (toggle) toggle.addEventListener('click', function() { setTimeout(syncMeta, 50); });
    })();
    </script>

    {{-- Flash Message Auto-Dismiss --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.sh-alert.alert-dismissible').forEach(function(alert) {
            var delay = alert.classList.contains('alert-danger') ? 7000 : 4000;
            setTimeout(function() {
                var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            }, delay);
        });
    });
    </script>

    {{-- Fitur 8: Loading States + Network Error + Toast (#1, #5, #13) --}}
    <script>
    // Toast notification system (#13)
    window.shToast = function(message, type) {
        type = type || 'success';
        var container = document.getElementById('shToastContainer');
        if (!container) return;
        var toast = document.createElement('div');
        toast.className = 'sh-toast sh-toast-' + type;
        var icon = type === 'success' ? 'ti-circle-check' : (type === 'error' ? 'ti-alert-triangle' : (type === 'warning' ? 'ti-alert-circle' : 'ti-info-circle'));
        toast.innerHTML = '<i class="ti ' + icon + '"></i> ' + message;
        container.appendChild(toast);
        setTimeout(function() {
            toast.classList.add('sh-toast-out');
            setTimeout(function() { toast.remove(); }, 300);
        }, 4000);
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Show toast for flash messages
        @if(session('success'))
        shToast(@json(session('success')), 'success');
        @endif
        @if(session('error'))
        shToast(@json(session('error')), 'error');
        @endif

        // Enhanced form submit with loading state + double-submit prevention (#1, #5)
        document.querySelectorAll('form').forEach(function(form) {
            // Skip forms handled via AJAX (review modal handles its own submit)
            if (form.dataset.ajaxHandled) return;

            var submitted = false;
            form.addEventListener('submit', function(e) {
                // Prevent double submission (#1)
                if (submitted) { e.preventDefault(); return; }
                submitted = true;

                var btn = form.querySelector('button[type="submit"]');
                if (btn && !btn.classList.contains('sh-btn-loading')) {
                    var inner = btn.innerHTML;
                    btn.innerHTML = '<span class="sh-btn-text">' + inner + '</span>';
                    btn.classList.add('sh-btn-loading');
                    btn.disabled = true;

                    // Reset on back-button navigation
                    window.addEventListener('pageshow', function onPageShow(ev) {
                        if (ev.persisted) {
                            btn.classList.remove('sh-btn-loading');
                            btn.disabled = false;
                            btn.innerHTML = inner;
                            submitted = false;
                        }
                        window.removeEventListener('pageshow', onPageShow);
                    });

                    // Fallback reset after 15s in case of network error (#5)
                    setTimeout(function() {
                        btn.classList.remove('sh-btn-loading');
                        btn.disabled = false;
                        btn.innerHTML = inner;
                        submitted = false;
                        shToast('Koneksi timeout. Silakan coba lagi.', 'error');
                    }, 15000);
                }
            });
        });

        // Network status detection (#5)
        window.addEventListener('offline', function() {
            shToast('Koneksi internet terputus. Periksa jaringan Anda.', 'error');
        });
        window.addEventListener('online', function() {
            shToast('Koneksi internet kembali aktif.', 'success');
        });
    });
    </script>

    {{-- Mobile Nav Toggle JS --}}
    <script>
    (function() {
        var menu   = document.getElementById('navbar-menu');
        var toggler = document.querySelector('.navbar-toggler[data-bs-target="#navbar-menu"]');
        if (!menu || !toggler) return;

        function isMobile() { return window.innerWidth < 768; }

        // Override Bootstrap collapse — use CSS max-height animation instead
        toggler.addEventListener('click', function(e) {
            if (!isMobile()) return; // let Bootstrap handle desktop
            e.stopPropagation();
            e.preventDefault();

            if (menu.classList.contains('show')) {
                menu.classList.remove('show');
            } else {
                menu.classList.add('show');
            }
        });

        // Close menu when a nav link inside is clicked
        menu.querySelectorAll('.nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (isMobile()) menu.classList.remove('show');
            });
        });

        // Close menu on outside click
        document.addEventListener('click', function(e) {
            if (isMobile() && menu.classList.contains('show')
                && !menu.contains(e.target)
                && !toggler.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        // Reset on resize to desktop
        window.addEventListener('resize', function() {
            if (!isMobile()) menu.classList.remove('show');
        });
    })();
    </script>

    {{-- #2 NProgress page load bar --}}
    <script>
    (function() {
        NProgress.configure({ showSpinner: false, speed: 300, minimum: 0.1 });
        // Start on every link click (not AJAX, not anchor, not logout)
        document.addEventListener('click', function(e) {
            var a = e.target.closest('a');
            if (!a) return;
            var href = a.getAttribute('href');
            if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript') || a.getAttribute('target') === '_blank') return;
            if (a.getAttribute('data-bs-toggle')) return; // Bootstrap toggles
            NProgress.start();
        });
        document.addEventListener('submit', function() {
            NProgress.start();
        });
        window.addEventListener('pageshow', function() {
            NProgress.done();
        });
        // Fallback done after load
        window.addEventListener('load', function() {
            NProgress.done();
        });
    })();
    </script>

    {{-- #14 Quick Actions Panel --}}
    <script>
    (function() {
        var toggle = document.getElementById('shQaToggle');
        var menu = document.getElementById('shQaMenu');
        var icon = document.getElementById('shQaIcon');
        if (!toggle || !menu) return;

        var open = false;
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            open = !open;
            menu.classList.toggle('open', open);
            toggle.classList.toggle('active', open);
            icon.className = open ? 'ti ti-x' : 'ti ti-bolt';
        });
        document.addEventListener('click', function() {
            if (open) {
                open = false;
                menu.classList.remove('open');
                toggle.classList.remove('active');
                icon.className = 'ti ti-bolt';
            }
        });
    })();
    </script>

    {{-- Sprint 2 #8: Toast for deleted_info flash --}}
    @if(session('deleted_info'))
    <script>document.addEventListener('DOMContentLoaded', function() { shToast(@json(session('deleted_info')), 'success'); });</script>
    @endif

    {{-- Sprint 8 #42: Font Size Adjuster --}}
    <script>
    (function() {
        var sizes = { small: '0.875rem', normal: '1rem', large: '1.125rem' };
        var saved = localStorage.getItem('sh-font-size') || 'normal';
        document.documentElement.style.fontSize = sizes[saved] || '1rem';

        function setSize(key) {
            document.documentElement.style.fontSize = sizes[key];
            localStorage.setItem('sh-font-size', key);
            updateActiveBtn(key);
        }

        function updateActiveBtn(key) {
            var map = { small: 'fontSmall', normal: 'fontNormal', large: 'fontLarge' };
            Object.keys(map).forEach(function(k) {
                var el = document.getElementById(map[k]);
                if (el) el.classList.toggle('sh-size-active', k === key);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateActiveBtn(saved);
            var s = document.getElementById('fontSmall');
            var n = document.getElementById('fontNormal');
            var l = document.getElementById('fontLarge');
            if (s) s.addEventListener('click', function() { setSize('small'); });
            if (n) n.addEventListener('click', function() { setSize('normal'); });
            if (l) l.addEventListener('click', function() { setSize('large'); });
        });
    })();
    </script>

    {{-- Sprint 8 #43: High Contrast Mode --}}
    <script>
    (function() {
        if (localStorage.getItem('sh-contrast') === 'high' || localStorage.getItem('sh-high-contrast') === 'true') {
            document.documentElement.setAttribute('data-contrast', 'high');
            document.documentElement.classList.add('high-contrast');
        }
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('contrastToggle');
            var icon = document.getElementById('contrastIcon');
            if (!btn) return;
            function updateIcon() {
                var isHigh = document.documentElement.getAttribute('data-contrast') === 'high';
                if (icon) icon.className = isHigh ? 'ti ti-contrast-2' : 'ti ti-contrast';
                btn.title = isHigh ? 'Mode Kontras Normal' : 'Mode Kontras Tinggi';
                var lbl = document.getElementById('contrastLabel');
                if (lbl) lbl.textContent = isHigh ? 'Kontras Normal' : 'Kontras Tinggi';
                btn.classList.toggle('sh-size-active', isHigh);
            }
            updateIcon();
            btn.addEventListener('click', function() {
                var isHigh = document.documentElement.getAttribute('data-contrast') === 'high';
                if (isHigh) {
                    document.documentElement.removeAttribute('data-contrast');
                    document.documentElement.classList.remove('high-contrast');
                    localStorage.removeItem('sh-contrast');
                    localStorage.removeItem('sh-high-contrast');
                } else {
                    document.documentElement.setAttribute('data-contrast', 'high');
                    document.documentElement.classList.add('high-contrast');
                    localStorage.setItem('sh-contrast', 'high');
                    localStorage.setItem('sh-high-contrast', 'true');
                }
                updateIcon();
            });
        });
    })();
    </script>

    {{-- Sprint 8 #44: Keyboard Shortcuts --}}
    <script>
    document.addEventListener('keydown', function(e) {
        // Skip if in input/textarea/select
        if (['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName)) return;
        if (e.ctrlKey || e.altKey || e.metaKey) return;

        if (e.key === '?') {
            e.preventDefault();
            var modal = document.getElementById('shKeyboardModal');
            if (modal) {
                var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                bsModal.toggle();
            }
        }
        if (e.key === 'n') {
            e.preventDefault();
            window.location.href = '/leave/select-type';
        }
        if (e.key === 'k') {
            e.preventDefault();
            window.location.href = '/kalender';
        }
        if (e.key === 'd') {
            e.preventDefault();
            window.location.href = '/dashboard';
        }
        if (e.key === 'Escape') {
            // Close all open Bootstrap modals
            document.querySelectorAll('.modal.show').forEach(function(el) {
                var m = bootstrap.Modal.getInstance(el);
                if (m) m.hide();
            });
        }
    });
    </script>

    {{-- Sprint 6 #34: Favicon Badge Counter --}}
    <script>
    @auth
    (function() {
        var unread = {{ \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count() }};
        if (unread <= 0) return;
        var link = document.querySelector("link[rel='icon']");
        if (!link) return;
        var img = new Image();
        img.onload = function() {
            var canvas = document.createElement('canvas');
            canvas.width = 32; canvas.height = 32;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, 32, 32);
            ctx.fillStyle = '#dc2626';
            ctx.beginPath();
            ctx.arc(26, 6, 8, 0, Math.PI * 2);
            ctx.fill();
            ctx.fillStyle = '#fff';
            ctx.font = 'bold 8px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(unread > 9 ? '9+' : String(unread), 26, 6);
            link.href = canvas.toDataURL();
        };
        img.src = link.href;
    })();
    @endauth
    </script>

    {{-- Sprint 6 #33: Browser Push Notification --}}
    <script>
    @auth
    document.addEventListener('DOMContentLoaded', function() {
        if (!('Notification' in window) || !('serviceWorker' in navigator)) return;
        var asked = localStorage.getItem('sh-push-asked');
        if (asked) return;
        // Ask for permission after 3s on first login
        setTimeout(function() {
            Notification.requestPermission().then(function(permission) {
                localStorage.setItem('sh-push-asked', '1');
                if (permission === 'granted') {
                    var lastCount = {{ \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count() }};
                    // Poll every 60 seconds for new notifications
                    setInterval(function() {
                        fetch('/notifications', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                        .then(function(r) { return r.json(); })
                        .catch(function() { return null; })
                        .then(function(data) {
                            if (!data || typeof data.unread_count === 'undefined') return;
                            if (data.unread_count > lastCount) {
                                new Notification('SiHEALING', {
                                    body: 'Ada ' + (data.unread_count - lastCount) + ' notifikasi baru.',
                                    icon: '/images/favicon-pn.png'
                                });
                            }
                            lastCount = data.unread_count;
                        });
                    }, 60000);
                }
            });
        }, 3000);
    });
    @endauth
    </script>

    {{-- Sprint 6 #36: Sound Notification Toggle --}}
    <script>
    window.shPlayNotifSound = function() {
        if (localStorage.getItem('sh-sound-notif') === 'off') return;
        // Short beep using Web Audio API
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.value = 880; osc.type = 'sine';
            gain.gain.setValueAtTime(0.1, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.2);
        } catch(e) {}
    };
    </script>

    {{-- Sprint 9 #49: Onboarding Tour (lazy-load Intro.js) --}}
    <script>
    @auth
    @if(!Auth::user()->isAdmin())
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('sh_onboarding_done')) return;
        // Lazy-load Intro.js only if needed
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/intro.js@7.2.0/minified/introjs.min.css';
        document.head.appendChild(link);
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/intro.js@7.2.0/minified/intro.min.js';
        script.onload = function() {
            if (typeof introJs === 'undefined') return;
            setTimeout(function() {
                introJs().setOptions({
                    steps: [
                        { title: 'Selamat Datang! 👋', intro: 'Ini adalah SiHEALING — Sistem Informasi Cuti Pengadilan Negeri Natuna. Mari kita kenalkan fitur-fiturnya.' },
                        { element: '.sh-navbar', title: 'Navigasi', intro: 'Gunakan menu di sini untuk berpindah halaman. Di mobile, ada navigasi bawah yang praktis.' },
                        { element: '#main-content', title: 'Area Konten', intro: 'Di sini ditampilkan informasi dan fitur utama sesuai halaman yang sedang dibuka.' },
                        { element: '.sh-qa-toggle', title: 'Aksi Cepat', intro: 'Tombol ini membuka panel aksi cepat — ajukan cuti, lihat kalender, dll.' },
                        { element: 'body', title: 'Selesai!', intro: 'Anda siap menggunakan SiHEALING! Tekan ? kapan saja untuk melihat pintasan keyboard.' }
                    ],
                    nextLabel: 'Lanjut →',
                    prevLabel: '← Kembali',
                    doneLabel: 'Mulai!',
                    showBullets: true,
                    exitOnEsc: true,
                    exitOnOverlayClick: false,
                    disableInteraction: true,
                }).oncomplete(function() {
                    localStorage.setItem('sh_onboarding_done', '1');
                }).onexit(function() {
                    localStorage.setItem('sh_onboarding_done', '1');
                }).start();
            }, 800);
        };
        document.body.appendChild(script);
    });
    @endif
    @endauth
    </script>

    {{-- Sprint 9 #48: Export Progress Overlay --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.sh-export-btn, a[href*="export"]').forEach(function(btn) {
            if (btn.classList.contains('sh-no-overlay')) return;
            btn.addEventListener('click', function(e) {
                if (btn.tagName === 'A' && !btn.href.includes('export')) return;
                var overlay = document.createElement('div');
                overlay.className = 'sh-export-overlay';
                overlay.innerHTML = '<div class="sh-export-overlay-inner">' +
                    '<div class="spinner-border text-success mb-3" style="width:2.5rem;height:2.5rem;"></div>' +
                    '<div class="fw-bold">Menyiapkan file...</div>' +
                    '<div class="text-muted" style="font-size:0.82rem;">Mohon tunggu sebentar</div>' +
                    '</div>';
                document.body.appendChild(overlay);
                setTimeout(function() { if (overlay.parentNode) overlay.remove(); }, 5000);
            });
        });
    });
    </script>

    {{-- T16-T17: Double-submit prevention & Session timeout warning --}}
    <script>
    // T16: Global double-submit prevention
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (form.dataset.submitting) { e.preventDefault(); return; }
        form.dataset.submitting = '1';
        var btn = form.querySelector('[type="submit"]');
        if (btn && !btn.id.includes('sh-confirm')) {
            btn.disabled = true;
            var orig = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...';
            setTimeout(function() { btn.disabled = false; btn.innerHTML = orig; delete form.dataset.submitting; }, 10000);
        }
    }, true);

    // T17: Session timeout warning (15 min idle)
    @auth
    (function() {
        var WARNING_MS  = 15 * 60 * 1000;
        var COUNTDOWN_S = 5 * 60;
        var timer, interval, modalInstance;

        function showWarning() {
            var el = document.getElementById('sh-session-modal');
            if (!el) return;
            modalInstance = new bootstrap.Modal(el, { backdrop: 'static', keyboard: false });
            modalInstance.show();
            var secs = COUNTDOWN_S;
            interval = setInterval(function() {
                secs--;
                var cd = document.getElementById('sh-countdown');
                if (cd) cd.textContent = Math.floor(secs/60).toString().padStart(2,'0') + ':' + (secs%60).toString().padStart(2,'0');
                if (secs <= 0) { clearInterval(interval); window.location.href = '{{ route("login") }}'; }
            }, 1000);
        }

        function resetTimer() {
            clearTimeout(timer);
            clearInterval(interval);
            if (modalInstance) { try { modalInstance.hide(); } catch(e) {} modalInstance = null; }
            timer = setTimeout(showWarning, WARNING_MS);
        }

        ['click','keydown','touchstart','mousemove'].forEach(function(ev) {
            document.addEventListener(ev, resetTimer, { passive: true });
        });
        resetTimer();
    })();
    @endauth

    // T20: Pull-to-refresh (mobile only)
    (function() {
        if (window.innerWidth > 768) return;
        var startY = 0, pulling = false;
        var bar = document.createElement('div');
        bar.style.cssText = 'position:fixed;top:0;left:0;right:0;height:4px;background:#166534;transform:scaleX(0);transform-origin:left;transition:transform 0.15s;z-index:9999;pointer-events:none;';
        document.body.appendChild(bar);
        document.addEventListener('touchstart', function(e) {
            if (window.scrollY === 0) { startY = e.touches[0].clientY; pulling = true; }
        }, { passive: true });
        document.addEventListener('touchmove', function(e) {
            if (!pulling) return;
            var dist = Math.min((e.touches[0].clientY - startY) / 80, 1);
            if (dist > 0) bar.style.transform = 'scaleX(' + dist + ')';
        }, { passive: true });
        document.addEventListener('touchend', function(e) {
            if (!pulling) return;
            pulling = false;
            var dist = (e.changedTouches[0].clientY - startY) / 80;
            if (dist >= 1) { bar.style.transform = 'scaleX(1)'; setTimeout(function() { window.location.reload(); }, 200); }
            else { bar.style.transform = 'scaleX(0)'; }
        });
    })();
    </script>

    @stack('scripts')

    <script>
    // T22: Page transition
    document.addEventListener('DOMContentLoaded', function() {
        var main = document.querySelector('.sh-main-content') || document.querySelector('main') || document.querySelector('.container-xl');
        if (main) main.classList.add('sh-page-transition');
    });
    </script>

    @auth
    {{-- T17: Session Timeout Warning --}}
    <div class="modal fade" id="sh-session-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-body text-center p-4">
                    <i class="ti ti-clock-exclamation mb-3" style="font-size:3rem; color:#d97706; display:block;"></i>
                    <h5 class="fw-bold mb-2">Sesi Hampir Habis</h5>
                    <p class="text-muted mb-3" style="font-size:0.88rem;">
                        Sesi Anda akan berakhir dalam <strong id="sh-countdown">5:00</strong>.<br>Perpanjang sesi?
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button onclick="window.location.reload()" class="btn btn-primary btn-sm" style="border-radius:8px;">
                            <i class="ti ti-refresh me-1"></i> Perpanjang
                        </button>
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('sh-logout-form').submit();"
                           class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="sh-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
    @endauth

    <script>
    // PWA: Register service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js').catch(function() {});
        });
    }
    </script>

    <script>
    // Bottom Sheet API
    window.SHBottomSheet = {
        _backdrop: null,
        _sheet: null,
        open: function(id) {
            var sheet = document.getElementById(id);
            if (!sheet) return;
            if (!this._backdrop) {
                this._backdrop = document.createElement('div');
                this._backdrop.className = 'sh-bottom-sheet-backdrop';
                this._backdrop.addEventListener('click', function() { SHBottomSheet.close(); });
                document.body.appendChild(this._backdrop);
            }
            this._sheet = sheet;
            this._backdrop.style.display = 'block';
            requestAnimationFrame(function() {
                SHBottomSheet._backdrop.classList.add('active');
                sheet.classList.add('active');
            });
            document.body.style.overflow = 'hidden';
        },
        close: function() {
            if (this._sheet) this._sheet.classList.remove('active');
            if (this._backdrop) {
                this._backdrop.classList.remove('active');
                setTimeout(function() {
                    if (SHBottomSheet._backdrop) SHBottomSheet._backdrop.style.display = 'none';
                }, 300);
            }
            document.body.style.overflow = '';
        }
    };
    // Close on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') SHBottomSheet.close();
    });
    </script>
    <script>
    // Long press preview (mobile)
    (function() {
        var preview = document.createElement('div');
        preview.className = 'sh-longpress-preview';
        preview.innerHTML = '<div class="sh-longpress-preview-title" id="sh-lp-title"></div><div id="sh-lp-body"></div>';
        document.body.appendChild(preview);

        var timer, activeEl;
        document.addEventListener('touchstart', function(e) {
            var card = e.target.closest('[data-preview]');
            if (!card) return;
            activeEl = card;
            timer = setTimeout(function() {
                try {
                    var data = JSON.parse(card.dataset.preview);
                    document.getElementById('sh-lp-title').textContent = data.title || 'Detail';
                    var body = document.getElementById('sh-lp-body');
                    body.innerHTML = Object.entries(data).filter(function(kv) { return kv[0] !== 'title'; }).map(function(kv) {
                        return '<div class="sh-longpress-preview-row"><span>' + kv[0] + '</span><strong>' + kv[1] + '</strong></div>';
                    }).join('');
                    var rect = card.getBoundingClientRect();
                    var top = Math.max(8, rect.top - 8);
                    var left = Math.min(window.innerWidth - 296, rect.left);
                    preview.style.top = top + 'px';
                    preview.style.left = left + 'px';
                    preview.classList.add('visible');
                } catch(e) {}
            }, 500);
        }, { passive: true });

        document.addEventListener('touchend', function() {
            clearTimeout(timer);
            preview.classList.remove('visible');
        }, { passive: true });
        document.addEventListener('touchmove', function() {
            clearTimeout(timer);
            preview.classList.remove('visible');
        }, { passive: true });
    })();
    </script>

    {{-- Floating back button (mobile, detail pages only) --}}
    @if(request()->is('leave/*') || request()->is('pegawai/*') || request()->is('profile*'))
    <a href="javascript:history.back()" class="sh-float-back" aria-label="Kembali">
        <i class="ti ti-arrow-left"></i>
    </a>
    @endif
<!-- Lottie success overlay -->
<div id="sh-success-overlay" class="sh-success-overlay">
    <lottie-player
        src="https://assets9.lottiefiles.com/packages/lf20_jbrw3hcz.json"
        background="transparent"
        speed="1.2"
        style="width: 180px; height: 180px;"
        autoplay
        id="sh-lottie-player">
    </lottie-player>
    <p>Pengajuan Berhasil!</p>
</div>
<script>
// Show Lottie success animation on successful leave submission
@if(session('success') && str_contains(session('success'), 'berhasil'))
(function() {
    var overlay = document.getElementById('sh-success-overlay');
    if (!overlay) return;
    overlay.classList.add('visible');
    setTimeout(function() {
        overlay.style.transition = 'opacity 0.5s';
        overlay.style.opacity = '0';
        setTimeout(function() {
            overlay.classList.remove('visible');
            overlay.style.opacity = '';
            overlay.style.transition = '';
        }, 500);
    }, 2500);
})();
@endif
</script>
</body>
</html>
