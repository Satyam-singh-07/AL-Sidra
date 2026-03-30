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

        // Filter by date availability
        if ($request->filled('date')) {
            $date = $request->date;
            $query->whereHas('availabilities', function($q) use ($date) {
                $q->where('available_date', $date)
                  ->where('status', 'available');
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
     * Get specific Muqquir details with availability
     */
    public function showMuqquir(int $id): JsonResponse
    {
        $muqquir = MuqquirProfile::with([
            'user:id,name,phone,email,address,latitude,longitude',
            'videos',
            'availabilities' => function($q) {
                $q->where('available_date', '>=', now()->toDateString())
                  ->orderBy('available_date');
            }
        ])
        ->where('status', 'approved')
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Muqquir details fetched successfully',
            'data' => $muqquir
        ]);
    }

    /**
     * Update available dates for the Muqquir
     */
    public function updateAvailability(UpdateMuqquirAvailabilityRequest $request): JsonResponse
    {
        $profile = auth()->user()->muqquirProfile;
        $requestedDates = array_unique($request->dates);

        try {
            DB::beginTransaction();

            // 1. Delete dates that are NOT in the requested list AND NOT booked
            MuqquirAvailability::where('muqquir_profile_id', $profile->id)
                ->whereNotIn('available_date', $requestedDates)
                ->where('status', 'available')
                ->delete();

            // 2. Identify dates to add (those that don't exist yet)
            $existingDates = MuqquirAvailability::where('muqquir_profile_id', $profile->id)
                ->pluck('available_date')
                ->map(fn($date) => $date->format('Y-m-d'))
                ->toArray();

            $newDates = array_diff($requestedDates, $existingDates);

            foreach ($newDates as $date) {
                MuqquirAvailability::create([
                    'muqquir_profile_id' => $profile->id,
                    'available_date' => $date,
                    'status' => 'available'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Availability updated successfully',
                'data' => $profile->load('availabilities')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update availability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Muqquir's current availability
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

        return response()->json([
            'success' => true,
            'data' => $availabilities
        ]);
    }
}
