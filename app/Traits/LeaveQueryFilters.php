<?php

namespace App\Traits;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;

trait LeaveQueryFilters
{
    protected function applyStatusFilter($query, Request $request): void
    {
        if (!$request->filled('status')) return;

        match ($request->status) {
            'disetujui' => $query->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED]),
            'ditolak'   => $query->whereIn('status', [LeaveRequest::STATUS_DITOLAK, LeaveRequest::STATUS_REJECTED]),
            default     => $query->where('status', $request->status),
        };
    }

    protected function applyTypeAndDateFilters($query, Request $request): void
    {
        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('start_date')) $query->where('start_date', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->where('end_date', '<=', $request->end_date);
    }

    protected function applyNameFilter($query, Request $request): void
    {
        if (!$request->filled('q')) return;
        $q = $request->q;
        $query->whereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
    }
}
