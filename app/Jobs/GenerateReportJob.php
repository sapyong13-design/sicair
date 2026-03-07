<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries   = 2;

    public function __construct(
        private string $reportType,
        private array  $params,
        private int    $requestedBy,
        private string $downloadToken
    ) {}

    public function handle(): void
    {
        $path = "reports/{$this->downloadToken}.pdf";

        try {
            // Generate report content based on type
            $content = match($this->reportType) {
                'laporan_bulanan'   => $this->generateLaporanBulanan(),
                'laporan_saldo'     => $this->generateLaporanSaldo(),
                default             => throw new \InvalidArgumentException("Unknown report type: {$this->reportType}"),
            };

            Storage::put($path, $content);

            // Notify user that report is ready
            $downloadUrl = route('reports.download', ['token' => $this->downloadToken]);
            Notification::create([
                'user_id' => $this->requestedBy,
                'type'    => 'report_ready',
                'title'   => 'Laporan Siap Diunduh',
                'message' => 'Laporan yang Anda minta telah selesai dibuat. Klik untuk mengunduh.',
                'link'    => $downloadUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('GenerateReportJob failed: ' . $e->getMessage());
        }
    }

    private function generateLaporanBulanan(): string
    {
        // Placeholder — replace with actual PDF generation logic
        // In real usage, call PdfExportService or barryvdh/laravel-dompdf
        return "PDF content for laporan bulanan";
    }

    private function generateLaporanSaldo(): string
    {
        return "PDF content for laporan saldo";
    }
}
