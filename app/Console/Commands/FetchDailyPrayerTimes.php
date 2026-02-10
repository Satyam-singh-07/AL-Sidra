<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\PrayerTime;
use Carbon\Carbon;

class FetchDailyPrayerTimes extends Command
{
    protected $signature = 'prayer-times:fetch';
    protected $description = 'Fetch and store daily prayer times';

    public function handle()
    {
        $date = Carbon::today()->format('d-m-Y');

        $lat = 28.6139;
        $lng = 77.209;

        $response = Http::timeout(15)->get(
            "https://api.aladhan.com/v1/timings/{$date}",
            [
                'latitude'  => $lat,
                'longitude' => $lng,
            ]
        );

        if (!$response->successful()) {
            $this->error('Failed to fetch prayer times');
            return Command::FAILURE;
        }

        $data = $response->json('data');

        PrayerTime::updateOrCreate(
            [
                'gregorian_date' => Carbon::createFromFormat(
                    'd-m-Y',
                    $data['date']['gregorian']['date']
                )->toDateString(),
                'latitude'  => $lat,
                'longitude' => $lng,
            ],
            [
                'timezone'  => $data['meta']['timezone'],
                'timings'   => $data['timings'],
                'date_meta' => $data['date'],
                'meta'      => $data['meta'],
                'raw'       => $response->json(),
            ]
        );

        $this->info('Prayer times stored successfully');

        return Command::SUCCESS;
    }
}
