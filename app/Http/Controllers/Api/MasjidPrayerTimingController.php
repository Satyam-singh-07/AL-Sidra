<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Masjid;
use App\Models\MasjidPrayerTiming;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        if ($timing) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'masjid_id'            => (int)$masjidId,
                    'date'                 => $todayDate,
                    'has_custom_schedule' => true,
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
                'date'                 => $todayDate,
                'has_custom_schedule' => false,
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

        return response()->json([
            'success' => true,
            'message' => 'Prayer timings deleted for specified date(s).',
            'data'    => [
                'deleted_count' => $deletedCount,
            ],
        ]);
    }
}

