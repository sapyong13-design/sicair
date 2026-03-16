@extends('layouts.app')
@section('title', 'Offline — SiCAIR')
@section('content')
<div class="text-center py-5">
    <i class="ti ti-wifi-off" style="font-size: 4rem; color: var(--sc-primary); opacity: 0.4; display: block; margin-bottom: 1rem;"></i>
    <h2 class="fw-bold mb-2">Tidak Ada Koneksi</h2>
    <p class="text-muted mb-4" style="max-width: 360px; margin: 0 auto 1.5rem;">
        Periksa koneksi internet Anda dan coba lagi.
    </p>
    <button onclick="window.location.reload()" class="btn btn-primary" style="border-radius: 12px;">
        <i class="ti ti-refresh me-1"></i> Coba Lagi
    </button>
</div>
@endsection
