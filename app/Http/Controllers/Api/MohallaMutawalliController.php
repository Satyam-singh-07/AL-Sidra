<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\Mohalla;
use App\Models\MohallaMutawalli;
use App\Models\Role;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MohallaMutawalliController extends Controller
{
    protected FirebaseNotificationService $firebase;

    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Mutvalli: Fetch List of Donors linked to a Masjid or Madarsa
     */
    public function getMasjidDonors(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
        ]);

        if (!$request->filled('masjid_id') && !$request->filled('madarsa_id')) {
            return response()->json(['message' => 'Please provide masjid_id or madarsa_id.'], 422);
        }

        $user = $request->user();

        // Access Control: Logged-in user must be Mutvalli of the place
        if ($request->filled('masjid_id')) {
            $masjid = Masjid::findOrFail($request->masjid_id);
            $isMutvalli = ($masjid->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($user->memberProfile && ($user->memberProfile->masjid_id == $masjid->id || ($user->memberProfile->place_type === 'masjid' && $user->memberProfile->place_id == $masjid->id)));

            if (!$isMutvalli) {
                return response()->json(['message' => 'Unauthorized. Only the Mutvalli can view the donor pool.'], 403);
            }
        } else {
            $madarsa = Madarsa::findOrFail($request->madarsa_id);
            $isMutvalli = ($madarsa->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($user->memberProfile && ($user->memberProfile->madarsa_id == $madarsa->id || ($user->memberProfile->place_type === 'madarsa' && $user->memberProfile->place_id == $madarsa->id)));

            if (!$isMutvalli) {
                return response()->json(['message' => 'Unauthorized. Only the Admin can view the donor pool.'], 403);
            }
        }

        // Fetch all users linked to this place as donors or members
        $donors = User::query()
            ->when($request->filled('masjid_id'), fn($q) => $q->where('selected_masjid_id', $request->masjid_id))
            ->when($request->filled('madarsa_id'), fn($q) => $q->where('selected_madarsa_id', $request->madarsa_id))
            ->select('id', 'name', 'phone', 'email', 'mohalla', 'mohalla_id', 'role', 'created_at')
            ->orderBy('name')
            ->get()
            ->map(function ($donor) {
                return [
                    'id' => $donor->id,
                    'name' => $donor->name,
                    'phone' => $donor->phone,
                    'email' => $donor->email,
                    'mohalla' => $donor->mohalla,
                    'mohalla_id' => $donor->mohalla_id,
                    'role' => $donor->role ?: 'donor',
                    'created_at' => $donor->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $donors,
        ]);
    }

    /**
     * Elevate Donor to Mohalla Mutvalli (Sub-Admin)
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
        $placeName = '';
        if ($request->filled('masjid_id')) {
            $masjid = Masjid::findOrFail($request->masjid_id);
            $isMutvalli = ($masjid->user_id === $user->id) ||
                $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin'])->exists() ||
                ($user->memberProfile && ($user->memberProfile->masjid_id == $masjid->id || ($user->memberProfile->place_type === 'masjid' && $user->memberProfile->place_id == $masjid->id)));

            if (!$isMutvalli) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $placeName = $masjid->name;

            $assignment = MohallaMutawalli::updateOrCreate(
                ['masjid_id' => $masjid->id, 'user_id' => $request->user_id],
                [
                    'mohalla_id' => $mohallaId,
                    'assigned_mohalla' => $mohallaName,
                    'status' => 'active',
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

            $placeName = $madarsa->name;

            $assignment = MohallaMutawalli::updateOrCreate(
                ['madarsa_id' => $madarsa->id, 'user_id' => $request->user_id],
                [
                    'mohalla_id' => $mohallaId,
                    'assigned_mohalla' => $mohallaName,
                    'status' => 'active',
                ]
            );
        }

        // 3. Elevate Target User Role
        $targetUser = User::find($request->user_id);
        if ($targetUser) {
            $targetUser->update([
                'role' => 'mohalla_mutawalli',
                'mohalla_id' => $mohallaId,
                'mohalla' => $mohallaName,
                'selected_masjid_id' => $request->masjid_id ?: $targetUser->selected_masjid_id,
                'selected_madarsa_id' => $request->madarsa_id ?: $targetUser->selected_madarsa_id,
            ]);

            try {
                $subRole = Role::firstOrCreate(['slug' => 'mohalla_mutawalli'], ['name' => 'Mohalla Mutawalli']);
                if (!$targetUser->roles()->where('roles.id', $subRole->id)->exists()) {
                    $targetUser->roles()->attach($subRole->id);
                }
            } catch (\Throwable $e) {
                // Ignore pivot error if roles table not populated
            }

            // 4. Send FCM Push Notification to the target user
            try {
                $title = "Sub-Admin Appointment";
                $body = "You have been appointed as Mohalla Mutvalli for {$mohallaName} at {$placeName}!";
                $data = [
                    'type' => 'sub_admin_appointed',
                    'masjid_id' => (string)($request->masjid_id ?? ''),
                    'madarsa_id' => (string)($request->madarsa_id ?? ''),
                    'mohalla' => (string)$mohallaName,
                    'mohalla_id' => (string)$mohallaId,
                    'role' => 'mohalla_mutawalli',
                ];

                $this->firebase->sendToUser($targetUser, $title, $body, $data);
            } catch (\Throwable $fcmError) {
                Log::error("FCM Error on Sub-Admin appointment: " . $fcmError->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Donor successfully elevated to Mohalla Mutvalli!',
            'data' => $assignment->load(['user:id,name,phone,email,role', 'mohallaRecord']),
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

        $query = MohallaMutawalli::with(['user:id,name,phone,email,role', 'mohallaRecord']);

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
     * Remove/Revoke Mohalla Mutawalli assignment
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

        // Revert user role back to 'donor'
        if ($assignment->user) {
            $assignment->user->update(['role' => 'donor']);
            try {
                $subRole = Role::where('slug', 'mohalla_mutawalli')->first();
                if ($subRole) {
                    $assignment->user->roles()->detach($subRole->id);
                }
            } catch (\Throwable $e) {
                // Ignore pivot error
            }
        }

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment removed successfully.',
        ]);
    }
}
