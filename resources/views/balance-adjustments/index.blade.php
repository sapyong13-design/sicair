@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Perubahan Saldo Cuti</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('pegawai.index') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Buat Perubahan
            </a>
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
                    <div class="col-md-2">
                        <select name="year" class="form-select">
                            <option value="">Semua Tahun</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Cari pegawai..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-secondary w-100">Cari</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('balance-adjustment.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Pegawai</th>
                            <th>Tahun</th>
                            <th>Tipe</th>
                            <th>Hari</th>
                            <th>Status</th>
                            <th>Alasan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adjustment)
                            <tr>
                                <td>
                                    <strong>{{ $adjustment->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $adjustment->user->email }}</small>
                                </td>
                                <td>{{ $adjustment->year }}</td>
                                <td>
                                    @switch($adjustment->type)
                                        @case('addition')
                                            <span class="badge bg-success">Penambahan</span>
                                            @break
                                        @case('deduction')
                                            <span class="badge bg-warning">Pengurangan</span>
                                            @break
                                        @case('correction')
                                            <span class="badge bg-info">Koreksi</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <strong class="{{ $adjustment->adjustment_days > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $adjustment->adjustment_days > 0 ? '+' : '' }}{{ $adjustment->adjustment_days }}
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $adjustment->getStatusBadgeClass() }}">
                                        {{ ucfirst($adjustment->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ Str::limit($adjustment->reason, 40) }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('balance-adjustment.show', $adjustment) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Belum ada perubahan saldo cuti
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $adjustments->links() }}
        </div>
    </div>
</div>
@endsection
