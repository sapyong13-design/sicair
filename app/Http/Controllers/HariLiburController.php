<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use Illuminate\Http\Request;

class HariLiburController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $hariLibur = HariLibur::where('tahun', $tahun)->orderBy('tanggal')->get();
        $tahunList = HariLibur::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        if ($tahunList->isEmpty()) {
            $tahunList = collect([date('Y')]);
        }

        return view('hari-libur.index', compact('hariLibur', 'tahun', 'tahunList'));
    }

    public function create()
    {
        return view('hari-libur.form', ['hariLibur' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date|unique:hari_libur,tanggal',
            'keterangan' => 'required|string|max:255',
            'is_cuti_bersama' => 'boolean',
        ]);

        $validated['tahun'] = date('Y', strtotime($validated['tanggal']));
        $validated['is_cuti_bersama'] = $request->boolean('is_cuti_bersama');

        HariLibur::create($validated);

        return redirect()->route('hari-libur.index')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function edit(HariLibur $hariLibur)
    {
        return view('hari-libur.form', compact('hariLibur'));
    }

    public function update(Request $request, HariLibur $hariLibur)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date|unique:hari_libur,tanggal,' . $hariLibur->id,
            'keterangan' => 'required|string|max:255',
            'is_cuti_bersama' => 'boolean',
        ]);

        $validated['tahun'] = date('Y', strtotime($validated['tanggal']));
        $validated['is_cuti_bersama'] = $request->boolean('is_cuti_bersama');

        $hariLibur->update($validated);

        return redirect()->route('hari-libur.index')->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(HariLibur $hariLibur)
    {
        $hariLibur->delete();
        return redirect()->route('hari-libur.index')->with('success', 'Hari libur berhasil dihapus.');
    }
}
