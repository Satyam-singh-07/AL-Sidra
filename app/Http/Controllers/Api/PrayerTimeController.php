<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrayerTime;
use Illuminate\Http\Request;

class PrayerTimeController extends Controller
{
    public function index()
    {
        $prayerTime = PrayerTime::select('timings')->first();

        return response()->json($prayerTime);
    }
}
