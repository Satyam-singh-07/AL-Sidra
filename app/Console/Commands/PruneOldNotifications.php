<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\PrayerNotificationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PruneOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:prune {--days=14 : Number of days of notifications to retain}';

    /**
     * The console command description.
     */
    protected $description = 'Permanently delete notifications and prayer logs older than 2 weeks (or specified days).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 14;
        }

        $cutoffDateTime = Carbon::now()->subDays($days);
        $cutoffDate = $cutoffDateTime->toDateString();

        $this->info("Pruning notifications older than {$days} days (cutoff: {$cutoffDateTime->toDateTimeString()})...");

        // 1. Delete notifications older than cutoff
        $deletedNotifications = Notification::where('created_at', '<', $cutoffDateTime)->delete();

        // 2. Delete prayer notification logs older than cutoff
        $deletedPrayerLogs = PrayerNotificationLog::where('created_at', '<', $cutoffDateTime)
            ->orWhere('gregorian_date', '<', $cutoffDate)
            ->delete();

        $this->info("Successfully deleted {$deletedNotifications} notification(s) and {$deletedPrayerLogs} prayer log(s).");

        Log::info("Notification cleanup completed: deleted {$deletedNotifications} notification(s) and {$deletedPrayerLogs} prayer log(s) older than {$days} days.");

        return Command::SUCCESS;
    }
}

