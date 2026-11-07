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
        $schedule
            ->command('prayer-times:fetch')
            ->dailyAt('00:15')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule
            ->command('prayer-times:notify')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule
            ->command('quotes:send')
            ->everyMinute()
            ->withoutOverlapping();

        // Daily cleanup: Permanently purge notifications and prayer logs older than 2 weeks
        $schedule
            ->command('notifications:prune --days=14')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();
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
