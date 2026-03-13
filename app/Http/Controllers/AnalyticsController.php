<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        $prevYear = $year - 1;

        $analyticsService = new AnalyticsService();

        // Current year analytics
        $analytics = $analyticsService->getDashboardAnalytics($year);
        $leaveBalances = $analyticsService->getLeaveBalanceOverview($year);

        // Chart data
        $chartByType = $analytics['charts']['by_type'] ?? [];
        $chartByStatus = $analytics['charts']['by_status'] ?? [];
        $chartMonthly = $analytics['charts']['monthly_trend'] ?? [];
        $chartByDepartment = $analytics['charts']['by_department'] ?? [];
        $topUsers = $analytics['topUsers'] ?? [];
        $upcomingLeaves = $analytics['upcomingLeaves'] ?? [];

        // #41 Year comparison
        $prevAnalytics = $analyticsService->getDashboardAnalytics($prevYear);
        $prevSummary = $prevAnalytics['summary'] ?? [];

        // #42 Heatmap data: per user per month
        $heatmapData = $this->getHeatmapData($year);

        // Sprint 7: extra charts
        $monthly12       = $analyticsService->getMonthlyTrend12();
        $byBagian        = $analyticsService->getByBagian($year);
        $heatmapByUnit   = $analyticsService->getHeatmapByUnit($year);

        // Available years for filter
        $availableYears = range(date('Y'), date('Y') - 3);

        return view('analytics.index', compact(
            'year', 'prevYear', 'analytics', 'leaveBalances',
            'chartByType', 'chartByStatus', 'chartMonthly', 'chartByDepartment',
            'topUsers', 'upcomingLeaves', 'prevSummary', 'heatmapData', 'availableYears',
            'monthly12', 'byBagian', 'heatmapByUnit'
        ));
    }

    /**
     * #43 Export annual report as CSV
     */
    public function exportAnnual(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));

        $leaves = LeaveRequest::with('user')
            ->whereYear('created_at', $year)
            ->orderBy('created_at')
            ->get();

        $format = $request->input('format', 'csv');

        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setTitle("Analytics Cuti {$year}")
                ->setCreator('SiHEALING - PN Natuna');
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Analytics ' . $year);

            $headers = ['No', 'Nama', 'NIP', 'Jabatan', 'Jenis Cuti', 'Tgl Mulai', 'Tgl Selesai', 'Hari Kerja', 'Status', 'Catatan'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue(chr(65 + $i) . '1', $h);
            }
            $sheet->getStyle('A1:J1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            foreach ($leaves as $i => $l) {
                $r = $i + 2;
                $sheet->setCellValue('A' . $r, $i + 1);
                $sheet->setCellValue('B' . $r, $l->user->name ?? '-');
                $sheet->setCellValue('C' . $r, $l->user->nip ?? '-');
                $sheet->setCellValue('D' . $r, $l->user->jabatan ?? '-');
                $sheet->setCellValue('E' . $r, $l->type_label ?? '-');
                $sheet->setCellValue('F' . $r, $l->start_date->format('d/m/Y'));
                $sheet->setCellValue('G' . $r, $l->end_date->format('d/m/Y'));
                $sheet->setCellValue('H' . $r, $l->total_hari_kerja ?? $l->total_days ?? 0);
                $sheet->setCellValue('I' . $r, $l->status_label ?? $l->status);
                $sheet->setCellValue('J' . $r, $l->catatan_pejabat ?? $l->catatan_atasan ?? '');
            }

            if ($leaves->isEmpty()) {
                $sheet->mergeCells('A2:J2');
                $sheet->setCellValue('A2', 'Tidak ada data cuti untuk tahun ini.');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '9CA3AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }

            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $filename = "analytics-cuti-{$year}.xlsx";
            $tempPath = tempnam(sys_get_temp_dir(), 'excel_');
            if ($tempPath === false) {
                abort(500, 'Gagal membuat file sementara untuk ekspor.');
            }
            $writer->save($tempPath);

            return response()->download($tempPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }

        $csv = "No,Nama,NIP,Jabatan,Jenis Cuti,Tanggal Mulai,Tanggal Selesai,Hari Kerja,Status,Catatan\n";
        $no = 1;
        foreach ($leaves as $l) {
            $csv .= implode(',', [
                $no++,
                '"' . str_replace('"', '""', $l->user->name) . '"',
                $l->user->nip,
                '"' . str_replace('"', '""', $l->user->jabatan ?? '') . '"',
                '"' . $l->type_label . '"',
                $l->start_date->format('d/m/Y'),
                $l->end_date->format('d/m/Y'),
                $l->total_hari_kerja ?? $l->total_days,
                $l->status_label,
                '"' . str_replace('"', '""', $l->catatan_pejabat ?? $l->catatan_atasan ?? '') . '"',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"laporan-cuti-{$year}.csv\"",
        ]);
    }

    /**
     * Sprint 7 #40: Export analytics as simple PDF (table-based, no charts)
     */
    public function exportPdf(Request $request)
    {
        $year             = (int) $request->input('year', date('Y'));
        $analyticsService = new AnalyticsService();
        $analytics        = $analyticsService->getDashboardAnalytics($year);
        $leaveBalances    = $analyticsService->getLeaveBalanceOverview($year);
        $byBagian         = $analyticsService->getByBagian($year);
        $monthly12        = $analyticsService->getMonthlyTrend12();

        $html = view('pdfs.analytics-report', compact('year', 'analytics', 'leaveBalances', 'byBagian', 'monthly12'))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    /**
     * #42 Heatmap: leaves per user per month
     */
    private function getHeatmapData(int $year): array
    {
        $rows = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
            ->selectRaw("users.name, users.id as user_id, CAST(strftime('%m', leave_requests.start_date) AS INTEGER) as month, sum(COALESCE(total_hari_kerja, CAST((julianday(end_date) - julianday(start_date)) AS INTEGER) + 1)) as total_days")
            ->whereIn('leave_requests.status', [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED])
            ->whereYear('leave_requests.start_date', $year)
            ->groupBy('users.id', 'users.name', 'month')
            ->orderBy('users.name')
            ->get();

        $users = $rows->pluck('name', 'user_id')->unique();
        $data = [];
        foreach ($users as $uid => $name) {
            $months = array_fill(1, 12, 0);
            foreach ($rows->where('user_id', $uid) as $row) {
                $months[(int)$row->month] = (int)$row->total_days;
            }
            $data[] = [
                'name' => $name,
                'months' => array_values($months),
                'total' => array_sum($months),
            ];
        }

        return $data;
    }
}
