<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Display audit logs list (admin only)
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Halaman ini hanya untuk Admin.');
        }

        $query = AuditLog::query();

        // Filter by model
        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by description keyword
        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->with('user')
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $totalLogs     = AuditLog::count();
        $todayLogs     = AuditLog::whereDate('created_at', today())->count();
        $uniqueModels  = AuditLog::distinct('model')->count('model');

        // All users for filter dropdown
        $users = User::orderBy('name')->get(['id', 'name', 'nip']);

        return view('admin.audit-logs.index', compact('logs', 'users', 'totalLogs', 'todayLogs', 'uniqueModels'));
    }

    /**
     * Show audit log details
     */
    public function show(AuditLog $auditLog)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Halaman ini hanya untuk Admin.');
        }

        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
