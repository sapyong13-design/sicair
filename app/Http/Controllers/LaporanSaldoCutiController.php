<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CutiTahunanCalculator;
use Illuminate\Http\Request;

class LaporanSaldoCutiController extends Controller
{
    public function index(Request $request)
    {
        $year      = $request->input('year', date('Y'));
        $search    = $request->input('search', '');
        $unitKerja = $request->input('unit_kerja', '');

        $query = User::query()->orderBy('name');
        if ($search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%"));
        }
        if ($unitKerja) {
            $query->where('unit_kerja', $unitKerja);
        }

        $users     = $query->get();
        $unitList  = User::select('unit_kerja')->distinct()->whereNotNull('unit_kerja')->orderBy('unit_kerja')->pluck('unit_kerja');
        $saldoData = [];

        foreach ($users as $user) {
            $calc      = new CutiTahunanCalculator($user);
            $info      = $calc->hitung();
            $saldoData[] = [
                'user'              => $user,
                'hak_cuti'          => $info['hak_dasar'] ?? 12,
                'carry_over'        => $info['carry_over'] ?? 0,
                'tambahan_terpencil'=> $info['tambahan_terpencil'] ?? 0,
                'total_hak'         => $info['total_hak'] ?? 12,
                'cuti_diambil'      => $info['cuti_diambil'] ?? 0,
                'sisa_cuti'         => $info['sisa'] ?? 0,
            ];
        }

        return view('laporan-saldo-cuti.index', compact('saldoData', 'unitList', 'year', 'search', 'unitKerja'));
    }

    public function export(Request $request)
    {
        $year      = $request->input('year', date('Y'));
        $search    = $request->input('search', '');
        $unitKerja = $request->input('unit_kerja', '');

        $query = User::query()->orderBy('name');
        if ($search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%"));
        }
        if ($unitKerja) {
            $query->where('unit_kerja', $unitKerja);
        }

        $users = $query->get();
        $csv   = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        $csv  .= "Nama,NIP,Jabatan,Unit Kerja,Hak Cuti,Carry Over,Terpencil,Total Hak,Diambil,Sisa\n";

        foreach ($users as $user) {
            $calc = new CutiTahunanCalculator($user);
            $info = $calc->hitung();
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $user->name) . '"',
                '"' . $user->nip . '"',
                '"' . ($user->jabatan ?? '') . '"',
                '"' . ($user->unit_kerja ?? '') . '"',
                $info['hak_dasar'] ?? 12,
                $info['carry_over'] ?? 0,
                $info['tambahan_terpencil'] ?? 0,
                $info['total_hak'] ?? 12,
                $info['cuti_diambil'] ?? 0,
                $info['sisa'] ?? 0,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="laporan-saldo-cuti-' . $year . '.csv"',
        ]);
    }
}
