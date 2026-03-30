<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMuqquirRequest;
use App\Models\MuqquirProfile;
use App\Models\MuqquirVideo;
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
}
