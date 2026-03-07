<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generate quota cuti tahunan otomatis setiap 1 Januari pukul 00:30
Schedule::command('cuti:generate-quota')->yearlyOn(1, 1, '00:30');

// Kirim pengingat cuti mendekati kadaluarsa setiap hari pukul 08:00
Schedule::command('cuti:remind-expiry')->dailyAt('08:00');
