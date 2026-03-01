@extends('layouts.app')

@section('content')
<div class="container-xl">
    <!-- Page title -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-history text-primary me-2"></i>Riwayat Saldo Cuti
                </h2>
                <p class="text-secondary">Saldo cuti Anda saat ini: <strong class="text-success">{{ $user->leave_balance }} hari</strong></p>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-xl">
        <!-- Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">50 Perubahan Terakhir</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tindakan</th>
                                    <th>Saldo Lama</th>
                                    <th>Saldo Baru</th>
                                    <th class="w-50">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $log)
                                <tr>
                                    <td class="text-nowrap">
                                        <span class="badge badge-outline" title="{{ $log->created_at->format('d M Y H:i:s') }}">
                                            {{ $log->created_at->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm" style="background-color: var(--sh-primary);">
                                            {{ $log->action_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $log->old_values['balance'] ?? '-' }}</strong> hari
                                    </td>
                                    <td>
                                        <strong class="@if(($log->new_values['balance'] ?? 0) > ($log->old_values['balance'] ?? 0)) text-success @else text-danger @endif">
                                            {{ $log->new_values['balance'] ?? '-' }}
                                        </strong> hari
                                    </td>
                                    <td>
                                        <small class="text-secondary">{{ $log->description }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-5">
                                        <div class="text-center">
                                            <div class="sh-empty-icon mb-3"><i class="ti ti-history"></i></div>
                                            <div class="fw-semibold mb-1" style="color:var(--sh-text);">Belum ada riwayat perubahan saldo</div>
                                            <div class="text-muted" style="font-size:0.85rem;">Riwayat akan muncul di sini setelah ada penyesuaian saldo</div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
