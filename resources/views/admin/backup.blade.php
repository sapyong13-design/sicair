@extends('layouts.app')
@section('title', 'Manajemen Backup - SiHEALING')

@section('content')
<div class="sh-page-header mb-4">
    <div>
        <h2 class="sh-page-title mb-1">
            <i class="ti ti-database-export me-2" style="color:var(--sh-primary);"></i>Manajemen Backup
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size:0.8rem;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.settings') }}">Pengaturan</a></li>
                <li class="breadcrumb-item active">Backup</li>
            </ol>
        </nav>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Manual Backup Card --}}
<div class="card sh-card mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-1"><i class="ti ti-shield-lock me-2 text-primary"></i>Backup Manual</h5>
        <p class="text-muted mb-3" style="font-size:0.875rem;">
            Backup otomatis berjalan setiap hari pukul 02:00 WIB. Menyimpan 14 backup terakhir.
        </p>
        <form method="POST" action="{{ route('admin.backup.run') }}">
            @csrf
            <button type="submit" class="btn sh-btn-primary"
                    onclick="return confirm('Buat backup database sekarang?')">
                <i class="ti ti-database-export me-1"></i> Buat Backup Sekarang
            </button>
        </form>
    </div>
</div>

{{-- Backup List Card --}}
<div class="card sh-card">
    <div class="card-body">
        <h5 class="fw-bold mb-3">
            <i class="ti ti-files me-2 text-primary"></i>Daftar Backup
            <span class="badge bg-secondary ms-1">{{ count($backups) }}</span>
        </h5>

        @if(empty($backups))
            <div class="text-center py-4 text-muted">
                <i class="ti ti-database-off" style="font-size:2rem;"></i>
                <p class="mt-2">Belum ada backup. Klik tombol di atas untuk membuat backup pertama.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Ukuran</th>
                        <th>Tanggal Dibuat</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $b)
                    <tr>
                        <td>
                            <i class="ti ti-database me-1 text-primary"></i>
                            <code style="font-size:0.8rem;">{{ $b['name'] }}</code>
                        </td>
                        <td>{{ $b['size'] }}</td>
                        <td>{{ $b['date'] }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.backup.download', ['filename' => $b['name']]) }}"
                               class="btn btn-sm btn-outline-success">
                                <i class="ti ti-download me-1"></i> Download
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
