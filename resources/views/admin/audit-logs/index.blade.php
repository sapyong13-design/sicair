@extends('layouts.app')

@section('title', 'Audit Log — SiCAIR')

@section('content')
<div class="sc-page-header mb-4 d-flex align-items-center justify-content-between">
    <h2 class="sc-page-title mb-0">
        <i class="ti ti-file-text me-2" style="color: var(--sc-primary);"></i> Audit Log
    </h2>
    <small class="text-muted">Riwayat seluruh perubahan sistem</small>
</div>

{{-- Stats Bar --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card sc-card text-center py-3">
            <div style="font-size:1.8rem; font-weight:700; color:var(--sc-primary);">{{ number_format($totalLogs) }}</div>
            <div class="text-muted" style="font-size:0.85rem;">Total Log</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card sc-card text-center py-3">
            <div style="font-size:1.8rem; font-weight:700; color:#16a34a;">{{ number_format($todayLogs) }}</div>
            <div class="text-muted" style="font-size:0.85rem;">Log Hari Ini</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card sc-card text-center py-3">
            <div style="font-size:1.8rem; font-weight:700; color:#7c3aed;">{{ number_format($uniqueModels) }}</div>
            <div class="text-muted" style="font-size:0.85rem;">Jenis Model</div>
        </div>
    </div>
</div>

<div class="card sc-card">
    <div class="card-body p-4">
        {{-- Filters --}}
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-2">
                <label class="form-label fw-semibold">Model</label>
                <select name="model" class="form-select" style="border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">-- Semua --</option>
                    <option value="LeaveRequest" {{ request('model') === 'LeaveRequest' ? 'selected' : '' }}>Pengajuan Cuti</option>
                    <option value="User" {{ request('model') === 'User' ? 'selected' : '' }}>Pengguna</option>
                    <option value="BalanceAdjustment" {{ request('model') === 'BalanceAdjustment' ? 'selected' : '' }}>Penyesuaian Saldo</option>
                    <option value="Amendment" {{ request('model') === 'Amendment' ? 'selected' : '' }}>Perubahan Cuti</option>
                    <option value="Appeal" {{ request('model') === 'Appeal' ? 'selected' : '' }}>Banding</option>
                    <option value="HariLibur" {{ request('model') === 'HariLibur' ? 'selected' : '' }}>Hari Libur</option>
                    <option value="Auth" {{ request('model') === 'Auth' ? 'selected' : '' }}>Login/Logout</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Aksi</label>
                <select name="action" class="form-select" style="border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">-- Semua --</option>
                    <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>Dibuat</option>
                    <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Diubah</option>
                    <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>Dihapus</option>
                    <option value="approve" {{ request('action') === 'approve' ? 'selected' : '' }}>Disetujui</option>
                    <option value="reject" {{ request('action') === 'reject' ? 'selected' : '' }}>Ditolak</option>
                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="approve_appeal" {{ request('action') === 'approve_appeal' ? 'selected' : '' }}>Setujui Banding</option>
                    <option value="approve_amendment" {{ request('action') === 'approve_amendment' ? 'selected' : '' }}>Setujui Perubahan</option>
                    <option value="approve_balance_adjustment" {{ request('action') === 'approve_balance_adjustment' ? 'selected' : '' }}>Setujui Penyesuaian Saldo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Pengguna</label>
                <select name="user_id" class="form-select" style="border-radius:10px;border:2px solid #e2e8f0;">
                    <option value="">-- Semua --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->nip }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Deskripsi</label>
                <input type="text" name="description" class="form-control" placeholder="Cari deskripsi..." value="{{ request('description') }}" style="border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Dari Tanggal</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" style="border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Sampai Tanggal</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" style="border-radius:10px;border:2px solid #e2e8f0;">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn sc-btn-primary">
                    <i class="ti ti-search me-1"></i> Filter
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-x me-1"></i> Reset
                </a>
            </div>
        </form>

        {{-- Result count --}}
        <div class="mb-3 text-muted" style="font-size:0.875rem;">
            Menampilkan {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} dari {{ number_format($logs->total()) }} log
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table mb-0" style="font-size:0.875rem;">
                <thead style="background:var(--sc-gray-100);">
                    <tr>
                        <th style="padding:0.75rem 1rem; white-space:nowrap;">Waktu</th>
                        <th style="padding:0.75rem 1rem;">Pengguna</th>
                        <th style="padding:0.75rem 1rem;">Model</th>
                        <th style="padding:0.75rem 1rem;">Aksi</th>
                        <th style="padding:0.75rem 1rem;">Deskripsi</th>
                        <th style="padding:0.75rem 1rem; white-space:nowrap;">IP Address</th>
                        <th style="padding:0.75rem 1rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:0.75rem 1rem; white-space:nowrap;">
                            <small class="text-muted">{{ $log->created_at->format('d M Y') }}</small><br>
                            <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                        </td>
                        <td style="padding:0.75rem 1rem;">
                            @if($log->user)
                                <div class="fw-semibold">{{ $log->user->name }}</div>
                                <small class="text-muted">{{ $log->user->nip }}</small>
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </td>
                        <td style="padding:0.75rem 1rem;">
                            <span class="badge" style="background:var(--sc-gray-200);color:#475569;font-size:0.78rem;">
                                {{ $log->model }}@if($log->model_id) #{{ $log->model_id }}@endif
                            </span>
                        </td>
                        <td style="padding:0.75rem 1rem;">
                            @php
                                $actionStyles = match(true) {
                                    in_array($log->action, ['create'])                          => ['bg'=>'#dcfce7','color'=>'#166534'],
                                    in_array($log->action, ['update'])                          => ['bg'=>'#fef9c3','color'=>'#92400e'],
                                    in_array($log->action, ['delete'])                          => ['bg'=>'#fee2e2','color'=>'#991b1b'],
                                    in_array($log->action, ['approve','approve_appeal','approve_amendment','approve_balance_adjustment']) => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                                    in_array($log->action, ['reject'])                          => ['bg'=>'#fce7f3','color'=>'#9d174d'],
                                    in_array($log->action, ['login'])                           => ['bg'=>'#e0e7ff','color'=>'#3730a3'],
                                    in_array($log->action, ['logout'])                          => ['bg'=>'#f1f5f9','color'=>'#475569'],
                                    default                                                      => ['bg'=>'#f1f5f9','color'=>'#475569'],
                                };
                            @endphp
                            <span class="badge" style="background:{{ $actionStyles['bg'] }};color:{{ $actionStyles['color'] }};font-size:0.78rem;font-weight:600;">
                                {{ $log->action_label }}
                            </span>
                        </td>
                        <td style="padding:0.75rem 1rem; max-width:280px;">
                            @if($log->description)
                                <span title="{{ $log->description }}" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $log->description }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="padding:0.75rem 1rem;">
                            @if(!$log->ip_address || $log->ip_address === '127.0.0.1')
                                <span class="badge" style="background:#f1f5f9;color:#64748b;font-size:0.75rem;border-radius:6px;">Lokal</span>
                            @else
                                <code style="font-size:0.78rem;background:#f8fafc;padding:2px 6px;border-radius:4px;color:#334155;">{{ $log->ip_address }}</code>
                            @endif
                        </td>
                        <td style="padding:0.75rem 1rem;">
                            <a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-sm btn-outline-primary" style="border-radius:6px;">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-5">
                            <div class="text-center">
                                <div class="sc-empty-icon mb-3"><i class="ti ti-file-off"></i></div>
                                <div class="fw-semibold mb-1" style="color:var(--sc-text);">
                                    {{ request()->hasAny(['model','action','user_id','description','from_date','to_date']) ? 'Tidak ada log yang sesuai filter' : 'Belum ada audit log' }}
                                </div>
                                <div class="text-muted" style="font-size:0.85rem;">
                                    {{ request()->hasAny(['model','action','user_id','description','from_date','to_date']) ? 'Coba ubah atau reset filter pencarian' : 'Log aktivitas akan terekam secara otomatis' }}
                                </div>
                                @if(request()->hasAny(['model','action','user_id','description','from_date','to_date']))
                                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary mt-3" style="border-radius:10px;">
                                    <i class="ti ti-x me-1"></i> Reset Filter
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
