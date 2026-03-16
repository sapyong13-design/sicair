<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class WhatsAppTest extends Command
{
    protected $signature = 'whatsapp:test
                            {phone : Nomor tujuan (08xx / 628xx / +628xx)}
                            {message? : Pesan yang dikirim (opsional)}';

    protected $description = 'Test pengiriman WhatsApp via Fonnte';

    public function handle(): int
    {
        $phone   = $this->argument('phone');
        $message = $this->argument('message')
            ?? 'Test SiCAIR WA Bot — ' . now()->format('d/m/Y H:i:s');

        $this->info("Mengirim ke : {$phone}");
        $this->line("Pesan       : {$message}");

        if (!config('whatsapp.enabled')) {
            $this->warn('WHATSAPP_ENABLED=false. Ubah ke true di .env untuk kirim nyata.');
            $this->line('(Pesan tidak dikirim — mode dry run)');
            return self::SUCCESS;
        }

        if (empty(config('whatsapp.fonnte.token'))) {
            $this->error('FONNTE_TOKEN belum diisi di .env.');
            return self::FAILURE;
        }

        $ok = WhatsAppService::send($phone, $message);

        if ($ok) {
            $this->info('Berhasil dikirim.');
            return self::SUCCESS;
        }

        $this->error('Gagal kirim. Periksa FONNTE_TOKEN dan storage/logs/laravel.log.');
        return self::FAILURE;
    }
}
