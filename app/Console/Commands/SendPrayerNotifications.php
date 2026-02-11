<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrayerTime;
use App\Models\User;
use App\Models\PrayerNotificationLog;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;

class SendPrayerNotifications extends Command
{
    protected $signature = 'prayer-times:notify';
    protected $description = 'Send prayer time notifications';

    protected FirebaseNotificationService $firebase;

    public function __construct(FirebaseNotificationService $firebase)
    {
        parent::__construct();
        $this->firebase = $firebase;
    }

    public function handle()
    {
        $now = '15:44';
        $today = Carbon::today()->toDateString();

        $prayerTime = PrayerTime::whereDate('gregorian_date', $today)->first();

        if (!$prayerTime) {
            $this->warn('No prayer time found for today');
            return Command::SUCCESS;
        }

        foreach ($prayerTime->timings as $prayer => $time) {

            $prayerKey = strtolower($prayer);

            if (!in_array($prayerKey, ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'])) {
                continue;
            }

            if ($time !== $now) {
                continue;
            }

            if (
                PrayerNotificationLog::where('gregorian_date', $today)
                ->where('prayer', $prayerKey)
                ->exists()
            ) {
                $this->warn("Already sent: {$prayerKey}");
                continue;
            }

            User::chunk(200, function ($users) use ($prayer, $time) {
                foreach ($users as $user) {
                    $this->firebase->sendToUser(
                        $user,
                        'Prayer Time',
                        "{$prayer} time has started ({$time})",
                        [
                            'type'   => 'prayer',
                            'prayer' => $prayer,
                            'time'   => $time,
                        ]
                    );
                }
            });

            PrayerNotificationLog::create([
                'gregorian_date' => $today,
                'prayer'         => $prayerKey,
            ]);

            $this->info("Notification sent for {$prayerKey}");
        }

        return Command::SUCCESS;
    }
}
