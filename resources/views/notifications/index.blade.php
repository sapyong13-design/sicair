@extends('layouts.app')

@section('title', 'Notifikasi - SiHEALING')

@section('content')
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sh-page-title mb-1">
                <i class="ti ti-bell me-1" style="color: var(--sh-primary);"></i>
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

<div class="card sh-card">
    @if($notifications->isEmpty())
    <div class="card-body py-5">
        <div class="text-center">
            <div class="sh-empty-icon"><i class="ti ti-bell-off"></i></div>
            <h4 class="fw-bold text-dark mb-1">Belum Ada Notifikasi</h4>
            <p class="text-muted mb-0">Anda belum memiliki notifikasi apapun.</p>
        </div>
    </div>
    @else
    <div class="list-group list-group-flush">
        @foreach($notifications as $notif)
        <div class="list-group-item px-4 py-3 {{ !$notif->is_read ? 'sh-notif-unread' : '' }}" style="border-left: 4px solid {{ match($notif->type) {
            'cuti_disetujui' => 'var(--sh-success)',
            'cuti_ditolak' => 'var(--sh-danger)',
            'cuti_diajukan', 'cuti_pertimbangan' => 'var(--sh-warning)',
            default => 'var(--sh-primary)',
        } }};">
            <div class="d-flex align-items-start gap-3">
                @php
                    $iconMap = [
                        'cuti_disetujui' => ['ti-circle-check', 'var(--sh-success)', 'var(--sh-success-light)'],
                        'cuti_ditolak' => ['ti-circle-x', 'var(--sh-danger)', 'var(--sh-danger-light)'],
                        'cuti_diajukan' => ['ti-file-plus', 'var(--sh-warning)', 'var(--sh-warning-light)'],
                        'cuti_pertimbangan' => ['ti-checklist', 'var(--sh-warning)', 'var(--sh-warning-light)'],
                        'info' => ['ti-info-circle', 'var(--sh-primary)', 'var(--sh-primary-light)'],
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
                        <span class="badge rounded-pill" style="background: var(--sh-primary); font-size: 0.65rem;">Baru</span>
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
    </div>

    @if($notifications->hasPages())
    <div class="card-footer" style="background: #fff; border-top: 2px solid var(--sh-gray-100); padding: 0.75rem 1.25rem;">
        {{ $notifications->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
