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
        $year = (int)$request->get('year', now()->year);
        $unitKerja = $request->get('unit_kerja');

        $pegawaiQuery = User::where('role', '!=', 'admin');
        if ($unitKerja) {
            $pegawaiQuery->where('unit_kerja', $unitKerja);
        }
        $pegawaiList = $pegawaiQuery->orderBy('name')->get();

        $leaveTypes = LeaveRequest::where('status', LeaveRequest::STATUS_DISETUJUI)
            ->orWhere('status', LeaveRequest::STATUS_APPROVED)
            ->whereRaw("strftime('%Y', start_date) = ?", [(string)$year])
            ->distinct()
            ->pluck('type');

        // Build pivot data: user_id => [type => total_days]
        $rawData = LeaveRequest::whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereRaw("strftime('%Y', start_date) = ?", [(string)$year])
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

    public function exportTahunan(Request $request)
    {
        $year = (int)$request->get('year', now()->year);
        $unitKerja = $request->get('unit_kerja');

        $format = $request->get('format', 'csv');

        $rows = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereRaw("strftime('%Y', start_date) = ?", [(string)$year])
            ->when($unitKerja, fn($q) => $q->whereHas('user', fn($u) => $u->where('unit_kerja', $unitKerja)))
            ->orderBy('start_date')
            ->get();

        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setTitle("Rekap Cuti {$year}")
                ->setCreator('SiHEALING - PN Natuna');
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
