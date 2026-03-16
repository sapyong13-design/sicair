@extends('layouts.app')

@section('title', '404 — Halaman Tidak Ditemukan')

@section('content')
<div class="text-center py-5">
    <div style="font-size: 6rem; font-weight: 900; color: var(--sc-primary, #166534); opacity: 0.15; line-height: 1;">404</div>
    <div style="margin-top: -1rem;">
        <i class="ti ti-file-search" style="font-size: 4rem; color: var(--sc-primary, #166534); opacity: 0.5;"></i>
    </div>
    <h2 class="fw-bold mt-3 mb-2">Halaman Tidak Ditemukan</h2>
    <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto 1.5rem;">
        Halaman yang Anda cari tidak ada atau sudah dipindahkan. Pastikan URL yang Anda masukkan sudah benar.
    </p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary" style="border-radius: 12px; padding: 0.6rem 1.5rem;">
        <i class="ti ti-home me-1"></i> Kembali ke Dashboard
    </a>
</div>
@endsection
