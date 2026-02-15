<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto carry-over unused leave days every January 1st at 01:00
        $schedule->command('app:carry-over-unused-leave')
            ->yearlyOn(1, 1, '01:00')
            ->onOneServer()
            ->withoutOverlapping()
            ->runInBackground();

        // Alternative: Run daily and check if it's January 1st (useful for testing)
        // $schedule->command('app:carry-over-unused-leave')
        //     ->daily()
        //     ->onOneServer()
        //     ->when(function () {
        //         return now()->month === 1 && now()->day === 1;
        //     });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
