@extends('layouts.app')

@section('title', 'Audit Log - SiHEALING')

@section('content')
<div class="sh-page-header mb-4">
    <h2 class="sh-page-title mb-0">
        <i class="ti ti-file-text me-2" style="color: var(--sh-primary);"></i> Audit Log
    </h2>
</div>

<div class="card sh-card">
    <div class="card-body p-4">
        {{-- Filters --}}
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Model</label>
                <select name="model" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0;">
                    <option value="">-- Semua Model --</option>
                    <option value="LeaveRequest" {{ request('model') === 'LeaveRequest' ? 'selected' : '' }}>Pengajuan Cuti</option>
                    <option value="User" {{ request('model') === 'User' ? 'selected' : '' }}>Pengguna</option>
                    <option value="Holiday" {{ request('model') === 'Holiday' ? 'selected' : '' }}>Hari Libur</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Aksi</label>
                <select name="action" class="form-select" style="border-radius: 10px; border: 2px solid #e2e8f0;">
                    <option value="">-- Semua --</option>
                    <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>Dibuat</option>
                    <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Diubah</option>
                    <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>Dihapus</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" style="border-radius: 10px; border: 2px solid #e2e8f0;">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" style="border-radius: 10px; border: 2px solid #e2e8f0;">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn sh-btn-primary flex-grow-1">
                    <i class="ti ti-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary flex-grow-1">
                    <i class="ti ti-x me-1"></i> Reset
                </a>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table mb-0" style="font-size: 0.9rem;">
                <thead style="background: var(--sh-gray-100);">
                    <tr>
                        <th style="padding: 1rem;">Waktu</th>
                        <th style="padding: 1rem;">Pengguna</th>
                        <th style="padding: 1rem;">Model</th>
                        <th style="padding: 1rem;">Aksi</th>
                        <th style="padding: 1rem;">IP Address</th>
                        <th style="padding: 1rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 1rem;">
                            <small class="text-muted">{{ $log->created_at->format('d M Y H:i:s') }}</small>
                        </td>
                        <td style="padding: 1rem;">
                            <strong>{{ $log->user?->name ?? 'System' }}</strong>
                        </td>
                        <td style="padding: 1rem;">
                            <span class="badge" style="background: var(--sh-gray-200); color: #475569;">{{ $log->model }}</span>
                        </td>
                        <td style="padding: 1rem;">
                            @switch($log->action)
                            @case('create')
                                <span class="sh-badge sh-badge-approved">{{ $log->action_label }}</span>
                            @break
                            @case('update')
                                <span class="sh-badge" style="background: #fef08a; color: #92400e;">{{ $log->action_label }}</span>
                            @break
                            @case('delete')
                                <span class="sh-badge sh-badge-rejected">{{ $log->action_label }}</span>
                            @break
                            @endswitch
                        </td>
                        <td style="padding: 1rem;">
                            <small class="text-muted">{{ $log->ip_address }}</small>
                        </td>
                        <td style="padding: 1rem;">
                            <a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 6px;">
                                <i class="ti ti-eye"></i> Lihat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Tidak ada audit log</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
