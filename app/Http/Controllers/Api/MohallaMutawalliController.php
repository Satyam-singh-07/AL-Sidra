<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\Mohalla;
use App\Models\MohallaMutawalli;
use Illuminate\Http\Request;

class MohallaMutawalliController extends Controller
{
    /**
     * Assign user as Mohalla Sub-Admin via mohalla_id OR assigned_mohalla string
     */
    public function assign(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
            'user_id' => 'required|exists:users,id',
            'mohalla_id' => 'nullable|exists:mohallas,id',
            'assigned_mohalla' => 'required_without:mohalla_id|string|max:255',
        ]);

        if (!$request->filled('masjid_id') && !$request->filled('madarsa_id')) {
            return response()->json(['message' => 'Please provide either masjid_id or madarsa_id.'], 422);
        }

        $user = $request->user();

        // 1. Resolve Mohalla Name & ID
        $mohallaId = $request->mohalla_id;
        $mohallaName = $request->assigned_mohalla;

        if ($mohallaId) {
            $mohallaRecord = Mohalla::findOrFail($mohallaId);
            $mohallaName = $mohallaRecord->name;
        } else {
            $mohallaRecord = Mohalla::firstOrCreate(
                [
                    'masjid_id' => $request->masjid_id,
                    'madarsa_id' => $request->madarsa_id,
                    'name' => trim($mohallaName),
                ],
                ['status' => 'active']
            );
            $mohallaId = $mohallaRecord->id;
        }

        // 2. Authorization & Persistence
        if ($request->filled('masjid_id')) {
            $masjid = Masjid::findOrFail($request->masjid_id);
            $isMutvalli = ($masjid->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($user->memberProfile && ($user->memberProfile->masjid_id == $masjid->id || ($user->memberProfile->place_type === 'masjid' && $user->memberProfile->place_id == $masjid->id)));

            if (!$isMutvalli) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $assignment = MohallaMutawalli::updateOrCreate(
                ['masjid_id' => $masjid->id, 'user_id' => $request->user_id],
                [
                    'mohalla_id' => $mohallaId,
                    'assigned_mohalla' => $mohallaName,
                ]
            );
        } else {
            $madarsa = Madarsa::findOrFail($request->madarsa_id);
            $isMutvalli = ($madarsa->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($user->memberProfile && ($user->memberProfile->madarsa_id == $madarsa->id || ($user->memberProfile->place_type === 'madarsa' && $user->memberProfile->place_id == $madarsa->id)));

            if (!$isMutvalli) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $assignment = MohallaMutawalli::updateOrCreate(
                ['madarsa_id' => $madarsa->id, 'user_id' => $request->user_id],
                [
                    'mohalla_id' => $mohallaId,
                    'assigned_mohalla' => $mohallaName,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Mohalla Sub-Admin assigned successfully.',
            'data' => $assignment->load(['user:id,name,phone', 'mohallaRecord']),
        ]);
    }

    /**
     * List Mohalla Mutawallis for a Masjid or Madarsa
     */
    public function index(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
        ]);

        $query = MohallaMutawalli::with(['user:id,name,phone', 'mohallaRecord']);

        if ($request->filled('masjid_id')) {
            $query->where('masjid_id', $request->masjid_id);
        } elseif ($request->filled('madarsa_id')) {
            $query->where('madarsa_id', $request->madarsa_id);
        }

        $list = $query->get();

        return response()->json([
            'success' => true,
            'data' => $list,
        ]);
    }

    /**
     * Remove Mohalla Mutawalli assignment
     */
    public function destroy(Request $request, $id)
    {
        $assignment = MohallaMutawalli::findOrFail($id);
        $user = $request->user();

        $isOwner = false;
        if ($assignment->masjid_id) {
            $isOwner = ($assignment->masjid->user_id === $user->id) || $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists();
        } elseif ($assignment->madarsa_id) {
            $isOwner = ($assignment->madarsa->user_id === $user->id) || $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists();
        }

        if (!$isOwner) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment removed successfully.',
        ]);
    }
}
