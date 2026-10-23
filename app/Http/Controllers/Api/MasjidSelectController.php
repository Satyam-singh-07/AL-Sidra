<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonationCampaign;
use App\Models\DonationLedger;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\Mohalla;
use App\Models\DonorRequest;
use App\Models\MohallaMutawalli;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Alias for selectMasjid - Select / Link User to Masjid or Madarsa as Donor
     */
    public function selectPlace(Request $request)
    {
        return $this->selectMasjid($request);
    }

    /**
     * Select / Link User to Masjid or Madarsa and Mohalla (Become a Donor)
     */
    public function selectMasjid(Request $request)
    {
        $request->validate([
            'masjid_id' => 'nullable|exists:masjids,id',
            'madarsa_id' => 'nullable|exists:madarsas,id',
            'place_id' => 'nullable',
            'mohalla_id' => 'nullable|exists:mohallas,id',
            'mohalla' => 'nullable|string|max:255',
        ]);

        $masjidId = $request->masjid_id ?? $request->place_id;
        $madarsaId = $request->madarsa_id;

        if (!$masjidId && !$madarsaId) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide either masjid_id or madarsa_id.',
            ], 422);
        }

        $mohallaId = $request->mohalla_id;
        $mohallaName = $request->mohalla;

        if ($mohallaId) {
            $mohallaRecord = Mohalla::find($mohallaId);
            if ($mohallaRecord) {
                $mohallaName = $mohallaRecord->name;
            }
        } elseif ($mohallaName) {
            $mohallaRecord = Mohalla::firstOrCreate(
                [
                    'masjid_id' => $masjidId,
                    'madarsa_id' => $madarsaId,
                    'name' => trim($mohallaName),
                ],
                ['status' => 'active']
            );
            $mohallaId = $mohallaRecord->id;
        } else {
            $mohallaName = 'General';
        }

        $user = $request->user();
        $user->update([
            'selected_masjid_id' => $masjidId,
            'selected_madarsa_id' => $madarsaId,
            'mohalla_id' => $mohallaId,
            'mohalla' => $mohallaName,
            'role' => $user->role === 'mohalla_mutawalli' ? 'mohalla_mutawalli' : 'donor',
        ]);

        // Ensure donor role in roles table
        try {
            $donorRole = Role::firstOrCreate(['slug' => 'donor'], ['name' => 'Donor']);
            if (!$user->roles()->where('roles.id', $donorRole->id)->exists()) {
                $user->roles()->attach($donorRole->id);
            }
        } catch (\Throwable $e) {
            // Ignore role pivot errors if roles table is not populated
        }

        $placeType = $masjidId ? "Masjid #{$masjidId}" : "Madarsa #{$madarsaId}";

        return response()->json([
            'success' => true,
            'message' => "Successfully registered as Donor for {$placeType}!",
            'data' => [
                'user_id' => $user->id,
                'masjid_id' => $user->selected_masjid_id,
                'madarsa_id' => $user->selected_madarsa_id,
                'mohalla' => $user->mohalla,
                'mohalla_id' => $user->mohalla_id,
                'role' => $user->role ?? 'donor',
            ],
        ]);
    }

    /**
     * Get Current User's Selected or Registered Place & Mohalla
     */
    public function myPlace(Request $request)
    {
        $user = $request->user()->load(['selectedMasjid', 'selectedMadarsa', 'memberProfile.masjid', 'memberProfile.madarsa', 'roles', 'mohallaRecord']);

        $masjid = null;
        $madarsa = null;
        $role = $user->role ?? 'user';

        // 1. Check if user is owner/Mutvalli of a Masjid or Madarsa
        $ownedMasjid = Masjid::where('user_id', $user->id)->first();
        if ($ownedMasjid) {
            $masjid = $ownedMasjid;
            $role = 'mutvalli';
        }

        $ownedMadarsa = Madarsa::where('user_id', $user->id)->first();
        if ($ownedMadarsa) {
            $madarsa = $ownedMadarsa;
            $role = 'mutvalli';
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

        // 3. Check Mohalla Mutawalli (Sub-Admin)
        $subAdminAssignment = MohallaMutawalli::where('user_id', $user->id)
            ->where(function ($q) use ($user, $masjid, $madarsa) {
                if ($masjid) $q->where('masjid_id', $masjid->id);
                elseif ($madarsa) $q->where('madarsa_id', $madarsa->id);
                elseif ($user->selected_masjid_id) $q->where('masjid_id', $user->selected_masjid_id);
                elseif ($user->selected_madarsa_id) $q->where('madarsa_id', $user->selected_madarsa_id);
            })
            ->first();

        if ($subAdminAssignment) {
            $role = 'mohalla_mutawalli';
        }

        // 4. Check Selected Place (Donor / General User)
        if (!$masjid && $user->selectedMasjid) {
            $masjid = $user->selectedMasjid;
            if ($role === 'user') {
                $role = 'donor';
            }
        }

        if (!$madarsa && $user->selectedMadarsa) {
            $madarsa = $user->selectedMadarsa;
            if ($role === 'user') {
                $role = 'donor';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'masjid' => $masjid,
                'madarsa' => $madarsa,
                'mohalla' => $user->mohalla,
                'mohalla_id' => $user->mohalla_id,
                'role' => $role,
                'user_role' => $role,
                'is_mutvalli' => ($role === 'mutvalli'),
                'is_sub_admin' => ($subAdminAssignment !== null),
                'sub_admin_details' => $subAdminAssignment,
                'is_linked' => ($masjid !== null || $madarsa !== null),
            ],
        ]);
    }

    /**
     * Update an active donor's details using their Donor ID (User ID).
     */
    public function updateDonor(Request $request, $id)
    {
        $validated = $request->validate([
            'masjid_id'   => 'nullable|integer',
            'madarsa_id'  => 'nullable|integer',
            'place_id'    => 'nullable|integer',
            'name'        => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'unit_count'  => 'nullable|integer|min:1|max:50',
            'phone'       => 'nullable|string|max:25',
            'mohalla_id'  => 'nullable|integer',
            'mohalla'     => 'nullable|string|max:255',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $user = User::findOrFail($id);
        $masjidId = $validated['masjid_id'] ?? $validated['place_id'] ?? $user->selected_masjid_id;
        $madarsaId = $validated['madarsa_id'] ?? $user->selected_madarsa_id;

        // Resolve Mohalla Name if mohalla_id is provided
        $mohallaName = $validated['mohalla'] ?? null;
        if (!empty($validated['mohalla_id'])) {
            $mohallaRecord = Mohalla::find($validated['mohalla_id']);
            if ($mohallaRecord) {
                $mohallaName = $mohallaRecord->name;
            }
        } elseif ($mohallaName && ($masjidId || $madarsaId)) {
            // Find or create mohalla by name
            $mohallaRecord = Mohalla::firstOrCreate(
                [
                    'masjid_id' => $masjidId,
                    'madarsa_id' => $madarsaId,
                    'name' => trim($mohallaName),
                ],
                ['status' => 'active']
            );
            $validated['mohalla_id'] = $mohallaRecord->id;
        }

        DB::transaction(function () use ($user, $validated, $masjidId, $madarsaId, $mohallaName) {
            // 1. Update the User record
            $user->name = trim($validated['name']);
            if (isset($validated['phone'])) {
                $user->phone = trim($validated['phone']);
            }
            if ($mohallaName !== null) {
                $user->mohalla = $mohallaName;
            }
            if (!empty($validated['mohalla_id'])) {
                $user->mohalla_id = $validated['mohalla_id'];
            }
            if (array_key_exists('father_name', $validated)) {
                $user->father_name = $validated['father_name'] !== null ? trim($validated['father_name']) : null;
            }
            if (isset($validated['unit_count'])) {
                $user->unit_count = (int)$validated['unit_count'];
            }
            if (array_key_exists('notes', $validated)) {
                $user->notes = $validated['notes'] !== null ? trim($validated['notes']) : null;
            }
            if ($masjidId && !$user->selected_masjid_id) {
                $user->selected_masjid_id = $masjidId;
            }
            if ($madarsaId && !$user->selected_madarsa_id) {
                $user->selected_madarsa_id = $madarsaId;
            }
            $user->save();

            // 2. Keep associated DonorRequest records in sync if table exists
            if (DB::getSchemaBuilder()->hasTable('donor_requests')) {
                $reqQuery = DonorRequest::where('user_id', $user->id);
                if ($masjidId) {
                    $reqQuery->where('masjid_id', $masjidId);
                } elseif ($madarsaId) {
                    $reqQuery->where('madarsa_id', $madarsaId);
                }
                $donorReqUpdate = array_filter([
                    'applicant_name' => $user->name,
                    'father_name'    => $user->father_name,
                    'unit_count'     => $user->unit_count,
                    'phone'          => $user->phone,
                    'mohalla'        => $user->mohalla,
                    'mohalla_id'     => $user->mohalla_id,
                    'notes'          => $user->notes,
                ], fn($val) => $val !== null);

                if (!empty($donorReqUpdate)) {
                    $reqQuery->update($donorReqUpdate);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Donor details updated successfully.',
            'data'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'father_name' => $user->father_name,
                'unit_count'  => $user->unit_count ?? 1,
                'phone'       => $user->phone,
                'email'       => $user->email,
                'mohalla'     => $user->mohalla,
                'mohalla_id'  => $user->mohalla_id,
                'role'        => $user->role ?: 'donor',
                'notes'       => $user->notes,
            ],
        ], 200);
    }
}
