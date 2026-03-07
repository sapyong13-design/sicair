@extends('layouts.app')

@section('title', '403 — Akses Ditolak')

@section('content')
<div class="text-center py-5">
    <div style="font-size: 6rem; font-weight: 900; color: #dc2626; opacity: 0.15; line-height: 1;">403</div>
    <div style="margin-top: -1rem;">
        <i class="ti ti-lock" style="font-size: 4rem; color: #dc2626; opacity: 0.5;"></i>
    </div>
    <h2 class="fw-bold mt-3 mb-2">Akses Ditolak</h2>
    <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto 1.5rem;">
        Anda tidak memiliki izin untuk mengakses halaman ini. Hubungi administrator jika ini adalah kesalahan.
    </p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary" style="border-radius: 12px; padding: 0.6rem 1.5rem;">
        <i class="ti ti-home me-1"></i> Kembali ke Dashboard
    </a>
</div>
@endsection
