<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\MohallaMutawalli;
use Illuminate\Http\Request;

class MohallaMutawalliController extends Controller
{
    /**
     * Assign user as Mohalla Mutvalli
     */
    public function assign(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
            'user_id' => 'required|exists:users,id',
            'assigned_mohalla' => 'required|string|max:255',
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

            $assignment = MohallaMutawalli::updateOrCreate(
                ['masjid_id' => $masjid->id, 'user_id' => $request->user_id],
                ['assigned_mohalla' => $request->assigned_mohalla]
            );
        } else {
            $madarsa = Madarsa::findOrFail($request->madarsa_id);
            if ($madarsa->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $assignment = MohallaMutawalli::updateOrCreate(
                ['madarsa_id' => $madarsa->id, 'user_id' => $request->user_id],
                ['assigned_mohalla' => $request->assigned_mohalla]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Mohalla Sub-Admin assigned successfully.',
            'data' => $assignment->load('user:id,name,phone'),
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

        $query = MohallaMutawalli::with('user:id,name,phone');

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
            $isOwner = Masjid::where('id', $assignment->masjid_id)->where('user_id', $user->id)->exists();
        } elseif ($assignment->madarsa_id) {
            $isOwner = Madarsa::where('id', $assignment->madarsa_id)->where('user_id', $user->id)->exists();
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
