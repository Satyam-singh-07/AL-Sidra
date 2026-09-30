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
     * Get Current User's Selected Place & Mohalla
     */
    public function myPlace(Request $request)
    {
        $user = $request->user()->load(['selectedMasjid', 'selectedMadarsa']);

        return response()->json([
            'success' => true,
            'data' => [
                'masjid' => $user->selectedMasjid,
                'madarsa' => $user->selectedMadarsa,
                'mohalla' => $user->mohalla,
            ],
        ]);
    }
}
