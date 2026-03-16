<?php

namespace App\Http\Controllers;

use App\Models\DinasLuar;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanBulananController extends Controller
{
    private array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $year  = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('n'));

        return view('laporan-bulanan.index', compact('year', 'month'));
    }

    public function export(Request $request)
    {
        $year  = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('n'));

        $startOfMonth = sprintf('%04d-%02d-01', $year, $month);
        $endOfMonth   = date('Y-m-t', strtotime($startOfMonth));
        $monthLabel   = $this->monthNames[$month] . ' ' . $year;

        // Fetch cuti disetujui yang overlap dengan bulan ini
        $leaves = LeaveRequest::with('user')
            ->whereIn('status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth)
            ->orderBy('start_date')
            ->get();

        // Fetch dinas luar yang overlap
        $dinasLuarList = DinasLuar::with('user')
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth)
            ->orderBy('start_date')
            ->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle("Laporan Bulanan $monthLabel")
            ->setCreator('SiCAIR - PN Natuna');

        // ---- Sheet 1: Cuti ----
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Cuti Disetujui');
        $this->buildCutiSheet($sheet1, $leaves, $monthLabel, $startOfMonth, $endOfMonth);

        // ---- Sheet 2: Dinas Luar ----
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Dinas Luar');
        $this->buildDinasLuarSheet($sheet2, $dinasLuarList, $monthLabel);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Laporan-Bulanan-' . $this->monthNames[$month] . '-' . $year . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildCutiSheet($sheet, $leaves, string $monthLabel, string $start, string $end): void
    {
        $primaryColor = '166534';
        $headerBg     = $primaryColor;
        $titleBg      = '14532d';

        // Title row
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'LAPORAN CUTI PEGAWAI — ' . strtoupper($monthLabel));
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $titleBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A2', 'Pengadilan Negeri Natuna — SiCAIR');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF' . $primaryColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Header row
        $headers = ['No', 'Nama Pegawai', 'NIP', 'Jabatan', 'Unit Kerja', 'Jenis Cuti', 'Tgl Mulai', 'Tgl Selesai', 'Hari Kerja', 'Keterangan'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $widths  = [5, 30, 22, 25, 25, 22, 14, 14, 12, 30];

        foreach ($headers as $i => $header) {
            $cell = $cols[$i] . '4';
            $sheet->setCellValue($cell, $header);
            $sheet->getColumnDimension($cols[$i])->setWidth($widths[$i]);
        }
        $sheet->getStyle('A4:J4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $headerBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(22);

        // Blank row 3
        $sheet->getRowDimension(3)->setRowHeight(6);

        // Data rows
        $row = 5;
        foreach ($leaves as $i => $lv) {
            $isEven = $i % 2 === 0;
            $rowBg  = $isEven ? 'FFF0FDF4' : 'FFFFFFFF';

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $lv->user->name ?? '-');
            $sheet->setCellValue('C' . $row, $lv->user->nip ?? '-');
            $sheet->setCellValue('D' . $row, $lv->user->jabatan ?? '-');
            $sheet->setCellValue('E' . $row, $lv->user->unit_kerja ?? '-');
            $sheet->setCellValue('F' . $row, $lv->type_label ?? '-');
            $sheet->setCellValue('G' . $row, $lv->start_date->format('d/m/Y'));
            $sheet->setCellValue('H' . $row, $lv->end_date->format('d/m/Y'));
            $sheet->setCellValue('I' . $row, $lv->total_hari_kerja ?? '-');
            $sheet->setCellValue('J' . $row, $lv->alasan ?? '-');

            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $rowBg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1FAE5']]],
                'font' => ['size' => 9],
            ]);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        if ($leaves->isEmpty()) {
            $sheet->mergeCells('A5:J5');
            $sheet->setCellValue('A5', 'Tidak ada cuti yang disetujui pada bulan ini.');
            $sheet->getStyle('A5')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['argb' => 'FF9CA3AF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row = 6;
        }

        // Summary row
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('A' . $row, 'Total: ' . $leaves->count() . ' cuti');
        $sheet->setCellValue('I' . $row, $leaves->sum('total_hari_kerja'));
        $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD1FAE5']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF' . $primaryColor]]],
        ]);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function buildDinasLuarSheet($sheet, $dinasLuarList, string $monthLabel): void
    {
        $primaryColor = 'ea580c';
        $titleBg      = 'c2410c';

        // Title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'LAPORAN DINAS LUAR — ' . strtoupper($monthLabel));
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $titleBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Pengadilan Negeri Natuna — SiCAIR');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF' . $primaryColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(6);

        $headers = ['No', 'Nama Pegawai', 'NIP', 'Jabatan', 'Tujuan Dinas', 'Tgl Mulai', 'Tgl Selesai', 'Durasi (Hari)'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $widths  = [5, 30, 22, 25, 30, 14, 14, 14];

        foreach ($headers as $i => $header) {
            $sheet->setCellValue($cols[$i] . '4', $header);
            $sheet->getColumnDimension($cols[$i])->setWidth($widths[$i]);
        }
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $primaryColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(22);

        $row = 5;
        foreach ($dinasLuarList as $i => $dl) {
            $isEven = $i % 2 === 0;
            $rowBg  = $isEven ? 'FFFFF7ED' : 'FFFFFFFF';

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $dl->user->name ?? '-');
            $sheet->setCellValue('C' . $row, $dl->user->nip ?? '-');
            $sheet->setCellValue('D' . $row, $dl->user->jabatan ?? '-');
            $sheet->setCellValue('E' . $row, $dl->tujuan);
            $sheet->setCellValue('F' . $row, $dl->start_date->format('d/m/Y'));
            $sheet->setCellValue('G' . $row, $dl->end_date->format('d/m/Y'));
            $sheet->setCellValue('H' . $row, $dl->durasi);

            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $rowBg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFED7AA']]],
                'font' => ['size' => 9],
            ]);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row . ':H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        if ($dinasLuarList->isEmpty()) {
            $sheet->mergeCells('A5:H5');
            $sheet->setCellValue('A5', 'Tidak ada dinas luar pada bulan ini.');
            $sheet->getStyle('A5')->applyFromArray([
                'font' => ['italic' => true, 'color' => ['argb' => 'FF9CA3AF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row = 6;
        }

        // Summary
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('A' . $row, 'Total: ' . $dinasLuarList->count() . ' pegawai dinas luar');
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFED7AA']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF' . $primaryColor]]],
        ]);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
}
