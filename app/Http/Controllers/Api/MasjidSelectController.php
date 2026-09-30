<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Madarsa;
use App\Models\Masjid;
use Illuminate\Http\Request;

class MasjidSelectController extends Controller
{
    /**
     * Search Masjids or Madarsas by Name or Unique ID
     */
    public function search(Request $request)
    {
        $search = $request->query('query');
        $type = $request->query('type', 'masjid'); // 'masjid' or 'madarsa'

        if ($type === 'madarsa') {
            $places = Madarsa::select('id', 'name', 'unique_id', 'address')
                ->where('status', 'active')
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('unique_id', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                })
                ->limit(20)
                ->get();
        } else {
            $places = Masjid::select('id', 'name', 'unique_id', 'address')
                ->where('status', 'active')
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('unique_id', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                })
                ->limit(20)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $places,
        ]);
    }

    /**
     * Select / Link User to Masjid or Madarsa and Mohalla
     */
    public function selectMasjid(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
            'mohalla' => 'required|string|max:255',
        ]);

        if (!$request->filled('masjid_id') && !$request->filled('madarsa_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide either masjid_id or madarsa_id.',
            ], 422);
        }

        $user = $request->user();
        $user->update([
            'selected_masjid_id' => $request->masjid_id,
            'selected_madarsa_id' => $request->madarsa_id,
            'mohalla' => $request->mohalla,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Masjid and Mohalla selected successfully.',
            'data' => [
                'user_id' => $user->id,
                'selected_masjid_id' => $user->selected_masjid_id,
                'selected_madarsa_id' => $user->selected_madarsa_id,
                'mohalla' => $user->mohalla,
            ],
        ]);
    }

    /**
     * Get Current User's Selected or Registered Place & Mohalla
     */
    public function myPlace(Request $request)
    {
        $user = $request->user()->load(['selectedMasjid', 'selectedMadarsa', 'memberProfile.masjid', 'memberProfile.madarsa']);

        $masjid = null;
        $madarsa = null;

        // 1. Check if user is owner/Mutawalli of a Masjid or Madarsa
        $ownedMasjid = Masjid::where('user_id', $user->id)->first();
        if ($ownedMasjid) {
            $masjid = $ownedMasjid;
        }

        $ownedMadarsa = Madarsa::where('user_id', $user->id)->first();
        if ($ownedMadarsa) {
            $madarsa = $ownedMadarsa;
        }

        // 2. Check Member Profile (Maulana / Imam / Qari / Staff)
        if (!$masjid && $user->memberProfile) {
            if ($user->memberProfile->masjid_id) {
                $masjid = $user->memberProfile->masjid;
            } elseif ($user->memberProfile->place_type === 'masjid' && $user->memberProfile->place_id) {
                $masjid = Masjid::find($user->memberProfile->place_id);
            }
        }

        if (!$madarsa && $user->memberProfile) {
            if ($user->memberProfile->madarsa_id) {
                $madarsa = $user->memberProfile->madarsa;
            } elseif ($user->memberProfile->place_type === 'madarsa' && $user->memberProfile->place_id) {
                $madarsa = Madarsa::find($user->memberProfile->place_id);
            }
        }

        // 3. Check Selected Place (Donor / General User)
        if (!$masjid && $user->selectedMasjid) {
            $masjid = $user->selectedMasjid;
        }

        if (!$madarsa && $user->selectedMadarsa) {
            $madarsa = $user->selectedMadarsa;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'masjid' => $masjid,
                'madarsa' => $madarsa,
                'mohalla' => $user->mohalla,
                'is_linked' => ($masjid !== null || $madarsa !== null),
            ],
        ]);
    }
}
