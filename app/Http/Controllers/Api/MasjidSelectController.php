<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonationCampaign;
use App\Models\DonationLedger;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\Mohalla;
use App\Models\MohallaMutawalli;
use App\Models\Role;
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
            'mohalla_id' => 'nullable|exists:mohallas,id',
            'mohalla' => 'required_without:mohalla_id|string|max:255',
        ]);

        if (!$request->filled('masjid_id') && !$request->filled('madarsa_id')) {
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
                    'masjid_id' => $request->masjid_id,
                    'madarsa_id' => $request->madarsa_id,
                    'name' => trim($mohallaName),
                ],
                ['status' => 'active']
            );
            $mohallaId = $mohallaRecord->id;
        }

        $user = $request->user();
        $user->update([
            'selected_masjid_id' => $request->masjid_id,
            'selected_madarsa_id' => $request->madarsa_id,
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

        // Auto-create ledger entries for all active campaigns in this place
        $activeCampaigns = DonationCampaign::where('status', 'active')
            ->when($request->filled('masjid_id'), function ($q) use ($request) {
                $q->where('masjid_id', $request->masjid_id);
            })
            ->when($request->filled('madarsa_id'), function ($q) use ($request) {
                $q->where('madarsa_id', $request->madarsa_id);
            })
            ->get();

        foreach ($activeCampaigns as $campaign) {
            // Check target mohalla scoping
            if (!empty($campaign->target_mohalla) && strtolower($campaign->target_mohalla) !== 'all') {
                if ($campaign->target_mohalla !== $mohallaName) {
                    continue;
                }
            }

            $unitRate = (float)($campaign->rate_per_unit ?? 0);
            $calcAmount = match ($campaign->category) {
                'mard', 'zameen', 'nikah' => $unitRate,
                default => 0.00,
            };

            DonationLedger::firstOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'donor_user_id' => $user->id,
                ],
                [
                    'masjid_id' => $request->masjid_id,
                    'madarsa_id' => $request->madarsa_id,
                    'mohalla' => $mohallaName ?? 'General',
                    'unit_count' => 1.00,
                    'calculated_amount' => $calcAmount,
                    'paid_amount' => 0.00,
                    'balance' => $calcAmount,
                    'payment_status' => 'unpaid',
                ]
            );
        }

        $placeType = $request->filled('masjid_id') ? "Masjid #{$request->masjid_id}" : "Madarsa #{$request->madarsa_id}";

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
                'is_sub_admin' => ($subAdminAssignment !== null),
                'sub_admin_details' => $subAdminAssignment,
                'is_linked' => ($masjid !== null || $madarsa !== null),
            ],
        ]);
    }
}
