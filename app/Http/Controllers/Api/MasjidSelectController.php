<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonationCampaign;
use App\Models\DonationLedger;
use App\Models\Madarsa;
use App\Models\Masjid;
use App\Models\MohallaMutawalli;
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
     * Select / Link User to Masjid or Madarsa and Mohalla (Become a Donor)
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
                    'mohalla' => $request->mohalla,
                    'unit_count' => 1.00,
                    'calculated_amount' => $calcAmount,
                    'paid_amount' => 0.00,
                    'balance' => $calcAmount,
                    'payment_status' => 'unpaid',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'You are now linked as a Donor to this place.',
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
        $user = $request->user()->load(['selectedMasjid', 'selectedMadarsa', 'memberProfile.masjid', 'memberProfile.madarsa', 'roles']);

        $masjid = null;
        $madarsa = null;

        // 1. Check if user is owner/Mutvalli of a Masjid or Madarsa
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

        // Determine Effective User Role for Management
        $hasMutvalliRole = $user->roles()->whereIn('slug', ['mutvalli', 'mutawalli', 'admin', 'super_admin'])->exists();
        $isCategoryMutvalli = false;
        if ($user->memberProfile && $user->memberProfile->category) {
            $catName = strtolower($user->memberProfile->category->name ?? '');
            if (str_contains($catName, 'mutvalli') || str_contains($catName, 'mutawalli') || str_contains($catName, 'admin')) {
                $isCategoryMutvalli = true;
            }
        }

        $isPlaceOwner = false;
        if ($masjid && $masjid->user_id === $user->id) {
            $isPlaceOwner = true;
        } elseif ($madarsa && $madarsa->user_id === $user->id) {
            $isPlaceOwner = true;
        }

        $isMutvalli = ($isPlaceOwner || $hasMutvalliRole || $isCategoryMutvalli);

        $isMohallaMutvalli = false;
        if ($masjid) {
            $isMohallaMutvalli = MohallaMutawalli::where('masjid_id', $masjid->id)->where('user_id', $user->id)->exists();
        } elseif ($madarsa) {
            $isMohallaMutvalli = MohallaMutawalli::where('madarsa_id', $madarsa->id)->where('user_id', $user->id)->exists();
        }

        $effectiveRole = 'donor';
        if ($isMutvalli) {
            $effectiveRole = 'mutvalli';
        } elseif ($isMohallaMutvalli) {
            $effectiveRole = 'mohalla_mutvalli';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'masjid' => $masjid,
                'madarsa' => $madarsa,
                'mohalla' => $user->mohalla,
                'is_linked' => ($masjid !== null || $madarsa !== null),
                'is_mutvalli' => $isMutvalli,
                'is_mohalla_mutvalli' => $isMohallaMutvalli,
                'user_role' => $effectiveRole,
            ],
        ]);
    }
}
