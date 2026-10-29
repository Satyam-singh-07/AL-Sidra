<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrayerTime;
use App\Models\User;
use App\Models\Masjid;
use App\Models\MasjidPrayerTiming;
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
        $now = Carbon::now()->format('H:i');
        $today = Carbon::today()->toDateString();

        // 1. Process Masjids with custom official timings for today
        $masjidTimings = MasjidPrayerTiming::with('masjid')
            ->where('date', $today)
            ->get();

        $customScheduledMasjidIds = [];

        foreach ($masjidTimings as $mt) {
            $masjidId = $mt->masjid_id;
            $customScheduledMasjidIds[] = $masjidId;
            $masjidName = $mt->masjid ? $mt->masjid->name : 'Masjid';

            $times = [
                'fajr'    => $mt->fajr,
                'dhuhr'   => $mt->dhuhr,
                'asr'     => $mt->asr,
                'maghrib' => $mt->maghrib,
                'isha'    => $mt->isha,
            ];

            foreach ($times as $prayerKey => $time) {
                if ($time !== $now) {
                    continue;
                }

                // Check if already notified for this masjid, date & prayer
                if (PrayerNotificationLog::where('gregorian_date', $today)
                    ->where('prayer', $prayerKey)
                    ->where('masjid_id', $masjidId)
                    ->exists()
                ) {
                    continue;
                }

                // Find users belonging to this Masjid:
                // Mutawalli / Owner, Mohalla Mutawalli, or registered Donor / Member
                $users = User::where(function ($q) use ($masjidId) {
                    $q->where('selected_masjid_id', $masjidId)
                      ->orWhereHas('memberProfile', function ($mq) use ($masjidId) {
                          $mq->where('place_type', 'masjid')->where('place_id', $masjidId);
                      });
                })->orWhereIn('id', function ($sub) use ($masjidId) {
                    $sub->select('user_id')->from('masjids')->where('id', $masjidId);
                })->get();

                $prayerTitle = ucfirst($prayerKey);
                foreach ($users as $user) {
                    $this->firebase->sendToUser(
                        $user,
                        'Prayer Time',
                        "{$prayerTitle} adhan time has started at {$masjidName} ({$time})",
                        [
                            'type'        => 'namaz',
                            'prayer'      => $prayerTitle,
                            'time'        => $time,
                            'is_official' => true,
                            'masjid_id'   => $masjidId,
                            'masjid_name' => $masjidName,
                        ]
                    );
                }

                PrayerNotificationLog::create([
                    'gregorian_date' => $today,
                    'prayer'         => $prayerKey,
                    'masjid_id'      => $masjidId,
                ]);
            }
        }

        // 2. Process Global / Standard prayer times for users not under custom-scheduled masjids
        $prayerTime = PrayerTime::whereDate('gregorian_date', $today)->first();
        if ($prayerTime) {
            foreach ($prayerTime->timings as $prayer => $time) {
                $prayerKey = strtolower($prayer);
                if (!in_array($prayerKey, ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'])) {
                    continue;
                }

                if ($time !== $now) {
                    continue;
                }

                if (PrayerNotificationLog::where('gregorian_date', $today)
                    ->where('prayer', $prayerKey)
                    ->whereNull('masjid_id')
                    ->exists()
                ) {
                    continue;
                }

                $globalUsersQuery = User::query();
                if (!empty($customScheduledMasjidIds)) {
                    $globalUsersQuery->where(function ($q) use ($customScheduledMasjidIds) {
                        $q->whereNull('selected_masjid_id')
                          ->orWhereNotIn('selected_masjid_id', $customScheduledMasjidIds);
                    })->whereDoesntHave('memberProfile', function ($mq) use ($customScheduledMasjidIds) {
                        $mq->where('place_type', 'masjid')->whereIn('place_id', $customScheduledMasjidIds);
                    })->whereNotIn('id', function ($sub) use ($customScheduledMasjidIds) {
                        $sub->select('user_id')->from('masjids')->whereIn('id', $customScheduledMasjidIds);
                    });
                }

                $globalUsersQuery->chunk(200, function ($users) use ($prayer, $time) {
                    foreach ($users as $user) {
                        $this->firebase->sendToUser(
                            $user,
                            'Prayer Time',
                            "{$prayer} time has started ({$time})",
                            [
                                'type'   => 'namaz',
                                'prayer' => $prayer,
                                'time'   => $time,
                            ]
                        );
                    }
                });

                PrayerNotificationLog::create([
                    'gregorian_date' => $today,
                    'prayer'         => $prayerKey,
                    'masjid_id'      => null,
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
