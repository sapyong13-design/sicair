<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\CutiRecord;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RemindCutiExpiry extends Command
{
    protected $signature = 'cuti:remind-expiry';
    protected $description = 'Kirim reminder untuk sisa cuti carry-over yang akan kadaluarsa 31 Maret';

    public function handle(): int
    {
        // Hanya berlaku Januari-Maret
        if (now()->month > 3) {
            $this->info('Bukan periode reminder (Jan-Mar). Skip.');
            return self::SUCCESS;
        }

        $expiryDate = now()->year . '-03-31';
        $daysLeft   = (int) now()->startOfDay()->diffInDays($expiryDate);

        // Hanya kirim di H-30 dan H-7
        if (!in_array($daysLeft, [30, 7])) {
            $this->info("Hari ini H-{$daysLeft} dari 31 Maret. Bukan hari reminder.");
            return self::SUCCESS;
        }

        // Cari pegawai yang memiliki carry_over > 0 di tahun berjalan
        // carry_over adalah sisa cuti dari tahun sebelumnya yang dibawa ke tahun ini
        $records = CutiRecord::with('user')
            ->where('tahun', now()->year)
            ->where('carry_over', '>', 0)
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get();

        $this->info("Mengirim reminder ke {$records->count()} pegawai (H-{$daysLeft})...");

        foreach ($records as $record) {
            $user = $record->user;
            if (!$user) continue;

            $sisa = $record->carry_over;

            // Pesan berbeda untuk H-30 vs reminder lainnya
            if ($daysLeft === 30) {
                $message = "Reminder SiCAIR: Sisa cuti Anda sebesar {$sisa} hari akan hangus pada 31 Maret " .
                    now()->year . ". Segera ajukan cuti sebelum terlambat.";
                $inAppMessage = "Sisa cuti Anda sebesar {$sisa} hari akan hangus pada 31 Maret. Segera ajukan cuti sebelum terlambat.";
            } else {
                $message = "Reminder SiCAIR: Anda masih memiliki {$sisa} hari cuti carry-over dari tahun " .
                    (now()->year - 1) . " yang akan kadaluarsa pada 31 Maret " . now()->year .
                    ". Segera ajukan cuti sebelum kadaluarsa!";
                $inAppMessage = "Anda memiliki {$sisa} hari sisa cuti dari tahun sebelumnya yang kadaluarsa 31 Maret.";
            }

            // In-app notification
            Notification::create([
                'user_id' => $user->id,
                'type'    => 'cuti_expiry_reminder',
                'title'   => "Sisa Cuti Carry-Over Akan Kadaluarsa H-{$daysLeft}",
                'message' => $inAppMessage,
                'link'    => route('leave.create'),
            ]);

            // Email notification if user has email
            if ($user->email) {
                try {
                    Mail::raw($message, fn($m) => $m->to($user->email)
                        ->subject("Reminder: Sisa Cuti Akan Kadaluarsa H-{$daysLeft}"));
                } catch (\Exception $e) {
                    $this->warn("Gagal kirim email ke {$user->name}: " . $e->getMessage());
                }
            }
        }

        $this->info("Reminder berhasil dikirim.");
        return self::SUCCESS;
    }
}
