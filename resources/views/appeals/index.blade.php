@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Daftar Banding Pengajuan Cuti</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                            <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Cari pegawai atau nomor cuti..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-secondary w-100">Cari</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('appeal.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Pegawai</th>
                            <th>No. Cuti</th>
                            <th>Tanggal Banding</th>
                            <th>Jenis Cuti</th>
                            <th>Status</th>
                            <th>Keputusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appeals as $appeal)
                            <tr>
                                <td>
                                    <strong>{{ $appeal->appellant->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $appeal->appellant->email }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('leave.show', $appeal->leaveRequest) }}" class="text-decoration-none">
                                        #{{ str_pad($appeal->leave_request_id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td>{{ $appeal->created_at->format('d/m/Y') }}</td>
                                <td>{{ $appeal->leaveRequest->jenis_cuti }}</td>
                                <td>
                                    <span class="badge bg-{{ $appeal->getStatusBadgeClass() }}">
                                        {{ ucfirst($appeal->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($appeal->decision)
                                        <span class="badge bg-{{ $appeal->decision === 'approved' ? 'success' : 'danger' }}">
                                            {{ $appeal->getDecisionLabelAttribute() }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('appeal.show', $appeal) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Belum ada banding pengajuan cuti
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $appeals->links() }}
        </div>
    </div>
</div>
@endsection
