<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CutiTahunanCalculator;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            // Prediksi saldo akhir tahun
            $bulanBerjalan = (int) date('n'); // bulan saat ini (1-12)
            $cutiDiambilTahunIni = $info['cuti_diambil'] ?? 0;
            $rataPerBulan = $bulanBerjalan > 0 ? ($cutiDiambilTahunIni / $bulanBerjalan) : 0;
            $sisaBulan = 12 - $bulanBerjalan;
            $prediksiTambahan = round($rataPerBulan * $sisaBulan);
            $prediksiSisa = max(0, ($info['sisa'] ?? 0) - $prediksiTambahan);

            $saldoData[] = [
                'user'               => $user,
                'hak_cuti'           => $info['hak_dasar'] ?? 12,
                'carry_over'         => $info['carry_over'] ?? 0,
                'tambahan_terpencil' => $info['tambahan_terpencil'] ?? 0,
                'total_hak'          => $info['total_hak'] ?? 12,
                'cuti_diambil'       => $info['cuti_diambil'] ?? 0,
                'sisa_cuti'          => $info['sisa'] ?? 0,
                'prediksi_sisa'      => $prediksiSisa,
                'rata_per_bulan'     => round($rataPerBulan, 1),
            ];
        }

        return view('laporan-saldo-cuti.index', compact('saldoData', 'unitList', 'year', 'search', 'unitKerja'));
    }

    public function export(Request $request)
    {
        $year      = $request->input('year', date('Y'));
        $format    = $request->input('format', 'csv');
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
        // Collect data for all users
        $rows = [];
        foreach ($users as $user) {
            $calc   = new CutiTahunanCalculator($user);
            $info   = $calc->hitung();
            $rows[] = [
                'name'              => $user->name,
                'nip'               => $user->nip,
                'jabatan'           => $user->jabatan ?? '',
                'unit_kerja'        => $user->unit_kerja ?? '',
                'hak_dasar'         => $info['hak_dasar'] ?? 12,
                'carry_over'        => $info['carry_over'] ?? 0,
                'tambahan_terpencil'=> $info['tambahan_terpencil'] ?? 0,
                'total_hak'         => $info['total_hak'] ?? 12,
                'cuti_diambil'      => $info['cuti_diambil'] ?? 0,
                'sisa'              => $info['sisa'] ?? 0,
            ];
        }

        // === Excel (.xlsx) export ===
        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Saldo Cuti ' . $year);

            // Header
            $headers = ['No', 'Nama', 'NIP', 'Jabatan', 'Unit Kerja', 'Hak Cuti', 'Carry Over', 'Terpencil', 'Total Hak', 'Diambil', 'Sisa'];
            foreach ($headers as $col => $header) {
                $cell = chr(65 + $col) . '1';
                $sheet->setCellValue($cell, $header);
            }
            $sheet->getStyle('A1:K1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '166534']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Data rows
            foreach ($rows as $i => $row) {
                $r = $i + 2;
                $sheet->setCellValue('A' . $r, $i + 1);
                $sheet->setCellValue('B' . $r, $row['name']);
                $sheet->setCellValue('C' . $r, $row['nip']);
                $sheet->setCellValue('D' . $r, $row['jabatan']);
                $sheet->setCellValue('E' . $r, $row['unit_kerja']);
                $sheet->setCellValue('F' . $r, $row['hak_dasar']);
                $sheet->setCellValue('G' . $r, $row['carry_over']);
                $sheet->setCellValue('H' . $r, $row['tambahan_terpencil']);
                $sheet->setCellValue('I' . $r, $row['total_hak']);
                $sheet->setCellValue('J' . $r, $row['cuti_diambil']);
                $sheet->setCellValue('K' . $r, $row['sisa']);
            }

            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer   = new Xlsx($spreadsheet);
            $filename = 'laporan-saldo-cuti-' . $year . '.xlsx';
            $tempPath = tempnam(sys_get_temp_dir(), 'excel_');
            $writer->save($tempPath);

            return response()->download($tempPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }

        // === CSV export (default) ===
        $csv  = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "Nama,NIP,Jabatan,Unit Kerja,Hak Cuti,Carry Over,Terpencil,Total Hak,Diambil,Sisa\n";
        foreach ($rows as $row) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $row['name']) . '"',
                '"' . $row['nip'] . '"',
                '"' . $row['jabatan'] . '"',
                '"' . $row['unit_kerja'] . '"',
                $row['hak_dasar'],
                $row['carry_over'],
                $row['tambahan_terpencil'],
                $row['total_hak'],
                $row['cuti_diambil'],
                $row['sisa'],
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="laporan-saldo-cuti-' . $year . '.csv"',
        ]);
    }
}
