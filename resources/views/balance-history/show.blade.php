@extends('layouts.app')

@section('title', 'Riwayat Saldo Cuti — ' . $user->name)

@section('content')
<div class="sh-page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="sh-page-title mb-1">
                <i class="ti ti-history me-1" style="color: var(--sh-primary);"></i>
                Riwayat Saldo Cuti
            </h2>
            <div class="text-muted" style="font-size: 0.85rem;">
                {{ $user->name }} &bull; NIP: {{ $user->nip }}
            </div>
        </div>
        <a href="{{ route('pegawai.show', $user) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke Profil
        </a>
    </div>
</div>

{{-- Summary card --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card sh-stat-card stat-success">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="sh-stat-label mb-1">Saldo Saat Ini</div>
                        <div class="sh-stat-number" style="color: var(--sh-success);">{{ $currentBalance }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">hari tersisa</div>
                    </div>
                    <div class="sh-stat-icon icon-success"><i class="ti ti-calendar-stats"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card sh-stat-card stat-warning">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="sh-stat-label mb-1">Total Perubahan</div>
                        <div class="sh-stat-number" style="color: var(--sh-warning);">{{ $history->count() }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">entri tercatat</div>
                    </div>
                    <div class="sh-stat-icon icon-warning"><i class="ti ti-history"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        @php
            $totalDeducted = $history->filter(fn($l) => ($l->new_values['balance'] ?? 0) < ($l->old_values['balance'] ?? 0))
                ->sum(fn($l) => ($l->old_values['balance'] ?? 0) - ($l->new_values['balance'] ?? 0));
        @endphp
        <div class="card sh-stat-card stat-danger">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="sh-stat-label mb-1">Total Terpakai</div>
                        <div class="sh-stat-number" style="color: var(--sh-danger);">{{ $totalDeducted }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">hari dikurangi</div>
                    </div>
                    <div class="sh-stat-icon icon-danger"><i class="ti ti-calendar-minus"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- History table --}}
<div class="card sh-card animate-in">
    <div class="card-header">
        <h3 class="card-title mb-0">
            <i class="ti ti-list me-2" style="color: var(--sh-primary);"></i>
            50 Perubahan Terakhir
        </h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th style="width: 130px;">Tanggal</th>
                    <th style="width: 160px;">Tindakan</th>
                    <th style="width: 100px;">Saldo Lama</th>
                    <th style="width: 100px;">Saldo Baru</th>
                    <th style="width: 80px;" class="text-center">Perubahan</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $log)
                @php
                    $old = $log->old_values['balance'] ?? null;
                    $new = $log->new_values['balance'] ?? null;
                    $diff = ($new !== null && $old !== null) ? $new - $old : null;
                @endphp
                <tr>
                    <td class="text-nowrap">
                        <div style="font-size: 0.82rem; font-weight: 600;">{{ $log->created_at->format('d M Y') }}</div>
                        <div class="text-muted" style="font-size: 0.72rem;">{{ $log->created_at->format('H:i') }}</div>
                    </td>
                    <td>
                        <span class="sh-badge sh-badge-{{ $diff > 0 ? 'approved' : ($diff < 0 ? 'rejected' : 'pending') }}"
                              style="font-size: 0.72rem;">
                            {{ $log->action_label }}
                        </span>
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $old ?? '-' }}</span>
                        @if($old !== null)<span class="text-muted"> hari</span>@endif
                    </td>
                    <td>
                        <span class="fw-bold {{ $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : '') }}">
                            {{ $new ?? '-' }}
                        </span>
                        @if($new !== null)<span class="text-muted"> hari</span>@endif
                    </td>
                    <td class="text-center">
                        @if($diff !== null)
                            <span class="fw-bold {{ $diff > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">{{ $log->description }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-5">
                        <div class="text-center">
                            <div class="sh-empty-icon mb-3"><i class="ti ti-history"></i></div>
                            <div class="fw-semibold mb-1" style="color: var(--sh-text);">Belum ada riwayat perubahan saldo</div>
                            <div class="text-muted" style="font-size: 0.85rem;">Riwayat akan muncul setelah ada penyesuaian saldo</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
