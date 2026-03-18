<?php

namespace App\Http\Controllers;

use App\Models\LeaveReasonTemplate;
use Illuminate\Http\Request;

class LeaveReasonTemplateController extends Controller
{
    /** JSON endpoint untuk dropdown di form pengajuan (semua role) */
    public function api()
    {
        $templates = LeaveReasonTemplate::active()->get(['id', 'label', 'body']);
        return response()->json($templates);
    }

    /** Halaman CRUD (admin only) */
    public function index()
    {
        $templates = LeaveReasonTemplate::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.leave-reason-templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:100',
            'body'       => 'required|string|max:1000',
            'sort_order' => 'integer|min:0',
        ]);
        LeaveReasonTemplate::create($validated);
        return back()->with('success', 'Template berhasil ditambahkan.');
    }

    public function update(Request $request, LeaveReasonTemplate $leaveReasonTemplate)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:100',
            'body'       => 'required|string|max:1000',
            'sort_order' => 'integer|min:0',
        ]);
        // Checkbox tidak dikirim browser saat unchecked — gunakan boolean() helper
        $validated['is_active'] = $request->boolean('is_active');
        $leaveReasonTemplate->update($validated);
        return back()->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(LeaveReasonTemplate $leaveReasonTemplate)
    {
        $leaveReasonTemplate->delete();
        return back()->with('success', 'Template berhasil dihapus.');
    }

    public function toggleActive(LeaveReasonTemplate $leaveReasonTemplate)
    {
        $leaveReasonTemplate->update(['is_active' => !$leaveReasonTemplate->is_active]);
        return back()->with('success', 'Status template diperbarui.');
    }
}
