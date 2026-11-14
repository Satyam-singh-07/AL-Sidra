<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdhanAudio;
use App\Models\Masjid;
use App\Models\MasjidPrayerTiming;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MasjidPrayerTimingController extends Controller
{
    /**
     * Check if user is authorized to manage prayer timings for the given Masjid.
     */
    private function checkAuthorization($user, int $masjidId): bool
    {
        if (!$user) {
            return false;
        }

        // Super Admin or Admin role
        if ($user->isSuperAdmin() || $user->roles()->whereIn('slug', ['super_admin', 'admin'])->exists() || in_array($user->role, ['super_admin', 'admin'])) {
            return true;
        }

        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return false;
        }

        // Masjid owner
        if ($masjid->user_id === $user->id) {
            return true;
        }

        // Mutawalli role matching this masjid
        $hasMutawalliRole = in_array($user->role, ['mutvalli', 'mutawalli']) ||
            $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli'])->exists();

        if ($hasMutawalliRole) {
            if ($user->selected_masjid_id == $masjidId) {
                return true;
            }
            if ($user->memberProfile && ($user->memberProfile->masjid_id == $masjidId || ($user->memberProfile->place_type === 'masjid' && $user->memberProfile->place_id == $masjidId))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dispatch a silent sync event to all devices connected to this Masjid.
     */
    private function notifyConnectedUsersTimetableSync(int $masjidId): void
    {
        try {
            $users = User::where(function ($q) use ($masjidId) {
                $q->where('selected_masjid_id', $masjidId)
                  ->orWhereHas('memberProfile', function ($mq) use ($masjidId) {
                      $mq->where('place_type', 'masjid')->where('place_id', $masjidId);
                  });
            })->orWhereIn('id', function ($sub) use ($masjidId) {
                $sub->select('user_id')->from('masjids')->where('id', $masjidId);
            })->get();

            $userIds = $users->pluck('id')->toArray();
            $tokens = UserFcmToken::whereIn('user_id', $userIds)
                ->pluck('token')
                ->unique()
                ->toArray();

            if (!empty($tokens)) {
                $firebase = app(FirebaseNotificationService::class);
                $firebase->sendToTokens(
                    $tokens,
                    'Prayer Timings Updated',
                    'Official prayer schedule has been updated.',
                    [
                        'type'      => 'silent_sync',
                        'action'    => 'sync_prayer_timings',
                        'masjid_id' => (string)$masjidId,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch timetable sync: ' . $e->getMessage());
        }
    }

    /**
     * 3.1 Fetch Monthly or Date-Range Timings
     * Route: GET /api/masjid/{masjid_id}/prayer-timings
     */
    public function index(Request $request, $masjidId)
    {
        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found.',
            ], 404);
        }

        $query = MasjidPrayerTiming::where('masjid_id', $masjidId);

        $startDate = null;
        $endDate = null;
        $monthLabel = null;

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($request->filled('month')) {
            $month = $request->query('month');
            try {
                $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $end = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
                $startDate = $start->toDateString();
                $endDate = $end->toDateString();
                $monthLabel = $month;
                $query->whereBetween('date', [$startDate, $endDate]);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid month format. Please use YYYY-MM.',
                ], 422);
            }
        } else {
            $now = Carbon::today();
            $monthLabel = $now->format('Y-m');
            $startDate = $now->copy()->startOfMonth()->toDateString();
            $endDate = $now->copy()->endOfMonth()->toDateString();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $timings = $query->orderBy('date', 'asc')->get()->map(function ($item) {
            return [
                'id'      => $item->id,
                'date'    => $item->date instanceof Carbon ? $item->date->format('Y-m-d') : substr((string)$item->date, 0, 10),
                'fajr'    => $item->fajr,
                'sunrise' => $item->sunrise,
                'dhuhr'   => $item->dhuhr,
                'asr'     => $item->asr,
                'maghrib' => $item->maghrib,
                'isha'    => $item->isha,
                'jummah'  => $item->jummah,
            ];
        });

        $responseData = [
            'masjid_id'             => (int)$masjidId,
            'total_configured_days' => $timings->count(),
            'timings'               => $timings,
        ];

        if ($monthLabel) {
            $responseData['month'] = $monthLabel;
        } else {
            $responseData['start_date'] = $startDate;
            $responseData['end_date'] = $endDate;
        }

        return response()->json([
            'success' => true,
            'data'    => $responseData,
        ]);
    }

    /**
     * 3.3 Fetch Today's Official Adhan Timings (Home / Banner Widget)
     * Route: GET /api/masjid/{masjid_id}/prayer-timings/today
     */
    public function today($masjidId)
    {
        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found.',
            ], 404);
        }

        $todayDate = Carbon::today()->toDateString();
        $timing = MasjidPrayerTiming::where('masjid_id', $masjidId)
            ->where('date', $todayDate)
            ->first();

        $hasCustomAdhan = !empty($masjid->adhan_audio) || !empty($masjid->adhan_audio_id);
        $adhanAudioUrl = $masjid->adhan_audio_url;
        $adhanAudioData = $masjid->adhanAudio ? [
            'id'        => $masjid->adhanAudio->id,
            'title'     => $masjid->adhanAudio->title,
            'reciter'   => $masjid->adhanAudio->reciter,
            'duration'  => $masjid->adhanAudio->duration,
            'audio_url' => $masjid->adhanAudio->audio_url,
        ] : null;

        if ($timing) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'masjid_id'            => (int)$masjidId,
                    'masjid_name'          => $masjid->name,
                    'date'                 => $todayDate,
                    'has_custom_schedule' => true,
                    'has_custom_adhan'    => $hasCustomAdhan,
                    'adhan_audio_id'      => $masjid->adhan_audio_id,
                    'adhan_audio_url'      => $adhanAudioUrl,
                    'adhan_audio'          => $adhanAudioData,
                    'timings'              => [
                        'fajr'    => $timing->fajr,
                        'sunrise' => $timing->sunrise,
                        'dhuhr'   => $timing->dhuhr,
                        'asr'     => $timing->asr,
                        'maghrib' => $timing->maghrib,
                        'isha'    => $timing->isha,
                        'jummah'  => $timing->jummah,
                    ],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'masjid_id'            => (int)$masjidId,
                'masjid_name'          => $masjid->name,
                'date'                 => $todayDate,
                'has_custom_schedule' => false,
                'has_custom_adhan'    => $hasCustomAdhan,
                'adhan_audio_id'      => $masjid->adhan_audio_id,
                'adhan_audio_url'      => $adhanAudioUrl,
                'adhan_audio'          => $adhanAudioData,
                'timings'              => null,
            ],
        ]);
    }

    /**
     * 3.2 Save / Upsert Prayer Timings (Single & Bulk Range)
     * Route: POST /api/masjid/{masjid_id}/prayer-timings
     */
    public function store(Request $request, $masjidId)
    {
        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found.',
            ], 404);
        }

        $user = $request->user();
        if (!$this->checkAuthorization($user, (int)$masjidId)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage prayer timings for this Masjid.',
            ], 403);
        }

        $rules = [
            'mode'            => 'required|in:single,bulk_range,dates_list',
            'timings'         => 'required|array',
            'timings.fajr'    => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)$/'],
            'timings.sunrise' => ['nullable', 'regex:/^([01]\d|2[0-3]):([0-5]\d)$/'],
            'timings.dhuhr'   => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)$/'],
            'timings.asr'     => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)$/'],
            'timings.maghrib' => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)$/'],
            'timings.isha'    => ['required', 'regex:/^([01]\d|2[0-3]):([0-5]\d)$/'],
            'timings.jummah'  => ['nullable', 'regex:/^([01]\d|2[0-3]):([0-5]\d)$/'],
        ];

        if ($request->input('mode') === 'single') {
            $rules['date'] = 'required|date_format:Y-m-d';
        } elseif ($request->input('mode') === 'bulk_range') {
            $rules['start_date'] = 'required|date_format:Y-m-d';
            $rules['end_date'] = 'required|date_format:Y-m-d|after_or_equal:start_date';
        } elseif ($request->input('mode') === 'dates_list') {
            $rules['dates'] = 'required|array|min:1|max:365';
            $rules['dates.*'] = 'required|date_format:Y-m-d';
        }

        $messages = [
            'timings.fajr.regex'    => 'The fajr time must be in HH:mm 24-hour format.',
            'timings.sunrise.regex' => 'The sunrise time must be in HH:mm 24-hour format.',
            'timings.dhuhr.regex'   => 'The dhuhr time must be in HH:mm 24-hour format.',
            'timings.asr.regex'     => 'The asr time must be in HH:mm 24-hour format.',
            'timings.maghrib.regex' => 'The maghrib time must be in HH:mm 24-hour format.',
            'timings.isha.regex'    => 'The isha time must be in HH:mm 24-hour format.',
            'timings.jummah.regex'  => 'The jummah time must be in HH:mm 24-hour format.',
            'end_date.after_or_equal' => 'The end date must be after or equal to start date.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $mode = $request->input('mode');
        $timings = $request->input('timings');
        $dates = [];

        if ($mode === 'single') {
            $dates = [$request->input('date')];
        } elseif ($mode === 'bulk_range') {
            $start = Carbon::parse($request->input('start_date'));
            $end = Carbon::parse($request->input('end_date'));

            // Date Range limit: Maximum bulk range allowed is 365 days
            if ($start->diffInDays($end) > 365) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => [
                        'end_date' => ['The date range cannot exceed 365 days.'],
                    ],
                ], 422);
            }

            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $dates[] = $date->toDateString();
            }
        } elseif ($mode === 'dates_list') {
            $dates = array_values(array_unique($request->input('dates')));
        }

        if (empty($dates)) {
            return response()->json([
                'success' => false,
                'message' => 'No dates provided for prayer timings.',
            ], 422);
        }

        $now = now();
        $records = [];
        foreach ($dates as $dateStr) {
            $records[] = [
                'masjid_id'   => (int)$masjidId,
                'date'        => $dateStr,
                'fajr'        => $timings['fajr'],
                'sunrise'     => $timings['sunrise'] ?? null,
                'dhuhr'       => $timings['dhuhr'],
                'asr'         => $timings['asr'],
                'maghrib'     => $timings['maghrib'],
                'isha'        => $timings['isha'],
                'jummah'      => $timings['jummah'] ?? null,
                'created_by'  => $user->id,
                'updated_by'  => $user->id,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        MasjidPrayerTiming::upsert(
            $records,
            ['masjid_id', 'date'],
            ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha', 'jummah', 'updated_by', 'updated_at']
        );

        sort($dates);

        $this->notifyConnectedUsersTimetableSync((int)$masjidId);

        return response()->json([
            'success' => true,
            'message' => 'Prayer timings successfully saved.',
            'data'    => [
                'affected_days' => count($dates),
                'start_date'    => reset($dates),
                'end_date'      => end($dates),
            ],
        ]);
    }

    /**
     * 3.4 Delete / Reset Timings for a Date or Date Range
     * Route: DELETE /api/masjid/{masjid_id}/prayer-timings
     */
    public function destroy(Request $request, $masjidId)
    {
        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found.',
            ], 404);
        }

        $user = $request->user();
        if (!$this->checkAuthorization($user, (int)$masjidId)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage prayer timings for this Masjid.',
            ], 403);
        }

        $query = MasjidPrayerTiming::where('masjid_id', $masjidId);

        if ($request->filled('date')) {
            $query->where('date', $request->input('date'));
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
        } elseif ($request->filled('dates') && is_array($request->input('dates'))) {
            $query->whereIn('date', $request->input('dates'));
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a date, dates array, or start_date and end_date to delete.',
            ], 422);
        }

        $deletedCount = $query->delete();

        $this->notifyConnectedUsersTimetableSync((int)$masjidId);

        return response()->json([
            'success' => true,
            'message' => 'Prayer timings deleted for specified date(s).',
            'data'    => [
                'deleted_count' => $deletedCount,
            ],
        ]);
    }

    /**
     * 3.4 Upload Custom Adhan Audio for Masjid
     * Route: POST /api/masjid/{masjid_id}/adhan-audio
     */
    public function uploadAdhanAudio(Request $request, $masjidId)
    {
        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found.',
            ], 404);
        }

        $user = $request->user();
        if (!$this->checkAuthorization($user, (int)$masjidId)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to upload Adhan audio for this Masjid.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'adhan_audio' => 'required|file|mimes:mp3,wav,ogg,aac,m4a|max:10240',
        ], [
            'adhan_audio.required' => 'Please select an audio file to upload.',
            'adhan_audio.mimes'    => 'Audio file must be an MP3, WAV, OGG, AAC, or M4A format.',
            'adhan_audio.max'      => 'Audio file size cannot exceed 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if ($masjid->adhan_audio) {
            Storage::disk('public')->delete($masjid->adhan_audio);
        }

        $path = $request->file('adhan_audio')->store('masjids/adhan', 'public');
        $masjid->update(['adhan_audio' => $path]);

        $this->notifyConnectedUsersTimetableSync((int)$masjidId);

        return response()->json([
            'success' => true,
            'message' => 'Custom Adhan audio uploaded successfully.',
            'data'    => [
                'masjid_id'       => (int)$masjidId,
                'adhan_audio_url' => $masjid->adhan_audio_url,
            ],
        ]);
    }

    /**
     * 3.5 Delete Custom Adhan Audio for Masjid (Revert to default)
     * Route: DELETE /api/masjid/{masjid_id}/adhan-audio
     */
    public function deleteAdhanAudio(Request $request, $masjidId)
    {
        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found.',
            ], 404);
        }

        $user = $request->user();
        if (!$this->checkAuthorization($user, (int)$masjidId)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to modify Adhan audio for this Masjid.',
            ], 403);
        }

        if ($masjid->adhan_audio) {
            Storage::disk('public')->delete($masjid->adhan_audio);
        }
        $masjid->update([
            'adhan_audio'    => null,
            'adhan_audio_id' => null,
        ]);

        $this->notifyConnectedUsersTimetableSync((int)$masjidId);

        return response()->json([
            'success' => true,
            'message' => 'Custom Adhan audio removed. Reverted to standard Adhan.',
            'data'    => [
                'masjid_id'       => (int)$masjidId,
                'has_custom_adhan'=> false,
                'adhan_audio_id'  => null,
                'adhan_audio_url' => null,
                'adhan_audio'     => null,
            ],
        ]);
    }

    /**
     * 3.6 Get Available Admin-Uploaded Adhan Audios
     * Route: GET /api/adhan-audios
     */
    public function getAvailableAdhanAudios()
    {
        $audios = AdhanAudio::where('status', 'active')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $audios,
        ]);
    }

    /**
     * 3.7 Set Adhan Audio for Masjid from Admin Library
     * Route: POST /api/masjid/{masjid_id}/set-adhan-audio
     */
    public function setAdhanAudio(Request $request, $masjidId)
    {
        $masjid = Masjid::find($masjidId);
        if (!$masjid) {
            return response()->json([
                'success' => false,
                'message' => 'Masjid not found.',
            ], 404);
        }

        $user = $request->user();
        if (!$this->checkAuthorization($user, (int)$masjidId)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to configure Adhan audio for this Masjid.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'adhan_audio_id' => 'required|exists:adhan_audios,id',
        ], [
            'adhan_audio_id.required' => 'Please select an Adhan audio.',
            'adhan_audio_id.exists'   => 'The selected Adhan audio does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $adhanAudio = AdhanAudio::where('id', $request->adhan_audio_id)
            ->where('status', 'active')
            ->first();

        if (!$adhanAudio) {
            return response()->json([
                'success' => false,
                'message' => 'The selected Adhan audio is not active.',
            ], 422);
        }

        // Clean up direct legacy file if any
        if ($masjid->adhan_audio) {
            Storage::disk('public')->delete($masjid->adhan_audio);
        }

        $masjid->update([
            'adhan_audio_id' => $adhanAudio->id,
            'adhan_audio'    => null,
        ]);

        $this->notifyConnectedUsersTimetableSync((int)$masjidId);

        return response()->json([
            'success' => true,
            'message' => 'Adhan sound set successfully.',
            'data'    => [
                'masjid_id'       => (int)$masjidId,
                'has_custom_adhan'=> true,
                'adhan_audio_id'  => $adhanAudio->id,
                'adhan_audio_url' => $adhanAudio->audio_url,
                'adhan_audio'     => [
                    'id'        => $adhanAudio->id,
                    'title'     => $adhanAudio->title,
                    'reciter'   => $adhanAudio->reciter,
                    'duration'  => $adhanAudio->duration,
                    'audio_url' => $adhanAudio->audio_url,
                ],
            ],
        ]);
    }
}

