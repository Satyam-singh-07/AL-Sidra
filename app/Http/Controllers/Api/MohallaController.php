<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\Mohalla;
use App\Models\User;
use Illuminate\Http\Request;

class MohallaController extends Controller
{
    /**
     * Get Mohalla objects & names for a given Masjid or Madarsa
     */
    public function index(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
        ]);

        $masjidId = $request->masjid_id;
        $madarsaId = $request->madarsa_id;

        if (!$masjidId && !$madarsaId) {
            return response()->json(['message' => 'Please provide masjid_id or madarsa_id.'], 422);
        }

        // 1. Aggregate distinct string names from users for fallback
        $userMohallaNames = User::query()
            ->when($masjidId, fn($q) => $q->where('selected_masjid_id', $masjidId))
            ->when($madarsaId, fn($q) => $q->where('selected_madarsa_id', $madarsaId))
            ->whereNotNull('mohalla')
            ->where('mohalla', '!=', '')
            ->distinct()
            ->pluck('mohalla')
            ->toArray();

        // Auto-register missing user mohallas into `mohallas` table for clean ID linkage
        foreach ($userMohallaNames as $mName) {
            $trimmed = trim($mName);
            if ($trimmed !== '') {
                Mohalla::firstOrCreate(
                    [
                        'masjid_id' => $masjidId,
                        'madarsa_id' => $madarsaId,
                        'name' => $trimmed,
                    ],
                    ['status' => 'active']
                );
            }
        }

        // Re-fetch clean list of all registered mohallas with IDs
        $mohallasList = Mohalla::query()
            ->when($masjidId, fn($q) => $q->where('masjid_id', $masjidId))
            ->when($madarsaId, fn($q) => $q->where('madarsa_id', $madarsaId))
            ->where('status', 'active')
            ->select('id', 'name', 'status')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $mohallasList,
        ]);
    }

    /**
     * Mutvalli registers a new Mohalla
     */
    public function store(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
            'name' => 'required|string|max:255',
        ]);

        if (!$request->filled('masjid_id') && !$request->filled('madarsa_id')) {
            return response()->json(['message' => 'Please provide masjid_id or madarsa_id.'], 422);
        }

        $user = $request->user();

        // Authorization check
        $isMutvalli = false;
        if ($request->filled('masjid_id')) {
            $masjid = Masjid::findOrFail($request->masjid_id);
            $isMutvalli = ($masjid->user_id === $user->id)
                || $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists()
                || ($user->memberProfile && ($user->memberProfile->masjid_id == $masjid->id || ($user->memberProfile->place_type === 'masjid' && $user->memberProfile->place_id == $masjid->id)));
        } elseif ($request->filled('madarsa_id')) {
            $madarsa = Madarsa::findOrFail($request->madarsa_id);
            $isMutvalli = ($madarsa->user_id === $user->id)
                || $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists()
                || ($user->memberProfile && ($user->memberProfile->madarsa_id == $madarsa->id || ($user->memberProfile->place_type === 'madarsa' && $user->memberProfile->place_id == $madarsa->id)));
        }

        if (!$isMutvalli) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $mohalla = Mohalla::firstOrCreate(
            [
                'masjid_id' => $request->masjid_id,
                'madarsa_id' => $request->madarsa_id,
                'name' => trim($request->name),
            ],
            [
                'status' => 'active',
                'created_by' => $user->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Mohalla registered successfully.',
            'data' => $mohalla,
        ], 201);
    }
}
