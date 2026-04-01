<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMuqquirRequest;
use App\Http\Requests\UpdateMuqquirAvailabilityRequest;
use App\Models\MuqquirProfile;
use App\Models\MuqquirVideo;
use App\Models\MuqquirAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MuqquirController extends Controller
{
    /**
     * Apply to become a muqquir
     */
    public function apply(StoreMuqquirRequest $request): JsonResponse
    {
        $userId = auth()->id();

        // Check if user already has a pending or approved registration
        $existingRegistration = MuqquirProfile::where('user_id', $userId)->first();

        if ($existingRegistration) {
            if ($existingRegistration->status !== 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active registration. Status: ' . $existingRegistration->status,
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            if ($existingRegistration) {
                $existingRegistration->update([
                    'description' => $request->description,
                    'account_no' => $request->account_no,
                    'ifsc_code' => $request->ifsc_code,
                    'travel_fee' => $request->travel_fee,
                    'status' => 'pending'
                ]);
                
                // Delete old videos if any (if re-applying)
                foreach ($existingRegistration->videos as $video) {
                    Storage::disk('public')->delete($video->video_path);
                    $video->delete();
                }

                $profile = $existingRegistration;
            } else {
                $profile = MuqquirProfile::create([
                    'user_id' => $userId,
                    'description' => $request->description,
                    'account_no' => $request->account_no,
                    'ifsc_code' => $request->ifsc_code,
                    'travel_fee' => $request->travel_fee,
                    'status' => 'pending'
                ]);
            }

            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $videoFile) {
                    $path = $videoFile->store('muqquir/videos', 'public');
                    MuqquirVideo::create([
                        'muqquir_profile_id' => $profile->id,
                        'video_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Muqquir application submitted successfully',
                'data' => $profile->load('videos')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all approved Muqquirs with optional filtering
     */
    public function getMuqquirs(Request $request): JsonResponse
    {
        $query = MuqquirProfile::with(['user:id,name,phone,email,address', 'videos'])
            ->where('status', 'approved');

        // Filter by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by date availability (Muqquir is NOT unavailable or booked on this date)
        if ($request->filled('date')) {
            $date = $request->date;
            $query->whereDoesntHave('availabilities', function($q) use ($date) {
                $q->where('available_date', $date)
                  ->whereIn('status', ['unavailable', 'booked']);
            });
        }

        $muqquirs = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Approved Muqquirs fetched successfully',
            'data' => $muqquirs->items(),
            'pagination' => [
                'total' => $muqquirs->total(),
                'per_page' => $muqquirs->perPage(),
                'current_page' => $muqquirs->currentPage(),
                'last_page' => $muqquirs->lastPage(),
            ]
        ]);
    }

    /**
     * Get specific Muqquir details with booked/unavailable dates
     */
    public function showMuqquir(int $id): JsonResponse
    {
        $muqquir = MuqquirProfile::with([
            'user:id,name,phone,email,address,latitude,longitude',
            'videos',
            'availabilities' => function($q) {
                $q->where('available_date', '>=', now()->toDateString())
                  ->whereIn('status', ['booked', 'unavailable'])
                  ->orderBy('available_date');
            }
        ])
        ->where('status', 'approved')
        ->findOrFail($id);

        $unavailableDates = $muqquir->availabilities->where('status', 'unavailable')
            ->pluck('available_date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->values();

        $bookedDates = $muqquir->availabilities->where('status', 'booked')
            ->pluck('available_date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->values();

        // Convert to array to merge custom fields
        $data = $muqquir->toArray();
        $data['unavailable_dates'] = $unavailableDates;
        $data['booked_dates'] = $bookedDates;
        unset($data['availabilities']); // Remove the raw relationship data

        return response()->json([
            'success' => true,
            'message' => 'Muqquir details fetched successfully',
            'data' => $data
        ]);
    }

    /**
     * Update unavailable dates for the Muqquir
     */
    public function updateAvailability(UpdateMuqquirAvailabilityRequest $request): JsonResponse
    {
        $profile = auth()->user()->muqquirProfile;
        $validated = $request->validated();
        $unavailableDates = array_unique($validated['unavailable_dates'] ?? []);

        try {
            DB::beginTransaction();

            // 1. Delete existing future unavailable dates (Preserve booked ones)
            MuqquirAvailability::where('muqquir_profile_id', $profile->id)
                ->where('available_date', '>=', now()->toDateString())
                ->where('status', 'unavailable')
                ->delete();

            // 2. Add new unavailable dates (only if not already booked)
            foreach ($unavailableDates as $date) {
                $existingBooked = MuqquirAvailability::where('muqquir_profile_id', $profile->id)
                    ->where('available_date', $date)
                    ->where('status', 'booked')
                    ->exists();

                if (!$existingBooked) {
                    MuqquirAvailability::updateOrCreate(
                        [
                            'muqquir_profile_id' => $profile->id,
                            'available_date' => $date
                        ],
                        [
                            'status' => 'unavailable'
                        ]
                    );
                }
            }

            DB::commit();

            return $this->getAvailability();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update unavailability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Muqquir's current availability (unavailable and booked dates)
     */
    public function getAvailability(): JsonResponse
    {
        $profile = auth()->user()->muqquirProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Muqquir profile not found'
            ], 404);
        }

        $availabilities = $profile->availabilities()
            ->where('available_date', '>=', now()->toDateString())
            ->orderBy('available_date')
            ->get();

        $unavailableDates = $availabilities->where('status', 'unavailable')
            ->pluck('available_date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->values();

        $bookedDates = $availabilities->where('status', 'booked')
            ->pluck('available_date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'unavailable_dates' => $unavailableDates,
                'booked_dates' => $bookedDates
            ]
        ]);
    }
}
