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
    // generate notifikasi setiap pagi jam 09:00
    $schedule->command('notify:expiring-members')->dailyAt('09:00');
    $schedule->command('db:backup --compress --rotate-days=30')->dailyAt('02:00');

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
