<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public static function send(string $phone, string $message, ?string $email = null, ?string $subject = null): bool
    {
        if (!config('whatsapp.enabled')) return false;

        // Normalize phone (08xx → 628xx)
        $phone = preg_replace('/^0/', '62', preg_replace('/\D/', '', $phone));
        if (empty($phone)) return false;

        $driver = config('whatsapp.driver', 'fonnte');
        $waSuccess = false;

        try {
            if ($driver === 'fonnte') {
                $response = Http::withHeaders([
                    'Authorization' => config('whatsapp.fonnte.token'),
                ])->post(config('whatsapp.fonnte.endpoint'), [
                    'target'  => $phone,
                    'message' => $message,
                ]);
                $waSuccess = $response->successful();
            } elseif ($driver === 'wablas') {
                $response = Http::withHeaders([
                    'Authorization' => config('whatsapp.wablas.token'),
                ])->post(config('whatsapp.wablas.endpoint') . '/send-message', [
                    'phone'   => $phone,
                    'message' => $message,
                ]);
                $waSuccess = $response->successful();
            }
        } catch (\Exception $e) {
            Log::warning('WhatsApp send failed: ' . $e->getMessage());
        }

        if (!$waSuccess && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                \Illuminate\Support\Facades\Mail::to($email)->send(
                    new \App\Mail\LeaveNotificationMail(
                        $subject ?? config('app.name') . ' — Notifikasi',
                        $message
                    )
                );
                \Illuminate\Support\Facades\Log::info("Email fallback sent to {$email}");
                return true;
            } catch (\Exception $mailEx) {
                \Illuminate\Support\Facades\Log::error("Email fallback gagal: " . $mailEx->getMessage());
            }
        }

        return $waSuccess;
    }
}
