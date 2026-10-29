<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masjid;
use App\Models\MasjidPrayerTiming;
use App\Models\PrayerTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrayerTimeController extends Controller
{
    public function index(Request $request)
    {
        $masjidId = $request->query('masjid_id');
        $masjid = null;

        // If not provided in query, attempt to resolve from authenticated Sanctum user
        if (!$masjidId) {
            $user = $request->user('sanctum');
            if ($user) {
                if (!empty($user->selected_masjid_id)) {
                    $masjidId = (int)$user->selected_masjid_id;
                } elseif ($user->memberProfile && $user->memberProfile->place_type === 'masjid' && !empty($user->memberProfile->place_id)) {
                    $masjidId = (int)$user->memberProfile->place_id;
                } elseif (!empty($user->memberProfile?->masjid_id)) {
                    $masjidId = (int)$user->memberProfile->masjid_id;
                } else {
                    $ownedMasjidId = Masjid::where('user_id', $user->id)->value('id');
                    if ($ownedMasjidId) {
                        $masjidId = (int)$ownedMasjidId;
                    }
                }
            }
        }

        if ($masjidId) {
            $masjid = Masjid::find($masjidId);
            if ($masjid) {
                $todayDate = Carbon::today()->toDateString();
                $timing = MasjidPrayerTiming::where('masjid_id', $masjidId)
                    ->where('date', $todayDate)
                    ->first();

                if ($timing) {
                    return response()->json([
                        'timings' => [
                            'Fajr'    => $timing->fajr,
                            'Sunrise' => $timing->sunrise,
                            'Dhuhr'   => $timing->dhuhr,
                            'Asr'     => $timing->asr,
                            'Maghrib' => $timing->maghrib,
                            'Isha'    => $timing->isha,
                            'Jummah'  => $timing->jummah,
                        ],
                        'is_official' => true,
                        'masjid_id'   => (int)$masjidId,
                        'masjid_name' => $masjid->name,
                    ]);
                }
            }
        }

        $prayerTime = PrayerTime::select('timings')->first();

        return response()->json([
            'timings'     => $prayerTime ? $prayerTime->timings : null,
            'is_official' => false,
            'masjid_id'   => $masjid ? (int)$masjid->id : null,
            'masjid_name' => $masjid ? $masjid->name : null,
        ]);
    }
}
