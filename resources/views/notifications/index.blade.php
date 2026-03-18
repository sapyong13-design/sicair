@extends('layouts.app')

@section('title', 'Notifikasi — SiCAIR')

@section('content')
{{-- Breadcrumb (#10) --}}
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Notifikasi</span>
</nav>

<div class="sc-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sc-page-title mb-1">
                <i class="ti ti-bell me-1" style="color: var(--sc-primary);" aria-hidden="true"></i>
                Notifikasi
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                {{ $notifications->total() }} notifikasi
            </div>
        </div>
        @if($notifications->where('is_read', false)->count() > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary" style="border-radius: 10px;">
                <i class="ti ti-checks me-1"></i> Tandai Semua Dibaca
            </button>
        </form>
        @endif
    </div>
</div>

<div class="card sc-card">
    @if($notifications->isEmpty())
    <div class="card-body py-5">
        <div class="text-center">
            {{-- #46 Empty state SVG: no-notifications --}}
            <svg width="96" height="96" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg" class="mb-3" aria-hidden="true">
                <circle cx="48" cy="48" r="44" fill="var(--sc-primary-light)"/>
                <path d="M48 22C38.06 22 30 30.06 30 40V56L24 62V65H72V62L66 56V40C66 30.06 57.94 22 48 22Z" fill="var(--sc-primary)" opacity="0.25"/>
                <path d="M48 26C39.16 26 32 33.16 32 42V56L26 62H70L64 56V42C64 33.16 56.84 26 48 26Z" stroke="var(--sc-primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                <path d="M44 66C44 68.21 45.79 70 48 70C50.21 70 52 68.21 52 66H44Z" fill="var(--sc-primary)" opacity="0.6"/>
                <path d="M38 28L58 68" stroke="var(--sc-danger)" stroke-width="2.5" stroke-linecap="round" opacity="0.7"/>
            </svg>
            <h4 class="fw-bold text-dark mb-1">Belum Ada Notifikasi</h4>
            <p class="text-muted mb-0">Anda belum memiliki notifikasi apapun.</p>
        </div>
    </div>
    @else
    @php
        $today     = \Carbon\Carbon::today();
        $yesterday = \Carbon\Carbon::yesterday();
        $grouped   = [];
        foreach ($notifications as $notif) {
            $date = $notif->created_at->startOfDay();
            if ($date->isSameDay($today)) {
                $key = 'Hari ini';
            } elseif ($date->isSameDay($yesterday)) {
                $key = 'Kemarin';
            } else {
                $key = $notif->created_at->diffForHumans(null, true) . ' lalu';
            }
            $grouped[$key][] = $notif;
        }
    @endphp
    <div class="list-group list-group-flush">
        @foreach($grouped as $dateLabel => $groupNotifs)
        {{-- Date separator --}}
        <div class="sc-notif-date-sep px-4 py-2 d-flex align-items-center gap-2">
            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--sc-text-muted);">
                {{ $dateLabel }}
            </span>
            <div style="flex: 1; height: 1px; background: var(--sc-gray-100);"></div>
        </div>
        @foreach($groupNotifs as $notif)
        <div class="list-group-item px-4 py-3 {{ !$notif->is_read ? 'sc-notif-unread' : '' }}" style="border-left: 4px solid {{ match($notif->type) {
            'cuti_disetujui' => 'var(--sc-success)',
            'cuti_ditolak' => 'var(--sc-danger)',
            'cuti_diajukan', 'cuti_pertimbangan' => 'var(--sc-warning)',
            default => 'var(--sc-primary)',
        } }};">
            <div class="d-flex align-items-start gap-3">
                @php
                    $iconMap = [
                        'cuti_disetujui' => ['ti-circle-check', 'var(--sc-success)', 'var(--sc-success-light)'],
                        'cuti_ditolak' => ['ti-circle-x', 'var(--sc-danger)', 'var(--sc-danger-light)'],
                        'cuti_diajukan' => ['ti-file-plus', 'var(--sc-warning)', 'var(--sc-warning-light)'],
                        'cuti_pertimbangan' => ['ti-checklist', 'var(--sc-warning)', 'var(--sc-warning-light)'],
                        'info' => ['ti-info-circle', 'var(--sc-primary)', 'var(--sc-primary-light)'],
                    ];
                    $icon = $iconMap[$notif->type] ?? $iconMap['info'];
                @endphp
                <div style="width: 42px; height: 42px; border-radius: 12px; background: {{ $icon[2] }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="ti {{ $icon[0] }}" style="font-size: 1.2rem; color: {{ $icon[1] }};"></i>
                </div>
                <div class="flex-fill">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;">{{ $notif->title }}</div>
                            <div class="text-muted" style="font-size: 0.82rem;">{{ $notif->message }}</div>
                        </div>
                        @if(!$notif->is_read)
                        <span class="badge rounded-pill" style="background: var(--sc-primary); font-size: 0.65rem;">Baru</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <span class="text-muted" style="font-size: 0.75rem;">
                            <i class="ti ti-clock me-1"></i>{{ $notif->created_at->diffForHumans() }}
                        </span>
                        @if($notif->link && !$notif->is_read)
                        <form method="POST" action="{{ route('notifications.read', $notif) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; font-size: 0.75rem; padding: 0.2rem 0.6rem;">
                                <i class="ti ti-eye me-1"></i> Lihat
                            </button>
                        </form>
                        @elseif($notif->link)
                        <a href="{{ $notif->link }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; font-size: 0.75rem; padding: 0.2rem 0.6rem;">
                            <i class="ti ti-eye me-1"></i> Lihat
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @endforeach
    </div>

    @if($notifications->hasPages())
    <div class="card-footer" style="background: #fff; border-top: 2px solid var(--sc-gray-100); padding: 0.75rem 1.25rem;">
        {{ $notifications->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
