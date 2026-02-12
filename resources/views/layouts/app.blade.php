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
        .navbar-brand-image { height: 2rem; }
        .status-badge-pending { background: #f59f00; color: #fff; }
        .status-badge-approved { background: #2fb344; color: #fff; }
        .status-badge-rejected { background: #d63939; color: #fff; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="navbar navbar-expand-md navbar-light d-print-none" style="background-color: #1a56db;">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="/dashboard" class="text-white text-decoration-none fw-bold">
                    <i class="ti ti-calendar-event me-1"></i> SiHEALING
                </a>
            </h1>
            <div class="navbar-nav flex-row order-md-last">
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0 text-white" data-bs-toggle="dropdown">
                        <span class="avatar avatar-sm bg-blue-lt">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </span>
                        <div class="d-none d-xl-block ps-2">
                            <div class="text-white">{{ Auth::user()->name }}</div>
                            <div class="mt-1 small" style="color: rgba(255,255,255,0.7);">
                                {{ ucfirst(Auth::user()->role) }} &middot; {{ Auth::user()->nip }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
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
                            <a class="nav-link text-white" href="/dashboard">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-home"></i>
                                </span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>
                        @if(!Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('leave.create') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-file-plus"></i>
                                </span>
                                <span class="nav-link-title">Ajukan Cuti</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="page-wrapper flex-fill">
        <div class="page-body">
            <div class="container-xl">
                {{-- Flash messages --}}
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <footer class="footer footer-transparent d-print-none mt-auto">
        <div class="container-xl">
            <div class="row text-center align-items-center">
                <div class="col-12">
                    <span class="text-muted">
                        &copy; {{ date('Y') }} SiHEALING &mdash; Sistem Informasi Hak Cuti & Administrasi Libur Pegawai
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Tabler JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
    @stack('scripts')
</body>
</html>
