<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\MasjidBroadcastSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BroadcastController extends Controller
{
    /**
     * Toggle Public Broadcast Mode for X Days
     */
    public function togglePublicMode(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
            'is_public' => 'required|boolean',
            'days' => 'required_if:is_public,true|nullable|integer|min:1|max:30',
        ]);

        if (!$request->filled('masjid_id') && !$request->filled('madarsa_id')) {
            return response()->json(['message' => 'Please provide either masjid_id or madarsa_id.'], 422);
        }

        $user = $request->user();

        if ($request->filled('masjid_id')) {
            $masjid = Masjid::findOrFail($request->masjid_id);
            if ($masjid->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            $setting = MasjidBroadcastSetting::firstOrNew(['masjid_id' => $masjid->id]);
        } else {
            $madarsa = Madarsa::findOrFail($request->madarsa_id);
            if ($madarsa->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            $setting = MasjidBroadcastSetting::firstOrNew(['madarsa_id' => $madarsa->id]);
        }

        $setting->is_public = (bool)$request->is_public;
        $setting->public_until = $request->is_public ? Carbon::now()->addDays((int)$request->days) : null;
        $setting->save();

        $message = $request->is_public
            ? "Collection list is now public for {$request->days} days."
            : "Collection list is now private.";

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'is_public' => $setting->is_public,
                'public_until' => $setting->public_until,
                'days_remaining' => $setting->days_remaining,
            ],
        ]);
    }

    /**
     * Get Current Broadcast Status
     */
    public function status(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
        ]);

        $setting = null;
        if ($request->filled('masjid_id')) {
            $setting = MasjidBroadcastSetting::where('masjid_id', $request->masjid_id)->first();
        } elseif ($request->filled('madarsa_id')) {
            $setting = MasjidBroadcastSetting::where('madarsa_id', $request->madarsa_id)->first();
        }

        $isPublic = $setting ? $setting->isCurrentlyPublic() : false;

        return response()->json([
            'success' => true,
            'is_public_broadcast' => $isPublic,
            'public_until' => $setting && $isPublic ? $setting->public_until : null,
            'days_remaining' => $setting && $isPublic ? $setting->days_remaining : 0,
        ]);
    }
}
