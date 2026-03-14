<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backup database setiap hari jam 02:00
Schedule::command('backup:database --keep=14')->dailyAt('02:00');

// Generate quota cuti tiap 1 Januari
Schedule::command('cuti:generate-quota')->yearlyOn(1, 1, '06:00');

// Carry over cuti tiap 1 Januari
Schedule::command('app:carry-over-unused-leave')->yearlyOn(1, 1, '06:30');

// Reminder pending approvals setiap hari kerja jam 09:00
Schedule::command('app:remind-pending-approvals')->weekdays()->at('09:00');

// Reminder cuti expiry setiap hari Januari-Maret
Schedule::command('cuti:remind-expiry')->dailyAt('08:00');
