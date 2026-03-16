<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanController extends Controller
{
    public function tahunan(Request $request)
    {
        if (!auth()->check()) {
            abort(401);
        }

        $year = max(2000, min(2050, (int)$request->get('year', now()->year)));
        $unitKerja = $request->get('unit_kerja');

        $pegawaiQuery = User::where('role', '!=', 'admin');
        if ($unitKerja) {
            $pegawaiQuery->where('unit_kerja', $unitKerja);
        }
        $pegawaiList = $pegawaiQuery->orderBy('name')->get();

        $leaveTypes = LeaveRequest::where('status', LeaveRequest::STATUS_DISETUJUI)
            ->orWhere('status', LeaveRequest::STATUS_APPROVED)
            ->whereYear('start_date', $year)
            ->distinct()
            ->pluck('type');

        // Build pivot data: user_id => [type => total_days]
        $rawData = LeaveRequest::whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('start_date', $year)
            ->when($unitKerja, fn($q) => $q->whereHas('user', fn($u) => $u->where('unit_kerja', $unitKerja)))
            ->selectRaw('user_id, type, SUM(total_hari_kerja) as total')
            ->groupBy('user_id', 'type')
            ->get();

        $pivot = [];
        foreach ($rawData as $row) {
            $pivot[$row->user_id][$row->type] = $row->total;
        }

        $years = range(now()->year, now()->year - 5);
        $unitList = User::distinct()->orderBy('unit_kerja')->whereNotNull('unit_kerja')->pluck('unit_kerja');
        $typeLabels = LeaveRequest::typeLabels();

        return view('laporan.tahunan', compact('pegawaiList', 'leaveTypes', 'pivot', 'year', 'years', 'unitKerja', 'unitList', 'typeLabels'));
    }

    public function unitKerja(Request $request)
    {
        if (!auth()->check()) {
            abort(401);
        }

        $year  = max(2000, min(2050, (int)$request->get('year', now()->year)));
        $bulan = $request->get('bulan') ? (int)$request->get('bulan') : null;

        $targetTypes = [
            LeaveRequest::TYPE_TAHUNAN,
            LeaveRequest::TYPE_SAKIT,
            LeaveRequest::TYPE_BESAR,
            LeaveRequest::TYPE_ALASAN_PENTING,
            LeaveRequest::TYPE_LUAR_TANGGUNGAN,
        ];

        // Query: sum hari per unit_kerja per type
        $query = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
            ->whereIn('leave_requests.status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereIn('leave_requests.type', $targetTypes)
            ->whereNull('leave_requests.deleted_at')
            ->whereYear('leave_requests.start_date', $year)
            ->select('users.unit_kerja', 'leave_requests.type',
                \DB::raw('SUM(leave_requests.total_hari_kerja) as total_hari'),
                \DB::raw('COUNT(DISTINCT leave_requests.user_id) as jumlah_pegawai_cuti'))
            ->groupBy('users.unit_kerja', 'leave_requests.type');

        if ($bulan) {
            $query->whereMonth('leave_requests.start_date', $bulan);
        }

        $rawRows = $query->get();

        // Jumlah pegawai aktif per unit_kerja
        $pegawaiPerUnit = User::whereNotNull('unit_kerja')
            ->where('role', '!=', 'admin')
            ->selectRaw('unit_kerja, COUNT(*) as jumlah')
            ->groupBy('unit_kerja')
            ->pluck('jumlah', 'unit_kerja');

        // Bangun pivot: unit_kerja => [type => total_hari]
        $pivot   = [];
        $unitSet = [];
        foreach ($rawRows as $row) {
            $uk = $row->unit_kerja ?? '(Tidak Diisi)';
            $unitSet[$uk] = true;
            $pivot[$uk][$row->type] = (int) $row->total_hari;
        }

        // Urutkan unit_kerja secara alfabet
        ksort($unitSet);
        $units = array_keys($unitSet);

        // Hitung total kolom (per type) dan total baris (per unit)
        $totalPerType = [];
        foreach ($targetTypes as $t) {
            $totalPerType[$t] = 0;
        }
        $grandTotal = 0;

        $rows = [];
        foreach ($units as $uk) {
            $rowTotal = 0;
            $typeTotals = [];
            foreach ($targetTypes as $t) {
                $val = $pivot[$uk][$t] ?? 0;
                $typeTotals[$t] = $val;
                $rowTotal += $val;
                $totalPerType[$t] += $val;
            }
            $grandTotal += $rowTotal;

            $jumlahPegawai = $pegawaiPerUnit[$uk] ?? 0;
            $rataRata = $jumlahPegawai > 0 ? round($rowTotal / $jumlahPegawai, 1) : 0;

            $rows[] = [
                'unit_kerja'     => $uk,
                'jumlah_pegawai' => $jumlahPegawai,
                'types'          => $typeTotals,
                'total'          => $rowTotal,
                'rata_rata'      => $rataRata,
            ];
        }

        $years   = range(now()->year, now()->year - 5);
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $typeLabels = [
            LeaveRequest::TYPE_TAHUNAN        => 'CT',
            LeaveRequest::TYPE_SAKIT          => 'CS',
            LeaveRequest::TYPE_BESAR          => 'CB',
            LeaveRequest::TYPE_ALASAN_PENTING => 'CAP',
            LeaveRequest::TYPE_LUAR_TANGGUNGAN => 'CLTN',
        ];

        return view('laporan.unit-kerja', compact(
            'rows', 'year', 'years', 'bulan', 'bulanList',
            'targetTypes', 'typeLabels', 'totalPerType', 'grandTotal'
        ));
    }

    public function exportTahunan(Request $request)
    {
        if (!auth()->check()) {
            abort(401);
        }

        $year = (int)$request->get('year', now()->year);
        $unitKerja = $request->get('unit_kerja');

        $format = $request->get('format', 'csv');

        $rows = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('start_date', $year)
            ->when($unitKerja, fn($q) => $q->whereHas('user', fn($u) => $u->where('unit_kerja', $unitKerja)))
            ->orderBy('start_date')
            ->get();

        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setTitle("Rekap Cuti {$year}")
                ->setCreator('SiCAIR - PN Natuna');
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Rekap Cuti ' . $year);

            $headers = ['No', 'NIP', 'Nama', 'Unit Kerja', 'Jabatan', 'Jenis Cuti', 'Tgl Mulai', 'Tgl Selesai', 'Hari Kerja'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue(chr(65 + $i) . '1', $h);
            }
            $sheet->getStyle('A1:I1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '166534']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $typeLabels = LeaveRequest::typeLabels();
            foreach ($rows as $i => $row) {
                $r = $i + 2;
                $sheet->setCellValue('A' . $r, $i + 1);
                $sheet->setCellValue('B' . $r, $row->user?->nip);
                $sheet->setCellValue('C' . $r, $row->user?->name);
                $sheet->setCellValue('D' . $r, $row->user?->unit_kerja);
                $sheet->setCellValue('E' . $r, $row->user?->jabatan);
                $sheet->setCellValue('F' . $r, $typeLabels[$row->type] ?? $row->type);
                $sheet->setCellValue('G' . $r, $row->start_date?->format('d/m/Y'));
                $sheet->setCellValue('H' . $r, $row->end_date?->format('d/m/Y'));
                $sheet->setCellValue('I' . $r, $row->total_hari_kerja ?? $row->total_days);
            }

            if ($rows->isEmpty()) {
                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue('A2', 'Tidak ada data cuti yang disetujui untuk periode ini.');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '9CA3AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }

            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $safeUnit = $unitKerja ? '-' . Str::slug($unitKerja) : '';
            $filename = 'rekap-cuti-' . $year . $safeUnit . '.xlsx';
            $tempPath = tempnam(sys_get_temp_dir(), 'excel_');
            if ($tempPath === false) {
                abort(500, 'Gagal membuat file sementara untuk ekspor.');
            }
            $writer->save($tempPath);

            return response()->download($tempPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }

        $safeUnitCsv = $unitKerja ? '-' . Str::slug($unitKerja) : '';
        $filename = "rekap-cuti-{$year}{$safeUnitCsv}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $typeLabels = LeaveRequest::typeLabels();
        $callback = function () use ($rows, $typeLabels) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['NIP', 'Nama', 'Unit Kerja', 'Jabatan', 'Jenis Cuti', 'Tgl Mulai', 'Tgl Selesai', 'Hari Kerja'], ',');
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->user?->nip,
                    $row->user?->name,
                    $row->user?->unit_kerja,
                    $row->user?->jabatan,
                    $typeLabels[$row->type] ?? $row->type,
                    $row->start_date?->format('d/m/Y'),
                    $row->end_date?->format('d/m/Y'),
                    $row->total_hari_kerja ?? $row->total_days,
                ], ',');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
