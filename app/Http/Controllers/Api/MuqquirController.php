<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMuqquirRequest;
use App\Http\Requests\UpdateMuqquirAvailabilityRequest;
use App\Models\MuqquirProfile;
use App\Models\MuqquirVideo;
use App\Models\MuqquirAvailability;
use Illuminate\Http\JsonResponse;
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
