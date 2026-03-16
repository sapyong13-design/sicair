@extends('layouts.app')

@section('title', 'Detail Audit Log - SiCAIR')

@section('content')
<div class="sc-page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="sc-page-title mb-0">
            <i class="ti ti-file-text me-2" style="color: var(--sc-primary);"></i> Detail Audit Log
        </h2>
        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Log Header --}}
    <div class="col-12">
        <div class="card sc-card">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-3">
                        <div>
                            <small class="text-muted d-block">Waktu</small>
                            <strong>{{ $auditLog->created_at->format('d M Y H:i:s') }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div>
                            <small class="text-muted d-block">Pengguna</small>
                            <strong>{{ $auditLog->user?->name ?? 'System' }}</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div>
                            <small class="text-muted d-block">Model</small>
                            <strong>{{ $auditLog->model }} (ID: {{ $auditLog->model_id }})</strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div>
                            <small class="text-muted d-block">Aksi</small>
                            @switch($auditLog->action)
                            @case('create')
                                <span class="sc-badge sc-badge-approved">{{ $auditLog->action_label }}</span>
                            @break
                            @case('update')
                                <span class="sc-badge" style="background: #fef08a; color: #92400e;">{{ $auditLog->action_label }}</span>
                            @break
                            @case('delete')
                                <span class="sc-badge sc-badge-rejected">{{ $auditLog->action_label }}</span>
                            @break
                            @endswitch
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Changes Details --}}
    @if($auditLog->old_values || $auditLog->new_values)
    <div class="col-12">
        <div class="card sc-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-list me-2"></i> Detail Perubahan
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size: 0.9rem;">
                        <thead style="background: var(--sc-gray-100);">
                            <tr>
                                <th style="padding: 0.75rem;">Field</th>
                                <th style="padding: 0.75rem;">Nilai Lama</th>
                                <th style="padding: 0.75rem;">Nilai Baru</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLog->old_values ?? [] as $key => $oldValue)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.75rem;"><strong>{{ ucwords(str_replace('_', ' ', $key)) }}</strong></td>
                                <td style="padding: 0.75rem;">
                                    <code style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        {{ is_array($oldValue) ? json_encode($oldValue) : $oldValue }}
                                    </code>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <code style="background: #dcfce7; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        {{ is_array($auditLog->new_values[$key] ?? null) ? json_encode($auditLog->new_values[$key]) : $auditLog->new_values[$key] ?? '-' }}
                                    </code>
                                </td>
                            </tr>
                            @endforeach

                            @foreach($auditLog->new_values ?? [] as $key => $newValue)
                            @if(!isset($auditLog->old_values[$key]))
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 0.75rem;"><strong>{{ ucwords(str_replace('_', ' ', $key)) }}</strong></td>
                                <td style="padding: 0.75rem;">-</td>
                                <td style="padding: 0.75rem;">
                                    <code style="background: #dcfce7; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                                    </code>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Metadata --}}
    <div class="col-12">
        <div class="card sc-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="ti ti-info-circle me-2"></i> Metadata
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div>
                            <small class="text-muted d-block mb-1">IP Address</small>
                            @if(!$auditLog->ip_address || $auditLog->ip_address === '127.0.0.1')
                                <span class="badge" style="background:#f1f5f9;color:#64748b;font-size:0.8rem;border-radius:6px;">Lokal</span>
                                @if($auditLog->ip_address)
                                <code style="font-size:0.82rem;margin-left:6px;">{{ $auditLog->ip_address }}</code>
                                @endif
                            @else
                                <code>{{ $auditLog->ip_address }}</code>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div>
                            <small class="text-muted d-block mb-1">User Agent</small>
                            @if($auditLog->user_agent)
                                <code style="font-size:0.78rem;word-break:break-all;">{{ $auditLog->user_agent }}</code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
