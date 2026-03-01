<?php

namespace App\Http\Controllers;

use App\Models\DinasLuar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DinasLuarController extends Controller
{
    public function index(Request $request)
    {
        $query = DinasLuar::with('user')->orderBy('start_date', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            $startOfMonth = "$tahun-$bulan-01";
            $endOfMonth   = date('Y-m-t', strtotime($startOfMonth));
            $query->where('start_date', '<=', $endOfMonth)
                  ->where('end_date', '>=', $startOfMonth);
        } elseif ($request->filled('tahun')) {
            $tahun = $request->tahun;
            $query->whereYear('start_date', $tahun);
        }

        $dinasLuarList = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('dinas-luar.index', compact('dinasLuarList', 'users'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('dinas-luar.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'tujuan'     => 'required|string|max:255',
            'keperluan'  => 'required|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'keterangan' => 'nullable|string',
            'dokumen'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $validated['created_by'] = Auth::id();

        if ($request->hasFile('dokumen')) {
            $validated['dokumen'] = $request->file('dokumen')
                ->store('dinas-luar-docs', 'public');
        }

        DinasLuar::create($validated);

        return redirect()->route('dinas-luar.index')
            ->with('success', 'Data dinas luar berhasil ditambahkan.');
    }

    public function edit(DinasLuar $dinasLuar)
    {
        $users = User::orderBy('name')->get();
        return view('dinas-luar.edit', compact('dinasLuar', 'users'));
    }

    public function update(Request $request, DinasLuar $dinasLuar)
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'tujuan'     => 'required|string|max:255',
            'keperluan'  => 'required|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'keterangan' => 'nullable|string',
            'dokumen'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('dokumen')) {
            // Delete old file if exists
            if ($dinasLuar->dokumen) {
                Storage::disk('public')->delete($dinasLuar->dokumen);
            }
            $validated['dokumen'] = $request->file('dokumen')
                ->store('dinas-luar-docs', 'public');
        }

        $dinasLuar->update($validated);

        return redirect()->route('dinas-luar.index')
            ->with('success', 'Data dinas luar berhasil diperbarui.');
    }

    public function destroy(DinasLuar $dinasLuar)
    {
        if ($dinasLuar->dokumen) {
            Storage::disk('public')->delete($dinasLuar->dokumen);
        }
        $dinasLuar->delete();

        return redirect()->route('dinas-luar.index')
            ->with('success', 'Data dinas luar berhasil dihapus.');
    }

    public function downloadDokumen(DinasLuar $dinasLuar)
    {
        $user = Auth::user();

        // Only admin, ketua, or the owner can download
        if (!$user->isAdmin() && !$user->isKetua() && $user->id !== $dinasLuar->user_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        if (!$dinasLuar->dokumen || !Storage::disk('public')->exists($dinasLuar->dokumen)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $dinasLuar->dokumen,
            'dinas-luar-' . $dinasLuar->id . '-' . $dinasLuar->user->name . '.' . pathinfo($dinasLuar->dokumen, PATHINFO_EXTENSION)
        );
    }
}
