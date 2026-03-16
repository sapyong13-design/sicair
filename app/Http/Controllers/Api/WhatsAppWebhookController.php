<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CutiRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verifikasi token Fonnte (jika webhook_secret diisi)
        $secret = config('whatsapp.fonnte.webhook_secret');
        if (!empty($secret)) {
            $incoming = $request->header('Authorization')
                ?? $request->input('token')
                ?? '';
            if ($incoming !== $secret) {
                Log::warning('WA webhook: invalid token from ' . $request->ip());
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        // Fonnte mengirim: sender, message, device
        $from    = $request->input('sender', '');
        $message = trim($request->input('message', ''));

        if (empty($from) || empty($message)) {
            return response()->json(['ok' => true]);
        }

        // Normalize nomor & cari user
        $normalized = preg_replace('/^0/', '62', preg_replace('/\D/', '', $from));
        $last9      = substr($normalized, -9);

        $user = User::where('telepon', 'LIKE', "%{$last9}")
            ->where('is_active', true)
            ->first();

        if (!$user) {
            WhatsAppService::send($from,
                "SiCAIR: Nomor Anda tidak terdaftar dalam sistem.\nHubungi admin untuk mendaftarkan nomor HP."
            );
            return response()->json(['ok' => true]);
        }

        // Parse perintah (case-insensitive, trim spasi berlebih)
        $cmd = strtoupper(preg_replace('/\s+/', ' ', trim($message)));

        $reply = match (true) {
            // Cek saldo / sisa cuti
            in_array($cmd, [
                'SALDO', 'CEK SALDO', 'CUTI', 'CEK CUTI',
                'SISA', 'SISA CUTI', 'INFO CUTI', 'INFO',
                'BALANCE', 'QUOTA', 'KUOTA',
            ]) => $this->replySaldo($user),

            // Status pengajuan aktif
            in_array($cmd, [
                'STATUS', 'CEK STATUS', 'PENGAJUAN',
                'CEK PENGAJUAN', 'PROGRESS', 'PROSES',
            ]) => $this->replyStatus($user),

            // Riwayat cuti
            in_array($cmd, [
                'HISTORY', 'RIWAYAT', 'HISTORI', 'HISTORIS',
                'CEK RIWAYAT', 'REKAP', 'LOG',
            ]) => $this->replyHistory($user),

            // Bantuan / menu
            in_array($cmd, [
                'HELP', 'BANTUAN', 'MENU', 'PERINTAH',
                'PANDUAN', 'PETUNJUK', '?', 'INFO BOT',
                'HALO', 'HI', 'HELLO', 'HAI', 'START',
            ]) => $this->replyHelp(),

            default => $this->replyUnknown(),
        };

        WhatsAppService::send($from, $reply);

        return response()->json(['ok' => true]);
    }

    private function replySaldo(User $user): string
    {
        $record = CutiRecord::where('user_id', $user->id)
            ->where('tahun', now()->year)
            ->first();

        $text  = "SALDO CUTI - " . now()->year . "\n";
        $text .= $user->name . "\n";
        $text .= str_repeat('-', 30) . "\n";
        $text .= "Sisa Cuti Tahunan : {$user->leave_balance} hari\n";

        if ($record) {
            $text .= "Hak Cuti          : {$record->hak_cuti} hari\n";
            if ($record->carry_over > 0) {
                $text .= "Carry-Over        : {$record->carry_over} hari\n";
            }
            if ($record->tambahan_terpencil > 0) {
                $text .= "Terpencil         : {$record->tambahan_terpencil} hari\n";
            }
            $text .= "Telah Diambil     : {$record->cuti_diambil} hari\n";
        }

        return rtrim($text);
    }

    private function replyStatus(User $user): string
    {
        $active = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', [
                LeaveRequest::STATUS_DIAJUKAN,
                LeaveRequest::STATUS_PERTIMBANGAN,
            ])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$active) {
            return "STATUS CUTI\n{$user->name}\n" . str_repeat('-', 30)
                . "\nTidak ada pengajuan cuti yang sedang aktif.";
        }

        $statusLabel = match ($active->status) {
            LeaveRequest::STATUS_DIAJUKAN     => 'Menunggu Pertimbangan Atasan',
            LeaveRequest::STATUS_PERTIMBANGAN => 'Menunggu Keputusan Pejabat',
            default                           => $active->status,
        };

        return "STATUS CUTI\n{$user->name}\n"
            . str_repeat('-', 30) . "\n"
            . "Jenis  : " . ($active->type_label ?? $active->type) . "\n"
            . "Mulai  : " . $active->start_date->format('d/m/Y') . "\n"
            . "Selesai: " . $active->end_date->format('d/m/Y') . "\n"
            . "Durasi : {$active->total_hari_kerja} hari kerja\n"
            . "Status : {$statusLabel}";
    }

    private function replyHistory(User $user): string
    {
        $list = LeaveRequest::where('user_id', $user->id)
            ->whereRaw("strftime('%Y', start_date) = ?", [(string) now()->year])
            ->orderBy('start_date', 'desc')
            ->limit(5)
            ->get();

        $header = "RIWAYAT CUTI " . now()->year . "\n{$user->name}\n" . str_repeat('-', 30);

        if ($list->isEmpty()) {
            return $header . "\nBelum ada riwayat cuti tahun ini.";
        }

        $text = $header . "\n";
        foreach ($list as $lr) {
            $statusShort = match ($lr->status) {
                LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED => 'DISETUJUI',
                LeaveRequest::STATUS_DITOLAK                                  => 'DITOLAK',
                LeaveRequest::STATUS_DIAJUKAN                                 => 'PENDING',
                LeaveRequest::STATUS_PERTIMBANGAN                             => 'PROSES',
                default                                                       => strtoupper(substr($lr->status, 0, 8)),
            };
            $text .= $lr->start_date->format('d/m') . '-' . $lr->end_date->format('d/m')
                . ' | ' . $lr->total_hari_kerja . 'h'
                . ' | ' . $statusShort . "\n";
        }

        return rtrim($text);
    }

    private function replyHelp(): string
    {
        return "SIHEALING WA BOT\n"
            . str_repeat('-', 30) . "\n"
            . "Cek Saldo Cuti:\n"
            . "  SALDO / CUTI / SISA / INFO\n\n"
            . "Status Pengajuan Aktif:\n"
            . "  STATUS / PENGAJUAN / PROSES\n\n"
            . "Riwayat Cuti Tahun Ini:\n"
            . "  RIWAYAT / HISTORY / REKAP\n\n"
            . "Tampilkan Menu Ini:\n"
            . "  BANTUAN / MENU / HELP / ?\n"
            . str_repeat('-', 30) . "\n"
            . config('app.url');
    }

    private function replyUnknown(): string
    {
        return "SiCAIR: Perintah tidak dikenal.\nKetik *BANTUAN* untuk daftar perintah.";
    }
}
