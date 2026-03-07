<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public static function send(string $phone, string $message): bool
    {
        if (!config('whatsapp.enabled')) return false;

        // Normalize phone (08xx → 628xx)
        $phone = preg_replace('/^0/', '62', preg_replace('/\D/', '', $phone));
        if (empty($phone)) return false;

        $driver = config('whatsapp.driver', 'fonnte');

        try {
            if ($driver === 'fonnte') {
                $response = Http::withHeaders([
                    'Authorization' => config('whatsapp.fonnte.token'),
                ])->post(config('whatsapp.fonnte.endpoint'), [
                    'target'  => $phone,
                    'message' => $message,
                ]);
                return $response->successful();
            }

            if ($driver === 'wablas') {
                $response = Http::withHeaders([
                    'Authorization' => config('whatsapp.wablas.token'),
                ])->post(config('whatsapp.wablas.endpoint') . '/send-message', [
                    'phone'   => $phone,
                    'message' => $message,
                ]);
                return $response->successful();
            }
        } catch (\Exception $e) {
            Log::warning('WhatsApp send failed: ' . $e->getMessage());
        }

        return false;
    }
}
